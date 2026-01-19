<?php
// FILE: api/admin.php - Admin Management API
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
    require_once __DIR__ . '/../classes/Admin.php';
    
    $database = new Database();
    $db = $database->connect();
    
    if (!User::isLoggedIn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Not authenticated']));
    }
    
    if (!User::hasRole('admin')) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Unauthorized - Admin access required']));
    }
} catch (Exception $e) {
    error_log('Init error: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Server error']));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    $admin = new Admin($db);
    $user = User::getCurrentUser();
    
    if ($method === 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // User Management
        if ($action === 'create-user') {
            $response = $admin->createUser(
                $data['full_name'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['role'] ?? 'student'
            );
        }
        
        elseif ($action === 'update-user') {
            $response = $admin->updateUser(
                $data['user_id'],
                $data['full_name'] ?? null,
                $data['email'] ?? null,
                $data['role'] ?? null,
                $data['status'] ?? null
            );
        }
        
        elseif ($action === 'change-user-status') {
            $response = $admin->changeUserStatus(
                $data['user_id'],
                $data['status']
            );
        }
    }
    
    elseif ($method === 'GET') {
        if ($action === 'all-users') {
            $response = $admin->getAllUsers();
        }
        
        elseif ($action === 'user-details') {
            $response = $admin->getUserDetails($_GET['user_id'] ?? 0);
        }
        
        elseif ($action === 'users-by-role') {
            $response = $admin->getUsersByRole($_GET['role'] ?? 'student');
        }
        
        elseif ($action === 'dashboard-stats') {
            $response = $admin->getDashboardStats();
        }
        
        elseif ($action === 'activity-log') {
            $response = $admin->getActivityLog($_GET['limit'] ?? 50);
        }
    }
    
    elseif ($method === 'DELETE') {
        if ($action === 'delete-user') {
            $response = $admin->deleteUser($_GET['user_id'] ?? 0);
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