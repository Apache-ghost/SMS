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
            // Get attendance percentage (last 30 days)
            $attendanceSql = "SELECT 
                             COUNT(CASE WHEN attendence = 'present' THEN 1 END) * 100.0 / COUNT(*) as attendance_percentage
                             FROM attendence 
                             WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmtAtt = mysqli_prepare($conn, $attendanceSql);
            mysqli_stmt_bind_param($stmtAtt, "s", $row['id']);
            mysqli_stmt_execute($stmtAtt);
            $attResult = mysqli_stmt_get_result($stmtAtt);
            $attData = mysqli_fetch_assoc($attResult);
            $row['attendance_percentage'] = round($attData['attendance_percentage'] ?? 0, 1);
            
            // Build full name
            $row['name'] = trim($row['fname'] . ' ' . $row['lname']);
            
            $children[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'children' => $children]);
    }
    
    // Get student grades/marks
    else if ($action == "get_student_grades") {
        $studentId = $_POST["student_id"];
        $guardianId = $_SESSION['parent_id'];
        
        // Verify parent has access to this student
        $verifySql = "SELECT COUNT(*) as count FROM student_parent_link WHERE student_id = ? AND guardian_id = ?";
        $stmt = mysqli_prepare($conn, $verifySql);
        mysqli_stmt_bind_param($stmt, "ss", $studentId, $guardianId);
        mysqli_stmt_execute($stmt);
        $verifyResult = mysqli_stmt_get_result($stmt);
        if (mysqli_fetch_assoc($verifyResult)['count'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
            exit();
        }
        
        // Get all exam marks for the student
        $sql = "SELECT e.exam_name, e.subject, e.total_marks, e.passing_marks, 
                       m.marks_obtained, m.remarks, m.exam_date, e.class, e.section
                FROM exams e
                LEFT JOIN marks m ON e.exam_id = m.exam_id AND m.student_id = ?
                WHERE e.class = (SELECT class FROM students WHERE id = ?)
                  AND e.section = (SELECT section FROM students WHERE id = ?)
                ORDER BY m.exam_date DESC, e.subject";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $studentId, $studentId, $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $grades = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['percentage'] = $row['marks_obtained'] ? round(($row['marks_obtained'] / $row['total_marks']) * 100, 1) : 0;
            $row['status'] = $row['marks_obtained'] >= $row['passing_marks'] ? 'Pass' : 'Fail';
            $grades[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'grades' => $grades]);
    }
    
    // Get student assignments
    else if ($action == "get_student_assignments") {
        $studentId = $_POST["student_id"] ?? null;
        $studentClass = $_POST["class"] ?? null;
        $section = $_POST["section"] ?? null;
        $guardianId = $_SESSION['parent_id'];
        
        if ($studentId) {
            // Verify parent has access
            $verifySql = "SELECT COUNT(*) as count FROM student_parent_link WHERE student_id = ? AND guardian_id = ?";
            $stmt = mysqli_prepare($conn, $verifySql);
            mysqli_stmt_bind_param($stmt, "ss", $studentId, $guardianId);
            mysqli_stmt_execute($stmt);
            $verifyResult = mysqli_stmt_get_result($stmt);
            if (mysqli_fetch_assoc($verifyResult)['count'] == 0) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                exit();
            }
            
            // Get class and section from student
            $classSql = "SELECT class, section FROM students WHERE id = ?";
            $stmt = mysqli_prepare($conn, $classSql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $classResult = mysqli_stmt_get_result($stmt);
            $classData = mysqli_fetch_assoc($classResult);
            $studentClass = $classData['class'];
            $section = $classData['section'];
        }
        
        // Get assignments for student's class
        $sql = "SELECT a.*, 
                       asub.submission_date, asub.status as submission_status, asub.marks_obtained, asub.feedback,
                       DATEDIFF(a.due_date, CURDATE()) as days_remaining
                FROM assignments a
                LEFT JOIN assignment_submissions asub ON a.assignment_id = asub.assignment_id AND asub.student_id = ?
                WHERE a.class = ? AND a.section = ?
                ORDER BY a.due_date DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $studentId, $studentClass, $section);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $assignments = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['is_overdue'] = $row['days_remaining'] < 0 && !$row['submission_status'];
            $row['is_upcoming'] = $row['days_remaining'] > 0 && $row['days_remaining'] <= 3;
            $assignments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'assignments' => $assignments]);
    }
    
    // Get announcements for parent
    else if ($action == "get_parent_announcements") {
        $guardianId = $_SESSION['parent_id'];
        
        // Get children's classes for filtering
        $childrenSql = "SELECT DISTINCT s.class, s.section 
                        FROM students s
                        JOIN student_parent_link spl ON s.id = spl.student_id
                        WHERE spl.guardian_id = ?";
        $stmt = mysqli_prepare($conn, $childrenSql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $childrenResult = mysqli_stmt_get_result($stmt);
        
        $classConditions = [];
        while ($child = mysqli_fetch_assoc($childrenResult)) {
            $classConditions[] = "(target_audience LIKE '%{$child['class']}%' OR target_audience = 'all')";
        }
        
        $whereClause = empty($classConditions) ? "target_audience = 'all'" : '(' . implode(' OR ', $classConditions) . ')';
        
        $sql = "SELECT * FROM announcements 
                WHERE status = 'published' AND " . $whereClause . "
                ORDER BY created_at DESC LIMIT 20";
        
        $result = mysqli_query($conn, $sql);
        $announcements = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $announcements[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'announcements' => $announcements]);
    }
    
    // Get recent activity/notifications
    else if ($action == "get_recent_activity") {
        $guardianId = $_SESSION['parent_id'];
        $limit = $_POST["limit"] ?? 5;
        
        $activities = [];
        
        // Get children
        $childrenSql = "SELECT s.id, s.fname, s.lname, s.class FROM students s
                        JOIN student_parent_link spl ON s.id = spl.student_id
                        WHERE spl.guardian_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $childrenResult = mysqli_stmt_get_result($stmt);
        $children = [];
        while ($child = mysqli_fetch_assoc($childrenResult)) {
            $children[] = $child;
        }
        
        foreach ($children as $child) {
            // Recent assignments
            $assignSql = "SELECT title, due_date, 'assignment' as type FROM assignments 
                          WHERE class = ? ORDER BY created_at DESC LIMIT 2";
            $stmt = mysqli_prepare($conn, $assignSql);
            mysqli_stmt_bind_param($stmt, "s", $child['class']);
            mysqli_stmt_execute($stmt);
            $assignResult = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($assignResult)) {
                $row['student_name'] = $child['fname'] . ' ' . $child['lname'];
                $activities[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'activities' => array_slice($activities, 0, $limit)]);
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
