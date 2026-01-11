<?php
// REQ-ACD-004: Calculate Cumulative Grades & Rankings
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Calculate cumulative grades for a student
    if ($action == "calculate_cumulative_grades") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get all marks for the course
            $sql = "SELECT sm.*, at.weightage, at.type_code 
                    FROM student_marks sm 
                    JOIN assessment_types at ON sm.assessment_type_id = at.assessment_type_id 
                    WHERE sm.student_id = ? AND sm.course_code = ? 
                    AND sm.academic_year = ? AND sm.semester = ? 
                    AND sm.status = 'published'";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $studentId, $courseCode, $academicYear, $semester);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $caMarks = 0;
            $examMarks = 0;
            $practicalMarks = 0;
            $projectMarks = 0;
            $totalWeightedMarks = 0;
            
            while ($row = mysqli_fetch_assoc($result)) {
                $weightedMark = ($row['marks_obtained'] / $row['total_marks']) * $row['weightage'];
                $totalWeightedMarks += $weightedMark;
                
                // Categorize marks
                if (in_array($row['type_code'], ['CA1', 'CA2', 'QUIZ', 'ASSIGNMENT'])) {
                    $caMarks += $weightedMark;
                } else if (in_array($row['type_code'], ['MIDTERM', 'FINAL'])) {
                    $examMarks += $weightedMark;
                } else if ($row['type_code'] == 'PRACTICAL') {
                    $practicalMarks += $weightedMark;
                } else if ($row['type_code'] == 'PROJECT') {
                    $projectMarks += $weightedMark;
                }
            }
            
            // Get student's class info
            $getStudent = "SELECT class, section FROM students WHERE id = ?";
            $stmtStudent = mysqli_prepare($conn, $getStudent);
            mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
            mysqli_stmt_execute($stmtStudent);
            $resultStudent = mysqli_stmt_get_result($stmtStudent);
            $studentData = mysqli_fetch_assoc($resultStudent);
            
            // Get credit hours
            $getCourse = "SELECT credit_hours FROM courses WHERE course_code = ?";
            $stmtCourse = mysqli_prepare($conn, $getCourse);
            mysqli_stmt_bind_param($stmtCourse, "s", $courseCode);
            mysqli_stmt_execute($stmtCourse);
            $resultCourse = mysqli_stmt_get_result($stmtCourse);
            $courseData = mysqli_fetch_assoc($resultCourse);
            $creditHours = $courseData['credit_hours'] ?? 3;
            
            // Calculate percentage and grade
            $percentage = $totalWeightedMarks;
            $gradeData = getGradeFromPercentage($conn, $percentage);
            $gradePointsEarned = $gradeData['grade_point'] * $creditHours;
            
            // Determine result status
            $resultStatus = ($percentage >= 40) ? 'pass' : 'fail';
            
            // Insert or update cumulative grade
            $cumulativeSql = "INSERT INTO cumulative_grades 
                             (student_id, course_code, class, section, academic_year, semester, 
                              ca_marks, exam_marks, practical_marks, project_marks, total_marks, 
                              percentage, grade, grade_point, credit_hours, grade_points_earned, 
                              result_status, calculated_date) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
                             ON DUPLICATE KEY UPDATE 
                             ca_marks = VALUES(ca_marks),
                             exam_marks = VALUES(exam_marks),
                             practical_marks = VALUES(practical_marks),
                             project_marks = VALUES(project_marks),
                             total_marks = VALUES(total_marks),
                             percentage = VALUES(percentage),
                             grade = VALUES(grade),
                             grade_point = VALUES(grade_point),
                             grade_points_earned = VALUES(grade_points_earned),
                             result_status = VALUES(result_status),
                             calculated_date = CURDATE()";
            
            $stmtCumulative = mysqli_prepare($conn, $cumulativeSql);
            mysqli_stmt_bind_param($stmtCumulative, "ssssssddddddsddds", 
                $studentId, $courseCode, $studentData['class'], $studentData['section'],
                $academicYear, $semester, $caMarks, $examMarks, $practicalMarks, 
                $projectMarks, $totalWeightedMarks, $percentage, $gradeData['grade'],
                $gradeData['grade_point'], $creditHours, $gradePointsEarned, $resultStatus);
            
            if (!mysqli_stmt_execute($stmtCumulative)) {
                throw new Exception("Error calculating cumulative grades: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Cumulative grades calculated!',
                'data' => [
                    'total_marks' => $totalWeightedMarks,
                    'percentage' => $percentage,
                    'grade' => $gradeData['grade'],
                    'grade_point' => $gradeData['grade_point']
                ]
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Calculate semester results
    else if ($action == "calculate_semester_results") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get all cumulative grades for the semester
            $sql = "SELECT * FROM cumulative_grades 
                    WHERE student_id = ? AND academic_year = ? AND semester = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $studentId, $academicYear, $semester);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $totalCredits = 0;
            $creditsEarned = 0;
            $totalGradePoints = 0;
            $totalMarksObtained = 0;
            $totalMaxMarks = 0;
            
            while ($row = mysqli_fetch_assoc($result)) {
                $totalCredits += $row['credit_hours'];
                $totalGradePoints += $row['grade_points_earned'];
                $totalMarksObtained += $row['total_marks'];
                $totalMaxMarks += $row['max_marks'];
                
                if ($row['result_status'] == 'pass') {
                    $creditsEarned += $row['credit_hours'];
                }
                
                $class = $row['class'];
                $section = $row['section'];
            }
            
            // Calculate GPA
            $gpa = ($totalCredits > 0) ? round($totalGradePoints / $totalCredits, 2) : 0;
            
            // Calculate overall percentage
            $percentage = ($totalMaxMarks > 0) ? round(($totalMarksObtained / $totalMaxMarks) * 100, 2) : 0;
            
            // Determine overall result
            $overallResult = ($creditsEarned == $totalCredits) ? 'pass' : 'fail';
            if ($overallResult == 'pass' && $percentage >= 85) {
                $overallResult = 'distinction';
            } else if ($overallResult == 'pass' && $percentage >= 75) {
                $overallResult = 'honors';
            }
            
            // Get CGPA (if exists)
            $cgpaSql = "SELECT AVG(gpa) as cgpa FROM semester_results 
                       WHERE student_id = ? AND academic_year <= ?";
            $stmtCgpa = mysqli_prepare($conn, $cgpaSql);
            mysqli_stmt_bind_param($stmtCgpa, "ss", $studentId, $academicYear);
            mysqli_stmt_execute($stmtCgpa);
            $resultCgpa = mysqli_stmt_get_result($stmtCgpa);
            $cgpaData = mysqli_fetch_assoc($resultCgpa);
            $cgpa = round($cgpaData['cgpa'] ?? $gpa, 2);
            
            // Insert or update semester results
            $resultSql = "INSERT INTO semester_results 
                         (student_id, class, section, academic_year, semester, total_credits, 
                          credits_earned, total_grade_points, gpa, cgpa, percentage, 
                          total_marks_obtained, total_max_marks, overall_result, 
                          generated_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
                         ON DUPLICATE KEY UPDATE 
                         total_credits = VALUES(total_credits),
                         credits_earned = VALUES(credits_earned),
                         total_grade_points = VALUES(total_grade_points),
                         gpa = VALUES(gpa),
                         cgpa = VALUES(cgpa),
                         percentage = VALUES(percentage),
                         total_marks_obtained = VALUES(total_marks_obtained),
                         total_max_marks = VALUES(total_max_marks),
                         overall_result = VALUES(overall_result),
                         generated_date = CURDATE()";
            
            $stmtResult = mysqli_prepare($conn, $resultSql);
            mysqli_stmt_bind_param($stmtResult, "sssssiiiddddis", 
                $studentId, $class, $section, $academicYear, $semester,
                $totalCredits, $creditsEarned, $totalGradePoints, $gpa, $cgpa,
                $percentage, $totalMarksObtained, $totalMaxMarks, $overallResult);
            
            if (!mysqli_stmt_execute($stmtResult)) {
                throw new Exception("Error calculating semester results: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Semester results calculated!',
                'data' => [
                    'gpa' => $gpa,
                    'cgpa' => $cgpa,
                    'percentage' => $percentage,
                    'overall_result' => $overallResult
                ]
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Calculate class rankings
    else if ($action == "calculate_rankings") {
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $section = mysqli_real_escape_string($conn, $_POST["section"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        $rankingType = mysqli_real_escape_string($conn, $_POST["ranking_type"] ?? 'class');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get all students' results for ranking
            $sql = "SELECT student_id, gpa, percentage, total_marks_obtained 
                    FROM semester_results 
                    WHERE class = ? AND academic_year = ? AND semester = ?";
            
            if (!empty($section) && $rankingType == 'section') {
                $sql .= " AND section = '$section'";
            }
            
            $sql .= " ORDER BY percentage DESC, gpa DESC";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $class, $academicYear, $semester);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $rank = 1;
            $totalStudents = mysqli_num_rows($result);
            
            // Delete existing rankings
            $deleteSql = "DELETE FROM class_rankings 
                         WHERE class = ? AND academic_year = ? AND semester = ? AND ranking_type = ?";
            $stmtDelete = mysqli_prepare($conn, $deleteSql);
            mysqli_stmt_bind_param($stmtDelete, "ssss", $class, $academicYear, $semester, $rankingType);
            mysqli_stmt_execute($stmtDelete);
            
            // Insert new rankings
            while ($row = mysqli_fetch_assoc($result)) {
                $insertSql = "INSERT INTO class_rankings 
                             (student_id, class, section, academic_year, semester, ranking_type, 
                              rank_position, total_students, gpa, percentage, total_marks, 
                              calculated_date) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
                
                $stmtInsert = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($stmtInsert, "ssssssiiddd", 
                    $row['student_id'], $class, $section, $academicYear, $semester,
                    $rankingType, $rank, $totalStudents, $row['gpa'], 
                    $row['percentage'], $row['total_marks_obtained']);
                
                mysqli_stmt_execute($stmtInsert);
                $rank++;
            }
            
            // Update semester_results with rank
            $updateRankSql = "UPDATE semester_results sr 
                             JOIN class_rankings cr ON sr.student_id = cr.student_id 
                             SET sr.rank_in_class = cr.rank_position, 
                                 sr.total_students = cr.total_students 
                             WHERE sr.class = ? AND sr.academic_year = ? 
                             AND sr.semester = ? AND cr.ranking_type = ?";
            
            $stmtUpdate = mysqli_prepare($conn, $updateRankSql);
            mysqli_stmt_bind_param($stmtUpdate, "ssss", $class, $academicYear, $semester, $rankingType);
            mysqli_stmt_execute($stmtUpdate);
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => "Rankings calculated for $totalStudents students!"
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Get grade statistics
    else if ($action == "get_grade_statistics") {
        $courseCode = mysqli_real_escape_string($conn, $_POST["course_code"]);
        $class = mysqli_real_escape_string($conn, $_POST["class"]);
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"]);
        $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
        $assessmentTypeId = $_POST["assessment_type_id"] ?? null;
        
        $sql = "SELECT 
                COUNT(*) as total_students,
                COUNT(CASE WHEN is_absent = 0 THEN 1 END) as students_appeared,
                COUNT(CASE WHEN percentage >= 40 AND is_absent = 0 THEN 1 END) as students_passed,
                COUNT(CASE WHEN percentage < 40 AND is_absent = 0 THEN 1 END) as students_failed,
                COUNT(CASE WHEN is_absent = 1 THEN 1 END) as students_absent,
                MAX(marks_obtained) as highest_marks,
                MIN(CASE WHEN is_absent = 0 THEN marks_obtained END) as lowest_marks,
                AVG(CASE WHEN is_absent = 0 THEN marks_obtained END) as average_marks,
                ROUND((COUNT(CASE WHEN percentage >= 40 AND is_absent = 0 THEN 1 END) / 
                       COUNT(CASE WHEN is_absent = 0 THEN 1 END)) * 100, 2) as pass_percentage
                FROM student_marks 
                WHERE course_code = ? AND class = ? AND academic_year = ? AND semester = ?";
        
        if ($assessmentTypeId) {
            $sql .= " AND assessment_type_id = $assessmentTypeId";
        }
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $courseCode, $class, $academicYear, $semester);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats = mysqli_fetch_assoc($result);
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
    
    // Fetch student semester results
    else if ($action == "fetch_semester_results") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"] ?? '');
        $class = mysqli_real_escape_string($conn, $_POST["class"] ?? '');
        $academicYear = mysqli_real_escape_string($conn, $_POST["academic_year"] ?? '');
        $semester = mysqli_real_escape_string($conn, $_POST["semester"] ?? '');
        
        $sql = "SELECT sr.*, s.fname, s.lname, s.admission_no 
                FROM semester_results sr 
                JOIN students s ON sr.student_id = s.id 
                WHERE 1=1";
        
        if (!empty($studentId)) {
            $sql .= " AND sr.student_id = '$studentId'";
        }
        if (!empty($class)) {
            $sql .= " AND sr.class = '$class'";
        }
        if (!empty($academicYear)) {
            $sql .= " AND sr.academic_year = '$academicYear'";
        }
        if (!empty($semester)) {
            $sql .= " AND sr.semester = '$semester'";
        }
        
        $sql .= " ORDER BY sr.academic_year DESC, sr.semester DESC";
        
        $result = mysqli_query($conn, $sql);
        $results = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $results]);
    }
}

// Helper function to get grade from percentage
function getGradeFromPercentage($conn, $percentage) {
    $sql = "SELECT grade, grade_point FROM grade_scale 
            WHERE $percentage >= min_percentage AND $percentage <= max_percentage 
            AND is_active = 1 
            ORDER BY min_percentage DESC LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return ['grade' => $row['grade'], 'grade_point' => $row['grade_point']];
    }
    
    return ['grade' => 'F', 'grade_point' => 0.00];
}

mysqli_close($conn);
?>
