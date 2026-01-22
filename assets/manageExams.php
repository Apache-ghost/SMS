<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require 'config.php';

// Clean output buffer and set JSON header
while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Get user role from database
$userId = $_SESSION['uid'];
$sql = "SELECT role FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit();
}

$userRole = $user['role'];

// Check if tables exist
$tablesExist = true;
$checkExams = @mysqli_query($conn, "SHOW TABLES LIKE 'exams'");
if (!$checkExams || mysqli_num_rows($checkExams) == 0) {
    $tablesExist = false;
}

// Check if exam_date column exists
$hasExamDate = false;
if ($tablesExist) {
    $checkCol = @mysqli_query($conn, "SHOW COLUMNS FROM exams LIKE 'exam_date'");
    $hasExamDate = $checkCol && mysqli_num_rows($checkCol) > 0;
}

// Admin actions
if ($userRole == 'admin') {
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $action = $_GET['action'] ?? '';
        
        // Get statistics
        if ($action == 'get_stats') {
            if (!$tablesExist) {
                echo json_encode(['status' => 'success', 'stats' => [
                    'total' => 0,
                    'upcoming' => 0,
                    'completed' => 0,
                    'results_published' => 0
                ]]);
                exit();
            }
            
            $stats = [];
            
            $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM exams");
            $stats['total'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            if ($hasExamDate) {
                $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM exams WHERE exam_date >= CURDATE()");
                $stats['upcoming'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
                
                $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM exams WHERE exam_date < CURDATE()");
                $stats['completed'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            } else {
                $stats['upcoming'] = 0;
                $stats['completed'] = $stats['total'];
            }
            
            $checkResults = @mysqli_query($conn, "SHOW TABLES LIKE 'exam_results'");
            if ($checkResults && mysqli_num_rows($checkResults) > 0) {
                $result = @mysqli_query($conn, "SELECT COUNT(DISTINCT exam_id) as count FROM exam_results");
                $stats['results_published'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            } else {
                $stats['results_published'] = 0;
            }
            
            echo json_encode(['status' => 'success', 'stats' => $stats]);
            exit();
        }
        
        // Get all exams
        else if ($action == 'get_exams') {
            if (!$tablesExist) {
                echo json_encode(['status' => 'success', 'exams' => []]);
                exit();
            }
            
            $class = $_GET['class'] ?? '';
            
            $sql = "SELECT * FROM exams";
            if ($class) {
                $sql .= " WHERE class = '" . mysqli_real_escape_string($conn, $class) . "'";
            }
            
            if ($hasExamDate) {
                $sql .= " ORDER BY exam_date DESC, timestamp DESC";
            } else {
                $sql .= " ORDER BY timestamp DESC";
            }
            
            $result = @mysqli_query($conn, $sql);
            $exams = [];
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $exams[] = $row;
                }
            }
            
            echo json_encode(['status' => 'success', 'exams' => $exams]);
            exit();
        }
        
        // Get students for exam results
        else if ($action == 'get_students_for_exam') {
            try {
                $examId = mysqli_real_escape_string($conn, $_GET['exam_id']);
                
                // Get exam details
                $examSql = "SELECT * FROM exams WHERE exam_id = ?";
                $stmt = mysqli_prepare($conn, $examSql);
                
                if (!$stmt) {
                    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
                    exit();
                }
                
                mysqli_stmt_bind_param($stmt, "s", $examId);
                mysqli_stmt_execute($stmt);
                $examResult = mysqli_stmt_get_result($stmt);
                $exam = mysqli_fetch_assoc($examResult);
                
                if (!$exam) {
                    echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
                    exit();
                }
                
                // Check if students table exists
                $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'students'");
                if (!$checkTable || mysqli_num_rows($checkTable) == 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Students table not found']);
                    exit();
                }
                
                // Get students in that class/section
                $studentsSql = "SELECT s.id, s.fname, s.lname, 
                                er.marks_obtained, er.grade, er.remarks
                                FROM students s
                                LEFT JOIN exam_results er ON s.id = er.student_id AND er.exam_id = ?
                                WHERE s.class = ? AND s.section = ?
                                ORDER BY s.fname, s.lname";
                
                $stmt = mysqli_prepare($conn, $studentsSql);
                
                if (!$stmt) {
                    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . mysqli_error($conn)]);
                    exit();
                }
                
                mysqli_stmt_bind_param($stmt, "sss", $examId, $exam['class'], $exam['section']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                $students = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $students[] = $row;
                }
                
                echo json_encode([
                    'status' => 'success', 
                    'students' => $students, 
                    'exam' => $exam,
                    'debug' => [
                        'exam_id' => $examId,
                        'class' => $exam['class'],
                        'section' => $exam['section'],
                        'student_count' => count($students)
                    ]
                ]);
                exit();
                
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
                exit();
            }
        }
    }
    
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        // Create exam
        if ($action == 'create_exam') {
            $examId = 'E' . time() . uniqid();
            $examTitle = mysqli_real_escape_string($conn, $_POST['exam_title']);
            $subject = mysqli_real_escape_string($conn, $_POST['subject']);
            $class = mysqli_real_escape_string($conn, $_POST['class']);
            $section = mysqli_real_escape_string($conn, $_POST['section']);
            $examDate = mysqli_real_escape_string($conn, $_POST['exam_date']);
            $totalMarks = mysqli_real_escape_string($conn, $_POST['total_marks']);
            $passingMarks = mysqli_real_escape_string($conn, $_POST['passing_marks']);
            $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
            
            $sql = "INSERT INTO exams (exam_id, exam_title, subject, class, section, exam_date, total_marks, passing_marks, description, timestamp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssss", $examId, $examTitle, $subject, $class, $section, $examDate, $totalMarks, $passingMarks, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'exam_id' => $examId]);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create exam']);
                exit();
            }
        }
        
        // Save results
        else if ($action == 'save_results') {
            $examId = mysqli_real_escape_string($conn, $_POST['exam_id']);
            $results = json_decode($_POST['results'], true);
            
            if (!$results || !is_array($results)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid results data']);
                exit();
            }
            
            // Get exam details for grade calculation
            $examSql = "SELECT * FROM exams WHERE exam_id = ?";
            $stmt = mysqli_prepare($conn, $examSql);
            mysqli_stmt_bind_param($stmt, "s", $examId);
            mysqli_stmt_execute($stmt);
            $examResult = mysqli_stmt_get_result($stmt);
            $exam = mysqli_fetch_assoc($examResult);
            
            $success = 0;
            foreach ($results as $result) {
                $studentId = mysqli_real_escape_string($conn, $result['student_id']);
                $marksObtained = floatval($result['marks_obtained']);
                $remarks = mysqli_real_escape_string($conn, $result['remarks'] ?? '');
                
                // Calculate grade
                $percentage = ($marksObtained / $exam['total_marks']) * 100;
                
                if ($marksObtained < $exam['passing_marks']) {
                    $grade = 'F';
                    $status = 'fail';
                } else if ($percentage >= 90) {
                    $grade = 'A+';
                    $status = 'pass';
                } else if ($percentage >= 80) {
                    $grade = 'A';
                    $status = 'pass';
                } else if ($percentage >= 70) {
                    $grade = 'B';
                    $status = 'pass';
                } else if ($percentage >= 60) {
                    $grade = 'C';
                    $status = 'pass';
                } else if ($percentage >= 50) {
                    $grade = 'D';
                    $status = 'pass';
                } else {
                    $grade = 'F';
                    $status = 'fail';
                }
                
                // Check if result already exists
                $checkSql = "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ?";
                $stmtCheck = mysqli_prepare($conn, $checkSql);
                mysqli_stmt_bind_param($stmtCheck, "ss", $examId, $studentId);
                mysqli_stmt_execute($stmtCheck);
                $checkResult = mysqli_stmt_get_result($stmtCheck);
                
                if (mysqli_num_rows($checkResult) > 0) {
                    // Update existing
                    $updateSql = "UPDATE exam_results SET marks_obtained = ?, total_marks = ?, percentage = ?, grade = ?, status = ?, remarks = ?, updated_at = NOW() 
                                  WHERE exam_id = ? AND student_id = ?";
                    $stmtUpdate = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($stmtUpdate, "dddsssss", $marksObtained, $exam['total_marks'], $percentage, $grade, $status, $remarks, $examId, $studentId);
                    if (mysqli_stmt_execute($stmtUpdate)) $success++;
                } else {
                    // Insert new
                    $insertSql = "INSERT INTO exam_results (exam_id, student_id, marks_obtained, total_marks, percentage, grade, status, remarks) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtInsert = mysqli_prepare($conn, $insertSql);
                    mysqli_stmt_bind_param($stmtInsert, "ssdddsss", $examId, $studentId, $marksObtained, $exam['total_marks'], $percentage, $grade, $status, $remarks);
                    if (mysqli_stmt_execute($stmtInsert)) $success++;
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => "$success results saved"]);
            exit();
        }
        
        // Delete exam
        else if ($action == 'delete_exam') {
            $examId = mysqli_real_escape_string($conn, $_POST['exam_id']);
            
            // Delete results first
            mysqli_query($conn, "DELETE FROM exam_results WHERE exam_id = '$examId'");
            
            // Delete exam
            $sql = "DELETE FROM exams WHERE exam_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $examId);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete exam']);
                exit();
            }
        }
    }
}

// Student actions
else if ($userRole == 'student') {
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $action = $_GET['action'] ?? '';
        $studentId = $_SESSION['uid'];
        
        // Get student's exams
        if ($action == 'get_my_exams') {
            // Get student class/section
            $studentSql = "SELECT class, section FROM students WHERE id = ?";
            $stmt = mysqli_prepare($conn, $studentSql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);
            
            if (!$student) {
                echo json_encode(['status' => 'error', 'message' => 'Student not found']);
                exit();
            }
            
            // Get exams for student's class
            $orderBy = $hasExamDate ? "ORDER BY e.exam_date DESC, e.timestamp DESC" : "ORDER BY e.timestamp DESC";
            
            $examsSql = "SELECT e.*, 
                         er.marks_obtained, er.total_marks, er.percentage, er.grade, er.status, er.remarks
                         FROM exams e
                         LEFT JOIN exam_results er ON e.exam_id = er.exam_id AND er.student_id = ?
                         WHERE e.class = ? AND e.section = ?
                         $orderBy";
            
            $stmt = mysqli_prepare($conn, $examsSql);
            mysqli_stmt_bind_param($stmt, "sss", $studentId, $student['class'], $student['section']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $exams = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $exams[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'exams' => $exams]);
            exit();
        }
        
        // Get exam statistics
        else if ($action == 'get_my_stats') {
            $studentSql = "SELECT class, section FROM students WHERE id = ?";
            $stmt = mysqli_prepare($conn, $studentSql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);
            
            $stats = [];
            
            // Total exams
            $sql = "SELECT COUNT(*) as count FROM exams WHERE class = ? AND section = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $student['class'], $student['section']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $stats['total_exams'] = mysqli_fetch_assoc($result)['count'];
            
            // Results published
            $sql = "SELECT COUNT(*) as count FROM exam_results WHERE student_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $stats['results_published'] = mysqli_fetch_assoc($result)['count'];
            
            // Pass count
            $sql = "SELECT COUNT(*) as count FROM exam_results WHERE student_id = ? AND status = 'pass'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $stats['passed'] = mysqli_fetch_assoc($result)['count'];
            
            // Average percentage
            $sql = "SELECT AVG((er.marks_obtained / e.total_marks) * 100) as avg 
                    FROM exam_results er
                    JOIN exams e ON er.exam_id = e.exam_id
                    WHERE er.student_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $stats['average'] = $row['avg'] ? number_format($row['avg'], 1) . '%' : '0%';
            
            echo json_encode(['status' => 'success', 'stats' => $stats]);
            exit();
        }
    }
}

// Default error for unauthorized access
echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
exit();
?>
