<?php
// Automated Parent Notification System
// This file contains functions to send automated notifications to parents
include("config.php");

// Function to send notification to parent
function sendParentNotification($conn, $parentId, $studentId, $type, $title, $message, $priority = 'normal', $relatedId = null, $actionUrl = null) {
    $sql = "INSERT INTO parent_notifications 
            (parent_id, student_id, notification_type, title, message, priority, related_id, action_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", $parentId, $studentId, $type, $title, $message, $priority, $relatedId, $actionUrl);
    
    return mysqli_stmt_execute($stmt);
}

// Notify parent about attendance
function notifyAttendanceAlert($conn, $studentId, $status, $date) {
    // Get parent ID
    $parentSql = "SELECT guardian_id FROM student_parent_link WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $parentSql);
    mysqli_stmt_bind_param($stmt, "s", $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($parent = mysqli_fetch_assoc($result)) {
        // Get student name
        $studentSql = "SELECT fname, lname FROM students WHERE id = ?";
        $stmtStudent = mysqli_prepare($conn, $studentSql);
        mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
        mysqli_stmt_execute($stmtStudent);
        $studentData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStudent));
        
        $studentName = $studentData['fname'] . ' ' . $studentData['lname'];
        $title = "Attendance Alert - " . ucfirst($status);
        $message = "$studentName was marked $status on $date.";
        $priority = ($status == 'absent') ? 'high' : 'normal';
        
        sendParentNotification($conn, $parent['guardian_id'], $studentId, 'attendance', $title, $message, $priority);
    }
}

// Notify parent about new grades
function notifyGradePublished($conn, $studentId, $subject, $marks, $totalMarks, $grade) {
    $parentSql = "SELECT guardian_id FROM student_parent_link WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $parentSql);
    mysqli_stmt_bind_param($stmt, "s", $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($parent = mysqli_fetch_assoc($result)) {
        $studentSql = "SELECT fname, lname FROM students WHERE id = ?";
        $stmtStudent = mysqli_prepare($conn, $studentSql);
        mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
        mysqli_stmt_execute($stmtStudent);
        $studentData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStudent));
        
        $studentName = $studentData['fname'] . ' ' . $studentData['lname'];
        $title = "New Grade Published - $subject";
        $message = "$studentName received grade $grade ($marks/$totalMarks) in $subject.";
        
        sendParentNotification($conn, $parent['guardian_id'], $studentId, 'grades', $title, $message, 'normal');
    }
}

// Notify parent about assignment
function notifyNewAssignment($conn, $studentId, $subject, $title, $dueDate) {
    $parentSql = "SELECT guardian_id FROM student_parent_link WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $parentSql);
    mysqli_stmt_bind_param($stmt, "s", $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($parent = mysqli_fetch_assoc($result)) {
        $studentSql = "SELECT fname, lname FROM students WHERE id = ?";
        $stmtStudent = mysqli_prepare($conn, $studentSql);
        mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
        mysqli_stmt_execute($stmtStudent);
        $studentData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStudent));
        
        $studentName = $studentData['fname'] . ' ' . $studentData['lname'];
        $notifTitle = "New Assignment - $subject";
        $message = "$studentName has a new assignment: $title. Due date: $dueDate";
        
        sendParentNotification($conn, $parent['guardian_id'], $studentId, 'assignment', $notifTitle, $message, 'normal');
    }
}

// Notify parent about announcement
function notifyAnnouncement($conn, $title, $description, $date) {
    // Send to all parents
    $parentSql = "SELECT DISTINCT spl.guardian_id, spl.student_id 
                  FROM student_parent_link spl
                  JOIN students s ON spl.student_id = s.id
                  WHERE s.request = ''";
    
    $result = mysqli_query($conn, $parentSql);
    
    while ($parent = mysqli_fetch_assoc($result)) {
        $notifTitle = "School Announcement: $title";
        $message = $description;
        
        sendParentNotification($conn, $parent['guardian_id'], $parent['student_id'], 'announcement', $notifTitle, $message, 'normal');
    }
}

// Notify parent about upcoming event
function notifyUpcomingEvent($conn, $eventTitle, $eventDate, $description) {
    // Send to all parents
    $parentSql = "SELECT DISTINCT spl.guardian_id, spl.student_id 
                  FROM student_parent_link spl
                  JOIN students s ON spl.student_id = s.id
                  WHERE s.request = ''";
    
    $result = mysqli_query($conn, $parentSql);
    
    while ($parent = mysqli_fetch_assoc($result)) {
        $title = "Upcoming Event: $eventTitle";
        $message = "$description on $eventDate";
        
        sendParentNotification($conn, $parent['guardian_id'], $parent['student_id'], 'event', $title, $message, 'normal');
    }
}

// Check and notify low attendance (under 75%)
function checkAndNotifyLowAttendance($conn) {
    $sql = "SELECT 
                a.student_id,
                COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(*) as attendance_percentage
            FROM attendence a
            WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY a.student_id
            HAVING attendance_percentage < 75";
    
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $parentSql = "SELECT guardian_id FROM student_parent_link WHERE student_id = ?";
        $stmt = mysqli_prepare($conn, $parentSql);
        mysqli_stmt_bind_param($stmt, "s", $row['student_id']);
        mysqli_stmt_execute($stmt);
        $parentResult = mysqli_stmt_get_result($stmt);
        
        while ($parent = mysqli_fetch_assoc($parentResult)) {
            $studentSql = "SELECT fname, lname FROM students WHERE id = ?";
            $stmtStudent = mysqli_prepare($conn, $studentSql);
            mysqli_stmt_bind_param($stmtStudent, "s", $row['student_id']);
            mysqli_stmt_execute($stmtStudent);
            $studentData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStudent));
            
            $studentName = $studentData['fname'] . ' ' . $studentData['lname'];
            $percentage = round($row['attendance_percentage'], 1);
            $title = "Low Attendance Alert";
            $message = "$studentName's attendance is $percentage% (below 75% requirement). Please ensure regular attendance.";
            
            sendParentNotification($conn, $parent['guardian_id'], $row['student_id'], 'attendance', $title, $message, 'urgent');
        }
    }
}

?>
