<?php
// Parent Portal Login Handler
error_reporting(0); // Suppress all errors/warnings
ini_set('display_errors', 0);

session_start();
include("config.php");

// Set JSON header for all responses except login redirects
if (isset($_POST['action']) && $_POST['action'] != 'login') {
    header('Content-Type: application/json');
}

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
        
        if (!$studentClass) {
            echo json_encode(['status' => 'error', 'message' => 'Student class not found']);
            exit();
        }
        
        // Check if assignment_submissions table exists
        $checkSubmissions = mysqli_query($conn, "SHOW TABLES LIKE 'assignment_submissions'");
        $hasSubmissionsTable = mysqli_num_rows($checkSubmissions) > 0;
        
        // Check if assignments table exists
        $checkAssignments = mysqli_query($conn, "SHOW TABLES LIKE 'assignments'");
        if (mysqli_num_rows($checkAssignments) == 0) {
            echo json_encode(['status' => 'success', 'assignments' => []]);
            exit();
        }
        
        // Build query based on available tables
        if ($hasSubmissionsTable) {
            $sql = "SELECT a.*, 
                           asub.submission_date, asub.status as submission_status, 
                           asub.marks_obtained, asub.feedback,
                           DATEDIFF(a.due_date, CURDATE()) as days_remaining,
                           s.subject_name as subject
                    FROM assignments a
                    LEFT JOIN assignment_submissions asub ON a.assignment_id = asub.assignment_id AND asub.student_id = ?
                    LEFT JOIN subjects s ON a.course_code = s.subject_id
                    WHERE a.class = ? AND (a.section = ? OR a.section IS NULL OR a.section = '')
                    AND a.status = 'published'
                    ORDER BY a.due_date DESC";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sis", $studentId, $studentClass, $section);
        } else {
            // No submissions table - just get assignments
            $sql = "SELECT a.*, 
                           DATEDIFF(a.due_date, CURDATE()) as days_remaining,
                           s.subject_name as subject,
                           NULL as submission_date,
                           NULL as submission_status,
                           NULL as marks_obtained,
                           NULL as feedback
                    FROM assignments a
                    LEFT JOIN subjects s ON a.course_code = s.subject_id
                    WHERE a.class = ? AND (a.section = ? OR a.section IS NULL OR a.section = '')
                    AND a.status = 'published'
                    ORDER BY a.due_date DESC";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $studentClass, $section);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $assignments = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['is_overdue'] = $row['days_remaining'] < 0 && (!$row['submission_status'] || $row['submission_status'] == 'pending');
            $row['is_upcoming'] = $row['days_remaining'] > 0 && $row['days_remaining'] <= 3;
            $row['subject'] = $row['subject'] ?? 'General';
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
        
        $classes = [];
        while ($child = mysqli_fetch_assoc($childrenResult)) {
            $classes[] = $child['class'];
        }
        
        $announcements = [];
        
        // Check if announcements table exists
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'announcements'");
        if (mysqli_num_rows($checkTable) > 0) {
            $classConditions = [];
            foreach ($classes as $class) {
                $classConditions[] = "(target_audience LIKE '%$class%' OR target_audience = 'all')";
            }
            
            $whereClause = empty($classConditions) ? "target_audience = 'all'" : '(' . implode(' OR ', $classConditions) . ')';
            
            $sql = "SELECT *, created_at as timestamp FROM announcements 
                    WHERE status = 'published' AND " . $whereClause . "
                    AND display_until >= NOW()
                    ORDER BY created_at DESC LIMIT 20";
            
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $row['priority'] = $row['priority'] ?? 'normal';
                $announcements[] = $row;
            }
        } else {
            // Fallback to notice table
            $classConditions = [];
            foreach ($classes as $class) {
                $classConditions[] = "class = '$class'";
            }
            
            $classWhere = empty($classConditions) ? "1=1" : '(' . implode(' OR ', $classConditions) . ')';
            
            $sql = "SELECT *, timestamp as created_at, 'normal' as priority FROM notice 
                    WHERE (role = 'student' AND ($classWhere)) OR (role = 'all' OR role = '') 
                    ORDER BY timestamp DESC LIMIT 20";
            
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $announcements[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'announcements' => $announcements]);
    }
    
    // Get upcoming events
    else if ($action == "get_upcoming_events") {
        $guardianId = $_SESSION['parent_id'];
        
        $events = [];
        
        // Check if noticeboard table exists
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'noticeboard'");
        if (mysqli_num_rows($checkTable) > 0) {
            $sql = "SELECT * FROM noticeboard 
                    WHERE date >= CURDATE() 
                    ORDER BY date ASC LIMIT 10";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $events[] = $row;
            }
        }
        
        // Also check announcements for events
        $checkAnnouncements = mysqli_query($conn, "SHOW TABLES LIKE 'announcements'");
        if (mysqli_num_rows($checkAnnouncements) > 0) {
            $sql = "SELECT title, published_date as date, content FROM announcements 
                    WHERE announcement_type = 'event' AND status = 'published' 
                    AND display_until >= NOW()
                    ORDER BY published_date ASC LIMIT 10";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $events[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'events' => $events]);
    }
    
    // Get detailed grades for a student
    else if ($action == "get_student_grades_detailed") {
        $studentId = $_POST["student_id"];
        $guardianId = $_SESSION['parent_id'];
        
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
        
        // Check if marks table exists
        $checkMarks = mysqli_query($conn, "SHOW TABLES LIKE 'marks'");
        if (mysqli_num_rows($checkMarks) > 0) {
            $sql = "SELECT m.*, e.exam_name, e.subject, e.total_marks, e.passing_marks, e.exam_date,
                           s.subject_name
                    FROM marks m
                    LEFT JOIN exams e ON m.exam_id = e.exam_id
                    LEFT JOIN subjects s ON e.subject = s.subject_id
                    WHERE m.student_id = ?
                    ORDER BY e.exam_date DESC, e.subject";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $grades = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $row['percentage'] = $row['marks_obtained'] && $row['total_marks'] ? 
                                    round(($row['marks_obtained'] / $row['total_marks']) * 100, 1) : 0;
                $row['status'] = $row['marks_obtained'] >= $row['passing_marks'] ? 'Pass' : 'Fail';
                $row['subject_display'] = $row['subject_name'] ?? $row['subject'];
                $grades[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'grades' => $grades]);
        } else {
            echo json_encode(['status' => 'success', 'grades' => []]);
        }
    }
    
    // Get student attendance
    else if ($action == "get_student_attendance") {
        $studentId = $_POST["student_id"];
        $guardianId = $_SESSION['parent_id'];
        
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
        
        // Check if attendence table exists
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'attendence'");
        if (mysqli_num_rows($checkTable) > 0) {
            $sql = "SELECT * FROM attendence 
                    WHERE student_id = ? 
                    ORDER BY date DESC 
                    LIMIT 90";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $attendance = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $attendance[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'attendance' => $attendance]);
        } else {
            echo json_encode(['status' => 'success', 'attendance' => []]);
        }
    }
    
    // Get student timetable
    else if ($action == "get_student_timetable") {
        $studentId = $_POST["student_id"] ?? null;
        $class = $_POST["class"];
        $section = $_POST["section"] ?? '';
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
        }
        
        // Check timetable table
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'timetable'");
        if (mysqli_num_rows($checkTable) > 0) {
            $sql = "SELECT tt.*, s.subject_name, t.name as teacher_name
                    FROM timetable tt
                    LEFT JOIN subjects s ON tt.subject_id = s.subject_id
                    LEFT JOIN teachers t ON tt.teacher_id = t.id
                    WHERE tt.class = ? AND (tt.section = ? OR tt.section IS NULL OR tt.section = '')
                    ORDER BY FIELD(tt.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), tt.period";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $class, $section);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $timetable = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $timetable[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'timetable' => $timetable]);
        } else {
            echo json_encode(['status' => 'success', 'timetable' => []]);
        }
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
        
        // Unread messages - check if table exists
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'parent_messages'");
        if (mysqli_num_rows($checkTable) > 0) {
            $messagesSql = "SELECT COUNT(*) as count FROM parent_messages 
                            WHERE receiver_id = ? AND receiver_type = 'parent' AND is_read = 0";
            $stmt = mysqli_prepare($conn, $messagesSql);
            mysqli_stmt_bind_param($stmt, "s", $guardianId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $stats['unread_messages'] = mysqli_fetch_assoc($result)['count'];
        } else {
            $stats['unread_messages'] = 0;
        }
        
        // Count announcements from announcements or notice table
        $checkAnnouncements = mysqli_query($conn, "SHOW TABLES LIKE 'announcements'");
        if (mysqli_num_rows($checkAnnouncements) > 0) {
            // Get children's classes
            $childrenSql = "SELECT DISTINCT s.class FROM students s
                            JOIN student_parent_link spl ON s.id = spl.student_id
                            WHERE spl.guardian_id = ?";
            $stmt = mysqli_prepare($conn, $childrenSql);
            mysqli_stmt_bind_param($stmt, "s", $guardianId);
            mysqli_stmt_execute($stmt);
            $childrenResult = mysqli_stmt_get_result($stmt);
            
            $classes = [];
            while ($child = mysqli_fetch_assoc($childrenResult)) {
                $classes[] = $child['class'];
            }
            
            if (!empty($classes)) {
                $classesStr = "'" . implode("','", $classes) . "'";
                $announceSql = "SELECT COUNT(*) as count FROM announcements 
                                WHERE status = 'published' AND 
                                (target_audience = 'all' OR target_audience IN ($classesStr))
                                AND display_until >= NOW()";
            } else {
                $announceSql = "SELECT COUNT(*) as count FROM announcements 
                                WHERE status = 'published' AND target_audience = 'all'
                                AND display_until >= NOW()";
            }
            $result = mysqli_query($conn, $announceSql);
            $stats['announcements_count'] = mysqli_fetch_assoc($result)['count'];
        } else {
            // Fallback to notice table
            $noticeSql = "SELECT COUNT(*) as count FROM notice 
                          WHERE (role = 'all' OR role = 'student' OR role = '')";
            $result = mysqli_query($conn, $noticeSql);
            $stats['announcements_count'] = mysqli_fetch_assoc($result)['count'];
        }
        
        // Upcoming events (next 7 days)
        $checkNoticeboard = mysqli_query($conn, "SHOW TABLES LIKE 'noticeboard'");
        if (mysqli_num_rows($checkNoticeboard) > 0) {
            $eventsSql = "SELECT COUNT(*) as count FROM noticeboard 
                          WHERE date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            $result = mysqli_query($conn, $eventsSql);
            $stats['upcoming_events'] = mysqli_fetch_assoc($result)['count'];
        } else {
            $stats['upcoming_events'] = 0;
        }
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
    
    // Get all parents for admin panel
    else if ($action == "get_all_parents") {
        // Check if parent_users table exists
        $checkTable = @mysqli_query($conn, "SHOW TABLES LIKE 'parent_users'");
        
        if (!$checkTable || mysqli_num_rows($checkTable) == 0) {
            // Table doesn't exist, return empty list
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'parents' => [], 'message' => 'Parent users table not found. Click Sync Parents to create it.']);
            exit();
        }
        
        $sql = "SELECT 
                    pu.parent_user_id,
                    pu.guardian_id,
                    pu.is_active,
                    pu.last_login,
                    COALESCE(sg.gname, 'Unknown') AS fname,
                    '' AS lname,
                    COALESCE(sg.email, pu.email, 'N/A') AS email,
                    COALESCE(sg.phone, 'N/A') AS phone,
                    COUNT(DISTINCT spl.student_id) AS children_count
                FROM parent_users pu
                LEFT JOIN student_guardian sg ON pu.guardian_id = sg.id
                LEFT JOIN student_parent_link spl ON sg.id = spl.parent_id
                GROUP BY pu.parent_user_id
                ORDER BY fname ASC";
        
        $result = @mysqli_query($conn, $sql);
        
        if ($result) {
            $parents = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $parents[] = $row;
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'parents' => $parents]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database query failed']);
        }
    }
    
    // Get portal statistics for admin
    else if ($action == "get_portal_stats") {
        $stats = [];
        
        // Check if parent_users table exists
        $checkTable = @mysqli_query($conn, "SHOW TABLES LIKE 'parent_users'");
        
        if (!$checkTable || mysqli_num_rows($checkTable) == 0) {
            // Return zeros if table doesn't exist
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'stats' => [
                'total_parents' => 0,
                'active_accounts' => 0,
                'logins_today' => 0,
                'total_messages' => 0
            ]]);
            exit();
        }
        
        // Total parents
        $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_users");
        $stats['total_parents'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
        
        // Active accounts
        $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_users WHERE is_active = 1");
        $stats['active_accounts'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
        
        // Logins today
        $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_users WHERE DATE(last_login) = CURDATE()");
        $stats['logins_today'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
        
        // Total messages
        $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_messages WHERE sender_type = 'parent'");
        $stats['total_messages'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'stats' => $stats]);
    }
    
    // Get parent details
    else if ($action == "get_parent_details") {
        $guardianId = mysqli_real_escape_string($conn, $_POST['guardian_id']);
        
        // Get parent info
        $sql = "SELECT sg.*, pu.email AS user_email, pu.is_active, pu.last_login
                FROM student_guardian sg
                LEFT JOIN parent_users pu ON sg.id = pu.guardian_id
                WHERE sg.id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $guardianId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $parent = mysqli_fetch_assoc($result);
        
        // Get children
        $childrenSql = "SELECT s.id, CONCAT(s.fname, ' ', s.lname) AS name, s.class, s.section
                        FROM students s
                        INNER JOIN student_parent_link spl ON s.id = spl.student_id
                        WHERE spl.parent_id = ?";
        
        $stmtChildren = mysqli_prepare($conn, $childrenSql);
        mysqli_stmt_bind_param($stmtChildren, "s", $guardianId);
        mysqli_stmt_execute($stmtChildren);
        $resultChildren = mysqli_stmt_get_result($stmtChildren);
        
        $children = [];
        while ($row = mysqli_fetch_assoc($resultChildren)) {
            $children[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'parent' => [
                'fname' => $parent['gname'],
                'lname' => '',
                'email' => $parent['user_email'] ?: $parent['email'],
                'phone' => $parent['phone']
            ],
            'children' => $children
        ]);
    }
    
    // Reset parent password
    else if ($action == "reset_parent_password") {
        $guardianId = mysqli_real_escape_string($conn, $_POST['guardian_id']);
        
        // Generate new password
        $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE parent_users SET password = ? WHERE guardian_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $guardianId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'new_password' => $newPassword]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Sync parents from student_guardian table
    else if ($action == "sync_parents") {
        // First, create parent_users table if it doesn't exist
        $createTableSql = "CREATE TABLE IF NOT EXISTS `parent_users` (
            `parent_user_id` INT AUTO_INCREMENT PRIMARY KEY,
            `guardian_id` VARCHAR(20) UNIQUE NOT NULL,
            `email` VARCHAR(100) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `last_login` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`guardian_id`) REFERENCES `student_guardian`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        mysqli_query($conn, $createTableSql);
        
        // Get all guardians not yet in parent_users
        $sql = "SELECT sg.* 
                FROM student_guardian sg
                LEFT JOIN parent_users pu ON sg.id = pu.guardian_id
                WHERE pu.parent_user_id IS NULL";
        
        $result = mysqli_query($conn, $sql);
        $synced = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $defaultPassword = password_hash('parent123', PASSWORD_DEFAULT);
            
            $insertSql = "INSERT INTO parent_users (guardian_id, email, password, is_active, created_at) 
                          VALUES (?, ?, ?, 1, NOW())";
            
            $stmt = mysqli_prepare($conn, $insertSql);
            $email = $row['email'] ?: $row['gname'] . '@parent.com';
            mysqli_stmt_bind_param($stmt, "sss", $row['id'], $email, $defaultPassword);
            
            if (mysqli_stmt_execute($stmt)) {
                $synced++;
            }
        }
        
        echo json_encode(['status' => 'success', 'synced' => $synced, 'message' => "$synced parent accounts synced"]);
    }
}

mysqli_close($conn);
?>
