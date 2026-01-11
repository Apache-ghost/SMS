<?php
// REQ-ACD-005: Student Submission Management - Submit assignments and view grades
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Submit assignment
    if ($action == "submit_assignment") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $submissionText = mysqli_real_escape_string($conn, $_POST["submission_text"] ?? '');
        $submissionMethod = mysqli_real_escape_string($conn, $_POST["submission_method"] ?? 'online');
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check if assignment exists and is open
            $checkSql = "SELECT * FROM assignments WHERE assignment_id = ?";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "i", $assignmentId);
            mysqli_stmt_execute($stmtCheck);
            $result = mysqli_stmt_get_result($stmtCheck);
            $assignment = mysqli_fetch_assoc($result);
            
            if (!$assignment) {
                throw new Exception("Assignment not found!");
            }
            
            if ($assignment['status'] != 'published') {
                throw new Exception("Assignment is not open for submissions!");
            }
            
            // Check if past due date
            $dueDate = strtotime($assignment['due_date']);
            $currentTime = time();
            $isLate = ($currentTime > $dueDate) ? 1 : 0;
            $daysLate = 0;
            $latePenalty = 0;
            
            if ($isLate) {
                if (!$assignment['late_submission_allowed']) {
                    throw new Exception("Late submissions are not allowed!");
                }
                
                $daysLate = ceil(($currentTime - $dueDate) / 86400);
                
                if ($daysLate > $assignment['max_late_days']) {
                    throw new Exception("Submission deadline has passed!");
                }
                
                $latePenalty = $daysLate * $assignment['late_penalty_per_day'];
            }
            
            // Check for existing submission
            $checkExisting = "SELECT * FROM student_submissions 
                             WHERE assignment_id = ? AND student_id = ? AND is_latest = 1";
            $stmtExisting = mysqli_prepare($conn, $checkExisting);
            mysqli_stmt_bind_param($stmtExisting, "is", $assignmentId, $studentId);
            mysqli_stmt_execute($stmtExisting);
            $resultExisting = mysqli_stmt_get_result($stmtExisting);
            $existingSubmission = mysqli_fetch_assoc($resultExisting);
            
            $submissionNumber = 1;
            $version = 1;
            
            if ($existingSubmission) {
                if (!$assignment['allow_resubmission']) {
                    throw new Exception("Resubmission is not allowed for this assignment!");
                }
                
                if ($existingSubmission['submission_number'] >= $assignment['max_resubmissions']) {
                    throw new Exception("Maximum resubmission limit reached!");
                }
                
                $submissionNumber = $existingSubmission['submission_number'] + 1;
                $version = $existingSubmission['version'] + 1;
                
                // Mark previous submission as not latest
                $updateOld = "UPDATE student_submissions SET is_latest = 0 
                             WHERE submission_id = ?";
                $stmtUpdateOld = mysqli_prepare($conn, $updateOld);
                mysqli_stmt_bind_param($stmtUpdateOld, "i", $existingSubmission['submission_id']);
                mysqli_stmt_execute($stmtUpdateOld);
            }
            
            // Insert new submission
            $insertSql = "INSERT INTO student_submissions 
                         (assignment_id, student_id, submission_number, submission_date, 
                          submission_text, is_late, days_late, late_penalty_applied, 
                          ip_address, submission_method, version, is_latest) 
                         VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = mysqli_prepare($conn, $insertSql);
            mysqli_stmt_bind_param($stmt, "isiiddssi", 
                $assignmentId, $studentId, $submissionNumber, $submissionText,
                $isLate, $daysLate, $latePenalty, $ipAddress, $submissionMethod, $version);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error submitting assignment: " . mysqli_error($conn));
            }
            
            $submissionId = mysqli_insert_id($conn);
            
            // Update assignment submission count
            $updateCount = "UPDATE assignments 
                           SET total_submissions = total_submissions + 1 
                           WHERE assignment_id = ?";
            $stmtCount = mysqli_prepare($conn, $updateCount);
            mysqli_stmt_bind_param($stmtCount, "i", $assignmentId);
            mysqli_stmt_execute($stmtCount);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Assignment submitted successfully!',
                'submission_id' => $submissionId,
                'is_late' => $isLate,
                'late_penalty' => $latePenalty
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Upload submission attachment
    else if ($action == "upload_submission_file") {
        $submissionId = mysqli_real_escape_string($conn, $_POST["submission_id"]);
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            // Get assignment ID to check file size limit
            $getSubmission = "SELECT s.assignment_id FROM student_submissions s WHERE s.submission_id = ?";
            $stmtGet = mysqli_prepare($conn, $getSubmission);
            mysqli_stmt_bind_param($stmtGet, "i", $submissionId);
            mysqli_stmt_execute($stmtGet);
            $result = mysqli_stmt_get_result($stmtGet);
            $submission = mysqli_fetch_assoc($result);
            
            $getAssignment = "SELECT max_file_size, submission_format FROM assignments WHERE assignment_id = ?";
            $stmtAssign = mysqli_prepare($conn, $getAssignment);
            mysqli_stmt_bind_param($stmtAssign, "i", $submission['assignment_id']);
            mysqli_stmt_execute($stmtAssign);
            $resultAssign = mysqli_stmt_get_result($stmtAssign);
            $assignment = mysqli_fetch_assoc($resultAssign);
            
            $uploadDir = "../studentUploads/submissions/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = basename($_FILES['file']['name']);
            $fileSize = $_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Check file size
            if ($fileSize > $assignment['max_file_size']) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'File size exceeds maximum allowed size!'
                ]);
                exit();
            }
            
            // Check file format if specified
            if (!empty($assignment['submission_format'])) {
                $allowedFormats = array_map('trim', explode(',', strtolower($assignment['submission_format'])));
                if (!in_array($fileExtension, $allowedFormats)) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'Invalid file format! Allowed: ' . $assignment['submission_format']
                    ]);
                    exit();
                }
            }
            
            // Generate unique filename
            $newFileName = "sub_" . $submissionId . "_" . time() . "." . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $sql = "INSERT INTO submission_attachments 
                        (submission_id, file_name, file_path, file_type, file_size) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "isssi", 
                    $submissionId, $fileName, $filePath, $fileType, $fileSize);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'File uploaded!',
                        'file_path' => $filePath,
                        'attachment_id' => mysqli_insert_id($conn)
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File upload failed!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No file selected!']);
        }
    }
    
    // Fetch student submissions
    else if ($action == "fetch_submissions") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"] ?? '');
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"] ?? '');
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? '');
        
        $sql = "SELECT s.*, st.fname, st.lname, st.admission_no, st.class, st.section,
                       a.title as assignment_title, a.max_marks, a.course_code,
                       ag.marks_obtained, ag.grade, ag.feedback, ag.status as grade_status
                FROM student_submissions s 
                JOIN students st ON s.student_id = st.id 
                JOIN assignments a ON s.assignment_id = a.assignment_id 
                LEFT JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                WHERE 1=1";
        
        if (!empty($assignmentId)) {
            $sql .= " AND s.assignment_id = '$assignmentId'";
        }
        if (!empty($studentId)) {
            $sql .= " AND s.student_id = '$studentId'";
        }
        if (!empty($status)) {
            $sql .= " AND s.status = '$status'";
        }
        
        $sql .= " ORDER BY s.submission_date DESC";
        
        $result = mysqli_query($conn, $sql);
        $submissions = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Get attachments
            $attachSql = "SELECT * FROM submission_attachments WHERE submission_id = ?";
            $stmtAttach = mysqli_prepare($conn, $attachSql);
            mysqli_stmt_bind_param($stmtAttach, "i", $row['submission_id']);
            mysqli_stmt_execute($stmtAttach);
            $resultAttach = mysqli_stmt_get_result($stmtAttach);
            $attachments = [];
            while ($attach = mysqli_fetch_assoc($resultAttach)) {
                $attachments[] = $attach;
            }
            $row['attachments'] = $attachments;
            
            $submissions[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $submissions]);
    }
    
    // Fetch submission details
    else if ($action == "fetch_submission_details") {
        $submissionId = mysqli_real_escape_string($conn, $_POST["submission_id"]);
        
        $sql = "SELECT s.*, st.fname, st.lname, st.admission_no, st.email,
                       a.title as assignment_title, a.description, a.max_marks, 
                       a.course_code, c.course_name,
                       ag.marks_obtained, ag.grade, ag.feedback, ag.grading_rubric,
                       ag.status as grade_status, ag.graded_date, 
                       t.fname as graded_by_fname, t.lname as graded_by_lname
                FROM student_submissions s 
                JOIN students st ON s.student_id = st.id 
                JOIN assignments a ON s.assignment_id = a.assignment_id 
                LEFT JOIN courses c ON a.course_code = c.course_code
                LEFT JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                LEFT JOIN teachers t ON ag.graded_by = t.id
                WHERE s.submission_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $submissionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $submission = mysqli_fetch_assoc($result);
        
        // Get attachments
        $attachSql = "SELECT * FROM submission_attachments WHERE submission_id = ?";
        $stmtAttach = mysqli_prepare($conn, $attachSql);
        mysqli_stmt_bind_param($stmtAttach, "i", $submissionId);
        mysqli_stmt_execute($stmtAttach);
        $resultAttach = mysqli_stmt_get_result($stmtAttach);
        $attachments = [];
        while ($row = mysqli_fetch_assoc($resultAttach)) {
            $attachments[] = $row;
        }
        $submission['attachments'] = $attachments;
        
        // Get comments
        $commentSql = "SELECT c.*, 
                              CASE 
                                WHEN c.commenter_type = 'teacher' THEN CONCAT(t.fname, ' ', t.lname)
                                WHEN c.commenter_type = 'student' THEN CONCAT(s.fname, ' ', s.lname)
                                ELSE 'Admin'
                              END as commenter_name
                       FROM submission_comments c
                       LEFT JOIN teachers t ON c.commenter_id = t.id AND c.commenter_type = 'teacher'
                       LEFT JOIN students s ON c.commenter_id = s.id AND c.commenter_type = 'student'
                       WHERE c.submission_id = ?
                       ORDER BY c.commented_at ASC";
        
        $stmtComment = mysqli_prepare($conn, $commentSql);
        mysqli_stmt_bind_param($stmtComment, "i", $submissionId);
        mysqli_stmt_execute($stmtComment);
        $resultComment = mysqli_stmt_get_result($stmtComment);
        $comments = [];
        while ($row = mysqli_fetch_assoc($resultComment)) {
            $comments[] = $row;
        }
        $submission['comments'] = $comments;
        
        echo json_encode(['status' => 'success', 'data' => $submission]);
    }
    
    // Add comment to submission
    else if ($action == "add_comment") {
        $submissionId = mysqli_real_escape_string($conn, $_POST["submission_id"]);
        $commenterId = mysqli_real_escape_string($conn, $_POST["commenter_id"]);
        $commenterType = mysqli_real_escape_string($conn, $_POST["commenter_type"]);
        $commentText = mysqli_real_escape_string($conn, $_POST["comment_text"]);
        $isPrivate = $_POST["is_private"] ?? 0;
        
        $sql = "INSERT INTO submission_comments 
                (submission_id, commenter_id, commenter_type, comment_text, is_private) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssi", 
            $submissionId, $commenterId, $commenterType, $commentText, $isPrivate);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Comment added!',
                'comment_id' => mysqli_insert_id($conn)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Fetch student's assignment list
    else if ($action == "fetch_student_assignments") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        
        // Get student's class and section
        $getStudent = "SELECT class, section FROM students WHERE id = ?";
        $stmtStudent = mysqli_prepare($conn, $getStudent);
        mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
        mysqli_stmt_execute($stmtStudent);
        $resultStudent = mysqli_stmt_get_result($stmtStudent);
        $student = mysqli_fetch_assoc($resultStudent);
        
        $sql = "SELECT a.*, c.course_name,
                       s.submission_id, s.submission_date, s.is_late, s.status as submission_status,
                       ag.marks_obtained, ag.grade, ag.status as grade_status
                FROM assignments a
                LEFT JOIN courses c ON a.course_code = c.course_code
                LEFT JOIN student_submissions s ON a.assignment_id = s.assignment_id 
                                                 AND s.student_id = ? AND s.is_latest = 1
                LEFT JOIN assignment_grades ag ON s.submission_id = ag.submission_id
                WHERE a.class = ? AND a.section = ? AND a.status = 'published'";
        
        if (!empty($academicYear)) {
            $sql .= " AND a.academic_year = '$academicYear'";
        }
        
        $sql .= " ORDER BY a.due_date DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sis", $studentId, $student['class'], $student['section']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $assignments = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine assignment status for student
            if ($row['submission_id']) {
                $row['student_status'] = 'submitted';
                if ($row['grade_status'] == 'published') {
                    $row['student_status'] = 'graded';
                }
            } else {
                $row['student_status'] = (strtotime($row['due_date']) < time()) ? 'overdue' : 'pending';
            }
            
            $assignments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }
}

mysqli_close($conn);
?>
