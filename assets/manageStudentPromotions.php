<?php
// Student Promotions Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create new promotion
    if ($action == "create_promotion") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $fromClass = mysqli_real_escape_string($conn, $_POST["from_class"]);
        $fromSection = mysqli_real_escape_string($conn, $_POST["from_section"]);
        $toClass = mysqli_real_escape_string($conn, $_POST["to_class"]);
        $toSection = mysqli_real_escape_string($conn, $_POST["to_section"]);
        $academicYearFrom = mysqli_real_escape_string($conn, $_POST["academic_year_from"]);
        $academicYearTo = mysqli_real_escape_string($conn, $_POST["academic_year_to"]);
        $promotionType = mysqli_real_escape_string($conn, $_POST["promotion_type"] ?? 'regular');
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        $promotedBy = mysqli_real_escape_string($conn, $_POST["promoted_by"]);
        
        $promotionDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Insert promotion record
            $sql = "INSERT INTO student_promotions (student_id, from_class, from_section, to_class, to_section, academic_year_from, academic_year_to, promotion_date, promotion_type, promotion_status, promoted_by, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssss", 
                $studentId, $fromClass, $fromSection, $toClass, $toSection, 
                $academicYearFrom, $academicYearTo, $promotionDate, $promotionType, 
                $promotedBy, $remarks);
            
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
            $getPromotion = "SELECT * FROM student_promotions WHERE promotion_id = ?";
            $stmt = mysqli_prepare($conn, $getPromotion);
            mysqli_stmt_bind_param($stmt, "i", $promotionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $promotion = mysqli_fetch_assoc($result);
            
            if (!$promotion) {
                throw new Exception("Promotion not found");
            }
            
            // Update promotion status
            $updatePromotion = "UPDATE student_promotions SET promotion_status = 'approved', approved_by = ? WHERE promotion_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updatePromotion);
            mysqli_stmt_bind_param($stmtUpdate, "si", $approvedBy, $promotionId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error updating promotion: " . mysqli_error($conn));
            }
            
            // Update student class and section
            $updateStudent = "UPDATE students SET class = ?, section = ? WHERE id = ?";
            $stmtStudent = mysqli_prepare($conn, $updateStudent);
            mysqli_stmt_bind_param($stmtStudent, "sss", $promotion['to_class'], $promotion['to_section'], $promotion['student_id']);
            
            if (!mysqli_stmt_execute($stmtStudent)) {
                throw new Exception("Error updating student: " . mysqli_error($conn));
            }
            
            // Create new enrollment record
            $enrollmentDate = date('Y-m-d');
            $insertEnrollment = "INSERT INTO student_enrollments (student_id, academic_year, class, section, enrollment_date, enrollment_status) 
                                VALUES (?, ?, ?, ?, ?, 'active')";
            $stmtEnroll = mysqli_prepare($conn, $insertEnrollment);
            mysqli_stmt_bind_param($stmtEnroll, "sssss", 
                $promotion['student_id'], $promotion['academic_year_to'], 
                $promotion['to_class'], $promotion['to_section'], $enrollmentDate);
            
            mysqli_stmt_execute($stmtEnroll); // Non-critical if enrollments table doesn't exist
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Promotion approved successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Fetch all promotions
    else if ($action == "fetch_promotions") {
        $status = $_POST["status"] ?? '';
        
        $sql = "SELECT p.*, s.fname, s.lname, s.email 
                FROM student_promotions p 
                JOIN students s ON p.student_id = s.id";
        
        if ($status) {
            $sql .= " WHERE p.promotion_status = '" . mysqli_real_escape_string($conn, $status) . "'";
        }
        
        $sql .= " ORDER BY p.promotion_date DESC";
        
        $result = mysqli_query($conn, $sql);
        $promotions = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $promotions[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $promotions]);
    }
    
    // Bulk promotion
    else if ($action == "bulk_promotion") {
        $fromClass = mysqli_real_escape_string($conn, $_POST["from_class"]);
        $toClass = mysqli_real_escape_string($conn, $_POST["to_class"]);
        $academicYearFrom = mysqli_real_escape_string($conn, $_POST["academic_year_from"]);
        $academicYearTo = mysqli_real_escape_string($conn, $_POST["academic_year_to"]);
        $promotedBy = mysqli_real_escape_string($conn, $_POST["promoted_by"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get all students in the class
            $getStudents = "SELECT id, class, section FROM students WHERE class = ? AND request = ''";
            $stmt = mysqli_prepare($conn, $getStudents);
            mysqli_stmt_bind_param($stmt, "s", $fromClass);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $promoted = 0;
            $promotionDate = date('Y-m-d');
            
            while ($student = mysqli_fetch_assoc($result)) {
                // Create promotion record
                $insertPromotion = "INSERT INTO student_promotions (student_id, from_class, from_section, to_class, to_section, academic_year_from, academic_year_to, promotion_date, promotion_type, promotion_status, promoted_by) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'regular', 'approved', ?)";
                
                $stmtPromo = mysqli_prepare($conn, $insertPromotion);
                mysqli_stmt_bind_param($stmtPromo, "sssssssss", 
                    $student['id'], $student['class'], $student['section'], 
                    $toClass, $student['section'], $academicYearFrom, $academicYearTo, 
                    $promotionDate, $promotedBy);
                
                if (mysqli_stmt_execute($stmtPromo)) {
                    // Update student class
                    $updateStudent = "UPDATE students SET class = ? WHERE id = ?";
                    $stmtUpdate = mysqli_prepare($conn, $updateStudent);
                    mysqli_stmt_bind_param($stmtUpdate, "ss", $toClass, $student['id']);
                    mysqli_stmt_execute($stmtUpdate);
                    
                    $promoted++;
                }
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => "$promoted students promoted successfully!"]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

mysqli_close($conn);
?>
