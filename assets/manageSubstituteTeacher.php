<?php
// Substitute Teacher Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create substitute request
    if ($action == "create_substitute_request") {
        $originalTeacherId = mysqli_real_escape_string($conn, $_POST["original_teacher_id"]);
        $substituteTeacherId = mysqli_real_escape_string($conn, $_POST["substitute_teacher_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        $substituteDate = mysqli_real_escape_string($conn, $_POST["substitute_date"]);
        $startTime = mysqli_real_escape_string($conn, $_POST["start_time"]);
        $endTime = mysqli_real_escape_string($conn, $_POST["end_time"]);
        $venue = mysqli_real_escape_string($conn, $_POST["venue"] ?? '');
        $reason = mysqli_real_escape_string($conn, $_POST["reason"]);
        $substituteType = mysqli_real_escape_string($conn, $_POST["substitute_type"] ?? 'single_day');
        $requestedBy = mysqli_real_escape_string($conn, $_POST["requested_by"] ?? '');
        $requestDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check if substitute teacher is available
            $checkAvailability = checkSubstituteAvailability($conn, $substituteTeacherId, $substituteDate, $startTime, $endTime);
            
            if (!$checkAvailability['available']) {
                throw new Exception($checkAvailability['message']);
            }
            
            $sql = "INSERT INTO substitute_teachers 
                    (original_teacher_id, substitute_teacher_id, course_code, class, section, 
                     substitute_date, start_time, end_time, venue, reason, substitute_type, 
                     status, request_date, requested_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'requested', ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssssss", 
                $originalTeacherId, $substituteTeacherId, $courseCode, $class, $section,
                $substituteDate, $startTime, $endTime, $venue, $reason, $substituteType,
                $requestDate, $requestedBy);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating substitute request: " . mysqli_error($conn));
            }
            
            $substituteId = mysqli_insert_id($conn);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Substitute request created successfully!',
                'substitute_id' => $substituteId
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Approve substitute request
    else if ($action == "approve_substitute") {
        $substituteId = mysqli_real_escape_string($conn, $_POST["substitute_id"]);
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"]);
        $approvedDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get substitute details
            $getSql = "SELECT * FROM substitute_teachers WHERE substitute_id = ?";
            $stmtGet = mysqli_prepare($conn, $getSql);
            mysqli_stmt_bind_param($stmtGet, "i", $substituteId);
            mysqli_stmt_execute($stmtGet);
            $result = mysqli_stmt_get_result($stmtGet);
            $substitute = mysqli_fetch_assoc($result);
            
            if (!$substitute) {
                throw new Exception("Substitute request not found!");
            }
            
            // Update substitute request
            $updateSql = "UPDATE substitute_teachers SET 
                         status = 'approved', 
                         approved_by = ?, 
                         approved_date = ? 
                         WHERE substitute_id = ?";
            
            $stmtUpdate = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($stmtUpdate, "ssi", $approvedBy, $approvedDate, $substituteId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error approving substitute: " . mysqli_error($conn));
            }
            
            // Update timetable period if period_id exists
            if (!empty($substitute['period_id'])) {
                $updatePeriod = "UPDATE timetable_periods SET 
                                is_substitute = 1, 
                                substitute_teacher_id = ?, 
                                substitute_reason = ? 
                                WHERE period_id = ?";
                
                $stmtPeriod = mysqli_prepare($conn, $updatePeriod);
                mysqli_stmt_bind_param($stmtPeriod, "ssi", 
                    $substitute['substitute_teacher_id'], $substitute['reason'], $substitute['period_id']);
                mysqli_stmt_execute($stmtPeriod);
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Substitute approved successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Complete substitute assignment
    else if ($action == "complete_substitute") {
        $substituteId = mysqli_real_escape_string($conn, $_POST["substitute_id"]);
        $topicsCovered = mysqli_real_escape_string($conn, $_POST["topics_covered"] ?? '');
        $completionNotes = mysqli_real_escape_string($conn, $_POST["completion_notes"] ?? '');
        $attendanceMarked = $_POST["attendance_marked"] ?? 0;
        
        $sql = "UPDATE substitute_teachers SET 
                status = 'completed', 
                topics_covered = ?, 
                completion_notes = ?,
                attendance_marked = ? 
                WHERE substitute_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssii", $topicsCovered, $completionNotes, $attendanceMarked, $substituteId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Substitute assignment completed!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error completing substitute: ' . mysqli_error($conn)]);
        }
    }
    
    // Reject substitute request
    else if ($action == "reject_substitute") {
        $substituteId = mysqli_real_escape_string($conn, $_POST["substitute_id"]);
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        
        $sql = "UPDATE substitute_teachers SET status = 'rejected', remarks = ? WHERE substitute_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $remarks, $substituteId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Substitute request rejected!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error rejecting substitute: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch substitute requests
    else if ($action == "fetch_substitute_requests") {
        $status = mysqli_real_escape_string($conn, $_POST["status"] ?? '');
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"] ?? '');
        $date = mysqli_real_escape_string($conn, $_POST["date"] ?? '');
        
        $sql = "SELECT st.*, 
                ot.fname as original_fname, ot.lname as original_lname, ot.email as original_email,
                sut.fname as substitute_fname, sut.lname as substitute_lname, sut.email as substitute_email,
                c.course_name 
                FROM substitute_teachers st 
                JOIN teachers ot ON st.original_teacher_id = ot.id 
                JOIN teachers sut ON st.substitute_teacher_id = sut.id 
                JOIN courses c ON st.course_code = c.course_code 
                WHERE 1=1";
        
        if (!empty($status)) {
            $sql .= " AND st.status = '$status'";
        }
        if (!empty($teacherId)) {
            $sql .= " AND (st.original_teacher_id = '$teacherId' OR st.substitute_teacher_id = '$teacherId')";
        }
        if (!empty($date)) {
            $sql .= " AND st.substitute_date = '$date'";
        }
        
        $sql .= " ORDER BY st.substitute_date DESC, st.start_time";
        
        $result = mysqli_query($conn, $sql);
        $substitutes = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $substitutes[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $substitutes]);
    }
    
    // Find available substitutes
    else if ($action == "find_available_substitutes") {
        $substituteDate = mysqli_real_escape_string($conn, $_POST["substitute_date"]);
        $startTime = mysqli_real_escape_string($conn, $_POST["start_time"]);
        $endTime = mysqli_real_escape_string($conn, $_POST["end_time"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"] ?? '');
        
        $dayOfWeek = date('l', strtotime($substituteDate)); // Get day name
        
        // Find teachers who are free at that time
        $sql = "SELECT t.id, t.fname, t.lname, t.email, t.phone, t.subjects 
                FROM teachers t 
                WHERE t.id NOT IN (
                    SELECT tp.teacher_id 
                    FROM timetable_periods tp 
                    JOIN timetable tt ON tp.timetable_id = tt.timetable_id 
                    WHERE tt.status = 'active' 
                    AND tp.day_of_week = ?
                    AND ((tp.start_time <= ? AND tp.end_time > ?) 
                         OR (tp.start_time < ? AND tp.end_time >= ?)
                         OR (tp.start_time >= ? AND tp.end_time <= ?))
                )
                AND t.id NOT IN (
                    SELECT st.substitute_teacher_id 
                    FROM substitute_teachers st 
                    WHERE st.substitute_date = ? 
                    AND st.status IN ('requested', 'approved')
                    AND ((st.start_time <= ? AND st.end_time > ?) 
                         OR (st.start_time < ? AND st.end_time >= ?)
                         OR (st.start_time >= ? AND st.end_time <= ?))
                )";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssssssss", 
            $dayOfWeek, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime,
            $substituteDate, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime);
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $availableTeachers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $availableTeachers[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $availableTeachers]);
    }
    
    // Update teacher availability
    else if ($action == "update_availability") {
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        $dayOfWeek = mysqli_real_escape_string($conn, $_POST["day_of_week"]);
        $startTime = mysqli_real_escape_string($conn, $_POST["start_time"]);
        $endTime = mysqli_real_escape_string($conn, $_POST["end_time"]);
        $isAvailable = $_POST["is_available"] ?? 1;
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        
        $sql = "INSERT INTO teacher_availability 
                (teacher_id, day_of_week, start_time, end_time, is_available, academic_year) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE is_available = VALUES(is_available)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $teacherId, $dayOfWeek, $startTime, $endTime, $isAvailable, $academicYear);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Availability updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating availability: ' . mysqli_error($conn)]);
        }
    }
}

// Helper function to check substitute availability
function checkSubstituteAvailability($conn, $teacherId, $date, $startTime, $endTime) {
    $dayOfWeek = date('l', strtotime($date));
    
    // Check if teacher has any scheduled class
    $sql = "SELECT * FROM timetable_periods tp 
            JOIN timetable t ON tp.timetable_id = t.timetable_id 
            WHERE tp.teacher_id = ? AND tp.day_of_week = ? 
            AND t.status = 'active'
            AND ((tp.start_time <= ? AND tp.end_time > ?) 
                 OR (tp.start_time < ? AND tp.end_time >= ?)
                 OR (tp.start_time >= ? AND tp.end_time <= ?))";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", $teacherId, $dayOfWeek, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return ['available' => false, 'message' => 'Substitute teacher has a scheduled class at this time'];
    }
    
    // Check if already assigned as substitute
    $sql2 = "SELECT * FROM substitute_teachers 
             WHERE substitute_teacher_id = ? AND substitute_date = ? 
             AND status IN ('requested', 'approved')
             AND ((start_time <= ? AND end_time > ?) 
                  OR (start_time < ? AND end_time >= ?)
                  OR (start_time >= ? AND end_time <= ?))";
    
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "ssssssss", $teacherId, $date, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    
    if (mysqli_num_rows($result2) > 0) {
        return ['available' => false, 'message' => 'Teacher already assigned as substitute at this time'];
    }
    
    return ['available' => true, 'message' => 'Teacher is available'];
}

mysqli_close($conn);
?>
