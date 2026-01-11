<?php
// REQ-ACD-007: Parent Notifications System
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create notification
    if ($action == "create_notification") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"] ?? null);
        $notificationType = mysqli_real_escape_string($conn, $_POST["notification_type"]);
        $title = mysqli_real_escape_string($conn, $_POST["title"]);
        $message = mysqli_real_escape_string($conn, $_POST["message"]);
        $priority = mysqli_real_escape_string($conn, $_POST["priority"] ?? 'normal');
        $referenceType = mysqli_real_escape_string($conn, $_POST["reference_type"] ?? null);
        $referenceId = $_POST["reference_id"] ?? null;
        $actionUrl = mysqli_real_escape_string($conn, $_POST["action_url"] ?? null);
        $sendEmail = $_POST["send_email"] ?? 1;
        $sendSms = $_POST["send_sms"] ?? 0;
        
        $sql = "INSERT INTO parent_notifications 
                (parent_id, student_id, notification_type, title, message, priority,
                 reference_type, reference_id, action_url, send_email, send_sms) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssissi", 
            $parentId, $studentId, $notificationType, $title, $message, $priority,
            $referenceType, $referenceId, $actionUrl, $sendEmail, $sendSms);
        
        if (mysqli_stmt_execute($stmt)) {
            $notificationId = mysqli_insert_id($conn);
            
            // TODO: Send email/SMS if requested
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Notification created!',
                'notification_id' => $notificationId
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Create notification from template
    else if ($action == "create_from_template") {
        $templateName = mysqli_real_escape_string($conn, $_POST["template_name"]);
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        $variables = json_decode($_POST["variables"], true);
        
        // Get template
        $getSql = "SELECT * FROM notification_templates WHERE template_name = ? AND is_active = 1";
        $stmtGet = mysqli_prepare($conn, $getSql);
        mysqli_stmt_bind_param($stmtGet, "s", $templateName);
        mysqli_stmt_execute($stmtGet);
        $result = mysqli_stmt_get_result($stmtGet);
        $template = mysqli_fetch_assoc($result);
        
        if (!$template) {
            echo json_encode(['status' => 'error', 'message' => 'Template not found!']);
            exit();
        }
        
        // Replace variables in template
        $subject = $template['subject_template'];
        $message = $template['message_template'];
        
        foreach ($variables as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value, $subject);
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        // Create notification
        $insertSql = "INSERT INTO parent_notifications 
                     (parent_id, student_id, notification_type, title, message, send_email) 
                     VALUES (?, ?, ?, ?, ?, 1)";
        
        $stmtInsert = mysqli_prepare($conn, $insertSql);
        $studentId = $variables['student_id'] ?? null;
        mysqli_stmt_bind_param($stmtInsert, "sssss", 
            $parentId, $studentId, $template['template_type'], $subject, $message);
        
        if (mysqli_stmt_execute($stmtInsert)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Notification created from template!',
                'notification_id' => mysqli_insert_id($conn)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Get notifications for parent
    else if ($action == "get_notifications") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        $isRead = $_POST["is_read"] ?? null;
        $notificationType = mysqli_real_escape_string($conn, $_POST["notification_type"] ?? '');
        $limit = intval($_POST["limit"] ?? 50);
        
        $sql = "SELECT n.*, s.fname as student_fname, s.lname as student_lname 
                FROM parent_notifications n
                LEFT JOIN students s ON n.student_id = s.id
                WHERE n.parent_id = ?";
        
        if ($isRead !== null) {
            $sql .= " AND n.is_read = " . intval($isRead);
        }
        
        if (!empty($notificationType)) {
            $sql .= " AND n.notification_type = '$notificationType'";
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $parentId, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $notifications]);
    }
    
    // Mark notification as read
    else if ($action == "mark_as_read") {
        $notificationId = mysqli_real_escape_string($conn, $_POST["notification_id"]);
        
        $sql = "UPDATE parent_notifications 
                SET is_read = 1, read_date = NOW() 
                WHERE notification_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $notificationId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Notification marked as read!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Mark all as read
    else if ($action == "mark_all_as_read") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        
        $sql = "UPDATE parent_notifications 
                SET is_read = 1, read_date = NOW() 
                WHERE parent_id = ? AND is_read = 0";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $parentId);
        
        if (mysqli_stmt_execute($stmt)) {
            $count = mysqli_affected_rows($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "$count notifications marked as read!"
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Delete notification
    else if ($action == "delete_notification") {
        $notificationId = mysqli_real_escape_string($conn, $_POST["notification_id"]);
        
        $sql = "DELETE FROM parent_notifications WHERE notification_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $notificationId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Notification deleted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Get unread count
    else if ($action == "get_unread_count") {
        $parentId = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        
        $sql = "SELECT COUNT(*) as count FROM parent_notifications 
                WHERE parent_id = ? AND is_read = 0";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        
        echo json_encode(['status' => 'success', 'count' => $data['count']]);
    }
    
    // Auto-notify on grade publish
    else if ($action == "notify_grade_published") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $grade = mysqli_real_escape_string($conn, $_POST["grade"]);
        $marksObtained = mysqli_real_escape_string($conn, $_POST["marks_obtained"]);
        $totalMarks = mysqli_real_escape_string($conn, $_POST["total_marks"]);
        
        // Get student and course info
        $getInfoSql = "SELECT s.fname, s.lname, c.course_name 
                      FROM students s, courses c 
                      WHERE s.id = ? AND c.course_code = ?";
        $stmtInfo = mysqli_prepare($conn, $getInfoSql);
        mysqli_stmt_bind_param($stmtInfo, "ss", $studentId, $courseCode);
        mysqli_stmt_execute($stmtInfo);
        $resultInfo = mysqli_stmt_get_result($stmtInfo);
        $info = mysqli_fetch_assoc($resultInfo);
        
        // Get parent IDs
        $getParentsSql = "SELECT parent_id FROM student_parent WHERE student_id = ?";
        $stmtParents = mysqli_prepare($conn, $getParentsSql);
        mysqli_stmt_bind_param($stmtParents, "s", $studentId);
        mysqli_stmt_execute($stmtParents);
        $resultParents = mysqli_stmt_get_result($stmtParents);
        
        $successCount = 0;
        
        while ($parent = mysqli_fetch_assoc($resultParents)) {
            $variables = json_encode([
                'student_id' => $studentId,
                'student_name' => $info['fname'] . ' ' . $info['lname'],
                'course_name' => $info['course_name'],
                'grade' => $grade,
                'marks_obtained' => $marksObtained,
                'total_marks' => $totalMarks,
                'school_name' => 'School Name' // TODO: Get from settings
            ]);
            
            // Create notification from template
            $_POST['parent_id'] = $parent['parent_id'];
            $_POST['template_name'] = 'grade_published';
            $_POST['variables'] = $variables;
            
            // Call create_from_template internally
            $templateName = 'grade_published';
            $parentId = $parent['parent_id'];
            $vars = json_decode($variables, true);
            
            $getSql = "SELECT * FROM notification_templates WHERE template_name = ? AND is_active = 1";
            $stmtGet = mysqli_prepare($conn, $getSql);
            mysqli_stmt_bind_param($stmtGet, "s", $templateName);
            mysqli_stmt_execute($stmtGet);
            $result = mysqli_stmt_get_result($stmtGet);
            $template = mysqli_fetch_assoc($result);
            
            if ($template) {
                $subject = $template['subject_template'];
                $message = $template['message_template'];
                
                foreach ($vars as $key => $value) {
                    $subject = str_replace('{' . $key . '}', $value, $subject);
                    $message = str_replace('{' . $key . '}', $value, $message);
                }
                
                $insertSql = "INSERT INTO parent_notifications 
                             (parent_id, student_id, notification_type, title, message, 
                              priority, send_email) 
                             VALUES (?, ?, 'grade_update', ?, ?, 'normal', 1)";
                
                $stmtInsert = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($stmtInsert, "ssss", 
                    $parentId, $studentId, $subject, $message);
                
                if (mysqli_stmt_execute($stmtInsert)) {
                    $successCount++;
                }
            }
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => "Notifications sent to $successCount parents!"
        ]);
    }
    
    // Clean up expired notifications
    else if ($action == "cleanup_expired") {
        $sql = "DELETE FROM parent_notifications 
                WHERE expires_at IS NOT NULL AND expires_at < NOW()";
        
        $result = mysqli_query($conn, $sql);
        $deleted = mysqli_affected_rows($conn);
        
        echo json_encode([
            'status' => 'success', 
            'message' => "$deleted expired notifications deleted!"
        ]);
    }
}

mysqli_close($conn);
?>
