<?php
// Curriculum Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create curriculum
    if ($action == "create_curriculum") {
        $curriculumCode = mysqli_real_escape_string($conn, $_POST["curriculum_code"]);
        $curriculumName = mysqli_real_escape_string($conn, $_POST["curriculum_name"]);
        $gradeLevel = mysqli_real_escape_string($conn, $_POST["grade_level"]);
        $departmentCode = mysqli_real_escape_string($conn, $_POST["department_code"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $description = mysqli_real_escape_string($conn, $_POST["description"] ?? '');
        $createdBy = mysqli_real_escape_string($conn, $_POST["created_by"] ?? '');
        
        $sql = "INSERT INTO curriculum (curriculum_code, curriculum_name, grade_level, department_code, academic_year, description, created_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $curriculumCode, $curriculumName, $gradeLevel, $departmentCode, $academicYear, $description, $createdBy);
        
        if (mysqli_stmt_execute($stmt)) {
            $curriculumId = mysqli_insert_id($conn);
            echo json_encode(['status' => 'success', 'message' => 'Curriculum created successfully!', 'curriculum_id' => $curriculumId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error creating curriculum: ' . mysqli_error($conn)]);
        }
    }
    
    // Add subject to curriculum
    else if ($action == "add_curriculum_subject") {
        $curriculumId = mysqli_real_escape_string($conn, $_POST["curriculum_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $subjectType = mysqli_real_escape_string($conn, $_POST["subject_type"] ?? 'core');
        $creditHours = $_POST["credit_hours"] ?? 3;
        $hoursPerWeek = $_POST["hours_per_week"] ?? 3;
        $passMarks = $_POST["pass_marks"] ?? 40;
        $totalMarks = $_POST["total_marks"] ?? 100;
        $weightage = $_POST["weightage"] ?? 0;
        $sequenceOrder = $_POST["sequence_order"] ?? 0;
        $prerequisite = mysqli_real_escape_string($conn, $_POST["prerequisite_course"] ?? '');
        $isMandatory = $_POST["is_mandatory"] ?? 1;
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? 'both');
        
        $sql = "INSERT INTO curriculum_subjects 
                (curriculum_id, course_code, subject_type, credit_hours, hours_per_week, pass_marks, total_marks, weightage, sequence_order, prerequisite_course, is_mandatory, semester) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issiiiidiiss", 
            $curriculumId, $courseCode, $subjectType, $creditHours, $hoursPerWeek, 
            $passMarks, $totalMarks, $weightage, $sequenceOrder, $prerequisite, $isMandatory, $semester);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update total credits
            $updateSql = "UPDATE curriculum SET total_credits = (
                SELECT SUM(credit_hours) FROM curriculum_subjects WHERE curriculum_id = ?
            ) WHERE curriculum_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($stmtUpdate, "ii", $curriculumId, $curriculumId);
            mysqli_stmt_execute($stmtUpdate);
            
            echo json_encode(['status' => 'success', 'message' => 'Subject added to curriculum!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding subject: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch curriculum by grade
    else if ($action == "fetch_curriculum") {
        $gradeLevel = mysqli_real_escape_string($conn, $_POST["grade_level"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        
        $sql = "SELECT c.*, d.department_name 
                FROM curriculum c 
                LEFT JOIN departments d ON c.department_code = d.department_code 
                WHERE 1=1";
        
        if (!empty($gradeLevel)) {
            $sql .= " AND c.grade_level = '$gradeLevel'";
        }
        if (!empty($academicYear)) {
            $sql .= " AND c.academic_year = '$academicYear'";
        }
        
        $sql .= " ORDER BY c.grade_level, c.created_at DESC";
        
        $result = mysqli_query($conn, $sql);
        $curriculums = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $curriculums[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $curriculums]);
    }
    
    // Fetch curriculum subjects
    else if ($action == "fetch_curriculum_subjects") {
        $curriculumId = mysqli_real_escape_string($conn, $_POST["curriculum_id"]);
        
        $sql = "SELECT cs.*, c.course_name, c.lecturer_name, c.department_code 
                FROM curriculum_subjects cs 
                JOIN courses c ON cs.course_code = c.course_code 
                WHERE cs.curriculum_id = ? 
                ORDER BY cs.sequence_order, cs.subject_type";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $curriculumId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $subjects = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $subjects[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $subjects]);
    }
    
    // Approve curriculum
    else if ($action == "approve_curriculum") {
        $curriculumId = mysqli_real_escape_string($conn, $_POST["curriculum_id"]);
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"]);
        $approvedDate = date('Y-m-d');
        
        $sql = "UPDATE curriculum SET status = 'active', approved_by = ?, approved_date = ? WHERE curriculum_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $approvedBy, $approvedDate, $curriculumId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Curriculum approved!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error approving curriculum: ' . mysqli_error($conn)]);
        }
    }
    
    // Remove subject from curriculum
    else if ($action == "remove_curriculum_subject") {
        $curriculumSubjectId = mysqli_real_escape_string($conn, $_POST["curriculum_subject_id"]);
        
        $sql = "DELETE FROM curriculum_subjects WHERE curriculum_subject_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $curriculumSubjectId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Subject removed from curriculum!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error removing subject: ' . mysqli_error($conn)]);
        }
    }
    
    // Assign teacher to subject
    else if ($action == "assign_teacher") {
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? 'both');
        $assignmentType = mysqli_real_escape_string($conn, $_POST["assignment_type"] ?? 'primary');
        $workloadHours = $_POST["workload_hours"] ?? 0;
        $assignedBy = mysqli_real_escape_string($conn, $_POST["assigned_by"] ?? '');
        $startDate = date('Y-m-d');
        
        $sql = "INSERT INTO subject_teachers 
                (course_code, teacher_id, class, section, academic_year, semester, assignment_type, start_date, workload_hours, assigned_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssss", 
            $courseCode, $teacherId, $class, $section, $academicYear, 
            $semester, $assignmentType, $startDate, $workloadHours, $assignedBy);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Teacher assigned successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error assigning teacher: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch teacher assignments
    else if ($action == "fetch_teacher_assignments") {
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"] ?? '');
        $class = mysqli_real_escape_string($conn, $_POST["class"] ?? '');
        
        $sql = "SELECT st.*, c.course_name, t.fname, t.lname 
                FROM subject_teachers st 
                JOIN courses c ON st.course_code = c.course_code 
                JOIN teachers t ON st.teacher_id = t.id 
                WHERE st.status = 'active'";
        
        if (!empty($teacherId)) {
            $sql .= " AND st.teacher_id = '$teacherId'";
        }
        if (!empty($class)) {
            $sql .= " AND st.class = '$class'";
        }
        
        $result = mysqli_query($conn, $sql);
        $assignments = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $assignments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }
}

mysqli_close($conn);
?>
