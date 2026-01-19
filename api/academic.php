<?php
// FILE: api/academic.php - Academic Module API
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(json_encode(['success' => true]));
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Server error']));
});

try {
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Academic.php';
    
    $database = new Database();
    $db = $database->connect();
    
    if (!User::isLoggedIn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Not authenticated']));
    }
} catch (Exception $e) {
    error_log('Init error: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    $academic = new Academic($db);
    $user = User::getCurrentUser();
    
    if ($method === 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // Programs
        if ($action === 'add-program') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->addProgram($data['program_name'], $data['program_code'], $data['duration_years'] ?? 4);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Courses
        elseif ($action === 'add-course') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->addCourse($data['course_code'], $data['course_name'], $data['credits'], $data['program_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Enrollments
        elseif ($action === 'enroll-student') {
            if (User::hasRole(['admin', 'staff', 'faculty'])) {
                $response = $academic->enrollStudent($data['user_id'], $data['course_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Add Grades
        elseif ($action === 'add-grade') {
            if (User::hasRole(['admin', 'faculty'])) {
                $response = $academic->addGrade(
                    $data['enrollment_id'],
                    $data['midterm_score'] ?? 0,
                    $data['final_score'] ?? 0,
                    $data['assignment_score'] ?? 0
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Add Attendance
        elseif ($action === 'add-attendance') {
            if (User::hasRole(['admin', 'faculty'])) {
                $response = $academic->addAttendance(
                    $data['enrollment_id'],
                    $data['attendance_date'],
                    $data['status']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Schedule Exam
        elseif ($action === 'schedule-exam') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->scheduleExam(
                    $data['course_id'],
                    $data['exam_date'],
                    $data['exam_time'],
                    $data['exam_type'],
                    $data['location']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Publish Results
        elseif ($action === 'publish-results') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->publishResults($data['course_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
    elseif ($method === 'GET') {
        if ($action === 'programs') {
            $response = $academic->getAllPrograms();
        }
        
        elseif ($action === 'courses') {
            if (isset($_GET['program_id'])) {
                $response = $academic->getCoursesByProgram($_GET['program_id']);
            } else {
                $response = $academic->getAllCourses();
            }
        }
        
        elseif ($action === 'student-enrollments') {
            $response = $academic->getStudentEnrollments($user['id']);
        }
        
        elseif ($action === 'course-enrollments') {
            if (User::hasRole(['admin', 'staff', 'faculty'])) {
                $response = $academic->getCourseEnrollments($_GET['course_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'student-transcript') {
            $response = $academic->getStudentTranscript($user['id']);
        }
        
        elseif ($action === 'attendance-summary') {
            if (isset($_GET['enrollment_id'])) {
                $response = $academic->getAttendanceSummary($_GET['enrollment_id']);
            } else {
                $response = ['success' => false, 'message' => 'Missing enrollment_id'];
            }
        }
        // In the GET section of academic.php, add:
        elseif ($action === 'total-enrollments') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->getTotalEnrollments();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }

        elseif ($action === 'active-students') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->getActiveStudents();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'course-exams') {
            if (isset($_GET['course_id'])) {
                $response = $academic->getCourseExams($_GET['course_id']);
            } else {
                $response = ['success' => false, 'message' => 'Missing course_id'];
            }
        }
        
        elseif ($action === 'course-results') {
            if (User::hasRole(['admin', 'staff', 'faculty'])) {
                $response = $academic->getCourseResults($_GET['course_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'instructor-courses') {
            $response = $academic->getInstructorCourses($user['id']);
        }
        
        elseif ($action === 'dashboard-stats') {
            $response = $academic->getDashboardStats($user['id'], $user['role']);
        }
    }
    
    elseif ($method === 'PUT') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if ($action === 'update-course') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->updateCourse(
                    $data['course_id'],
                    $data['course_name'],
                    $data['credits']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'update-grade') {
            if (User::hasRole(['admin', 'faculty'])) {
                $response = $academic->updateGrade(
                    $data['grade_id'],
                    $data['midterm_score'],
                    $data['final_score'],
                    $data['assignment_score']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
    elseif ($method === 'DELETE') {
        if ($action === 'delete-course') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->deleteCourse($_GET['course_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'delete-program') {
            if (User::hasRole(['admin'])) {
                $response = $academic->deleteProgram($_GET['program_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'unenroll-student') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $academic->unenrollStudent($_GET['enrollment_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    http_response_code(500);
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($response);
exit;
?>