<?php
// FILE: classes/Academic.php - Academic Module Operations

class Academic {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ==================== PROGRAMS ====================
    
    public function addProgram($program_name, $program_code, $duration_years) {
        if (empty($program_name) || empty($program_code)) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO academic_programs (program_name, program_code, duration_years, created_at) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $program_name, $program_code, $duration_years);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Program added successfully', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add program'];
    }

    public function getAllPrograms() {
        $query = "SELECT * FROM academic_programs ORDER BY program_name";
        $result = $this->db->query($query);

        if ($result) {
            $programs = [];
            while ($row = $result->fetch_assoc()) {
                $programs[] = $row;
            }
            return ['success' => true, 'data' => $programs];
        }
        return ['success' => false, 'message' => 'Failed to fetch programs'];
    }
    public function getTotalEnrollments() {
    $query = "SELECT COUNT(*) as total FROM enrollments WHERE status = 'enrolled'";
    $result = $this->db->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        return ['success' => true, 'total' => $row['total']];
    }
    return ['success' => false, 'total' => 0];
}

public function getActiveStudents() {
    $query = "SELECT COUNT(DISTINCT user_id) as total FROM enrollments WHERE status = 'enrolled'";
    $result = $this->db->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        return ['success' => true, 'total' => $row['total']];
    }
    return ['success' => false, 'total' => 0];
}


    public function deleteProgram($program_id) {
        $query = "DELETE FROM academic_programs WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $program_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Program deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete program'];
    }

    // ==================== COURSES ====================
    
    public function addCourse($course_code, $course_name, $credits, $program_id) {
        if (empty($course_code) || empty($course_name) || !$credits) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO courses (course_code, course_name, credits, program_id, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssii", $course_code, $course_name, $credits, $program_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Course added successfully', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add course'];
    }

    public function getAllCourses() {
        $query = "SELECT c.*, ap.program_name FROM courses c 
                  LEFT JOIN academic_programs ap ON c.program_id = ap.id 
                  ORDER BY c.course_name";
        $result = $this->db->query($query);

        if ($result) {
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            return ['success' => true, 'data' => $courses];
        }
        return ['success' => false, 'message' => 'Failed to fetch courses'];
    }

    public function getCoursesByProgram($program_id) {
        $query = "SELECT * FROM courses WHERE program_id = ? ORDER BY course_name";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            return ['success' => true, 'data' => $courses];
        }
        return ['success' => false, 'message' => 'Failed to fetch courses'];
    }

    public function updateCourse($course_id, $course_name, $credits) {
        $query = "UPDATE courses SET course_name = ?, credits = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sii", $course_name, $credits, $course_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Course updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update course'];
    }

    public function deleteCourse($course_id) {
        $query = "DELETE FROM courses WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $course_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Course deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete course'];
    }

    // ==================== ENROLLMENTS ====================
    
    public function enrollStudent($user_id, $course_id) {
        // Check if already enrolled
        $check = "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?";
        $stmt = $this->db->prepare($check);
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Student already enrolled in this course'];
        }

        $query = "INSERT INTO enrollments (user_id, course_id, enrollment_date, status) 
                  VALUES (?, ?, NOW(), 'enrolled')";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $user_id, $course_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Student enrolled successfully', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to enroll student'];
    }

    public function getStudentEnrollments($user_id) {
        $query = "SELECT e.id, e.enrollment_date, e.status, c.id as course_id, c.course_code, 
                         c.course_name, c.credits, ap.program_name
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  LEFT JOIN academic_programs ap ON c.program_id = ap.id
                  WHERE e.user_id = ? AND e.status IN ('enrolled', 'completed')
                  ORDER BY e.enrollment_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $enrollments = [];
        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $row;
        }
        return ['success' => true, 'data' => $enrollments];
    }

    public function getCourseEnrollments($course_id) {
        $query = "SELECT e.id, e.enrollment_date, e.status, u.id as user_id, u.full_name, u.email
                  FROM enrollments e
                  JOIN users u ON e.user_id = u.id
                  WHERE e.course_id = ?
                  ORDER BY u.full_name";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $enrollments = [];
        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $row;
        }
        return ['success' => true, 'data' => $enrollments];
    }

    public function unenrollStudent($enrollment_id) {
        $query = "UPDATE enrollments SET status = 'dropped' WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $enrollment_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Student unenrolled successfully'];
        }
        return ['success' => false, 'message' => 'Failed to unenroll student'];
    }

    // ==================== GRADES ====================
    
    public function addGrade($enrollment_id, $midterm_score, $final_score, $assignment_score) {
        // Check if grade exists
        $check = "SELECT id FROM grades WHERE enrollment_id = ?";
        $stmt = $this->db->prepare($check);
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $this->updateGrade($result->fetch_assoc()['id'], $midterm_score, $final_score, $assignment_score);
        }

        // Calculate total score
        $total_score = ($midterm_score * 0.3) + ($final_score * 0.5) + ($assignment_score * 0.2);
        $grade = $this->calculateGradeLetters($total_score);

        $query = "INSERT INTO grades (enrollment_id, midterm_score, final_score, assignment_score, total_score, grade, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("idddds", $enrollment_id, $midterm_score, $final_score, $assignment_score, $total_score, $grade);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grade added successfully', 'total_score' => $total_score, 'grade' => $grade];
        }
        return ['success' => false, 'message' => 'Failed to add grade'];
    }

    public function updateGrade($grade_id, $midterm_score, $final_score, $assignment_score) {
        $total_score = ($midterm_score * 0.3) + ($final_score * 0.5) + ($assignment_score * 0.2);
        $grade = $this->calculateGradeLetters($total_score);

        $query = "UPDATE grades SET midterm_score = ?, final_score = ?, assignment_score = ?, 
                  total_score = ?, grade = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ddddsi", $midterm_score, $final_score, $assignment_score, $total_score, $grade, $grade_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grade updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update grade'];
    }

    private function calculateGradeLetters($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    // ==================== ATTENDANCE ====================
    
    public function addAttendance($enrollment_id, $attendance_date, $status) {
        // Check if record exists for this date
        $check = "SELECT id FROM attendance WHERE enrollment_id = ? AND attendance_date = ?";
        $stmt = $this->db->prepare($check);
        $stmt->bind_param("is", $enrollment_id, $attendance_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $update = "UPDATE attendance SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($update);
            $stmt->bind_param("si", $status, $row['id']);
            $stmt->execute();
            return ['success' => true, 'message' => 'Attendance updated'];
        }

        $query = "INSERT INTO attendance (enrollment_id, attendance_date, status, created_at) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iss", $enrollment_id, $attendance_date, $status);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Attendance recorded'];
        }
        return ['success' => false, 'message' => 'Failed to record attendance'];
    }

    public function getAttendanceSummary($enrollment_id) {
        $query = "SELECT 
                    COUNT(*) as total_classes,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                    ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
                  FROM attendance
                  WHERE enrollment_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        }
        return ['success' => true, 'data' => ['total_classes' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'percentage' => 0]];
    }

    // ==================== EXAMINATIONS ====================
    
    public function scheduleExam($course_id, $exam_date, $exam_time, $exam_type, $location) {
        if (empty($exam_date) || empty($exam_time) || empty($exam_type)) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO examinations (course_id, exam_date, exam_time, exam_type, location, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issss", $course_id, $exam_date, $exam_time, $exam_type, $location);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Exam scheduled successfully', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to schedule exam'];
    }

    public function getCourseExams($course_id) {
        $query = "SELECT * FROM examinations WHERE course_id = ? ORDER BY exam_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        return ['success' => true, 'data' => $exams];
    }

    public function publishResults($course_id) {
        $query = "UPDATE grades g 
                  SET g.published = 1
                  WHERE g.enrollment_id IN (
                    SELECT e.id FROM enrollments e WHERE e.course_id = ?
                  )";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $course_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Results published successfully'];
        }
        return ['success' => false, 'message' => 'Failed to publish results'];
    }

    public function getCourseResults($course_id) {
        $query = "SELECT g.*, u.full_name, u.email, c.course_name
                  FROM grades g
                  JOIN enrollments e ON g.enrollment_id = e.id
                  JOIN users u ON e.user_id = u.id
                  JOIN courses c ON e.course_id = c.id
                  WHERE e.course_id = ? AND g.published = 1
                  ORDER BY g.total_score DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        return ['success' => true, 'data' => $results];
    }

    // ==================== TRANSCRIPTS ====================
    
    public function getStudentTranscript($user_id) {
        $query = "SELECT 
                    c.course_code,
                    c.course_name,
                    c.credits,
                    g.midterm_score,
                    g.final_score,
                    g.assignment_score,
                    g.total_score,
                    g.grade,
                    e.enrollment_date
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  LEFT JOIN grades g ON e.id = g.enrollment_id
                  WHERE e.user_id = ? AND e.status IN ('enrolled', 'completed')
                  ORDER BY e.enrollment_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $transcript = [];
        $total_credits = 0;
        $total_points = 0;

        while ($row = $result->fetch_assoc()) {
            $transcript[] = $row;
            $total_credits += $row['credits'];
            // Calculate GPA points
            $grade_points = [
                'A' => 4.0, 'B' => 3.0, 'C' => 2.0, 'D' => 1.0, 'F' => 0.0
            ];
            $total_points += ($grade_points[$row['grade']] ?? 0) * $row['credits'];
        }

        $gpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;

        return [
            'success' => true,
            'data' => $transcript,
            'gpa' => $gpa,
            'total_credits' => $total_credits
        ];
    }

    // ==================== INSTRUCTOR COURSES ====================
    
    public function getInstructorCourses($user_id) {
        $query = "SELECT DISTINCT c.* FROM courses c
                  JOIN enrollments e ON c.id = e.course_id
                  WHERE c.instructor_id = ? OR e.user_id = ?
                  ORDER BY c.course_name";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        return ['success' => true, 'data' => $courses];
    }

    // ==================== DASHBOARD STATS ====================
    
    public function getDashboardStats($user_id, $role) {
        $stats = ['success' => true, 'data' => []];

        if ($role === 'student') {
            // Student stats
            $enrolledQuery = "SELECT COUNT(*) as count FROM enrollments WHERE user_id = ? AND status = 'enrolled'";
            $stmt = $this->db->prepare($enrolledQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['data']['enrolled_courses'] = $result->fetch_assoc()['count'];

            // Average GPA
            $gpaQuery = "SELECT AVG(g.total_score) as avg_score FROM grades g
                         JOIN enrollments e ON g.enrollment_id = e.id
                         WHERE e.user_id = ?";
            $stmt = $this->db->prepare($gpaQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['data']['average_score'] = round($result->fetch_assoc()['avg_score'] ?? 0, 2);
        }
        
        elseif ($role === 'faculty') {
            // Faculty stats
            $coursesQuery = "SELECT COUNT(DISTINCT course_id) as count FROM enrollments WHERE user_id = ?";
            $stmt = $this->db->prepare($coursesQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['data']['total_courses'] = $result->fetch_assoc()['count'];

            $studentQuery = "SELECT COUNT(DISTINCT user_id) as count FROM enrollments WHERE course_id IN 
                            (SELECT id FROM courses WHERE instructor_id = ?)";
            $stmt = $this->db->prepare($studentQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['data']['total_students'] = $result->fetch_assoc()['count'];
        }

        return $stats;
    }
}
?> 