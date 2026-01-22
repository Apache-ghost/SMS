<?php
error_reporting(0); // Suppress all errors/warnings
ini_set('display_errors', 0);

session_start();
require 'config.php';

// Set JSON header for all responses
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get grade statistics
    if ($action == 'get_grade_stats') {
        $stats = [];
        
        // Check if marks table exists
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'marks'");
        
        if (mysqli_num_rows($checkTable) == 0) {
            // Return zeros if table doesn't exist
            echo json_encode(['status' => 'success', 'stats' => [
                'total' => 0,
                'published' => 0,
                'pending' => 0,
                'average' => '0%'
            ]]);
            exit();
        }
        
        // Total grades
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM marks");
        $stats['total'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
        
        // Published grades (assuming marks_obtained > 0 means published)
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM marks WHERE marks_obtained > 0");
        $stats['published'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
        
        // Pending grades
        $stats['pending'] = $stats['total'] - $stats['published'];
        
        // Average grade
        $result = mysqli_query($conn, "SELECT AVG((marks_obtained / total_marks) * 100) as avg FROM marks WHERE total_marks > 0");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        $avg = $row ? $row['avg'] : 0;
        $stats['average'] = $avg ? number_format($avg, 1) . '%' : '0%';
        
        echo json_encode(['status' => 'success', 'stats' => $stats]);
    }
    
    // Save grades
    else if ($action == 'save_grades') {
        $class = mysqli_real_escape_string($conn, $_POST['class']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $assessmentType = mysqli_real_escape_string($conn, $_POST['assessment_type']);
        $grades = json_decode($_POST['grades'], true);
        
        if (!$grades || !is_array($grades)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid grades data']);
            exit();
        }
        
        $success = 0;
        $errors = 0;
        
        foreach ($grades as $grade) {
            $studentId = mysqli_real_escape_string($conn, $grade['student_id']);
            $marks = floatval($grade['marks']);
            $total = floatval($grade['total']);
            $remarks = mysqli_real_escape_string($conn, $grade['remarks'] ?? '');
            
            // Calculate percentage and grade
            $percentage = ($marks / $total) * 100;
            
            if ($percentage >= 90) $gradeValue = 'A+';
            elseif ($percentage >= 80) $gradeValue = 'A';
            elseif ($percentage >= 70) $gradeValue = 'B';
            elseif ($percentage >= 60) $gradeValue = 'C';
            elseif ($percentage >= 50) $gradeValue = 'D';
            else $gradeValue = 'F';
            
            // Check if record exists
            $checkSql = "SELECT id FROM marks 
                         WHERE student_id = ? AND subject_code = ? AND exam_type = ?";
            $stmt = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmt, "sss", $studentId, $subject, $assessmentType);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                // Update existing record
                $row = mysqli_fetch_assoc($result);
                $updateSql = "UPDATE marks 
                              SET marks_obtained = ?, total_marks = ?, percentage = ?, 
                                  grade = ?, remarks = ?, updated_at = NOW()
                              WHERE id = ?";
                $stmtUpdate = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmtUpdate, "dddssi", $marks, $total, $percentage, 
                                       $gradeValue, $remarks, $row['id']);
                
                if (mysqli_stmt_execute($stmtUpdate)) {
                    $success++;
                } else {
                    $errors++;
                }
            } else {
                // Insert new record
                $insertSql = "INSERT INTO marks 
                              (student_id, subject_code, exam_type, marks_obtained, total_marks, 
                               percentage, grade, class, remarks, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmtInsert = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($stmtInsert, "sssdddsss", 
                    $studentId, $subject, $assessmentType, $marks, $total, 
                    $percentage, $gradeValue, $class, $remarks);
                
                if (mysqli_stmt_execute($stmtInsert)) {
                    $success++;
                } else {
                    $errors++;
                }
            }
        }
        
        if ($errors > 0) {
            echo json_encode([
                'status' => 'warning', 
                'message' => "Saved $success grades, $errors errors"
            ]);
        } else {
            echo json_encode([
                'status' => 'success', 
                'message' => "Successfully saved $success grades"
            ]);
        }
    }
    
    // Get grades for a student
    else if ($action == 'get_student_grades') {
        $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
        
        $sql = "SELECT m.*, s.subject_name 
                FROM marks m
                LEFT JOIN subjects s ON m.subject_code = s.subject_code
                WHERE m.student_id = ?
                ORDER BY m.created_at DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $grades = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $grades[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'grades' => $grades]);
    }
    
    // Get grades by class
    else if ($action == 'get_class_grades') {
        $class = mysqli_real_escape_string($conn, $_POST['class']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject'] ?? '');
        
        $sql = "SELECT m.*, 
                       CONCAT(st.fname, ' ', st.lname) AS student_name,
                       s.subject_name
                FROM marks m
                LEFT JOIN students st ON m.student_id = st.id
                LEFT JOIN subjects s ON m.subject_code = s.subject_code
                WHERE m.class = ?";
        
        if ($subject) {
            $sql .= " AND m.subject_code = ?";
        }
        
        $sql .= " ORDER BY st.fname ASC, m.created_at DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($subject) {
            mysqli_stmt_bind_param($stmt, "ss", $class, $subject);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $class);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $grades = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $grades[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'grades' => $grades]);
    }
    
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}

mysqli_close($conn);
?>