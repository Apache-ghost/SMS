<?php
// REQ-ACD-007: Parent Portal Access Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create parent portal account
    if ($action == "create_portal_account") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        $username = mysqli_real_escape_string($conn, $_POST["username"]);
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $phone = mysqli_real_escape_string($conn, $_POST["phone"] ?? '');
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check if parent exists
            $checkParent = "SELECT * FROM parent_guardian WHERE guardian_id = ?";
            $stmtCheck = mysqli_prepare($conn, $checkParent);
            mysqli_stmt_bind_param($stmtCheck, "s", $parentId);
            mysqli_stmt_execute($stmtCheck);
            $result = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($result) == 0) {
                throw new Exception("Parent/Guardian not found!");
            }
            
            // Check if username already exists
            $checkUser = "SELECT * FROM parent_portal_access WHERE username = ?";
            $stmtCheckUser = mysqli_prepare($conn, $checkUser);
            mysqli_stmt_bind_param($stmtCheckUser, "s", $username);
            mysqli_stmt_execute($stmtCheckUser);
            
            if (mysqli_num_rows(mysqli_stmt_get_result($stmtCheckUser)) > 0) {
                throw new Exception("Username already exists!");
            }
            
            $sql = "INSERT INTO parent_portal_access 
                    (parent_id, username, password, email, phone) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $parentId, $username, $password, $email, $phone);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating portal account: " . mysqli_error($conn));
            }
            
            $portalId = mysqli_insert_id($conn);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Parent portal account created successfully!',
                'portal_id' => $portalId
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Parent login
    else if ($action == "parent_login") {
        $username = mysqli_real_escape_string($conn, $_POST["username"]);
        $password = $_POST["password"];
        
        $sql = "SELECT p.*, pg.fname, pg.lname, pg.relationship 
                FROM parent_portal_access p
                JOIN parent_guardian pg ON p.parent_id = pg.guardian_id
                WHERE p.username = ? AND p.is_active = 1";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                // Update login info
                $updateSql = "UPDATE parent_portal_access 
                             SET last_login = NOW(), login_count = login_count + 1 
                             WHERE portal_id = ?";
                $stmtUpdate = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmtUpdate, "i", $row['portal_id']);
                mysqli_stmt_execute($stmtUpdate);
                
                // Log activity
                $logSql = "INSERT INTO parent_activity_log 
                          (parent_id, activity_type, activity_description, ip_address) 
                          VALUES (?, 'login', 'User logged in', ?)";
                $stmtLog = mysqli_prepare($conn, $logSql);
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                mysqli_stmt_bind_param($stmtLog, "ss", $row['parent_id'], $ipAddress);
                mysqli_stmt_execute($stmtLog);
                
                // Remove password from response
                unset($row['password']);
                unset($row['password_reset_token']);
                unset($row['two_factor_secret']);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Login successful!',
                    'data' => $row
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid password!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found or account inactive!']);
        }
    }
    
    // Get parent's children
    else if ($action == "get_children") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        
        $sql = "SELECT s.*, sp.relation_type 
                FROM students s
                JOIN student_parent sp ON s.id = sp.student_id
                WHERE sp.parent_id = ? AND s.enrollment_status = 'active'
                ORDER BY s.class, s.fname";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $children = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $children[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $children]);
    }
    
    // Get student dashboard for parent
    else if ($action == "get_student_dashboard") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        
        // Verify parent-student relationship
        $verifySql = "SELECT * FROM student_parent WHERE student_id = ? AND parent_id = ?";
        $stmtVerify = mysqli_prepare($conn, $verifySql);
        mysqli_stmt_bind_param($stmtVerify, "ss", $studentId, $parentId);
        mysqli_stmt_execute($stmtVerify);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($stmtVerify)) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied!']);
            exit();
        }
        
        $dashboard = [];
        
        // Get student info
        $studentSql = "SELECT * FROM students WHERE id = ?";
        $stmtStudent = mysqli_prepare($conn, $studentSql);
        mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
        mysqli_stmt_execute($stmtStudent);
        $dashboard['student'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtStudent));
        
        // Get recent attendance
        $attendanceSql = "SELECT * FROM attendence 
                         WHERE student_id = ? 
                         ORDER BY date DESC LIMIT 10";
        $stmtAttendance = mysqli_prepare($conn, $attendanceSql);
        mysqli_stmt_bind_param($stmtAttendance, "s", $studentId);
        mysqli_stmt_execute($stmtAttendance);
        $result = mysqli_stmt_get_result($stmtAttendance);
        $attendance = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $attendance[] = $row;
        }
        $dashboard['recent_attendance'] = $attendance;
        
        // Get recent grades
        $gradesSql = "SELECT cg.*, c.course_name 
                     FROM cumulative_grades cg
                     JOIN courses c ON cg.course_code = c.course_code
                     WHERE cg.student_id = ? 
                     ORDER BY cg.calculated_date DESC LIMIT 5";
        $stmtGrades = mysqli_prepare($conn, $gradesSql);
        mysqli_stmt_bind_param($stmtGrades, "s", $studentId);
        mysqli_stmt_execute($stmtGrades);
        $result = mysqli_stmt_get_result($stmtGrades);
        $grades = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $grades[] = $row;
        }
        $dashboard['recent_grades'] = $grades;
        
        // Get pending assignments
        $assignmentsSql = "SELECT a.*, c.course_name, s.submission_id
                          FROM assignments a
                          JOIN courses c ON a.course_code = c.course_code
                          LEFT JOIN student_submissions s ON a.assignment_id = s.assignment_id 
                                                           AND s.student_id = ? AND s.is_latest = 1
                          WHERE a.class = ? AND a.status = 'published' 
                          AND a.due_date >= CURDATE() AND s.submission_id IS NULL
                          ORDER BY a.due_date ASC LIMIT 5";
        $stmtAssignments = mysqli_prepare($conn, $assignmentsSql);
        mysqli_stmt_bind_param($stmtAssignments, "si", $studentId, $dashboard['student']['class']);
        mysqli_stmt_execute($stmtAssignments);
        $result = mysqli_stmt_get_result($stmtAssignments);
        $assignments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $assignments[] = $row;
        }
        $dashboard['pending_assignments'] = $assignments;
        
        // Log activity
        $logSql = "INSERT INTO parent_activity_log 
                  (parent_id, activity_type, activity_description) 
                  VALUES (?, 'view_dashboard', 'Viewed student dashboard')";
        $stmtLog = mysqli_prepare($conn, $logSql);
        mysqli_stmt_bind_param($stmtLog, "s", $parentId);
        mysqli_stmt_execute($stmtLog);
        
        echo json_encode(['status' => 'success', 'data' => $dashboard]);
    }
    
    // Update portal preferences
    else if ($action == "update_preferences") {
        $portalId = mysqli_real_escape_string($conn, $_POST["portal_id"]);
        $notificationPreferences = mysqli_real_escape_string($conn, $_POST["notification_preferences"]);
        $preferredLanguage = mysqli_real_escape_string($conn, $_POST["preferred_language"] ?? 'en');
        
        $sql = "UPDATE parent_portal_access 
                SET notification_preferences = ?, preferred_language = ? 
                WHERE portal_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $notificationPreferences, $preferredLanguage, $portalId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Preferences updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Change password
    else if ($action == "change_password") {
        $portalId = mysqli_real_escape_string($conn, $_POST["portal_id"]);
        $currentPassword = $_POST["current_password"];
        $newPassword = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
        
        // Verify current password
        $checkSql = "SELECT password FROM parent_portal_access WHERE portal_id = ?";
        $stmtCheck = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmtCheck, "i", $portalId);
        mysqli_stmt_execute($stmtCheck);
        $result = mysqli_stmt_get_result($stmtCheck);
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($currentPassword, $user['password'])) {
            $sql = "UPDATE parent_portal_access SET password = ? WHERE portal_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $newPassword, $portalId);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect!']);
        }
    }
    
    // Reset password request
    else if ($action == "request_password_reset") {
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        
        $sql = "SELECT * FROM parent_portal_access WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $updateSql = "UPDATE parent_portal_access 
                         SET password_reset_token = ?, password_reset_expires = ? 
                         WHERE portal_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($stmtUpdate, "ssi", $token, $expires, $row['portal_id']);
            
            if (mysqli_stmt_execute($stmtUpdate)) {
                // TODO: Send email with reset link
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Password reset instructions sent to email!',
                    'token' => $token // In production, don't return token
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email not found!']);
        }
    }
    
    // Get activity log
    else if ($action == "get_activity_log") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        $limit = intval($_POST["limit"] ?? 50);
        
        $sql = "SELECT * FROM parent_activity_log 
                WHERE parent_id = ? 
                ORDER BY activity_timestamp DESC 
                LIMIT ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $parentId, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $logs = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $logs]);
    }
}

mysqli_close($conn);
?>
