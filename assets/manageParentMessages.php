<?php
// REQ-ACD-007: Parent-Teacher Messaging System
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Send message
    if ($action == "send_message") {
        $subject = mysqli_real_escape_string($conn, $_POST["subject"]);
        $messageBody = mysqli_real_escape_string($conn, $_POST["message_body"]);
        $senderId = mysqli_real_escape_string($conn, $_POST["sender_id"]);
        $senderType = mysqli_real_escape_string($conn, $_POST["sender_type"]);
        $recipientId = mysqli_real_escape_string($conn, $_POST["recipient_id"]);
        $recipientType = mysqli_real_escape_string($conn, $_POST["recipient_type"]);
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"] ?? null);
        $messageType = mysqli_real_escape_string($conn, $_POST["message_type"] ?? 'general');
        $priority = mysqli_real_escape_string($conn, $_POST["priority"] ?? 'normal');
        $parentMessageId = $_POST["parent_message_id"] ?? null;
        
        mysqli_begin_transaction($conn);
        
        try {
            $sql = "INSERT INTO parent_messages 
                    (subject, message_body, sender_id, sender_type, recipient_id, recipient_type,
                     student_id, message_type, priority, parent_message_id, sent_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssi", 
                $subject, $messageBody, $senderId, $senderType, $recipientId, $recipientType,
                $studentId, $messageType, $priority, $parentMessageId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error sending message: " . mysqli_error($conn));
            }
            
            $messageId = mysqli_insert_id($conn);
            
            // If replying, mark parent message as replied
            if ($parentMessageId) {
                $updateParent = "UPDATE parent_messages SET is_replied = 1 WHERE message_id = ?";
                $stmtUpdate = mysqli_prepare($conn, $updateParent);
                mysqli_stmt_bind_param($stmtUpdate, "i", $parentMessageId);
                mysqli_stmt_execute($stmtUpdate);
            }
            
            // Create notification for recipient
            if ($recipientType == 'parent') {
                $notifSql = "INSERT INTO parent_notifications 
                            (parent_id, student_id, notification_type, title, message, priority,
                             reference_type, reference_id) 
                            VALUES (?, ?, 'general', ?, ?, ?, 'parent_messages', ?)";
                $stmtNotif = mysqli_prepare($conn, $notifSql);
                $notifTitle = "New message from " . ($senderType == 'teacher' ? 'Teacher' : 'Admin');
                mysqli_stmt_bind_param($stmtNotif, "sssssi", 
                    $recipientId, $studentId, $notifTitle, $subject, $priority, $messageId);
                mysqli_stmt_execute($stmtNotif);
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Message sent successfully!',
                'message_id' => $messageId
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Upload message attachment
    else if ($action == "upload_attachment") {
        $messageId = mysqli_real_escape_string($conn, $_POST["message_id"]);
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $uploadDir = "../adminUploads/messages/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = basename($_FILES['file']['name']);
            $fileSize = $_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $newFileName = "msg_" . $messageId . "_" . time() . "." . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $sql = "INSERT INTO message_attachments 
                        (message_id, file_name, file_path, file_type, file_size) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "isssi", $messageId, $fileName, $filePath, $fileType, $fileSize);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'File uploaded!',
                        'attachment_id' => mysqli_insert_id($conn)
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File upload failed!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No file selected!']);
        }
    }
    
    // Get messages (inbox)
    else if ($action == "get_messages") {
        $userId = mysqli_real_escape_string($conn, $_POST["user_id"]);
        $userType = mysqli_real_escape_string($conn, $_POST["user_type"]);
        $folder = mysqli_real_escape_string($conn, $_POST["folder"] ?? 'inbox');
        $isRead = $_POST["is_read"] ?? null;
        
        $sql = "SELECT m.*, 
                       s.fname as student_fname, s.lname as student_lname,
                       CASE 
                         WHEN m.sender_type = 'teacher' THEN CONCAT(t.fname, ' ', t.lname)
                         WHEN m.sender_type = 'parent' THEN CONCAT(pg.fname, ' ', pg.lname)
                         ELSE 'Admin'
                       END as sender_name,
                       CASE 
                         WHEN m.recipient_type = 'teacher' THEN CONCAT(t2.fname, ' ', t2.lname)
                         WHEN m.recipient_type = 'parent' THEN CONCAT(pg2.fname, ' ', pg2.lname)
                         ELSE 'Admin'
                       END as recipient_name
                FROM parent_messages m
                LEFT JOIN students s ON m.student_id = s.id
                LEFT JOIN teachers t ON m.sender_id = t.id AND m.sender_type = 'teacher'
                LEFT JOIN parent_guardian pg ON m.sender_id = pg.guardian_id AND m.sender_type = 'parent'
                LEFT JOIN teachers t2 ON m.recipient_id = t2.id AND m.recipient_type = 'teacher'
                LEFT JOIN parent_guardian pg2 ON m.recipient_id = pg2.guardian_id AND m.recipient_type = 'parent'
                WHERE ";
        
        if ($folder == 'inbox') {
            $sql .= "m.recipient_id = '$userId' AND m.recipient_type = '$userType' AND m.is_archived = 0";
        } else if ($folder == 'sent') {
            $sql .= "m.sender_id = '$userId' AND m.sender_type = '$userType' AND m.is_archived = 0";
        } else if ($folder == 'archived') {
            $sql .= "(m.recipient_id = '$userId' AND m.recipient_type = '$userType' OR 
                      m.sender_id = '$userId' AND m.sender_type = '$userType') AND m.is_archived = 1";
        } else if ($folder == 'starred') {
            $sql .= "(m.recipient_id = '$userId' AND m.recipient_type = '$userType' OR 
                      m.sender_id = '$userId' AND m.sender_type = '$userType') AND m.is_starred = 1";
        }
        
        if ($isRead !== null) {
            $sql .= " AND m.is_read = " . intval($isRead);
        }
        
        $sql .= " ORDER BY m.sent_date DESC";
        
        $result = mysqli_query($conn, $sql);
        $messages = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Get attachment count
            $countSql = "SELECT COUNT(*) as count FROM message_attachments WHERE message_id = ?";
            $stmtCount = mysqli_prepare($conn, $countSql);
            mysqli_stmt_bind_param($stmtCount, "i", $row['message_id']);
            mysqli_stmt_execute($stmtCount);
            $resultCount = mysqli_stmt_get_result($stmtCount);
            $countData = mysqli_fetch_assoc($resultCount);
            $row['attachment_count'] = $countData['count'];
            
            $messages[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $messages]);
    }
    
    // Get message thread
    else if ($action == "get_message_thread") {
        $messageId = mysqli_real_escape_string($conn, $_POST["message_id"]);
        
        // Get main message
        $sql = "SELECT m.*, 
                       s.fname as student_fname, s.lname as student_lname,
                       CASE 
                         WHEN m.sender_type = 'teacher' THEN CONCAT(t.fname, ' ', t.lname)
                         WHEN m.sender_type = 'parent' THEN CONCAT(pg.fname, ' ', pg.lname)
                         ELSE 'Admin'
                       END as sender_name,
                       CASE 
                         WHEN m.recipient_type = 'teacher' THEN CONCAT(t2.fname, ' ', t2.lname)
                         WHEN m.recipient_type = 'parent' THEN CONCAT(pg2.fname, ' ', pg2.lname)
                         ELSE 'Admin'
                       END as recipient_name
                FROM parent_messages m
                LEFT JOIN students s ON m.student_id = s.id
                LEFT JOIN teachers t ON m.sender_id = t.id AND m.sender_type = 'teacher'
                LEFT JOIN parent_guardian pg ON m.sender_id = pg.guardian_id AND m.sender_type = 'parent'
                LEFT JOIN teachers t2 ON m.recipient_id = t2.id AND m.recipient_type = 'teacher'
                LEFT JOIN parent_guardian pg2 ON m.recipient_id = pg2.guardian_id AND m.recipient_type = 'parent'
                WHERE m.message_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $message = mysqli_fetch_assoc($result);
        
        // Get attachments
        $attachSql = "SELECT * FROM message_attachments WHERE message_id = ?";
        $stmtAttach = mysqli_prepare($conn, $attachSql);
        mysqli_stmt_bind_param($stmtAttach, "i", $messageId);
        mysqli_stmt_execute($stmtAttach);
        $resultAttach = mysqli_stmt_get_result($stmtAttach);
        $attachments = [];
        while ($row = mysqli_fetch_assoc($resultAttach)) {
            $attachments[] = $row;
        }
        $message['attachments'] = $attachments;
        
        // Get replies
        $repliesSql = "SELECT m.*, 
                              CASE 
                                WHEN m.sender_type = 'teacher' THEN CONCAT(t.fname, ' ', t.lname)
                                WHEN m.sender_type = 'parent' THEN CONCAT(pg.fname, ' ', pg.lname)
                                ELSE 'Admin'
                              END as sender_name
                       FROM parent_messages m
                       LEFT JOIN teachers t ON m.sender_id = t.id AND m.sender_type = 'teacher'
                       LEFT JOIN parent_guardian pg ON m.sender_id = pg.guardian_id AND m.sender_type = 'parent'
                       WHERE m.parent_message_id = ?
                       ORDER BY m.sent_date ASC";
        
        $stmtReplies = mysqli_prepare($conn, $repliesSql);
        mysqli_stmt_bind_param($stmtReplies, "i", $messageId);
        mysqli_stmt_execute($stmtReplies);
        $resultReplies = mysqli_stmt_get_result($stmtReplies);
        $replies = [];
        while ($row = mysqli_fetch_assoc($resultReplies)) {
            $replies[] = $row;
        }
        $message['replies'] = $replies;
        
        echo json_encode(['status' => 'success', 'data' => $message]);
    }
    
    // Mark message as read
    else if ($action == "mark_as_read") {
        $messageId = mysqli_real_escape_string($conn, $_POST["message_id"]);
        
        $sql = "UPDATE parent_messages 
                SET is_read = 1, read_date = NOW() 
                WHERE message_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Message marked as read!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Toggle starred
    else if ($action == "toggle_starred") {
        $messageId = mysqli_real_escape_string($conn, $_POST["message_id"]);
        
        $sql = "UPDATE parent_messages 
                SET is_starred = NOT is_starred 
                WHERE message_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Starred status updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Archive message
    else if ($action == "archive_message") {
        $messageId = mysqli_real_escape_string($conn, $_POST["message_id"]);
        
        $sql = "UPDATE parent_messages 
                SET is_archived = 1 
                WHERE message_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Message archived!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Delete message
    else if ($action == "delete_message") {
        $messageId = mysqli_real_escape_string($conn, $_POST["message_id"]);
        
        $sql = "DELETE FROM parent_messages WHERE message_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Message deleted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Get unread count
    else if ($action == "get_unread_count") {
        $userId = mysqli_real_escape_string($conn, $_POST["user_id"]);
        $userType = mysqli_real_escape_string($conn, $_POST["user_type"]);
        
        $sql = "SELECT COUNT(*) as count FROM parent_messages 
                WHERE recipient_id = ? AND recipient_type = ? 
                AND is_read = 0 AND is_archived = 0";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $userId, $userType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        
        echo json_encode(['status' => 'success', 'count' => $data['count']]);
    }
    
    // Get teachers for messaging (for parents)
    else if ($action == "get_teachers") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        // Get student's class teachers
        $sql = "SELECT DISTINCT t.id, t.fname, t.lname, t.email, t.phone, c.course_name
                FROM curriculum_subjects cs
                JOIN subject_teachers st ON cs.curriculum_subject_id = st.curriculum_subject_id
                JOIN teachers t ON st.teacher_id = t.id
                JOIN courses c ON cs.course_code = c.course_code
                JOIN students s ON cs.class = s.class
                WHERE s.id = ? AND st.is_active = 1
                ORDER BY t.fname, t.lname";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $teachers = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $teachers[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $teachers]);
    }
}

mysqli_close($conn);
?>
