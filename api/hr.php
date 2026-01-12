<?php
// FILE: api/hr.php - HR & Administration Module API
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
    require_once __DIR__ . '/../classes/HR.php';
    
    $database = new Database();
    $db = $database->connect();
    
    if (!User::isLoggedIn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Not authenticated']));
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
    $hr = new HR($db);
    $user = User::getCurrentUser();
    
    if ($method === 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // Employee Management
        if ($action === 'add-employee') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->addEmployee($data['user_id'], $data['employee_id'], $data['designation'], $data['department'], $data['salary']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Payroll
        elseif ($action === 'add-payroll') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->addPayroll(
                    $data['employee_id'],
                    $data['month'],
                    $data['basic_salary'],
                    $data['allowances'],
                    $data['deductions'],
                    $data['net_salary']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Attendance
        elseif ($action === 'add-attendance') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->markAttendance(
                    $data['employee_id'],
                    $data['attendance_date'],
                    $data['status']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Leave Request
        elseif ($action === 'request-leave') {
            $response = $hr->requestLeave(
                $user['id'],
                $data['start_date'],
                $data['end_date'],
                $data['leave_type'],
                $data['reason']
            );
        }
        
        // Approve/Reject Leave
        elseif ($action === 'approve-leave') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->approveLeave($data['leave_id'], $data['status']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Performance Review
        elseif ($action === 'add-performance') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->addPerformanceReview(
                    $data['employee_id'],
                    $data['review_date'],
                    $data['rating'],
                    $data['comments']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Asset Management
        elseif ($action === 'add-asset') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->addAsset(
                    $data['asset_name'],
                    $data['category'],
                    $data['quantity'],
                    $data['location']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Assign Asset
        elseif ($action === 'assign-asset') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->assignAsset(
                    $data['asset_id'],
                    $data['employee_id'],
                    $data['assignment_date']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
    elseif ($method === 'GET') {
        // Employee Management
        if ($action === 'all-employees') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getAllEmployees();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'employee-details') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getEmployeeDetails($_GET['employee_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'search-employees') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->searchEmployees($_GET['query']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Payroll
        elseif ($action === 'payroll-records') {
            if (User::hasRole(['admin', 'staff'])) {
                $month = $_GET['month'] ?? date('Y-m');
                $response = $hr->getPayrollRecords($month);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'employee-payroll') {
            $response = $hr->getEmployeePayroll($user['id']);
        }
        
        // Attendance
        elseif ($action === 'attendance-records') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getAttendanceRecords($_GET['month'] ?? date('Y-m'));
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'employee-attendance') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getEmployeeAttendance($_GET['employee_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Leave Management
        elseif ($action === 'my-leaves') {
            $response = $hr->getEmployeeLeaves($user['id']);
        }
        
        elseif ($action === 'all-leave-requests') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getAllLeaveRequests();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'pending-leaves') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getPendingLeaves();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Performance
        elseif ($action === 'performance-records') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getPerformanceRecords();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'employee-performance') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getEmployeePerformance($_GET['employee_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Assets
        elseif ($action === 'all-assets') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getAllAssets();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'asset-assignments') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getAssetAssignments();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'employee-assets') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getEmployeeAssets($_GET['employee_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // HR Dashboard
        elseif ($action === 'dashboard-stats') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getDashboardStats();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'leave-analytics') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->getLeaveAnalytics();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'notifications') {
            $response = $hr->getNotifications($user['id'], $_GET['limit'] ?? 10);
        }
    }
    
    elseif ($method === 'PUT') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if ($action === 'update-employee') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->updateEmployee(
                    $data['employee_id'],
                    $data['designation'],
                    $data['department'],
                    $data['salary']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
    elseif ($method === 'DELETE') {
        if ($action === 'delete-asset') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $hr->deleteAsset($_GET['asset_id']);
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