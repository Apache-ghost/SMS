<?php
// FILE: classes/Finance.php - Finance & Marketing Operations

class Finance {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ==================== INVOICE MANAGEMENT ====================
    
    public function createInvoice($student_id, $amount, $description, $due_date = null) {
        if (empty($student_id) || !$amount) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $invoice_number = 'INV-' . date('YmdHis') . '-' . $student_id;
        $due_date = $due_date ?: date('Y-m-d', strtotime('+30 days'));

        $query = "INSERT INTO invoices (student_id, invoice_number, amount, description, due_date, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isdss", $student_id, $invoice_number, $amount, $description, $due_date);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Invoice created', 'id' => $this->db->insert_id, 'invoice_number' => $invoice_number];
        }
        return ['success' => false, 'message' => 'Failed to create invoice'];
    }

    public function getStudentInvoices($student_id) {
        $query = "SELECT * FROM invoices WHERE student_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        return ['success' => true, 'data' => $invoices];
    }
    // Add this method to your Finance class in classes/Finance.php
public function getAllPayments() {
    $query = "SELECT p.*, u.full_name, i.invoice_number 
              FROM payments p 
              JOIN users u ON p.student_id = u.id 
              LEFT JOIN invoices i ON p.invoice_id = i.id 
              ORDER BY p.payment_date DESC";
    $result = $this->db->query($query);

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    return ['success' => true, 'data' => $payments];
}

public function getOutstandingInvoices() {
    $query = "SELECT i.*, u.full_name, u.email 
              FROM invoices i 
              JOIN users u ON i.student_id = u.id 
              WHERE i.status = 'pending' 
              ORDER BY i.due_date ASC";
    $result = $this->db->query($query);

    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    return ['success' => true, 'data' => $invoices];
}

public function getStudentOutstandingInvoices($student_id) {
    $query = "SELECT * FROM invoices 
              WHERE student_id = ? AND status = 'pending' 
              ORDER BY due_date ASC";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    return ['success' => true, 'data' => $invoices];
}

    public function getAllInvoices() {
        $query = "SELECT i.*, u.full_name, u.email FROM invoices i 
                  JOIN users u ON i.student_id = u.id 
                  ORDER BY i.created_at DESC";
        $result = $this->db->query($query);

        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        return ['success' => true, 'data' => $invoices];
    }

    public function getInvoiceDetails($invoice_id) {
        $query = "SELECT i.*, u.full_name, u.email FROM invoices i 
                  JOIN users u ON i.student_id = u.id 
                  WHERE i.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        }
        return ['success' => false, 'message' => 'Invoice not found'];
    }

    public function updateInvoiceStatus($invoice_id, $status) {
        $valid_statuses = ['pending', 'paid', 'overdue', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $query = "UPDATE invoices SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $invoice_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Invoice status updated'];
        }
        return ['success' => false, 'message' => 'Failed to update status'];
    }

    // ==================== PAYMENT PROCESSING ====================
    
    public function processPayment($invoice_id, $amount, $payment_method) {
        $invoiceQuery = "SELECT student_id, amount FROM invoices WHERE id = ?";
        $stmt = $this->db->prepare($invoiceQuery);
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();

        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        if ($amount > $invoice['amount']) {
            return ['success' => false, 'message' => 'Payment exceeds invoice amount'];
        }

        $reference_number = 'PAY-' . date('YmdHis') . '-' . $invoice_id;
        $payment_date = date('Y-m-d H:i:s');

        $paymentQuery = "INSERT INTO payments (invoice_id, student_id, amount, payment_method, reference_number, status, payment_date, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'completed', ?, NOW())";
        $paymentStmt = $this->db->prepare($paymentQuery);
        $paymentStmt->bind_param("iidsss", $invoice_id, $invoice['student_id'], $amount, $payment_method, $reference_number, $payment_date);

        if ($paymentStmt->execute()) {
            $payment_id = $this->db->insert_id;
            
            $checkQuery = "SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bind_param("i", $invoice_id);
            $checkStmt->execute();
            $paid = $checkStmt->get_result()->fetch_assoc()['paid'];

            if ($paid >= $invoice['amount']) {
                $this->updateInvoiceStatus($invoice_id, 'paid');
            }

            return ['success' => true, 'message' => 'Payment processed', 'payment_id' => $payment_id, 'reference' => $reference_number];
        }
        return ['success' => false, 'message' => 'Payment processing failed'];
    }

    public function getStudentPayments($student_id) {
        $query = "SELECT p.*, i.invoice_number, i.amount as invoice_amount FROM payments p 
                  JOIN invoices i ON p.invoice_id = i.id 
                  WHERE p.student_id = ? 
                  ORDER BY p.payment_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return ['success' => true, 'data' => $payments];
    }

    public function getPaymentReceipt($payment_id) {
        $query = "SELECT p.*, i.invoice_number, u.full_name, u.email FROM payments p 
                  JOIN invoices i ON p.invoice_id = i.id 
                  JOIN users u ON p.student_id = u.id 
                  WHERE p.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        }
        return ['success' => false, 'message' => 'Payment not found'];
    }

    // ==================== EXPENSE TRACKING ====================
    
    public function addExpense($category, $amount, $description, $expense_date) {
        if (empty($category) || !$amount) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO expenses (category, amount, description, expense_date, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sdss", $category, $amount, $description, $expense_date);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Expense recorded', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to record expense'];
    }

    public function getExpenses($start_date, $end_date) {
        $query = "SELECT * FROM expenses WHERE expense_date BETWEEN ? AND ? ORDER BY expense_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $expenses = [];
        while ($row = $result->fetch_assoc()) {
            $expenses[] = $row;
        }
        return ['success' => true, 'data' => $expenses];
    }

    public function getExpenseSummary($start_date, $end_date) {
        $query = "SELECT category, SUM(amount) as total, COUNT(*) as count 
                  FROM expenses 
                  WHERE expense_date BETWEEN ? AND ? 
                  GROUP BY category";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $summary = [];
        $total_expenses = 0;
        while ($row = $result->fetch_assoc()) {
            $summary[] = $row;
            $total_expenses += $row['total'];
        }

        return ['success' => true, 'data' => $summary, 'total' => $total_expenses];
    }

    public function deleteExpense($expense_id) {
        $query = "DELETE FROM expenses WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $expense_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Expense deleted'];
        }
        return ['success' => false, 'message' => 'Failed to delete expense'];
    }

    // ==================== MARKETING CAMPAIGNS ====================
    
    public function createCampaign($campaign_name, $campaign_type, $budget, $start_date, $end_date) {
        if (empty($campaign_name) || !$budget) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO campaigns (campaign_name, campaign_type, budget, start_date, end_date, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, 'active', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssdss", $campaign_name, $campaign_type, $budget, $start_date, $end_date);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Campaign created', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to create campaign'];
    }

    public function getAllCampaigns() {
        $query = "SELECT * FROM campaigns ORDER BY created_at DESC";
        $result = $this->db->query($query);

        $campaigns = [];
        while ($row = $result->fetch_assoc()) {
            $campaigns[] = $row;
        }
        return ['success' => true, 'data' => $campaigns];
    }

    public function getCampaignDetails($campaign_id) {
        $query = "SELECT * FROM campaigns WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'data' => $row];
        }
        return ['success' => false, 'message' => 'Campaign not found'];
    }

    public function addLead($campaign_id, $lead_name, $lead_email, $lead_phone = null) {
        if (empty($campaign_id) || empty($lead_name) || empty($lead_email)) {
            return ['success' => false, 'message' => 'All fields required'];
        }

        $query = "INSERT INTO leads (campaign_id, lead_name, lead_email, lead_phone, status, created_at) 
                  VALUES (?, ?, ?, ?, 'new', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isss", $campaign_id, $lead_name, $lead_email, $lead_phone);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Lead added', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add lead'];
    }
    // Add this method to get all campaigns (for dropdowns)
    public function getAllCampaignsForDropdown() {
        $query = "SELECT id, campaign_name FROM campaigns WHERE status = 'active' ORDER BY campaign_name";
        $result = $this->db->query($query);
        
        $campaigns = [];
        while ($row = $result->fetch_assoc()) {
            $campaigns[] = $row;
        }
        return ['success' => true, 'data' => $campaigns];
    }

    // Add this method to get campaign leads (already exists but let's verify)
    public function getCampaignLeads($campaign_id) {
        $query = "SELECT l.*, c.campaign_name 
                FROM leads l 
                JOIN campaigns c ON l.campaign_id = c.id 
                WHERE l.campaign_id = ? 
                ORDER BY l.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $leads = [];
        while ($row = $result->fetch_assoc()) {
            $leads[] = $row;
        }
        return ['success' => true, 'data' => $leads];
    }

    // Add this method for campaign analytics (already exists but let's verify)
    public function getCampaignAnalytics($campaign_id) {
        // Get campaign details
        $campaignQuery = "SELECT id, campaign_name, budget, start_date, end_date FROM campaigns WHERE id = ?";
        $stmt = $this->db->prepare($campaignQuery);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $campaign = $stmt->get_result()->fetch_assoc();

        if (!$campaign) {
            return ['success' => false, 'message' => 'Campaign not found'];
        }

        // Get leads count
        $leadsQuery = "SELECT COUNT(*) as total_leads, 
                            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as conversions 
                    FROM leads WHERE campaign_id = ?";
        $stmt = $this->db->prepare($leadsQuery);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $leads = $stmt->get_result()->fetch_assoc();

        // Get conversion value
        $conversionQuery = "SELECT SUM(c.conversion_value) as conversion_value 
                        FROM conversions c 
                        JOIN leads l ON c.lead_id = l.id 
                        WHERE l.campaign_id = ?";
        $stmt = $this->db->prepare($conversionQuery);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $conversion = $stmt->get_result()->fetch_assoc();

        $total_leads = $leads['total_leads'] ?? 0;
        $conversions = $leads['conversions'] ?? 0;
        $conversion_value = $conversion['conversion_value'] ?? 0;
        
        // Calculate metrics
        $conversion_rate = $total_leads > 0 ? round(($conversions / $total_leads) * 100, 2) : 0;
        $roi = $campaign['budget'] > 0 ? round((($conversion_value - $campaign['budget']) / $campaign['budget']) * 100, 2) : 0;

        return [
            'success' => true,
            'data' => [
                'campaign_name' => $campaign['campaign_name'],
                'budget' => (float)$campaign['budget'],
                'total_leads' => (int)$total_leads,
                'conversions' => (int)$conversions,
                'conversion_value' => (float)$conversion_value,
                'conversion_rate' => (float)$conversion_rate,
                'roi' => (float)$roi,
                'period' => date('M d', strtotime($campaign['start_date'])) . ' - ' . date('M d, Y', strtotime($campaign['end_date']))
            ]
        ];
    }


    public function recordConversion($lead_id, $conversion_value) {
        $updateQuery = "UPDATE leads SET status = 'converted', conversion_date = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("i", $lead_id);
        $stmt->execute();

        $conversionQuery = "INSERT INTO conversions (lead_id, conversion_value, created_at) VALUES (?, ?, NOW())";
        $convStmt = $this->db->prepare($conversionQuery);
        $convStmt->bind_param("id", $lead_id, $conversion_value);

        if ($convStmt->execute()) {
            return ['success' => true, 'message' => 'Conversion recorded', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to record conversion'];
    }
    // ==================== STUDENT MANAGEMENT ====================
    
    public function getAllStudents() {
        $query = "SELECT id, full_name, email FROM users WHERE role = 'student' ORDER BY full_name";
        $result = $this->db->query($query);

        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        return ['success' => true, 'data' => $students];
    }
    

    // ==================== FINANCIAL REPORTING ====================
    
    public function getFinancialSummary($start_date, $end_date) {
        $revenueQuery = "SELECT SUM(amount) as total_revenue FROM payments WHERE payment_date BETWEEN ? AND ? AND status = 'completed'";
        $stmt = $this->db->prepare($revenueQuery);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $revenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

        $expenseQuery = "SELECT SUM(amount) as total_expenses FROM expenses WHERE expense_date BETWEEN ? AND ?";
        $stmt = $this->db->prepare($expenseQuery);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $expenses = $stmt->get_result()->fetch_assoc()['total_expenses'] ?? 0;

        $pendingQuery = "SELECT SUM(amount) as pending_amount FROM invoices WHERE status = 'pending' AND created_at BETWEEN ? AND ?";
        $stmt = $this->db->prepare($pendingQuery);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc()['pending_amount'] ?? 0;

        $net_profit = $revenue - $expenses;

        return [
            'success' => true,
            'data' => [
                'revenue' => (float)$revenue,
                'expenses' => (float)$expenses,
                'net_profit' => (float)$net_profit,
                'pending_invoices' => (float)$pending
            ]
        ];
    }

    public function getDashboardStats() {
        $revenueQuery = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
        $revenue = $this->db->query($revenueQuery)->fetch_assoc()['total'] ?? 0;

        $expenseQuery = "SELECT SUM(amount) as total FROM expenses";
        $expenses = $this->db->query($expenseQuery)->fetch_assoc()['total'] ?? 0;

        $pendingQuery = "SELECT COUNT(*) as count FROM invoices WHERE status = 'pending'";
        $pending = $this->db->query($pendingQuery)->fetch_assoc()['count'] ?? 0;

        $campaignsQuery = "SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'";
        $campaigns = $this->db->query($campaignsQuery)->fetch_assoc()['count'] ?? 0;

        return [
            'success' => true,
            'data' => [
                'total_revenue' => (float)$revenue,
                'total_expenses' => (float)$expenses,
                'pending_invoices' => $pending,
                'active_campaigns' => $campaigns
            ]
        ];
    }

    public function getStudentDashboardStats($student_id) {
        $invoicesQuery = "SELECT COUNT(*) as count, SUM(amount) as total FROM invoices WHERE student_id = ?";
        $stmt = $this->db->prepare($invoicesQuery);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $invoices = $stmt->get_result()->fetch_assoc();

        $paidQuery = "SELECT SUM(amount) as total FROM payments WHERE student_id = ? AND status = 'completed'";
        $stmt = $this->db->prepare($paidQuery);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $paid = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        $outstanding = ($invoices['total'] ?? 0) - $paid;

        return [
            'success' => true,
            'data' => [
                'total_invoices' => $invoices['count'] ?? 0,
                'total_amount' => (float)($invoices['total'] ?? 0),
                'paid_amount' => (float)$paid,
                'outstanding_amount' => (float)$outstanding
            ]
        ];
    }
}
?>