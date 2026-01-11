<?php
// Timetable Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create timetable
    if ($action == "create_timetable") {
        $timetableCode = mysqli_real_escape_string($conn, $_POST["timetable_code"]);
        $timetableName = mysqli_real_escape_string($conn, $_POST["timetable_name"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? '1');
        $effectiveFrom = mysqli_real_escape_string($conn, $_POST["effective_from"]);
        $createdBy = mysqli_real_escape_string($conn, $_POST["created_by"] ?? '');
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        
        $sql = "INSERT INTO timetable (timetable_code, timetable_name, class, section, academic_year, semester, effective_from, created_by, remarks, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssss", $timetableCode, $timetableName, $class, $section, $academicYear, $semester, $effectiveFrom, $createdBy, $remarks);
        
        if (mysqli_stmt_execute($stmt)) {
            $timetableId = mysqli_insert_id($conn);
            echo json_encode(['status' => 'success', 'message' => 'Timetable created successfully!', 'timetable_id' => $timetableId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error creating timetable: ' . mysqli_error($conn)]);
        }
    }
    
    // Add period to timetable
    else if ($action == "add_period") {
        $timetableId = mysqli_real_escape_string($conn, $_POST["timetable_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        $dayOfWeek = mysqli_real_escape_string($conn, $_POST["day_of_week"]);
        $periodNumber = $_POST["period_number"];
        $startTime = mysqli_real_escape_string($conn, $_POST["start_time"]);
        $endTime = mysqli_real_escape_string($conn, $_POST["end_time"]);
        $venue = mysqli_real_escape_string($conn, $_POST["venue"]);
        $groupNumber = mysqli_real_escape_string($conn, $_POST["group_number"] ?? '');
        $periodType = mysqli_real_escape_string($conn, $_POST["period_type"] ?? 'lecture');
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check for conflicts
            $conflicts = checkTimetableConflicts($conn, $timetableId, $teacherId, $venue, $dayOfWeek, $startTime, $endTime);
            
            if (!empty($conflicts)) {
                throw new Exception("Conflicts detected: " . implode(", ", $conflicts));
            }
            
            $sql = "INSERT INTO timetable_periods 
                    (timetable_id, course_code, teacher_id, day_of_week, period_number, start_time, end_time, venue, group_number, period_type, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isssssssss", 
                $timetableId, $courseCode, $teacherId, $dayOfWeek, $periodNumber, 
                $startTime, $endTime, $venue, $groupNumber, $periodType, $remarks);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error adding period: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Period added successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Fetch timetable
    else if ($action == "fetch_timetable") {
        $timetableId = mysqli_real_escape_string($conn, $_POST["timetable_id"] ?? '');
        $class = mysqli_real_escape_string($conn, $_POST["class"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        
        $sql = "SELECT * FROM timetable WHERE 1=1";
        
        if (!empty($timetableId)) {
            $sql .= " AND timetable_id = '$timetableId'";
        }
        if (!empty($class)) {
            $sql .= " AND class = '$class'";
        }
        if (!empty($academicYear)) {
            $sql .= " AND academic_year = '$academicYear'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = mysqli_query($conn, $sql);
        $timetables = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $timetables[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $timetables]);
    }
    
    // Fetch timetable periods
    else if ($action == "fetch_periods") {
        $timetableId = mysqli_real_escape_string($conn, $_POST["timetable_id"]);
        
        $sql = "SELECT tp.*, c.course_name, t.fname, t.lname, cl.classroom_name 
                FROM timetable_periods tp 
                JOIN courses c ON tp.course_code = c.course_code 
                JOIN teachers t ON tp.teacher_id = t.id 
                LEFT JOIN classrooms cl ON tp.venue = cl.classroom_code 
                WHERE tp.timetable_id = ? 
                ORDER BY 
                    FIELD(tp.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    tp.start_time";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $timetableId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $periods = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $periods[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $periods]);
    }
    
    // Approve timetable
    else if ($action == "approve_timetable") {
        $timetableId = mysqli_real_escape_string($conn, $_POST["timetable_id"]);
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"]);
        $approvedDate = date('Y-m-d');
        
        $sql = "UPDATE timetable SET status = 'active', approved_by = ?, approved_date = ? WHERE timetable_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $approvedBy, $approvedDate, $timetableId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Timetable approved and activated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error approving timetable: ' . mysqli_error($conn)]);
        }
    }
    
    // Update period
    else if ($action == "update_period") {
        $periodId = mysqli_real_escape_string($conn, $_POST["period_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        $startTime = mysqli_real_escape_string($conn, $_POST["start_time"]);
        $endTime = mysqli_real_escape_string($conn, $_POST["end_time"]);
        $venue = mysqli_real_escape_string($conn, $_POST["venue"]);
        
        $sql = "UPDATE timetable_periods SET 
                course_code = ?, teacher_id = ?, start_time = ?, end_time = ?, venue = ? 
                WHERE period_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $courseCode, $teacherId, $startTime, $endTime, $venue, $periodId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Period updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating period: ' . mysqli_error($conn)]);
        }
    }
    
    // Delete period
    else if ($action == "delete_period") {
        $periodId = mysqli_real_escape_string($conn, $_POST["period_id"]);
        
        $sql = "DELETE FROM timetable_periods WHERE period_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $periodId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Period deleted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting period: ' . mysqli_error($conn)]);
        }
    }
    
    // Get teacher's timetable
    else if ($action == "fetch_teacher_timetable") {
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        
        $sql = "SELECT tp.*, c.course_name, t.timetable_name, t.class, t.section 
                FROM timetable_periods tp 
                JOIN courses c ON tp.course_code = c.course_code 
                JOIN timetable t ON tp.timetable_id = t.timetable_id 
                WHERE tp.teacher_id = ? AND t.status = 'active'";
        
        if (!empty($academicYear)) {
            $sql .= " AND t.academic_year = '$academicYear'";
        }
        
        $sql .= " ORDER BY 
                  FIELD(tp.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                  tp.start_time";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $teacherId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $schedule = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $schedule[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $schedule]);
    }
}

// Helper function to check conflicts
function checkTimetableConflicts($conn, $timetableId, $teacherId, $venue, $dayOfWeek, $startTime, $endTime) {
    $conflicts = [];
    
    // Check teacher clash
    $sqlTeacher = "SELECT * FROM timetable_periods tp 
                   JOIN timetable t ON tp.timetable_id = t.timetable_id 
                   WHERE tp.teacher_id = ? AND tp.day_of_week = ? 
                   AND t.status = 'active'
                   AND ((tp.start_time <= ? AND tp.end_time > ?) 
                        OR (tp.start_time < ? AND tp.end_time >= ?)
                        OR (tp.start_time >= ? AND tp.end_time <= ?))";
    
    $stmt = mysqli_prepare($conn, $sqlTeacher);
    mysqli_stmt_bind_param($stmt, "ssssssss", $teacherId, $dayOfWeek, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $conflicts[] = "Teacher already has a class at this time";
    }
    
    // Check venue clash
    $sqlVenue = "SELECT * FROM timetable_periods tp 
                 JOIN timetable t ON tp.timetable_id = t.timetable_id 
                 WHERE tp.venue = ? AND tp.day_of_week = ? 
                 AND t.status = 'active'
                 AND ((tp.start_time <= ? AND tp.end_time > ?) 
                      OR (tp.start_time < ? AND tp.end_time >= ?)
                      OR (tp.start_time >= ? AND tp.end_time <= ?))";
    
    $stmt2 = mysqli_prepare($conn, $sqlVenue);
    mysqli_stmt_bind_param($stmt2, "ssssssss", $venue, $dayOfWeek, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    
    if (mysqli_num_rows($result2) > 0) {
        $conflicts[] = "Venue already occupied at this time";
    }
    
    return $conflicts;
}

mysqli_close($conn);
?>
