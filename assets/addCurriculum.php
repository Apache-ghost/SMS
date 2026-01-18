<?php
ob_start(); // Start output buffering
session_start();
include("config.php");
ob_clean(); // Clear any output before headers
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
        
        // Handle file upload
        $fileName = null;
        if (isset($_FILES['curriculum_file']) && $_FILES['curriculum_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../curriculumUploads/';
            $fileExtension = pathinfo($_FILES['curriculum_file']['name'], PATHINFO_EXTENSION);
            $fileName = 'curriculum_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['curriculum_file']['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload curriculum file');
            }
        }
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        // Insert curriculum into curriculum_master table
        $sql = "INSERT INTO curriculum_master (curriculum_name, academic_year, class, department_code, total_credits, status, file_path, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        $classNum = intval($gradeLevel);
        $totalCredits = count($subjectNames);
        mysqli_stmt_bind_param($stmt, "ssisss", $curriculumName, $academicYear, $classNum, $department, $totalCredits, $fileName);
        
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
            $theoryHours = intval($subjectNames[$i]); // Using subject_name field for theory hours
            $practicalHours = $hours; // Using hours_per_week for practical hours
            
            // First, ensure the course exists in courses table
            $checkCourse = "SELECT course_code FROM courses WHERE course_code = ?";
            $checkStmt = mysqli_prepare($conn, $checkCourse);
            mysqli_stmt_bind_param($checkStmt, "s", $subjectCode);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) == 0) {
                // Course doesn't exist, create it from subjects table
                $getSubject = "SELECT subject_name, class FROM subjects WHERE subject_id = ?";
                $getStmt = mysqli_prepare($conn, $getSubject);
                mysqli_stmt_bind_param($getStmt, "s", $subjectCode);
                mysqli_stmt_execute($getStmt);
                $subjectResult = mysqli_stmt_get_result($getStmt);
                
                if ($subjectRow = mysqli_fetch_assoc($subjectResult)) {
                    $insertCourse = "INSERT INTO courses (course_code, course_name, course_type, credit_hours, is_active) VALUES (?, ?, 'core', ?, 1)";
                    $courseStmt = mysqli_prepare($conn, $insertCourse);
                    $creditHours = ceil(($theoryHours + $practicalHours) / 2);
                    mysqli_stmt_bind_param($courseStmt, "ssi", $subjectCode, $subjectRow['subject_name'], $creditHours);
                    mysqli_stmt_execute($courseStmt);
                    mysqli_stmt_close($courseStmt);
                }
                mysqli_stmt_close($getStmt);
            }
            mysqli_stmt_close($checkStmt);
            
            // Now insert into curriculum_subjects
            mysqli_stmt_bind_param($subjectStmt, "isiii", $curriculumId, $subjectCode, $classNum, $theoryHours, $practicalHours);
            
            if (!mysqli_stmt_execute($subjectStmt)) {
                throw new Exception("Failed to add subject with code: " . $subjectCode);
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        $totalSubjects = count($subjectNames);
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

ob_clean(); // Clear any output before JSON
echo json_encode($response);
ob_end_flush();
?>
