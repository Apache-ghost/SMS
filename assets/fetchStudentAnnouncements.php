<?php
// Fetch announcements for student dashboard
session_start();
include("config.php");

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - No session']);
    exit();
}

$studentId = $_SESSION['uid'];

// Get student's class and section
$studentQuery = "SELECT class, section FROM students WHERE id = ?";
$stmt = mysqli_prepare($conn, $studentQuery);
mysqli_stmt_bind_param($stmt, "s", $studentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found', 'student_id' => $studentId]);
    exit();
}

$class = $student['class'];
$section = $student['section'];

// Fetch published announcements for students
$sql = "SELECT a.*, 
               CASE 
                 WHEN a.published_by IN (SELECT id FROM teachers) 
                 THEN (SELECT CONCAT(fname, ' ', lname) FROM teachers WHERE id = a.published_by)
                 WHEN a.published_by IN (SELECT id FROM admins) 
                 THEN (SELECT CONCAT(fname, ' ', lname) FROM admins WHERE id = a.published_by)
                 ELSE 'Admin'
               END as publisher_name,
               ar.is_read,
               ar.read_date
        FROM announcements a 
        LEFT JOIN announcement_recipients ar 
            ON a.announcement_id = ar.announcement_id 
            AND ar.user_id = ? 
            AND ar.user_type = 'student'
        WHERE a.status = 'published'
          AND NOW() BETWEEN a.display_from AND a.display_until
          AND (
              a.target_audience = 'all' 
              OR a.target_audience = 'students'
              OR (a.target_audience = 'class_specific' AND a.class = ? AND (a.section IS NULL OR a.section = ?))
          )
        ORDER BY a.is_pinned DESC, a.published_date DESC
        LIMIT 20";

$stmtAnn = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmtAnn, "sis", $studentId, $class, $section);
mysqli_stmt_execute($stmtAnn);
$result = mysqli_stmt_get_result($stmtAnn);

$announcements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = $row;
}

if (mysqli_error($conn)) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

echo json_encode([
    'status' => 'success', 
    'data' => $announcements,
    'count' => count($announcements)
]);

mysqli_stmt_close($stmtAnn);
mysqli_close($conn);
?>
