<?php
// MUST BE FIRST LINE - NO OUTPUT BEFORE THIS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(json_encode(['success' => true]));
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Server error']));
});

// Load config and classes
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../classes/User.php';
    
    $database = new Database();
    $db = $database->connect();
} catch (Exception $e) {
    error_log('Init error: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    if ($method === 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if ($action === 'register') {
            $user = new User($db);
            $response = $user->register(
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['full_name'] ?? '',
                $data['role'] ?? 'student'
            );
        } elseif ($action === 'login') {
            $user = new User($db);
            $response = $user->login(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );
        } elseif ($action === 'logout') {
            User::logout();
            $response = ['success' => true, 'message' => 'Logged out successfully'];
        }
    } elseif ($method === 'GET') {
        if ($action === 'current-user') {
            if (User::isLoggedIn()) {
                $user = User::getCurrentUser();
                $response = ['success' => true, 'user' => $user];
            } else {
                $response = ['success' => false, 'message' => 'Not authenticated'];
            }
        } elseif ($action === 'check-session') {
            if (User::isLoggedIn()) {
                $response = ['success' => true, 'active' => true];
            } else {
                $response = ['success' => false, 'message' => 'Not authenticated'];
            }
        }
    }
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    http_response_code(500);
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

$database->closeConnection();
echo json_encode($response);
exit;
?>
