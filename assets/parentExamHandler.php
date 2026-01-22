<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require 'config.php';

ob_clean();
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

if (!$user || $user['role'] != 'parent') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $parentId = $_SESSION['uid'];
    
    // Get exam results for a specific student
    if ($action == 'get_exam_results') {
        $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
        
        // Verify this student belongs to this parent
        $verifySql = "SELECT id FROM students WHERE id = ? AND parent_id = ?";
        $stmt = mysqli_prepare($conn, $verifySql);
        mysqli_stmt_bind_param($stmt, "ss", $studentId, $parentId);
        mysqli_stmt_execute($stmt);
        $verifyResult = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($verifyResult) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access to student data']);
            exit();
        }
        
        // Get exam results from exam_results table
        $resultsSql = "SELECT er.*, e.exam_title, e.subject, e.exam_date, e.timestamp
                       FROM exam_results er
                       JOIN exams e ON er.exam_id = e.exam_id
                       WHERE er.student_id = ?
                       ORDER BY e.exam_date DESC, e.timestamp DESC";
        
        $stmt = mysqli_prepare($conn, $resultsSql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $results = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Ensure percentage is formatted properly
            $row['percentage'] = number_format($row['percentage'], 1);
            $results[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'results' => $results]);
        exit();
    }
    
    // Get exam statistics for a specific student
    else if ($action == 'get_exam_stats') {
        $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
        
        // Verify this student belongs to this parent
        $verifySql = "SELECT class, section FROM students WHERE id = ? AND parent_id = ?";
        $stmt = mysqli_prepare($conn, $verifySql);
        mysqli_stmt_bind_param($stmt, "ss", $studentId, $parentId);
        mysqli_stmt_execute($stmt);
        $verifyResult = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($verifyResult) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access to student data']);
            exit();
        }
        
        $student = mysqli_fetch_assoc($verifyResult);
        $stats = [];
        
        // Total exams for student's class
        $sql = "SELECT COUNT(*) as count FROM exams WHERE class = ? AND section = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $student['class'], $student['section']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['total_exams'] = mysqli_fetch_assoc($result)['count'];
        
        // Results published for this student
        $sql = "SELECT COUNT(*) as count FROM exam_results WHERE student_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['results_published'] = mysqli_fetch_assoc($result)['count'];
        
        // Passed exams count
        $sql = "SELECT COUNT(*) as count FROM exam_results WHERE student_id = ? AND status = 'pass'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['passed'] = mysqli_fetch_assoc($result)['count'];
        
        // Average percentage
        $sql = "SELECT AVG(percentage) as avg FROM exam_results WHERE student_id = ?";
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

// Default error
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit();
?>
