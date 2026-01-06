<!-- FILE: api/auth.php -->
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/User.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$response = [];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

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
            if (User::checkSessionTimeout()) {
                $response = ['success' => true, 'active' => true];
            } else {
                $response = ['success' => false, 'message' => 'Session expired'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Not authenticated'];
        }
    }
}

echo json_encode($response);
?>