<?php
// Assignment Submission Handler for Students
// Handles: Submit assignment, View submissions, Check status

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = array('status' => 'error', 'message' => 'Invalid request');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Check if student is logged in
    if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
        $response['message'] = 'Unauthorized - Student login required';
        echo json_encode($response);
        exit();
    }
    
    switch ($action) {
        case 'submit_assignment':
            submitAssignment($conn);
            break;
            
        case 'fetch_student_submissions':
            fetchStudentSubmissions($conn);
            break;
            
        default:
            echo json_encode($response);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if (!isset($_SESSION['uid'])) {
        $response['message'] = 'Unauthorized';
        echo json_encode($response);
        exit();
    }
    
    switch ($action) {
        case 'fetch_student_assignments':
            fetchStudentAssignments($conn);
            break;
            
        case 'fetch_submission_status':
            fetchSubmissionStatus($conn);
            break;
            
        default:
            echo json_encode($response);
    }
} else {
    echo json_encode($response);
}

function submitAssignment($conn) {
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    $student_id = $_SESSION['uid'];
    $submission_text = mysqli_real_escape_string($conn, $_POST['submission_text'] ?? '');
    
    if (!$assignment_id) {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid assignment ID'));
        return;
    }
    
    // Check if assignment exists and get due date
    $checkSql = "SELECT due_date, title, max_marks FROM assignments WHERE assignment_id = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "i", $assignment_id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $assignment = mysqli_fetch_assoc($checkResult);
    
    if (!$assignment) {
        echo json_encode(array('status' => 'error', 'message' => 'Assignment not found'));
        return;
    }
    
    // Determine if submission is late
    $status = 'submitted';
    if (strtotime($assignment['due_date']) < time()) {
        $status = 'late';
    }
    
    $attachment = null;
    
    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../studentSubmissions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $filename = 'submission_' . $student_id . '_' . $assignment_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
            $attachment = $filename;
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error uploading file'));
            return;
        }
    }
    
    // Check if already submitted
    $existingSql = "SELECT submission_id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?";
    $existingStmt = mysqli_prepare($conn, $existingSql);
    mysqli_stmt_bind_param($existingStmt, "is", $assignment_id, $student_id);
    mysqli_stmt_execute($existingStmt);
    $existingResult = mysqli_stmt_get_result($existingStmt);
    
    if (mysqli_num_rows($existingResult) > 0) {
        // Update existing submission
        $updateSql = "UPDATE assignment_submissions 
                     SET submission_text = ?, attachment = ?, submitted_at = NOW(), status = ?
                     WHERE assignment_id = ? AND student_id = ?";
        $stmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($stmt, "sssis", $submission_text, $attachment, $status, $assignment_id, $student_id);
    } else {
        // Insert new submission
        $insertSql = "INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, attachment, status) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertSql);
        mysqli_stmt_bind_param($stmt, "issss", $assignment_id, $student_id, $submission_text, $attachment, $status);
        
        // Update total_submissions count in assignments table
        $updateCountSql = "UPDATE assignments SET total_submissions = total_submissions + 1 WHERE assignment_id = ?";
        $countStmt = mysqli_prepare($conn, $updateCountSql);
        mysqli_stmt_bind_param($countStmt, "i", $assignment_id);
        mysqli_stmt_execute($countStmt);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Assignment submitted successfully!',
            'submission_status' => $status
        ));
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Error submitting assignment: ' . mysqli_error($conn)
        ));
    }
    
    mysqli_stmt_close($stmt);
}

function fetchStudentAssignments($conn) {
    $student_id = $_SESSION['uid'];
    
    // Get student's class and section
    $studentSql = "SELECT class, section FROM students WHERE id = ?";
    $studentStmt = mysqli_prepare($conn, $studentSql);
    mysqli_stmt_bind_param($studentStmt, "s", $student_id);
    mysqli_stmt_execute($studentStmt);
    $studentResult = mysqli_stmt_get_result($studentStmt);
    $student = mysqli_fetch_assoc($studentResult);
    
    if (!$student) {
        echo json_encode(array('status' => 'error', 'message' => 'Student not found'));
        return;
    }
    
    $class = $student['class'];
    $section = $student['section'];
    
    // Fetch assignments for student's class using existing table structure
    $sql = "SELECT a.*, 
            CONCAT(u.fname, ' ', u.lname) as teacher_name,
            sub.submission_id, sub.submitted_at, sub.status as submission_status, 
            sub.marks_obtained, sub.feedback, sub.graded_at
            FROM assignments a
            LEFT JOIN users u ON a.teacher_id = u.id
            LEFT JOIN assignment_submissions sub ON a.assignment_id = sub.assignment_id AND sub.student_id = ?
            WHERE a.class = ? AND a.section = ? AND a.status = 'published'
            ORDER BY a.due_date ASC, a.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $student_id, $class, $section);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $assignments = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $row['is_overdue'] = (strtotime($row['due_date']) < time() && !$row['submission_id']);
        $assignments[] = $row;
    }
    
    echo json_encode(array(
        'status' => 'success',
        'assignments' => $assignments
    ));
    
    mysqli_stmt_close($stmt);
}

function fetchSubmissionStatus($conn) {
    $assignment_id = intval($_GET['assignment_id'] ?? 0);
    $student_id = $_SESSION['uid'];
    
    $sql = "SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $assignment_id, $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(array(
            'status' => 'success',
            'submission' => $row
        ));
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'No submission found'
        ));
    }
    
    mysqli_stmt_close($stmt);
}

function fetchStudentSubmissions($conn) {
    $student_id = $_SESSION['uid'];
    
    $sql = "SELECT sub.*, a.title, a.course_code, a.max_marks, a.due_date,
            CONCAT(u.fname, ' ', u.lname) as teacher_name
            FROM assignment_submissions sub
            JOIN assignments a ON sub.assignment_id = a.assignment_id
            LEFT JOIN users u ON a.teacher_id = u.id
            WHERE sub.student_id = ?
            ORDER BY sub.submitted_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $submissions = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $submissions[] = $row;
    }
    
    echo json_encode(array(
        'status' => 'success',
        'submissions' => $submissions
    ));
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
