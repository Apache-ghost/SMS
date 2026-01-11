<?php
// REQ-ACD-005: Assignment Grading - Grade submissions and provide feedback
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Grade assignment submission
    if ($action == "grade_submission") {
        $submissionId = mysqli_real_escape_string($conn, $_POST["submission_id"]);
        $marksObtained = mysqli_real_escape_string($conn, $_POST["marks_obtained"]);
        $maxMarks = mysqli_real_escape_string($conn, $_POST["max_marks"]);
        $feedback = mysqli_real_escape_string($conn, $_POST["feedback"] ?? '');
        $privateNotes = mysqli_real_escape_string($conn, $_POST["private_notes"] ?? '');
        $gradingRubric = mysqli_real_escape_string($conn, $_POST["grading_rubric"] ?? '');
        $gradedBy = mysqli_real_escape_string($conn, $_POST["graded_by"]);
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? 'draft');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get submission details
            $getSub = "SELECT s.assignment_id, s.student_id, s.late_penalty_applied, a.max_marks 
                      FROM student_submissions s 
                      JOIN assignments a ON s.assignment_id = a.assignment_id 
                      WHERE s.submission_id = ?";
            $stmtGet = mysqli_prepare($conn, $getSub);
            mysqli_stmt_bind_param($stmtGet, "i", $submissionId);
            mysqli_stmt_execute($stmtGet);
            $result = mysqli_stmt_get_result($stmtGet);
            $submission = mysqli_fetch_assoc($result);
            
            if (!$submission) {
                throw new Exception("Submission not found!");
            }
            
            // Apply late penalty
            $finalMarks = $marksObtained;
            if ($submission['late_penalty_applied'] > 0) {
                $penalty = ($marksObtained * $submission['late_penalty_applied']) / 100;
                $finalMarks = max(0, $marksObtained - $penalty);
            }
            
            // Calculate grade
            $percentage = ($finalMarks / $maxMarks) * 100;
            $grade = getGradeFromPercentage($conn, $percentage);
            
            // Check if grade already exists
            $checkGrade = "SELECT grade_id FROM assignment_grades WHERE submission_id = ?";
            $stmtCheck = mysqli_prepare($conn, $checkGrade);
            mysqli_stmt_bind_param($stmtCheck, "i", $submissionId);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            $existingGrade = mysqli_fetch_assoc($resultCheck);
            
            if ($existingGrade) {
                // Update existing grade
                $sql = "UPDATE assignment_grades 
                        SET marks_obtained = ?, max_marks = ?, grade = ?, 
                            feedback = ?, private_notes = ?, grading_rubric = ?,
                            last_modified_by = ?, last_modified_date = NOW(),
                            status = ?, updated_at = NOW()
                        WHERE grade_id = ?";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ddsssssssi", 
                    $finalMarks, $maxMarks, $grade, $feedback, $privateNotes, 
                    $gradingRubric, $gradedBy, $status, $existingGrade['grade_id']);
            } else {
                // Insert new grade
                $sql = "INSERT INTO assignment_grades 
                        (submission_id, assignment_id, student_id, marks_obtained, max_marks, 
                         grade, feedback, private_notes, grading_rubric, graded_by, 
                         graded_date, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iisddssssss", 
                    $submissionId, $submission['assignment_id'], $submission['student_id'],
                    $finalMarks, $maxMarks, $grade, $feedback, $privateNotes, 
                    $gradingRubric, $gradedBy, $status);
            }
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error grading submission: " . mysqli_error($conn));
            }
            
            // Update submission status
            $updateSub = "UPDATE student_submissions SET status = 'graded' WHERE submission_id = ?";
            $stmtUpdateSub = mysqli_prepare($conn, $updateSub);
            mysqli_stmt_bind_param($stmtUpdateSub, "i", $submissionId);
            mysqli_stmt_execute($stmtUpdateSub);
            
            // Update assignment graded count
            $updateAssignment = "UPDATE assignments 
                                SET graded_submissions = graded_submissions + 1 
                                WHERE assignment_id = ? 
                                AND graded_submissions < total_submissions";
            $stmtUpdateAssign = mysqli_prepare($conn, $updateAssignment);
            mysqli_stmt_bind_param($stmtUpdateAssign, "i", $submission['assignment_id']);
            mysqli_stmt_execute($stmtUpdateAssign);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Submission graded successfully!',
                'final_marks' => $finalMarks,
                'grade' => $grade,
                'percentage' => round($percentage, 2)
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Publish grade to student
    else if ($action == "publish_grade") {
        $gradeId = mysqli_real_escape_string($conn, $_POST["grade_id"]);
        
        $sql = "UPDATE assignment_grades 
                SET status = 'published', published_date = NOW() 
                WHERE grade_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $gradeId);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update submission status
            $updateSub = "UPDATE student_submissions s
                         JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                         SET s.status = 'returned'
                         WHERE ag.grade_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateSub);
            mysqli_stmt_bind_param($stmtUpdate, "i", $gradeId);
            mysqli_stmt_execute($stmtUpdate);
            
            echo json_encode(['status' => 'success', 'message' => 'Grade published to student!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Bulk publish grades
    else if ($action == "bulk_publish_grades") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Publish all finalized grades
            $sql = "UPDATE assignment_grades 
                    SET status = 'published', published_date = NOW() 
                    WHERE assignment_id = ? AND status = 'finalized'";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $assignmentId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error publishing grades: " . mysqli_error($conn));
            }
            
            $publishedCount = mysqli_affected_rows($conn);
            
            // Update submission status
            $updateSub = "UPDATE student_submissions s
                         JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                         SET s.status = 'returned'
                         WHERE ag.assignment_id = ? AND ag.status = 'published'";
            $stmtUpdate = mysqli_prepare($conn, $updateSub);
            mysqli_stmt_bind_param($stmtUpdate, "i", $assignmentId);
            mysqli_stmt_execute($stmtUpdate);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "$publishedCount grades published!"
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Request resubmission
    else if ($action == "request_resubmission") {
        $submissionId = mysqli_real_escape_string($conn, $_POST["submission_id"]);
        $feedback = mysqli_real_escape_string($conn, $_POST["feedback"]);
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Update submission status
            $sql = "UPDATE student_submissions 
                    SET status = 'resubmit_required' 
                    WHERE submission_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $submissionId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error requesting resubmission: " . mysqli_error($conn));
            }
            
            // Add comment with feedback
            $commentSql = "INSERT INTO submission_comments 
                          (submission_id, commenter_id, commenter_type, comment_text) 
                          VALUES (?, ?, 'teacher', ?)";
            $stmtComment = mysqli_prepare($conn, $commentSql);
            mysqli_stmt_bind_param($stmtComment, "iss", $submissionId, $teacherId, $feedback);
            mysqli_stmt_execute($stmtComment);
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Resubmission requested!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Fetch grades for assignment
    else if ($action == "fetch_grades") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"] ?? '');
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? '');
        
        $sql = "SELECT ag.*, s.fname, s.lname, s.admission_no, s.email,
                       sub.submission_date, sub.is_late, sub.late_penalty_applied,
                       t.fname as graded_by_fname, t.lname as graded_by_lname
                FROM assignment_grades ag
                JOIN students s ON ag.student_id = s.id
                JOIN student_submissions sub ON ag.submission_id = sub.submission_id
                LEFT JOIN teachers t ON ag.graded_by = t.id
                WHERE 1=1";
        
        if (!empty($assignmentId)) {
            $sql .= " AND ag.assignment_id = '$assignmentId'";
        }
        if (!empty($status)) {
            $sql .= " AND ag.status = '$status'";
        }
        
        $sql .= " ORDER BY ag.graded_date DESC";
        
        $result = mysqli_query($conn, $sql);
        $grades = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $grades[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $grades]);
    }
    
    // Fetch ungraded submissions
    else if ($action == "fetch_ungraded_submissions") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        $sql = "SELECT s.*, st.fname, st.lname, st.admission_no, st.email,
                       a.max_marks, a.title as assignment_title
                FROM student_submissions s
                JOIN students st ON s.student_id = st.id
                JOIN assignments a ON s.assignment_id = a.assignment_id
                LEFT JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                WHERE s.assignment_id = ? AND s.is_latest = 1 AND ag.grade_id IS NULL
                ORDER BY s.submission_date ASC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $submissions = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Get attachment count
            $countSql = "SELECT COUNT(*) as count FROM submission_attachments WHERE submission_id = ?";
            $stmtCount = mysqli_prepare($conn, $countSql);
            mysqli_stmt_bind_param($stmtCount, "i", $row['submission_id']);
            mysqli_stmt_execute($stmtCount);
            $resultCount = mysqli_stmt_get_result($stmtCount);
            $countData = mysqli_fetch_assoc($resultCount);
            $row['attachment_count'] = $countData['count'];
            
            $submissions[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $submissions]);
    }
    
    // Bulk grade submissions (quick grading)
    else if ($action == "bulk_grade") {
        $grades = json_decode($_POST["grades"], true);
        $gradedBy = mysqli_real_escape_string($conn, $_POST["graded_by"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            $successCount = 0;
            
            foreach ($grades as $gradeData) {
                $submissionId = $gradeData['submission_id'];
                $marksObtained = $gradeData['marks_obtained'];
                $maxMarks = $gradeData['max_marks'];
                $feedback = $gradeData['feedback'] ?? '';
                
                // Get submission details
                $getSub = "SELECT assignment_id, student_id, late_penalty_applied 
                          FROM student_submissions WHERE submission_id = ?";
                $stmtGet = mysqli_prepare($conn, $getSub);
                mysqli_stmt_bind_param($stmtGet, "i", $submissionId);
                mysqli_stmt_execute($stmtGet);
                $result = mysqli_stmt_get_result($stmtGet);
                $submission = mysqli_fetch_assoc($result);
                
                // Apply late penalty
                $finalMarks = $marksObtained;
                if ($submission['late_penalty_applied'] > 0) {
                    $penalty = ($marksObtained * $submission['late_penalty_applied']) / 100;
                    $finalMarks = max(0, $marksObtained - $penalty);
                }
                
                // Calculate grade
                $percentage = ($finalMarks / $maxMarks) * 100;
                $grade = getGradeFromPercentage($conn, $percentage);
                
                // Insert grade
                $sql = "INSERT INTO assignment_grades 
                        (submission_id, assignment_id, student_id, marks_obtained, max_marks, 
                         grade, feedback, graded_by, graded_date, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'finalized')";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iisddss", 
                    $submissionId, $submission['assignment_id'], $submission['student_id'],
                    $finalMarks, $maxMarks, $grade, $feedback, $gradedBy);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Update submission status
                    $updateSub = "UPDATE student_submissions SET status = 'graded' WHERE submission_id = ?";
                    $stmtUpdate = mysqli_prepare($conn, $updateSub);
                    mysqli_stmt_bind_param($stmtUpdate, "i", $submissionId);
                    mysqli_stmt_execute($stmtUpdate);
                    
                    $successCount++;
                }
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "$successCount submissions graded!"
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Get grading statistics
    else if ($action == "get_grading_statistics") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        $sql = "SELECT 
                COUNT(DISTINCT s.student_id) as total_submissions,
                COUNT(DISTINCT ag.grade_id) as graded_count,
                COUNT(DISTINCT CASE WHEN ag.status = 'published' THEN ag.grade_id END) as published_count,
                AVG(ag.marks_obtained) as average_marks,
                MAX(ag.marks_obtained) as highest_marks,
                MIN(ag.marks_obtained) as lowest_marks,
                AVG(ag.percentage) as average_percentage,
                COUNT(CASE WHEN ag.percentage >= 40 THEN 1 END) as pass_count,
                COUNT(CASE WHEN ag.percentage < 40 THEN 1 END) as fail_count
                FROM student_submissions s
                LEFT JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                WHERE s.assignment_id = ? AND s.is_latest = 1";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats = mysqli_fetch_assoc($result);
        
        // Grade distribution
        $distSql = "SELECT ag.grade, COUNT(*) as count
                   FROM assignment_grades ag
                   JOIN student_submissions s ON ag.submission_id = s.submission_id
                   WHERE ag.assignment_id = ? AND s.is_latest = 1
                   GROUP BY ag.grade
                   ORDER BY ag.grade";
        
        $stmtDist = mysqli_prepare($conn, $distSql);
        mysqli_stmt_bind_param($stmtDist, "i", $assignmentId);
        mysqli_stmt_execute($stmtDist);
        $resultDist = mysqli_stmt_get_result($stmtDist);
        $distribution = [];
        while ($row = mysqli_fetch_assoc($resultDist)) {
            $distribution[] = $row;
        }
        
        $stats['grade_distribution'] = $distribution;
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
}

// Helper function to get grade from percentage
function getGradeFromPercentage($conn, $percentage) {
    $sql = "SELECT grade FROM grade_scale 
            WHERE $percentage >= min_percentage AND $percentage <= max_percentage 
            AND is_active = 1 
            ORDER BY min_percentage DESC LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['grade'];
    }
    
    return 'F';
}

mysqli_close($conn);
?>
