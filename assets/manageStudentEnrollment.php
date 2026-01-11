<?php
// REQ-ACD-001: Student Enrollment Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create new enrollment
    if ($action == "create_enrollment") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"]);
        $rollNo = mysqli_real_escape_string($conn, $_POST["roll_no"] ?? '');
        $subjects = mysqli_real_escape_string($conn, $_POST["subjects"] ?? '');
        $feeCategory = mysqli_real_escape_string($conn, $_POST["fee_category"] ?? '');
        $scholarshipApplied = $_POST["scholarship_applied"] ?? 0;
        $scholarshipAmount = $_POST["scholarship_amount"] ?? 0;
        $enrollmentDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check if enrollment already exists for this academic year
            $checkSql = "SELECT * FROM student_enrollments WHERE student_id = ? AND academic_year = ?";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "ss", $studentId, $academicYear);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                throw new Exception("Enrollment already exists for this academic year!");
            }
            
            // Insert enrollment record
            $sql = "INSERT INTO student_enrollments 
                    (student_id, academic_year, class, section, roll_no, enrollment_date, 
                     enrollment_status, subjects, fee_category, scholarship_applied, scholarship_amount) 
                    VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssssid", 
                $studentId, $academicYear, $class, $section, $rollNo, $enrollmentDate,
                $subjects, $feeCategory, $scholarshipApplied, $scholarshipAmount);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating enrollment: " . mysqli_error($conn));
            }
            
            // Update student's current class and section
            $updateStudent = "UPDATE students SET class = ?, section = ?, enrollment_status = 'active' WHERE id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateStudent);
            mysqli_stmt_bind_param($stmtUpdate, "sss", $class, $section, $studentId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error updating student: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Enrollment created successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Update enrollment
    else if ($action == "update_enrollment") {
        $enrollmentId = mysqli_real_escape_string($conn, $_POST["enrollment_id"]);
        $rollNo = mysqli_real_escape_string($conn, $_POST["roll_no"] ?? '');
        $subjects = mysqli_real_escape_string($conn, $_POST["subjects"] ?? '');
        $feeCategory = mysqli_real_escape_string($conn, $_POST["fee_category"] ?? '');
        $scholarshipApplied = $_POST["scholarship_applied"] ?? 0;
        $scholarshipAmount = $_POST["scholarship_amount"] ?? 0;
        $enrollmentStatus = mysqli_real_escape_string($conn, $_POST["enrollment_status"] ?? 'active');
        
        $sql = "UPDATE student_enrollments SET 
                roll_no = ?, 
                subjects = ?, 
                fee_category = ?, 
                scholarship_applied = ?, 
                scholarship_amount = ?,
                enrollment_status = ?
                WHERE enrollment_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssidsi", 
            $rollNo, $subjects, $feeCategory, $scholarshipApplied, 
            $scholarshipAmount, $enrollmentStatus, $enrollmentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Enrollment updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating enrollment: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch student enrollments
    else if ($action == "fetch_enrollments") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        $sql = "SELECT * FROM student_enrollments 
                WHERE student_id = ? 
                ORDER BY academic_year DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $enrollments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $enrollments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $enrollments]);
    }
    
    // Fetch current enrollment
    else if ($action == "fetch_current_enrollment") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        $sql = "SELECT * FROM student_enrollments 
                WHERE student_id = ? AND enrollment_status = 'active'
                ORDER BY enrollment_date DESC 
                LIMIT 1";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No active enrollment found']);
        }
    }
    
    // Fetch all enrollments for a class
    else if ($action == "fetch_class_enrollments") {
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        
        $sql = "SELECT se.*, s.fname, s.lname, s.father, s.email, s.phone, s.image 
                FROM student_enrollments se 
                JOIN students s ON se.student_id = s.id 
                WHERE se.academic_year = ? AND se.class = ?";
        
        if (!empty($section)) {
            $sql .= " AND se.section = ?";
        }
        
        $sql .= " ORDER BY se.roll_no ASC";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!empty($section)) {
            mysqli_stmt_bind_param($stmt, "sss", $academicYear, $class, $section);
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $academicYear, $class);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $enrollments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $enrollments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $enrollments]);
    }
    
    // Complete enrollment (at year end)
    else if ($action == "complete_enrollment") {
        $enrollmentId = mysqli_real_escape_string($conn, $_POST["enrollment_id"]);
        
        $sql = "UPDATE student_enrollments SET 
                enrollment_status = 'completed'
                WHERE enrollment_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $enrollmentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Enrollment completed successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error completing enrollment: ' . mysqli_error($conn)]);
        }
    }
    
    // Discontinue enrollment
    else if ($action == "discontinue_enrollment") {
        $enrollmentId = mysqli_real_escape_string($conn, $_POST["enrollment_id"]);
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            $sql = "UPDATE student_enrollments SET 
                    enrollment_status = 'discontinued'
                    WHERE enrollment_id = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $enrollmentId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error discontinuing enrollment: " . mysqli_error($conn));
            }
            
            // Update student status
            $updateStudent = "UPDATE students SET enrollment_status = 'withdrawn' WHERE id = ?";
            $stmtStudent = mysqli_prepare($conn, $updateStudent);
            mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
            
            if (!mysqli_stmt_execute($stmtStudent)) {
                throw new Exception("Error updating student status: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Enrollment discontinued successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Get enrollment statistics
    else if ($action == "get_enrollment_stats") {
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        
        $sql = "SELECT 
                class,
                section,
                COUNT(*) as total_students,
                SUM(CASE WHEN scholarship_applied = 1 THEN 1 ELSE 0 END) as scholarship_students,
                SUM(scholarship_amount) as total_scholarship_amount
                FROM student_enrollments 
                WHERE academic_year = ? AND enrollment_status = 'active'
                GROUP BY class, section
                ORDER BY class, section";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $academicYear);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $stats = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $stats[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
}

mysqli_close($conn);
?>
