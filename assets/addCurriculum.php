<?php
session_start();
include("config.php");
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_curriculum') {
    try {
        $curriculumName = mysqli_real_escape_string($conn, $_POST['curriculum_name']);
        $gradeLevel = mysqli_real_escape_string($conn, $_POST['grade_level']);
        $academicYear = mysqli_real_escape_string($conn, $_POST['academic_year']);
        $department = mysqli_real_escape_string($conn, $_POST['department'] ?? '');
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        
        $subjectNames = $_POST['subject_name'] ?? [];
        $subjectCodes = $_POST['subject_code'] ?? [];
        $hoursPerWeek = $_POST['hours_per_week'] ?? [];
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        // Insert curriculum into curriculum_master table
        $sql = "INSERT INTO curriculum_master (curriculum_name, academic_year, class, department_code, total_credits, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        $classNum = intval($gradeLevel);
        $totalCredits = count($subjectNames);
        mysqli_stmt_bind_param($stmt, "ssisi", $curriculumName, $academicYear, $classNum, $department, $totalCredits);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to create curriculum");
        }
        
        $curriculumId = mysqli_insert_id($conn);
        
        // Insert subjects using existing curriculum_subjects table structure
        $subjectSql = "INSERT INTO curriculum_subjects (curriculum_id, course_code, class, theory_hours, practical_hours, is_mandatory, semester) VALUES (?, ?, ?, ?, ?, 1, 1)";
        $subjectStmt = mysqli_prepare($conn, $subjectSql);
        
        for ($i = 0; $i < count($subjectNames); $i++) {
            $subjectCode = mysqli_real_escape_string($conn, $subjectCodes[$i]);
            $hours = intval($hoursPerWeek[$i]);
            $classNum = intval($gradeLevel);
            $theoryHours = $hours;
            $practicalHours = 0;
            
            mysqli_stmt_bind_param($subjectStmt, "isiii", $curriculumId, $subjectCode, $classNum, $theoryHours, $practicalHours);
            
            if (!mysqli_stmt_execute($subjectStmt)) {
                throw new Exception("Failed to add subject with code: " . $subjectCode);
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        $response['status'] = 'success';
        $response['message'] = 'Curriculum created successfully with ' . $totalSubjects . ' subjects!';
        $response['curriculum_id'] = $curriculumId;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?>
