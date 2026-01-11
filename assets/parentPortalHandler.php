<?php
// Parent Portal Login Handler
include("config.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Parent login
    if ($action == "login" || !isset($action)) {
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $password = $_POST["password"];
        
        // Query parent_users table
        $sql = "SELECT pu.*, sg.gname, sg.relation 
                FROM parent_users pu
                JOIN student_guardian sg ON pu.guardian_id = sg.id
                WHERE pu.email = ? AND pu.is_active = 1";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['parent_id'] = $row['guardian_id'];
                $_SESSION['parent_user_id'] = $row['parent_user_id'];
                $_SESSION['parent_name'] = $row['gname'];
                $_SESSION['parent_email'] = $row['email'];
                $_SESSION['role'] = 'parent';
                
                // Update last login
                $updateSql = "UPDATE parent_users SET last_login = NOW() WHERE parent_user_id = ?";
                $stmtUpdate = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmtUpdate, "i", $row['parent_user_id']);
                mysqli_stmt_execute($stmtUpdate);
                
                // Redirect to dashboard
                header("Location: ../parent_panel/dashboard.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid password!";
                header("Location: ../parent_login.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Account not found or inactive!";
            header("Location: ../parent_login.php");
            exit();
        }
    }
    
    // Get parent's children
    else if ($action == "get_children") {
        $guardianId = $_SESSION['parent_id'] ?? $_POST["guardian_id"];
        
        $sql = "SELECT s.*, spl.relationship 
                FROM students s
                JOIN student_parent_link spl ON s.id = spl.student_id
                WHERE spl.guardian_id = ?
                ORDER BY s.class, s.fname";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $children = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Get latest attendance percentage
            $attendanceSql = "SELECT 
                             COUNT(CASE WHEN status = 'present' THEN 1 END) * 100.0 / COUNT(*) as attendance_percentage
                             FROM attendence 
                             WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmtAtt = mysqli_prepare($conn, $attendanceSql);
            mysqli_stmt_bind_param($stmtAtt, "s", $row['id']);
            mysqli_stmt_execute($stmtAtt);
            $attResult = mysqli_stmt_get_result($stmtAtt);
            $attData = mysqli_fetch_assoc($attResult);
            $row['attendance_percentage'] = round($attData['attendance_percentage'] ?? 0, 1);
            
            $children[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $children]);
    }
    
    // Get unread messages count
    else if ($action == "get_unread_messages") {
        $guardianId = $_SESSION['parent_id'] ?? $_POST["guardian_id"];
        
        $sql = "SELECT COUNT(*) as count FROM parent_messages 
                WHERE receiver_id = ? AND receiver_type = 'parent' AND is_read = 0";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        echo json_encode(['status' => 'success', 'count' => $row['count']]);
    }
    
    // Get notifications
    else if ($action == "get_notifications") {
        $guardianId = $_SESSION['parent_id'] ?? $_POST["guardian_id"];
        $limit = $_POST["limit"] ?? 10;
        
        $sql = "SELECT pn.*, s.fname, s.lname, s.class 
                FROM parent_notifications pn
                JOIN students s ON pn.student_id = s.id
                WHERE pn.parent_id = ?
                ORDER BY pn.created_at DESC
                LIMIT ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $guardianId, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $notifications]);
    }
    
    // Get dashboard stats
    else if ($action == "get_dashboard_stats") {
        $guardianId = $_SESSION['parent_id'] ?? $_POST["guardian_id"];
        
        $stats = [];
        
        // Children count
        $childrenSql = "SELECT COUNT(*) as count FROM student_parent_link WHERE guardian_id = ?";
        $stmt = mysqli_prepare($conn, $childrenSql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['children_count'] = mysqli_fetch_assoc($result)['count'];
        
        // Unread messages
        $messagesSql = "SELECT COUNT(*) as count FROM parent_messages 
                        WHERE receiver_id = ? AND receiver_type = 'parent' AND is_read = 0";
        $stmt = mysqli_prepare($conn, $messagesSql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['unread_messages'] = mysqli_fetch_assoc($result)['count'];
        
        // Unread notifications
        $notifSql = "SELECT COUNT(*) as count FROM parent_notifications 
                     WHERE parent_id = ? AND is_read = 0";
        $stmt = mysqli_prepare($conn, $notifSql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['new_notifications'] = mysqli_fetch_assoc($result)['count'];
        
        // Upcoming events (next 7 days)
        $eventsSql = "SELECT COUNT(*) as count FROM noticeboard 
                      WHERE date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $result = mysqli_query($conn, $eventsSql);
        $stats['upcoming_events'] = mysqli_fetch_assoc($result)['count'];
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
}

mysqli_close($conn);
?>
