<?php
// REQ-ACD-004: Assessment & Grading - Grade Entry & Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Enter/Update student marks
    if ($action == "enter_marks") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $assessmentTypeId = mysqli_real_escape_string($conn, $_POST["assessment_type_id"]);
        $examId = $_POST["exam_id"] ?? null;
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? '1');
        $marksObtained = $_POST["marks_obtained"];
        $totalMarks = $_POST["total_marks"] ?? 100;
        $isAbsent = $_POST["is_absent"] ?? 0;
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        $enteredBy = mysqli_real_escape_string($conn, $_POST["entered_by"] ?? '');
        $entryDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Validate marks
            $validation = validateMarks($conn, $marksObtained, $totalMarks, $studentId, $courseCode);
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }
            
            // Calculate percentage and grade
            $percentage = ($marksObtained / $totalMarks) * 100;
            $gradeData = getGradeFromPercentage($conn, $percentage);
            
            // Check if entry exists
            $checkSql = "SELECT mark_id FROM student_marks 
                        WHERE student_id = ? AND course_code = ? AND assessment_type_id = ? 
                        AND academic_year = ? AND semester = ?";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "ssiss", $studentId, $courseCode, $assessmentTypeId, $academicYear, $semester);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                // Update existing entry
                $row = mysqli_fetch_assoc($resultCheck);
                $markId = $row['mark_id'];
                
                // Log old value
                logGradeChange($conn, $markId, $studentId, $courseCode, 'update', 'marks_obtained', $marksObtained, $enteredBy);
                
                $updateSql = "UPDATE student_marks SET 
                             marks_obtained = ?, total_marks = ?, percentage = ?, 
                             grade = ?, grade_point = ?, is_absent = ?, remarks = ?,
                             entered_by = ?, entry_date = ?, status = 'submitted'
                             WHERE mark_id = ?";
                
                $stmtUpdate = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmtUpdate, "dddsddisssi", 
                    $marksObtained, $totalMarks, $percentage, $gradeData['grade'], 
                    $gradeData['grade_point'], $isAbsent, $remarks, $enteredBy, $entryDate, $markId);
                
                if (!mysqli_stmt_execute($stmtUpdate)) {
                    throw new Exception("Error updating marks: " . mysqli_error($conn));
                }
            } else {
                // Insert new entry
                $insertSql = "INSERT INTO student_marks 
                             (student_id, course_code, exam_id, assessment_type_id, class, section, 
                              academic_year, semester, marks_obtained, total_marks, percentage, 
                              grade, grade_point, is_absent, remarks, entered_by, entry_date, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted')";
                
                $stmtInsert = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($stmtInsert, "ssisssssdddsddisss", 
                    $studentId, $courseCode, $examId, $assessmentTypeId, $class, $section,
                    $academicYear, $semester, $marksObtained, $totalMarks, $percentage,
                    $gradeData['grade'], $gradeData['grade_point'], $isAbsent, $remarks, 
                    $enteredBy, $entryDate);
                
                if (!mysqli_stmt_execute($stmtInsert)) {
                    throw new Exception("Error entering marks: " . mysqli_error($conn));
                }
                
                $markId = mysqli_insert_id($conn);
                logGradeChange($conn, $markId, $studentId, $courseCode, 'insert', '', '', $enteredBy);
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Marks entered successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Bulk mark entry
    else if ($action == "bulk_enter_marks") {
        $marksData = json_decode($_POST["marks_data"], true);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $assessmentTypeId = mysqli_real_escape_string($conn, $_POST["assessment_type_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? '1');
        $enteredBy = mysqli_real_escape_string($conn, $_POST["entered_by"] ?? '');
        
        mysqli_begin_transaction($conn);
        
        try {
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($marksData as $mark) {
                $studentId = mysqli_real_escape_string($conn, $mark['student_id']);
                $marksObtained = $mark['marks_obtained'];
                $totalMarks = $mark['total_marks'] ?? 100;
                $isAbsent = $mark['is_absent'] ?? 0;
                
                $percentage = ($marksObtained / $totalMarks) * 100;
                $gradeData = getGradeFromPercentage($conn, $percentage);
                
                $sql = "INSERT INTO student_marks 
                       (student_id, course_code, assessment_type_id, class, section, 
                        academic_year, semester, marks_obtained, total_marks, percentage, 
                        grade, grade_point, is_absent, entered_by, entry_date, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'submitted')
                       ON DUPLICATE KEY UPDATE 
                       marks_obtained = VALUES(marks_obtained), 
                       percentage = VALUES(percentage),
                       grade = VALUES(grade),
                       grade_point = VALUES(grade_point),
                       is_absent = VALUES(is_absent)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssissssdddsddis", 
                    $studentId, $courseCode, $assessmentTypeId, $mark['class'], $mark['section'],
                    $academicYear, $semester, $marksObtained, $totalMarks, $percentage,
                    $gradeData['grade'], $gradeData['grade_point'], $isAbsent, $enteredBy);
                
                if (mysqli_stmt_execute($stmt)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "Marks entered for $successCount students. Errors: $errorCount",
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Verify marks
    else if ($action == "verify_marks") {
        $markId = mysqli_real_escape_string($conn, $_POST["mark_id"]);
        $verifiedBy = mysqli_real_escape_string($conn, $_POST["verified_by"]);
        $verifiedDate = date('Y-m-d');
        
        $sql = "UPDATE student_marks SET status = 'verified', verified_by = ?, verified_date = ? WHERE mark_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $verifiedBy, $verifiedDate, $markId);
        
        if (mysqli_stmt_execute($stmt)) {
            logGradeChange($conn, $markId, '', '', 'verify', '', '', $verifiedBy);
            echo json_encode(['status' => 'success', 'message' => 'Marks verified!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error verifying marks: ' . mysqli_error($conn)]);
        }
    }
    
    // Publish marks
    else if ($action == "publish_marks") {
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $assessmentTypeId = mysqli_real_escape_string($conn, $_POST["assessment_type_id"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        
        $sql = "UPDATE student_marks SET status = 'published' 
                WHERE course_code = ? AND assessment_type_id = ? AND class = ? 
                AND academic_year = ? AND semester = ? AND status = 'verified'";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sisss", $courseCode, $assessmentTypeId, $class, $academicYear, $semester);
        
        if (mysqli_stmt_execute($stmt)) {
            $affectedRows = mysqli_affected_rows($conn);
            echo json_encode(['status' => 'success', 'message' => "Marks published for $affectedRows students!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error publishing marks: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch student marks
    else if ($action == "fetch_student_marks") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? '');
        
        $sql = "SELECT sm.*, c.course_name, at.type_name, at.weightage 
                FROM student_marks sm 
                JOIN courses c ON sm.course_code = c.course_code 
                JOIN assessment_types at ON sm.assessment_type_id = at.assessment_type_id 
                WHERE sm.student_id = ? AND sm.status = 'published'";
        
        if (!empty($academicYear)) {
            $sql .= " AND sm.academic_year = '$academicYear'";
        }
        if (!empty($semester)) {
            $sql .= " AND sm.semester = '$semester'";
        }
        
        $sql .= " ORDER BY sm.course_code, at.display_order";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $marks = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $marks[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $marks]);
    }
    
    // Fetch marks for grading
    else if ($action == "fetch_marks_for_grading") {
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $assessmentTypeId = mysqli_real_escape_string($conn, $_POST["assessment_type_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        
        $sql = "SELECT sm.*, s.fname, s.lname, s.image, s.admission_no 
                FROM student_marks sm 
                JOIN students s ON sm.student_id = s.id 
                WHERE sm.course_code = ? AND sm.class = ? AND sm.assessment_type_id = ? 
                AND sm.academic_year = ? AND sm.semester = ? 
                ORDER BY s.fname, s.lname";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssiss", $courseCode, $class, $assessmentTypeId, $academicYear, $semester);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $marks = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $marks[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $marks]);
    }
    
    // Delete marks
    else if ($action == "delete_marks") {
        $markId = mysqli_real_escape_string($conn, $_POST["mark_id"]);
        $deletedBy = mysqli_real_escape_string($conn, $_POST["deleted_by"] ?? '');
        
        // Get mark details before deleting
        $getSql = "SELECT * FROM student_marks WHERE mark_id = ?";
        $stmtGet = mysqli_prepare($conn, $getSql);
        mysqli_stmt_bind_param($stmtGet, "i", $markId);
        mysqli_stmt_execute($stmtGet);
        $result = mysqli_stmt_get_result($stmtGet);
        $mark = mysqli_fetch_assoc($result);
        
        if ($mark) {
            logGradeChange($conn, $markId, $mark['student_id'], $mark['course_code'], 'delete', '', '', $deletedBy);
            
            $sql = "DELETE FROM student_marks WHERE mark_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $markId);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Marks deleted!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting marks: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Marks not found!']);
        }
    }
}

// Helper function to validate marks
function validateMarks($conn, $marks, $totalMarks, $studentId, $courseCode) {
    // Check basic range
    if ($marks < 0 || $marks > $totalMarks) {
        return ['valid' => false, 'message' => 'Marks must be between 0 and ' . $totalMarks];
    }
    
    // Check validation rules
    $sql = "SELECT * FROM grade_validation_rules WHERE is_active = 1 AND is_blocking = 1";
    $result = mysqli_query($conn, $sql);
    
    while ($rule = mysqli_fetch_assoc($result)) {
        if ($rule['rule_type'] == 'min_max') {
            if ($marks < $rule['min_value'] || $marks > $rule['max_value']) {
                return ['valid' => false, 'message' => $rule['error_message']];
            }
        }
    }
    
    return ['valid' => true, 'message' => ''];
}

// Helper function to get grade from percentage
function getGradeFromPercentage($conn, $percentage) {
    $sql = "SELECT grade, grade_point FROM grade_scale 
            WHERE $percentage >= min_percentage AND $percentage <= max_percentage 
            AND is_active = 1 
            ORDER BY min_percentage DESC LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return ['grade' => $row['grade'], 'grade_point' => $row['grade_point']];
    }
    
    return ['grade' => 'F', 'grade_point' => 0.00];
}

// Helper function to log grade changes
function logGradeChange($conn, $markId, $studentId, $courseCode, $action, $fieldName, $newValue, $changedBy) {
    $sql = "INSERT INTO grade_audit_log (mark_id, student_id, course_code, action, field_name, new_value, changed_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issssss", $markId, $studentId, $courseCode, $action, $fieldName, $newValue, $changedBy);
    mysqli_stmt_execute($stmt);
}

mysqli_close($conn);
?>
