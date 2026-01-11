<?php
session_start();
include("config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

// Mark student self-attendance
if ($action == 'student_mark_present') {
    $studentId = $_SESSION['uid'] ?? '';
    
    if (empty($studentId)) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    
    // Get student details
    $sql = "SELECT id, fname, lname, class, section FROM students WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($student = mysqli_fetch_assoc($result)) {
        $today = date('Y-m-d');
        
        // Check if already marked today
        $checkSql = "SELECT * FROM attendence 
                     WHERE student_id = ? 
                     AND DATE(date) = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "ss", $studentId, $today);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Attendance already marked today']);
            exit;
        }
        
        // Mark present
        $insertSql = "INSERT INTO attendence (student_id, attendence, class, section, date) 
                      VALUES (?, '1', ?, ?, NOW())";
        $insertStmt = mysqli_prepare($conn, $insertSql);
        mysqli_stmt_bind_param($insertStmt, "sss", $studentId, $student['class'], $student['section']);
        
        if (mysqli_stmt_execute($insertStmt)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Attendance marked successfully!',
                'student_name' => $student['fname'] . ' ' . $student['lname'],
                'date' => date('F j, Y')
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to mark attendance']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    }
    exit;
}

// Check if student marked attendance today
else if ($action == 'check_student_attendance') {
    $studentId = $_SESSION['uid'] ?? '';
    
    if (empty($studentId)) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    
    $today = date('Y-m-d');
    $sql = "SELECT * FROM attendence 
            WHERE student_id = ? 
            AND DATE(date) = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $studentId, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'status' => 'success',
            'marked' => true,
            'attendance_status' => $row['attendence'] == '1' ? 'present' : 'absent',
            'time' => date('g:i A', strtotime($row['date']))
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'marked' => false
        ]);
    }
    exit;
}

// Admin: Get students for attendance
else if ($action == 'get_students_for_attendance') {
    $class = $_POST['class'] ?? '';
    $section = $_POST['section'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    
    if (empty($class) || empty($section)) {
        echo json_encode(['status' => 'error', 'message' => 'Class and section required']);
        exit;
    }
    
    // Get all students
    $sql = "SELECT id, fname, lname, email, image FROM students 
            WHERE class = ? AND section = ? 
            ORDER BY fname ASC, lname ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $class, $section);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if attendance exists for this date
        $attSql = "SELECT attendence FROM attendence 
                   WHERE student_id = ? AND DATE(date) = ?";
        $attStmt = mysqli_prepare($conn, $attSql);
        mysqli_stmt_bind_param($attStmt, "ss", $row['id'], $date);
        mysqli_stmt_execute($attStmt);
        $attResult = mysqli_stmt_get_result($attStmt);
        
        $attendance_status = null;
        if ($attRow = mysqli_fetch_assoc($attResult)) {
            $attendance_status = $attRow['attendence'] == '1' ? 'present' : 'absent';
        }
        
        $students[] = [
            'id' => $row['id'],
            'name' => $row['fname'] . ' ' . $row['lname'],
            'email' => $row['email'],
            'image' => $row['image'],
            'attendance_status' => $attendance_status
        ];
    }
    
    echo json_encode(['status' => 'success', 'students' => $students, 'date' => $date]);
    exit;
}

// Admin: Submit attendance for multiple students
else if ($action == 'submit_attendance') {
    $class = $_POST['class'] ?? '';
    $section = $_POST['section'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $attendance = json_decode($_POST['attendance'] ?? '[]', true);
    
    if (empty($class) || empty($section) || empty($attendance)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
        exit;
    }
    
    mysqli_begin_transaction($conn);
    
    try {
        // Delete existing attendance for this class/section/date
        $deleteSql = "DELETE FROM attendence 
                      WHERE class = ? AND section = ? AND DATE(date) = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSql);
        mysqli_stmt_bind_param($deleteStmt, "sss", $class, $section, $date);
        mysqli_stmt_execute($deleteStmt);
        
        // Insert new attendance records
        $insertSql = "INSERT INTO attendence (student_id, attendence, class, section, date) 
                      VALUES (?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertSql);
        
        $successCount = 0;
        foreach ($attendance as $record) {
            $studentId = $record['student_id'];
            $status = $record['status'] == 'present' ? '1' : '0';
            $fullDate = $date . ' ' . date('H:i:s');
            
            mysqli_stmt_bind_param($insertStmt, "sssss", $studentId, $status, $class, $section, $fullDate);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $successCount++;
            }
        }
        
        mysqli_commit($conn);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance recorded for ' . $successCount . ' students',
            'count' => $successCount
        ]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit attendance']);
    }
    exit;
}

// Get attendance statistics
else if ($action == 'get_attendance_stats') {
    $studentId = $_SESSION['uid'] ?? '';
    
    if (empty($studentId)) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    
    $currentYear = date("Y");
    $currentMonth = date('m');
    
    $startDate = "";
    $endDate = "";
    if ($currentMonth <= 3) {
        $startDate = ($currentYear - 1) . "-04-01";
        $endDate = $currentYear . "-03-31";
    } else {
        $startDate = $currentYear . "-04-01";
        $endDate = ($currentYear + 1) . "-03-31";
    }
    
    // Count present days
    $presentSql = "SELECT COUNT(*) as count FROM attendence 
                   WHERE student_id = ? AND attendence = '1' 
                   AND date BETWEEN ? AND ?";
    $stmt = mysqli_prepare($conn, $presentSql);
    mysqli_stmt_bind_param($stmt, "sss", $studentId, $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $presentRow = mysqli_fetch_assoc($result);
    $presentCount = $presentRow['count'];
    
    // Count total days
    $totalSql = "SELECT COUNT(*) as count FROM attendence 
                 WHERE student_id = ? 
                 AND date BETWEEN ? AND ?";
    $stmt = mysqli_prepare($conn, $totalSql);
    mysqli_stmt_bind_param($stmt, "sss", $studentId, $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($result);
    $totalCount = $totalRow['count'];
    
    $percentage = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 2) : 0;
    
    echo json_encode([
        'status' => 'success',
        'present' => $presentCount,
        'absent' => $totalCount - $presentCount,
        'total' => $totalCount,
        'percentage' => $percentage
    ]);
    exit;
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

mysqli_close($conn);
?>
