<?php
// FILE: api/finance.php - Finance & Marketing Module API
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
    require_once __DIR__ . '/../classes/Finance.php';
    
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
    $finance = new Finance($db);
    $user = User::getCurrentUser();
    
    if ($method === 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // Fee Management
        if ($action === 'create-invoice') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->createInvoice(
                    $data['student_id'],
                    $data['amount'],
                    $data['description'],
                    $data['due_date'] ?? null
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Payment Processing
        elseif ($action === 'process-payment') {
            $response = $finance->processPayment(
                $data['invoice_id'],
                $data['amount'],
                $data['payment_method']
            );
        }
        
        // Expense Tracking
        elseif ($action === 'add-expense') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->addExpense(
                    $data['category'],
                    $data['amount'],
                    $data['description'],
                    $data['expense_date'] ?? date('Y-m-d')
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Marketing Campaign
        elseif ($action === 'create-campaign') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->createCampaign(
                    $data['campaign_name'],
                    $data['campaign_type'],
                    $data['budget'],
                    $data['start_date'],
                    $data['end_date']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Track Lead
        elseif ($action === 'add-lead') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->addLead(
                    $data['campaign_id'],
                    $data['lead_name'],
                    $data['lead_email'],
                    $data['lead_phone'] ?? null
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Record Conversion
        elseif ($action === 'record-conversion') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->recordConversion(
                    $data['lead_id'],
                    $data['conversion_value']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
    elseif ($method === 'GET') {
        // Fee Management
        if ($action === 'my-invoices') {
            $response = $finance->getStudentInvoices($user['id']);
        }
        
        elseif ($action === 'all-invoices') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getAllInvoices();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'invoice-details') {
            $response = $finance->getInvoiceDetails($_GET['invoice_id']);
        }
        
        // Payment Tracking
        elseif ($action === 'my-payments') {
            $response = $finance->getStudentPayments($user['id']);
        }
        
        elseif ($action === 'payment-receipt') {
            $response = $finance->getPaymentReceipt($_GET['payment_id']);
        }
        // Add this in the GET section (around line 100)
        elseif ($action === 'all-students') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getAllStudents();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }

        // Expenses
        elseif ($action === 'expenses') {
            if (User::hasRole(['admin', 'staff'])) {
                $startDate = $_GET['start_date'] ?? date('Y-m-01');
                $endDate = $_GET['end_date'] ?? date('Y-m-t');
                $response = $finance->getExpenses($startDate, $endDate);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'expense-summary') {
            if (User::hasRole(['admin', 'staff'])) {
                $startDate = $_GET['start_date'] ?? date('Y-m-01');
                $endDate = $_GET['end_date'] ?? date('Y-m-t');
                $response = $finance->getExpenseSummary($startDate, $endDate);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Marketing Campaigns
        elseif ($action === 'campaigns') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getAllCampaigns();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        elseif ($action === 'campaign-details') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getCampaignDetails($_GET['campaign_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
       
        // Leads
        elseif ($action === 'campaign-leads') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getCampaignLeads($_GET['campaign_id']);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        
        // Financial Dashboard
        elseif ($action === 'financial-summary') {
            if (User::hasRole(['admin', 'staff'])) {
                $startDate = $_GET['start_date'] ?? date('Y-m-01');
                $endDate = $_GET['end_date'] ?? date('Y-m-t');
                $response = $finance->getFinancialSummary($startDate, $endDate);
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        // Add this in the GET section (around line 100)
        elseif ($action === 'payments-tracking') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getAllPayments();
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
        elseif ($action === 'outstanding-invoices') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getOutstandingInvoices();
            } else {
                $response = $finance->getStudentOutstandingInvoices($user['id']);
            }
        }
        // Add these in the GET section
    elseif ($action === 'campaigns-dropdown') {
        if (User::hasRole(['admin', 'staff'])) {
            $response = $finance->getAllCampaignsForDropdown();
        } else {
            $response = ['success' => false, 'message' => 'Unauthorized'];
        }
    }
    elseif ($action === 'campaign-analytics') {
        if (User::hasRole(['admin', 'staff'])) {
            $response = $finance->getCampaignAnalytics($_GET['campaign_id']);
        } else {
            $response = ['success' => false, 'message' => 'Unauthorized'];
        }
    }
// The 'campaign-leads' endpoint should already exist - verify it's there
        
        elseif ($action === 'dashboard-stats') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->getDashboardStats();
            } else if ($user['role'] === 'student') {
                $response = $finance->getStudentDashboardStats($user['id']);
            }
        }
    }
    
    elseif ($method === 'PUT') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if ($action === 'update-invoice-status') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->updateInvoiceStatus(
                    $data['invoice_id'],
                    $data['status']
                );
            } else {
                $response = ['success' => false, 'message' => 'Unauthorized'];
            }
        }
    }
    
    elseif ($method === 'DELETE') {
        if ($action === 'delete-expense') {
            if (User::hasRole(['admin', 'staff'])) {
                $response = $finance->deleteExpense($_GET['expense_id']);
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