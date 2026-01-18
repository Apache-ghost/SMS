<?php
// REQ-ACD-007: Announcements and Calendar Management
session_start();
include("config.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create announcement
    if ($action == "create_announcement") {
        $title = mysqli_real_escape_string($conn, $_POST["title"]);
        $content = mysqli_real_escape_string($conn, $_POST["content"]);
        $announcementType = mysqli_real_escape_string($conn, $_POST["announcement_type"] ?? 'general');
        $priority = mysqli_real_escape_string($conn, $_POST["priority"] ?? 'normal');
        $targetAudience = mysqli_real_escape_string($conn, $_POST["target_audience"]);
        $class = !empty($_POST["class"]) ? intval($_POST["class"]) : null;
        $section = !empty($_POST["section"]) ? mysqli_real_escape_string($conn, $_POST["section"]) : null;
        $departmentCode = !empty($_POST["department_code"]) ? mysqli_real_escape_string($conn, $_POST["department_code"]) : null;
        $displayFrom = mysqli_real_escape_string($conn, $_POST["display_from"]);
        $displayUntil = mysqli_real_escape_string($conn, $_POST["display_until"]);
        $isPinned = isset($_POST["is_pinned"]) ? 1 : 0;
        $allowComments = isset($_POST["allow_comments"]) ? 1 : 0;
        $externalLink = !empty($_POST["external_link"]) ? mysqli_real_escape_string($conn, $_POST["external_link"]) : null;
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? 'draft');
        $publishedBy = mysqli_real_escape_string($conn, $_POST["published_by"]);
        
        $sql = "INSERT INTO announcements 
                (title, content, announcement_type, priority, target_audience, class, section,
                 department_code, display_from, display_until, is_pinned, allow_comments,
                 external_link, status, published_by, published_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . mysqli_error($conn)]);
            exit();
        }
        
        mysqli_stmt_bind_param($stmt, "ssssssssssiisss", 
            $title, $content, $announcementType, $priority, $targetAudience, $class, $section,
            $departmentCode, $displayFrom, $displayUntil, $isPinned, $allowComments,
            $externalLink, $status, $publishedBy);
        
        if (mysqli_stmt_execute($stmt)) {
            $announcementId = mysqli_insert_id($conn);
            
            // If published, create notifications
            if ($status == 'published') {
                createAnnouncementNotifications($conn, $announcementId, $targetAudience, $class, $section, $departmentCode, $title);
            }
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Announcement created!',
                'announcement_id' => $announcementId
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    }
    
    // Update announcement
    else if ($action == "update_announcement") {
        $announcementId = mysqli_real_escape_string($conn, $_POST["announcement_id"]);
        $title = mysqli_real_escape_string($conn, $_POST["title"]);
        $content = mysqli_real_escape_string($conn, $_POST["content"]);
        $displayFrom = mysqli_real_escape_string($conn, $_POST["display_from"]);
        $displayUntil = mysqli_real_escape_string($conn, $_POST["display_until"]);
        $isPinned = $_POST["is_pinned"] ?? 0;
        
        $sql = "UPDATE announcements 
                SET title = ?, content = ?, display_from = ?, display_until = ?, is_pinned = ? 
                WHERE announcement_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssis", $title, $content, $displayFrom, $displayUntil, $isPinned, $announcementId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Announcement updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Publish announcement
    else if ($action == "publish_announcement") {
        $announcementId = mysqli_real_escape_string($conn, $_POST["announcement_id"]);
        
        // Get announcement details
        $getAnn = "SELECT * FROM announcements WHERE announcement_id = ?";
        $stmtGet = mysqli_prepare($conn, $getAnn);
        mysqli_stmt_bind_param($stmtGet, "i", $announcementId);
        mysqli_stmt_execute($stmtGet);
        $result = mysqli_stmt_get_result($stmtGet);
        $announcement = mysqli_fetch_assoc($result);
        
        $sql = "UPDATE announcements 
                SET status = 'published', published_date = NOW() 
                WHERE announcement_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $announcementId);
        
        if (mysqli_stmt_execute($stmt)) {
            // Create notifications
            createAnnouncementNotifications($conn, $announcementId, $announcement['target_audience'], 
                                          $announcement['class'], $announcement['section'], 
                                          $announcement['department_code'], $announcement['title']);
            
            echo json_encode(['status' => 'success', 'message' => 'Announcement published!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Get announcements
    else if ($action == "get_announcements") {
        $targetAudience = mysqli_real_escape_string($conn, $_POST["target_audience"] ?? '');
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? '');
        $class = $_POST["class"] ?? null;
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        
        $sql = "SELECT a.*, 
                       CASE 
                         WHEN a.published_by IN (SELECT id FROM teachers) 
                         THEN (SELECT CONCAT(fname, ' ', lname) FROM teachers WHERE id = a.published_by)
                         ELSE 'Admin'
                       END as publisher_name
                FROM announcements a 
                WHERE (a.display_from <= NOW() OR ? = 'all')";
        
        $showAll = ($status == 'draft') ? 'all' : 'active';
        
        if (!empty($targetAudience)) {
            $sql .= " AND (a.target_audience = '$targetAudience' OR a.target_audience = 'all')";
        }
        
        if (!empty($status)) {
            $sql .= " AND a.status = '$status'";
        }
        
        if ($class !== null) {
            $sql .= " AND (a.class = $class OR a.class IS NULL)";
        }
        
        if (!empty($section)) {
            $sql .= " AND (a.section = '$section' OR a.section IS NULL)";
        }
        
        $sql .= " ORDER BY a.is_pinned DESC, a.published_date DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $showAll);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $announcements = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Check if expired
            $row['is_expired'] = (strtotime($row['display_until']) < time());
            $announcements[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $announcements]);
    }
    
    // Get single announcement details
    else if ($action == "get_announcement_details") {
        $announcementId = mysqli_real_escape_string($conn, $_POST["announcement_id"]);
        
        $sql = "SELECT a.*, 
                       CASE 
                         WHEN a.published_by IN (SELECT id FROM teachers) 
                         THEN (SELECT CONCAT(fname, ' ', lname) FROM teachers WHERE id = a.published_by)
                         WHEN a.published_by IN (SELECT id FROM admins) 
                         THEN (SELECT CONCAT(fname, ' ', lname) FROM admins WHERE id = a.published_by)
                         ELSE 'Admin'
                       END as publisher_name
                FROM announcements a 
                WHERE a.announcement_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $announcementId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Announcement not found']);
        }
    }
    
    // Mark announcement as read
    else if ($action == "mark_announcement_read") {
        $announcementId = mysqli_real_escape_string($conn, $_POST["announcement_id"]);
        $userId = mysqli_real_escape_string($conn, $_POST["user_id"]);
        $userType = mysqli_real_escape_string($conn, $_POST["user_type"]);
        
        // Check if already exists
        $checkSql = "SELECT * FROM announcement_recipients 
                    WHERE announcement_id = ? AND user_id = ? AND user_type = ?";
        $stmtCheck = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmtCheck, "iss", $announcementId, $userId, $userType);
        mysqli_stmt_execute($stmtCheck);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($stmtCheck)) > 0) {
            $sql = "UPDATE announcement_recipients 
                    SET is_read = 1, read_date = NOW() 
                    WHERE announcement_id = ? AND user_id = ? AND user_type = ?";
        } else {
            $sql = "INSERT INTO announcement_recipients 
                    (announcement_id, user_id, user_type, is_read, read_date) 
                    VALUES (?, ?, ?, 1, NOW())";
        }
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $announcementId, $userId, $userType);
        
        if (mysqli_stmt_execute($stmt)) {
            // Increment view count
            $updateView = "UPDATE announcements SET view_count = view_count + 1 WHERE announcement_id = ?";
            $stmtView = mysqli_prepare($conn, $updateView);
            mysqli_stmt_bind_param($stmtView, "i", $announcementId);
            mysqli_stmt_execute($stmtView);
            
            echo json_encode(['status' => 'success', 'message' => 'Announcement marked as read!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Add comment to announcement
    else if ($action == "add_comment") {
        $announcementId = mysqli_real_escape_string($conn, $_POST["announcement_id"]);
        $commenterId = mysqli_real_escape_string($conn, $_POST["commenter_id"]);
        $commenterType = mysqli_real_escape_string($conn, $_POST["commenter_type"]);
        $commentText = mysqli_real_escape_string($conn, $_POST["comment_text"]);
        
        $sql = "INSERT INTO announcement_comments 
                (announcement_id, commenter_id, commenter_type, comment_text) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isss", $announcementId, $commenterId, $commenterType, $commentText);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Comment added!',
                'comment_id' => mysqli_insert_id($conn)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Delete announcement
    else if ($action == "delete_announcement") {
        $announcementId = mysqli_real_escape_string($conn, $_POST["announcement_id"]);
        
        $sql = "DELETE FROM announcements WHERE announcement_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $announcementId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Announcement deleted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Create calendar event
    else if ($action == "create_calendar_event") {
        $eventTitle = mysqli_real_escape_string($conn, $_POST["event_title"]);
        $eventDescription = mysqli_real_escape_string($conn, $_POST["event_description"] ?? '');
        $eventType = mysqli_real_escape_string($conn, $_POST["event_type"]);
        $eventCategory = mysqli_real_escape_string($conn, $_POST["event_category"] ?? 'general');
        $startDate = mysqli_real_escape_string($conn, $_POST["start_date"]);
        $endDate = mysqli_real_escape_string($conn, $_POST["end_date"]);
        $startTime = mysqli_real_escape_string($conn, $_POST["start_time"] ?? null);
        $endTime = mysqli_real_escape_string($conn, $_POST["end_time"] ?? null);
        $isAllDay = $_POST["is_all_day"] ?? 0;
        $location = mysqli_real_escape_string($conn, $_POST["location"] ?? null);
        $organizer = mysqli_real_escape_string($conn, $_POST["organizer"] ?? null);
        $targetAudience = mysqli_real_escape_string($conn, $_POST["target_audience"]);
        $class = $_POST["class"] ?? null;
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? null);
        $departmentCode = mysqli_real_escape_string($conn, $_POST["department_code"] ?? null);
        $colorCode = mysqli_real_escape_string($conn, $_POST["color_code"] ?? '#3788d8');
        $registrationRequired = $_POST["registration_required"] ?? 0;
        $maxParticipants = $_POST["max_participants"] ?? null;
        $createdBy = mysqli_real_escape_string($conn, $_POST["created_by"]);
        
        $sql = "INSERT INTO school_calendar 
                (event_title, event_description, event_type, event_category, start_date, end_date,
                 start_time, end_time, is_all_day, location, organizer, target_audience, class,
                 section, department_code, color_code, registration_required, max_participants,
                 created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssisssisssiis", 
            $eventTitle, $eventDescription, $eventType, $eventCategory, $startDate, $endDate,
            $startTime, $endTime, $isAllDay, $location, $organizer, $targetAudience, $class,
            $section, $departmentCode, $colorCode, $registrationRequired, $maxParticipants,
            $createdBy);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Calendar event created!',
                'event_id' => mysqli_insert_id($conn)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
    
    // Get calendar events
    else if ($action == "get_calendar_events") {
        $startDate = mysqli_real_escape_string($conn, $_POST["start_date"] ?? date('Y-m-01'));
        $endDate = mysqli_real_escape_string($conn, $_POST["end_date"] ?? date('Y-m-t'));
        $targetAudience = mysqli_real_escape_string($conn, $_POST["target_audience"] ?? '');
        $eventType = mysqli_real_escape_string($conn, $_POST["event_type"] ?? '');
        
        $sql = "SELECT * FROM school_calendar 
                WHERE is_cancelled = 0 
                AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?))";
        
        if (!empty($targetAudience)) {
            $sql .= " AND (target_audience = '$targetAudience' OR target_audience = 'all')";
        }
        
        if (!empty($eventType)) {
            $sql .= " AND event_type = '$eventType'";
        }
        
        $sql .= " ORDER BY start_date, start_time";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $startDate, $endDate, $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $events = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $events]);
    }
    
    // Register for event
    else if ($action == "register_for_event") {
        $eventId = mysqli_real_escape_string($conn, $_POST["event_id"]);
        $userId = mysqli_real_escape_string($conn, $_POST["user_id"]);
        $userType = mysqli_real_escape_string($conn, $_POST["user_type"]);
        
        // Check if event requires registration
        $checkSql = "SELECT * FROM school_calendar WHERE event_id = ?";
        $stmtCheck = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmtCheck, "i", $eventId);
        mysqli_stmt_execute($stmtCheck);
        $result = mysqli_stmt_get_result($stmtCheck);
        $event = mysqli_fetch_assoc($result);
        
        if (!$event['registration_required']) {
            echo json_encode(['status' => 'error', 'message' => 'Registration not required for this event!']);
            exit();
        }
        
        // Check if already registered
        $checkReg = "SELECT * FROM calendar_registrations WHERE event_id = ? AND user_id = ?";
        $stmtCheckReg = mysqli_prepare($conn, $checkReg);
        mysqli_stmt_bind_param($stmtCheckReg, "is", $eventId, $userId);
        mysqli_stmt_execute($stmtCheckReg);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($stmtCheckReg)) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Already registered!']);
            exit();
        }
        
        // Check capacity
        if ($event['max_participants']) {
            $countSql = "SELECT COUNT(*) as count FROM calendar_registrations WHERE event_id = ?";
            $stmtCount = mysqli_prepare($conn, $countSql);
            mysqli_stmt_bind_param($stmtCount, "i", $eventId);
            mysqli_stmt_execute($stmtCount);
            $resultCount = mysqli_stmt_get_result($stmtCount);
            $countData = mysqli_fetch_assoc($resultCount);
            
            if ($countData['count'] >= $event['max_participants']) {
                echo json_encode(['status' => 'error', 'message' => 'Event is full!']);
                exit();
            }
        }
        
        $sql = "INSERT INTO calendar_registrations (event_id, user_id, user_type) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $eventId, $userId, $userType);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Successfully registered!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    }
}

// Helper function to create notifications for announcements
function createAnnouncementNotifications($conn, $announcementId, $targetAudience, $class, $section, $departmentCode, $title) {
    if ($targetAudience == 'parents' || $targetAudience == 'all') {
        $sql = "SELECT DISTINCT pg.guardian_id FROM parent_guardian pg";
        
        if ($class !== null) {
            $sql .= " JOIN student_parent sp ON pg.guardian_id = sp.parent_id
                     JOIN students s ON sp.student_id = s.id
                     WHERE s.class = $class";
            if ($section) {
                $sql .= " AND s.section = '$section'";
            }
        }
        
        $result = mysqli_query($conn, $sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifSql = "INSERT INTO parent_notifications 
                        (parent_id, notification_type, title, message, reference_type, reference_id) 
                        VALUES (?, 'announcement', ?, 'New announcement posted', 'announcements', ?)";
            $stmtNotif = mysqli_prepare($conn, $notifSql);
            mysqli_stmt_bind_param($stmtNotif, "ssi", $row['guardian_id'], $title, $announcementId);
            mysqli_stmt_execute($stmtNotif);
        }
    }
}

mysqli_close($conn);
?>
