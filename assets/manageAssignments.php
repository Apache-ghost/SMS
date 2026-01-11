<?php
// REQ-ACD-005: Assignment Management - Create, publish, and manage assignments
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create new assignment
    if ($action == "create_assignment") {
        $assignmentCode = mysqli_real_escape_string($conn, $_POST["assignment_code"]);
        $title = mysqli_real_escape_string($conn, $_POST["title"]);
        $description = mysqli_real_escape_string($conn, $_POST["description"] ?? '');
        $instructions = mysqli_real_escape_string($conn, $_POST["instructions"] ?? '');
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        $assignmentType = mysqli_real_escape_string($conn, $_POST["assignment_type"] ?? 'homework');
        $maxMarks = mysqli_real_escape_string($conn, $_POST["max_marks"] ?? 100);
        $weightage = mysqli_real_escape_string($conn, $_POST["weightage"] ?? 0);
        $difficultyLevel = mysqli_real_escape_string($conn, $_POST["difficulty_level"] ?? 'medium');
        $estimatedDuration = mysqli_real_escape_string($conn, $_POST["estimated_duration"] ?? null);
        $assignedDate = mysqli_real_escape_string($conn, $_POST["assigned_date"]);
        $dueDate = mysqli_real_escape_string($conn, $_POST["due_date"]);
        $lateSubmissionAllowed = $_POST["late_submission_allowed"] ?? 1;
        $latePenaltyPerDay = mysqli_real_escape_string($conn, $_POST["late_penalty_per_day"] ?? 5);
        $maxLateDays = mysqli_real_escape_string($conn, $_POST["max_late_days"] ?? 3);
        $allowResubmission = $_POST["allow_resubmission"] ?? 0;
        $maxResubmissions = mysqli_real_escape_string($conn, $_POST["max_resubmissions"] ?? 1);
        $submissionFormat = mysqli_real_escape_string($conn, $_POST["submission_format"] ?? '');
        $maxFileSize = mysqli_real_escape_string($conn, $_POST["max_file_size"] ?? 10485760);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? 'draft');
        $createdBy = mysqli_real_escape_string($conn, $_POST["created_by"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            $sql = "INSERT INTO assignments 
                    (assignment_code, title, description, instructions, course_code, class, section,
                     teacher_id, assignment_type, max_marks, weightage, difficulty_level, 
                     estimated_duration, assigned_date, due_date, late_submission_allowed, 
                     late_penalty_per_day, max_late_days, allow_resubmission, max_resubmissions,
                     submission_format, max_file_size, academic_year, semester, status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssissddsisddiiiisiss", 
                $assignmentCode, $title, $description, $instructions, $courseCode, $class, 
                $section, $teacherId, $assignmentType, $maxMarks, $weightage, $difficultyLevel,
                $estimatedDuration, $assignedDate, $dueDate, $lateSubmissionAllowed, 
                $latePenaltyPerDay, $maxLateDays, $allowResubmission, $maxResubmissions,
                $submissionFormat, $maxFileSize, $academicYear, $semester, $status, $createdBy);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating assignment: " . mysqli_error($conn));
            }
            
            $assignmentId = mysqli_insert_id($conn);
            
            // Initialize statistics
            $statSql = "INSERT INTO assignment_statistics (assignment_id) VALUES (?)";
            $stmtStat = mysqli_prepare($conn, $statSql);
            mysqli_stmt_bind_param($stmtStat, "i", $assignmentId);
            mysqli_stmt_execute($stmtStat);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Assignment created successfully!',
                'assignment_id' => $assignmentId
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Publish assignment
    else if ($action == "publish_assignment") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            $sql = "UPDATE assignments 
                    SET status = 'published', published_date = NOW() 
                    WHERE assignment_id = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $assignmentId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error publishing assignment: " . mysqli_error($conn));
            }
            
            // Get assignment details for notification
            $getAssignment = "SELECT class, section, course_code, title, due_date FROM assignments WHERE assignment_id = ?";
            $stmtGet = mysqli_prepare($conn, $getAssignment);
            mysqli_stmt_bind_param($stmtGet, "i", $assignmentId);
            mysqli_stmt_execute($stmtGet);
            $result = mysqli_stmt_get_result($stmtGet);
            $assignment = mysqli_fetch_assoc($result);
            
            // Get all students in the class
            $getStudents = "SELECT id FROM students WHERE class = ? AND section = ? AND enrollment_status = 'active'";
            $stmtStudents = mysqli_prepare($conn, $getStudents);
            mysqli_stmt_bind_param($stmtStudents, "is", $assignment['class'], $assignment['section']);
            mysqli_stmt_execute($stmtStudents);
            $resultStudents = mysqli_stmt_get_result($stmtStudents);
            
            $totalStudents = mysqli_num_rows($resultStudents);
            
            // Update statistics
            $updateStat = "UPDATE assignment_statistics SET total_students = ? WHERE assignment_id = ?";
            $stmtUpdateStat = mysqli_prepare($conn, $updateStat);
            mysqli_stmt_bind_param($stmtUpdateStat, "ii", $totalStudents, $assignmentId);
            mysqli_stmt_execute($stmtUpdateStat);
            
            // Create reminders for students
            while ($student = mysqli_fetch_assoc($resultStudents)) {
                $reminderSql = "INSERT INTO assignment_reminders 
                               (assignment_id, reminder_type, recipient_type, recipient_id, 
                                reminder_date, message) 
                               VALUES (?, 'due_soon', 'student', ?, 
                                       DATE_SUB(?, INTERVAL 1 DAY), 
                                       'Assignment due tomorrow')";
                $stmtReminder = mysqli_prepare($conn, $reminderSql);
                mysqli_stmt_bind_param($stmtReminder, "iss", $assignmentId, $student['id'], $assignment['due_date']);
                mysqli_stmt_execute($stmtReminder);
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "Assignment published to $totalStudents students!"
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Update assignment
    else if ($action == "update_assignment") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        $title = mysqli_real_escape_string($conn, $_POST["title"]);
        $description = mysqli_real_escape_string($conn, $_POST["description"]);
        $instructions = mysqli_real_escape_string($conn, $_POST["instructions"]);
        $dueDate = mysqli_real_escape_string($conn, $_POST["due_date"]);
        $maxMarks = mysqli_real_escape_string($conn, $_POST["max_marks"]);
        
        $sql = "UPDATE assignments 
                SET title = ?, description = ?, instructions = ?, due_date = ?, max_marks = ?,
                    updated_at = NOW()
                WHERE assignment_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssdi", $title, $description, $instructions, $dueDate, $maxMarks, $assignmentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Close assignment (no more submissions)
    else if ($action == "close_assignment") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        $sql = "UPDATE assignments SET status = 'closed' WHERE assignment_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment closed!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Delete assignment
    else if ($action == "delete_assignment") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        $sql = "DELETE FROM assignments WHERE assignment_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment deleted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Upload assignment attachment
    else if ($action == "upload_attachment") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        $attachmentType = mysqli_real_escape_string($conn, $_POST["attachment_type"] ?? 'reference');
        $description = mysqli_real_escape_string($conn, $_POST["description"] ?? '');
        $uploadedBy = mysqli_real_escape_string($conn, $_POST["uploaded_by"]);
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $uploadDir = "../adminUploads/assignments/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = basename($_FILES['file']['name']);
            $fileSize = $_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $newFileName = "assign_" . $assignmentId . "_" . time() . "." . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $sql = "INSERT INTO assignment_attachments 
                        (assignment_id, file_name, file_path, file_type, file_size, 
                         attachment_type, description, uploaded_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "isssiss", 
                    $assignmentId, $fileName, $filePath, $fileType, $fileSize,
                    $attachmentType, $description, $uploadedBy);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'File uploaded!',
                        'file_path' => $filePath
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
    
    // Fetch assignments
    else if ($action == "fetch_assignments") {
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"] ?? '');
        $class = mysqli_real_escape_string($conn, $_POST["class"] ?? '');
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"] ?? '');
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        
        $sql = "SELECT a.*, c.course_name, t.fname as teacher_fname, t.lname as teacher_lname,
                       stat.total_students, stat.total_submissions, stat.pending_grading
                FROM assignments a 
                LEFT JOIN courses c ON a.course_code = c.course_code 
                LEFT JOIN teachers t ON a.teacher_id = t.id 
                LEFT JOIN assignment_statistics stat ON a.assignment_id = stat.assignment_id
                WHERE 1=1";
        
        if (!empty($courseCode)) {
            $sql .= " AND a.course_code = '$courseCode'";
        }
        if (!empty($class)) {
            $sql .= " AND a.class = '$class'";
        }
        if (!empty($teacherId)) {
            $sql .= " AND a.teacher_id = '$teacherId'";
        }
        if (!empty($status)) {
            $sql .= " AND a.status = '$status'";
        }
        if (!empty($academicYear)) {
            $sql .= " AND a.academic_year = '$academicYear'";
        }
        
        $sql .= " ORDER BY a.due_date DESC, a.created_at DESC";
        
        $result = mysqli_query($conn, $sql);
        $assignments = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Check if overdue
            $row['is_overdue'] = (strtotime($row['due_date']) < time() && $row['status'] == 'published');
            $assignments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }
    
    // Fetch single assignment details
    else if ($action == "fetch_assignment_details") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        $sql = "SELECT a.*, c.course_name, t.fname as teacher_fname, t.lname as teacher_lname
                FROM assignments a 
                LEFT JOIN courses c ON a.course_code = c.course_code 
                LEFT JOIN teachers t ON a.teacher_id = t.id 
                WHERE a.assignment_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $assignment = mysqli_fetch_assoc($result);
        
        // Get attachments
        $attachSql = "SELECT * FROM assignment_attachments WHERE assignment_id = ?";
        $stmtAttach = mysqli_prepare($conn, $attachSql);
        mysqli_stmt_bind_param($stmtAttach, "i", $assignmentId);
        mysqli_stmt_execute($stmtAttach);
        $resultAttach = mysqli_stmt_get_result($stmtAttach);
        $attachments = [];
        while ($row = mysqli_fetch_assoc($resultAttach)) {
            $attachments[] = $row;
        }
        
        $assignment['attachments'] = $attachments;
        
        echo json_encode(['status' => 'success', 'data' => $assignment]);
    }
    
    // Calculate assignment statistics
    else if ($action == "calculate_statistics") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get assignment due date
            $getAssignment = "SELECT due_date, class, section FROM assignments WHERE assignment_id = ?";
            $stmtGet = mysqli_prepare($conn, $getAssignment);
            mysqli_stmt_bind_param($stmtGet, "i", $assignmentId);
            mysqli_stmt_execute($stmtGet);
            $result = mysqli_stmt_get_result($stmtGet);
            $assignment = mysqli_fetch_assoc($result);
            $dueDate = $assignment['due_date'];
            
            // Count total students
            $countStudents = "SELECT COUNT(*) as total FROM students 
                             WHERE class = ? AND section = ? AND enrollment_status = 'active'";
            $stmtCount = mysqli_prepare($conn, $countStudents);
            mysqli_stmt_bind_param($stmtCount, "is", $assignment['class'], $assignment['section']);
            mysqli_stmt_execute($stmtCount);
            $resultCount = mysqli_stmt_get_result($stmtCount);
            $countData = mysqli_fetch_assoc($resultCount);
            $totalStudents = $countData['total'];
            
            // Count submissions
            $submissionStats = "SELECT 
                               COUNT(DISTINCT student_id) as total_submissions,
                               COUNT(CASE WHEN is_late = 0 THEN 1 END) as on_time,
                               COUNT(CASE WHEN is_late = 1 THEN 1 END) as late
                               FROM student_submissions 
                               WHERE assignment_id = ? AND is_latest = 1";
            
            $stmtSub = mysqli_prepare($conn, $submissionStats);
            mysqli_stmt_bind_param($stmtSub, "i", $assignmentId);
            mysqli_stmt_execute($stmtSub);
            $resultSub = mysqli_stmt_get_result($stmtSub);
            $subStats = mysqli_fetch_assoc($resultSub);
            
            // Count graded submissions
            $gradeStats = "SELECT 
                          COUNT(*) as graded_count,
                          MAX(marks_obtained) as highest,
                          MIN(marks_obtained) as lowest,
                          AVG(marks_obtained) as average,
                          (COUNT(CASE WHEN percentage >= 40 THEN 1 END) / COUNT(*)) * 100 as pass_rate
                          FROM assignment_grades 
                          WHERE assignment_id = ? AND status = 'published'";
            
            $stmtGrade = mysqli_prepare($conn, $gradeStats);
            mysqli_stmt_bind_param($stmtGrade, "i", $assignmentId);
            mysqli_stmt_execute($stmtGrade);
            $resultGrade = mysqli_stmt_get_result($stmtGrade);
            $gradeData = mysqli_fetch_assoc($resultGrade);
            
            $pendingSubmissions = $totalStudents - $subStats['total_submissions'];
            $pendingGrading = $subStats['total_submissions'] - $gradeData['graded_count'];
            
            // Update statistics
            $updateStat = "UPDATE assignment_statistics 
                          SET total_students = ?,
                              total_submissions = ?,
                              on_time_submissions = ?,
                              late_submissions = ?,
                              pending_submissions = ?,
                              graded_count = ?,
                              pending_grading = ?,
                              highest_marks = ?,
                              lowest_marks = ?,
                              average_marks = ?,
                              pass_rate = ?,
                              last_calculated = NOW()
                          WHERE assignment_id = ?";
            
            $stmtUpdate = mysqli_prepare($conn, $updateStat);
            mysqli_stmt_bind_param($stmtUpdate, "iiiiiidddddi",
                $totalStudents, $subStats['total_submissions'], $subStats['on_time'],
                $subStats['late'], $pendingSubmissions, $gradeData['graded_count'],
                $pendingGrading, $gradeData['highest'], $gradeData['lowest'],
                $gradeData['average'], $gradeData['pass_rate'], $assignmentId);
            
            mysqli_stmt_execute($stmtUpdate);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success',
                'message' => 'Statistics calculated!',
                'data' => [
                    'total_students' => $totalStudents,
                    'total_submissions' => $subStats['total_submissions'],
                    'pending_submissions' => $pendingSubmissions,
                    'graded_count' => $gradeData['graded_count'],
                    'pending_grading' => $pendingGrading
                ]
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Fetch submissions for grading
    else if ($action == "fetch_submissions") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"] ?? $_GET["assignment_id"]);
        
        $sql = "SELECT sub.*, CONCAT(s.fname, ' ', s.lname) as student_name, s.id as student_id
                FROM assignment_submissions sub
                JOIN students s ON sub.student_id = s.id
                WHERE sub.assignment_id = ?
                ORDER BY sub.submitted_at DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $submissions = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $submissions[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'submissions' => $submissions]);
    }
    
    // Fetch assignment details
    else if ($action == "fetch_assignment_details") {
        $assignmentId = mysqli_real_escape_string($conn, $_POST["assignment_id"] ?? $_GET["assignment_id"]);
        
        $sql = "SELECT * FROM assignments WHERE assignment_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $assignmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($assignment = mysqli_fetch_assoc($result)) {
            echo json_encode(['status' => 'success', 'assignment' => $assignment]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Assignment not found']);
        }
    }
    
    // Grade submission
    else if ($action == "grade_submission") {
        $submissionId = mysqli_real_escape_string($conn, $_POST["submission_id"]);
        $marksObtained = mysqli_real_escape_string($conn, $_POST["marks_obtained"]);
        $feedback = mysqli_real_escape_string($conn, $_POST["feedback"] ?? '');
        $gradedBy = $_SESSION['uid'] ?? $_POST["graded_by"];
        
        mysqli_begin_transaction($conn);
        
        try {
            // Update submission
            $sql = "UPDATE assignment_submissions 
                    SET marks_obtained = ?, feedback = ?, graded_by = ?, 
                        graded_at = NOW(), status = 'graded'
                    WHERE submission_id = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "dssi", $marksObtained, $feedback, $gradedBy, $submissionId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error grading submission: " . mysqli_error($conn));
            }
            
            // Get assignment ID to update graded count
            $getAssignmentSql = "SELECT assignment_id FROM assignment_submissions WHERE submission_id = ?";
            $stmtGet = mysqli_prepare($conn, $getAssignmentSql);
            mysqli_stmt_bind_param($stmtGet, "i", $submissionId);
            mysqli_stmt_execute($stmtGet);
            $resultGet = mysqli_stmt_get_result($stmtGet);
            $subData = mysqli_fetch_assoc($resultGet);
            
            // Update graded_submissions count in assignments table
            $updateCountSql = "UPDATE assignments SET graded_submissions = graded_submissions + 1 
                              WHERE assignment_id = ?";
            $countStmt = mysqli_prepare($conn, $updateCountSql);
            mysqli_stmt_bind_param($countStmt, "i", $subData['assignment_id']);
            mysqli_stmt_execute($countStmt);
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Submission graded successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

mysqli_close($conn);
?>
