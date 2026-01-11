<?php
// REQ-ACD-001: Student Promotion Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create promotion request
    if ($action == "create_promotion") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $fromClass = mysqli_real_escape_string($conn, $_POST["from_class"]);
        $fromSection = mysqli_real_escape_string($conn, $_POST["from_section"]);
        $toClass = mysqli_real_escape_string($conn, $_POST["to_class"]);
        $toSection = mysqli_real_escape_string($conn, $_POST["to_section"]);
        $academicYearFrom = mysqli_real_escape_string($conn, $_POST["academic_year_from"]);
        $academicYearTo = mysqli_real_escape_string($conn, $_POST["academic_year_to"]);
        $promotionType = mysqli_real_escape_string($conn, $_POST["promotion_type"] ?? 'regular');
        $performanceGrade = mysqli_real_escape_string($conn, $_POST["performance_grade"] ?? '');
        $attendancePercentage = $_POST["attendance_percentage"] ?? 0;
        $conductRemarks = mysqli_real_escape_string($conn, $_POST["conduct_remarks"] ?? '');
        $detentionReason = mysqli_real_escape_string($conn, $_POST["detention_reason"] ?? '');
        $promotedBy = mysqli_real_escape_string($conn, $_POST["promoted_by"] ?? '');
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        $promotionDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check if promotion already exists
            $checkSql = "SELECT * FROM student_promotions 
                        WHERE student_id = ? AND academic_year_from = ? AND academic_year_to = ?";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "sss", $studentId, $academicYearFrom, $academicYearTo);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                throw new Exception("Promotion record already exists for this academic year!");
            }
            
            // Insert promotion record
            $sql = "INSERT INTO student_promotions 
                    (student_id, from_class, from_section, to_class, to_section, 
                     academic_year_from, academic_year_to, promotion_date, promotion_status, 
                     promotion_type, detention_reason, performance_grade, attendance_percentage, 
                     conduct_remarks, promoted_by, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssssdsss", 
                $studentId, $fromClass, $fromSection, $toClass, $toSection,
                $academicYearFrom, $academicYearTo, $promotionDate, $promotionType,
                $detentionReason, $performanceGrade, $attendancePercentage,
                $conductRemarks, $promotedBy, $remarks);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating promotion: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Promotion created successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Approve promotion
    else if ($action == "approve_promotion") {
        $promotionId = mysqli_real_escape_string($conn, $_POST["promotion_id"]);
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get promotion details
            $getSql = "SELECT * FROM student_promotions WHERE promotion_id = ?";
            $stmtGet = mysqli_prepare($conn, $getSql);
            mysqli_stmt_bind_param($stmtGet, "i", $promotionId);
            mysqli_stmt_execute($stmtGet);
            $resultGet = mysqli_stmt_get_result($stmtGet);
            $promotion = mysqli_fetch_assoc($resultGet);
            
            if (!$promotion) {
                throw new Exception("Promotion not found!");
            }
            
            // Update promotion status
            $updatePromotion = "UPDATE student_promotions SET 
                               promotion_status = 'approved',
                               approved_by = ?
                               WHERE promotion_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updatePromotion);
            mysqli_stmt_bind_param($stmtUpdate, "si", $approvedBy, $promotionId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error approving promotion: " . mysqli_error($conn));
            }
            
            // For detention, don't update student class
            if ($promotion['promotion_type'] != 'detention') {
                // Update student's current class and section
                $updateStudent = "UPDATE students SET 
                                 class = ?, 
                                 section = ?
                                 WHERE id = ?";
                $stmtStudent = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtStudent, "sss", 
                    $promotion['to_class'], $promotion['to_section'], $promotion['student_id']);
                
                if (!mysqli_stmt_execute($stmtStudent)) {
                    throw new Exception("Error updating student: " . mysqli_error($conn));
                }
                
                // Complete old enrollment
                $completeEnrollment = "UPDATE student_enrollments SET 
                                      enrollment_status = 'completed'
                                      WHERE student_id = ? AND academic_year = ?";
                $stmtComplete = mysqli_prepare($conn, $completeEnrollment);
                mysqli_stmt_bind_param($stmtComplete, "ss", 
                    $promotion['student_id'], $promotion['academic_year_from']);
                mysqli_stmt_execute($stmtComplete);
                
                // Create new enrollment for promoted class
                $enrollmentDate = date('Y-m-d');
                $createEnrollment = "INSERT INTO student_enrollments 
                                    (student_id, academic_year, class, section, enrollment_date, enrollment_status) 
                                    VALUES (?, ?, ?, ?, ?, 'active')";
                $stmtEnroll = mysqli_prepare($conn, $createEnrollment);
                mysqli_stmt_bind_param($stmtEnroll, "sssss", 
                    $promotion['student_id'], $promotion['academic_year_to'], 
                    $promotion['to_class'], $promotion['to_section'], $enrollmentDate);
                
                if (!mysqli_stmt_execute($stmtEnroll)) {
                    throw new Exception("Error creating new enrollment: " . mysqli_error($conn));
                }
            }
            
            // Update promotion status to completed
            $completeSql = "UPDATE student_promotions SET promotion_status = 'completed' WHERE promotion_id = ?";
            $stmtComp = mysqli_prepare($conn, $completeSql);
            mysqli_stmt_bind_param($stmtComp, "i", $promotionId);
            mysqli_stmt_execute($stmtComp);
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Promotion approved and completed successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Reject promotion
    else if ($action == "reject_promotion") {
        $promotionId = mysqli_real_escape_string($conn, $_POST["promotion_id"]);
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        
        $sql = "UPDATE student_promotions SET 
                promotion_status = 'rejected',
                remarks = ?
                WHERE promotion_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $remarks, $promotionId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Promotion rejected!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error rejecting promotion: ' . mysqli_error($conn)]);
        }
    }
    
    // Bulk promotion for entire class
    else if ($action == "bulk_promote_class") {
        $fromClass = mysqli_real_escape_string($conn, $_POST["from_class"]);
        $fromSection = mysqli_real_escape_string($conn, $_POST["from_section"]);
        $toClass = mysqli_real_escape_string($conn, $_POST["to_class"]);
        $toSection = mysqli_real_escape_string($conn, $_POST["to_section"]);
        $academicYearFrom = mysqli_real_escape_string($conn, $_POST["academic_year_from"]);
        $academicYearTo = mysqli_real_escape_string($conn, $_POST["academic_year_to"]);
        $promotedBy = mysqli_real_escape_string($conn, $_POST["promoted_by"] ?? '');
        $promotionDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get all active students in the class
            $getStudents = "SELECT s.id, se.performance_grade, se.attendance_percentage 
                           FROM students s 
                           JOIN student_enrollments se ON s.id = se.student_id 
                           WHERE s.class = ? AND s.section = ? 
                           AND s.enrollment_status = 'active' 
                           AND se.academic_year = ? AND se.enrollment_status = 'active'";
            
            $stmtGet = mysqli_prepare($conn, $getStudents);
            mysqli_stmt_bind_param($stmtGet, "sss", $fromClass, $fromSection, $academicYearFrom);
            mysqli_stmt_execute($stmtGet);
            $resultGet = mysqli_stmt_get_result($stmtGet);
            
            $promotedCount = 0;
            
            while ($student = mysqli_fetch_assoc($resultGet)) {
                // Create promotion record for each student
                $insertPromotion = "INSERT INTO student_promotions 
                                   (student_id, from_class, from_section, to_class, to_section, 
                                    academic_year_from, academic_year_to, promotion_date, 
                                    promotion_status, promotion_type, promoted_by) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', 'regular', ?)";
                
                $stmtPromotion = mysqli_prepare($conn, $insertPromotion);
                mysqli_stmt_bind_param($stmtPromotion, "sssssssss", 
                    $student['id'], $fromClass, $fromSection, $toClass, $toSection,
                    $academicYearFrom, $academicYearTo, $promotionDate, $promotedBy);
                
                if (!mysqli_stmt_execute($stmtPromotion)) {
                    throw new Exception("Error creating promotion for student " . $student['id']);
                }
                
                // Update student's class and section
                $updateStudent = "UPDATE students SET class = ?, section = ? WHERE id = ?";
                $stmtUpdate = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtUpdate, "sss", $toClass, $toSection, $student['id']);
                
                if (!mysqli_stmt_execute($stmtUpdate)) {
                    throw new Exception("Error updating student " . $student['id']);
                }
                
                // Complete old enrollment
                $completeEnrollment = "UPDATE student_enrollments SET enrollment_status = 'completed' 
                                      WHERE student_id = ? AND academic_year = ?";
                $stmtComplete = mysqli_prepare($conn, $completeEnrollment);
                mysqli_stmt_bind_param($stmtComplete, "ss", $student['id'], $academicYearFrom);
                mysqli_stmt_execute($stmtComplete);
                
                // Create new enrollment
                $enrollmentDate = date('Y-m-d');
                $createEnrollment = "INSERT INTO student_enrollments 
                                    (student_id, academic_year, class, section, enrollment_date, enrollment_status) 
                                    VALUES (?, ?, ?, ?, ?, 'active')";
                $stmtEnroll = mysqli_prepare($conn, $createEnrollment);
                mysqli_stmt_bind_param($stmtEnroll, "sssss", 
                    $student['id'], $academicYearTo, $toClass, $toSection, $enrollmentDate);
                
                if (!mysqli_stmt_execute($stmtEnroll)) {
                    throw new Exception("Error creating enrollment for student " . $student['id']);
                }
                
                // Mark promotion as completed
                $completeSql = "UPDATE student_promotions SET promotion_status = 'completed' 
                               WHERE student_id = ? AND academic_year_from = ? AND academic_year_to = ?";
                $stmtComp = mysqli_prepare($conn, $completeSql);
                mysqli_stmt_bind_param($stmtComp, "sss", $student['id'], $academicYearFrom, $academicYearTo);
                mysqli_stmt_execute($stmtComp);
                
                $promotedCount++;
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "Successfully promoted $promotedCount students!",
                'count' => $promotedCount
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Fetch student promotion history
    else if ($action == "fetch_promotion_history") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        $sql = "SELECT * FROM student_promotions 
                WHERE student_id = ? 
                ORDER BY academic_year_from DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $promotions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $promotions[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $promotions]);
    }
    
    // Fetch pending promotions
    else if ($action == "fetch_pending_promotions") {
        $sql = "SELECT sp.*, s.fname, s.lname, s.father, s.email 
                FROM student_promotions sp 
                JOIN students s ON sp.student_id = s.id 
                WHERE sp.promotion_status = 'pending' 
                ORDER BY sp.promotion_date DESC";
        
        $result = mysqli_query($conn, $sql);
        $promotions = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $promotions[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $promotions]);
    }
}

mysqli_close($conn);
?>
