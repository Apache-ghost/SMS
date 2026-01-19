<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance & Marketing Module - ERP System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        body {
            background: #f9fafb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 30px;
        }
        .sidebar {
            background: white;
            min-height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
        }
        .sidebar .nav-link {
            color: #6b7280;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary);
            color: white;
        }
        .main-content {
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .stat-card {
            padding: 25px;
            border-left: 5px solid var(--primary);
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin: 10px 0;
        }
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        .table-responsive {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background: #f3f4f6;
            border: none;
            padding: 15px;
            font-weight: 600;
            color: #1f2937;
        }
        .table tbody td {
            padding: 15px;
            border-color: #e5e7eb;
            vertical-align: middle;
        }
        .badge-status {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-paid {
            background: #dcfce7;
            color: #166534;
        }
        .badge-overdue {
            background: #fee2e2;
            color: #991b1b;
        }
        .modal-header {
            background: var(--primary);
            color: white;
            border: none;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .hidden {
            display: none;
        }
        .invoice-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 5px solid var(--primary);
        }
        .metric-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .metric {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-top: 4px solid var(--primary);
        }
        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin: 10px 0;
        }
        .metric-label {
            color: #6b7280;
            font-size: 13px;
        }
        .receipt-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
            border: 1px solid #e5e7eb;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .receipt-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-weight: 700;
            font-size: 18px;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
            <h3 style="margin: 0; color: #1f2937;">💰 Finance & Marketing Module</h3>
            <div>
                <span id="userNameDisplay" style="color: #6b7280; font-size: 14px; margin-right: 20px;"></span>
                <button onclick="goBack()" class="btn btn-outline-secondary btn-sm">Back to Dashboard</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="sidebar">
                    <h5 style="margin-bottom: 20px; color: #1f2937;">Navigation</h5>
                    <div class="nav-link active" onclick="switchToSection('dashboard')">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </div>
                    <div id="studentSection">
                        <div class="nav-link" onclick="switchToSection('my-invoices')">
                            <i class="fas fa-file-invoice"></i> My Invoices
                        </div>
                        <div class="nav-link" onclick="switchToSection('my-payments')">
                            <i class="fas fa-credit-card"></i> My Payments
                        </div>
                        <div class="nav-link" onclick="switchToSection('payment-portal')">
                            <i class="fas fa-cash-register"></i> Pay Now
                        </div>
                    </div>
                    <hr>
                    <div id="adminSection" class="hidden">
                        <h6 style="color: #6b7280; margin: 20px 0 10px;">Finance</h6>
                        <div class="nav-link" onclick="switchToSection('all-invoices')">
                            <i class="fas fa-file-invoice-dollar"></i> All Invoices
                        </div>
                        <div class="nav-link" onclick="switchToSection('payments-tracking')">
                            <i class="fas fa-receipt"></i> Payments
                        </div>
                        <div class="nav-link" onclick="switchToSection('expenses')">
                            <i class="fas fa-money-bill-wave"></i> Expenses
                        </div>
                        <div class="nav-link" onclick="switchToSection('financial-reports')">
                            <i class="fas fa-chart-bar"></i> Reports
                        </div>
                        <h6 style="color: #6b7280; margin: 20px 0 10px;">Marketing</h6>
                        <div class="nav-link" onclick="switchToSection('campaigns')">
                            <i class="fas fa-bullhorn"></i> Campaigns
                        </div>
                        <div class="nav-link" onclick="switchToSection('leads')">
                            <i class="fas fa-users"></i> Leads
                        </div>
                        <div class="nav-link" onclick="switchToSection('analytics')">
                            <i class="fas fa-analytics"></i> Analytics
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content">
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>

                    <!-- Dashboard Section -->
                    <div id="dashboard" class="section">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">Dashboard</h2>
                        <div class="metric-box">
                            <div class="metric">
                                <div class="metric-label">Total Revenue</div>
                                <div class="metric-value" id="totalRevenue">₦0</div>
                            </div>
                            <div class="metric">
                                <div class="metric-label">Total Expenses</div>
                                <div class="metric-value" id="totalExpenses">₦0</div>
                            </div>
                            <div class="metric">
                                <div class="metric-label">Pending Invoices</div>
                                <div class="metric-value" id="pendingCount">0</div>
                            </div>
                            <div class="metric">
                                <div class="metric-label">Active Campaigns</div>
                                <div class="metric-value" id="activeCampaigns">0</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-transparent p-4 border-bottom">
                                        <h5 style="margin: 0;">Recent Invoices</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="recentInvoices">Loading...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-transparent p-4 border-bottom">
                                        <h5 style="margin: 0;">Quick Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <button onclick="switchToSection('payment-portal')" class="btn btn-success w-100 mb-2">
                                            <i class="fas fa-cash-register"></i> Make Payment
                                        </button>
                                        <button onclick="switchToSection('my-invoices')" class="btn btn-primary w-100 mb-2">
                                            <i class="fas fa-file-invoice"></i> View Invoices
                                        </button>
                                        <button onclick="downloadStatement()" class="btn btn-info w-100">
                                            <i class="fas fa-download"></i> Download Statement
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Invoices Section -->
                    <div id="my-invoices" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">My Invoices</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="myInvoicesTable">
                                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- My Payments Section -->
                    <div id="my-payments" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">My Payments</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Reference #</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="myPaymentsTable">
                                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Portal -->
                    <div id="payment-portal" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">Payment Portal</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-transparent p-4 border-bottom">
                                        <h5 style="margin: 0;">Select Invoice</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Outstanding Invoices</label>
                                            <select id="invoiceSelect" class="form-select" onchange="loadInvoiceAmount()">
                                                <option value="">Choose an invoice...</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Amount</label>
                                            <input type="number" id="paymentAmount" class="form-control" placeholder="0.00" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Method</label>
                                            <select id="paymentMethod" class="form-select">
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="cash">Cash</option>
                                                <option value="cheque">Cheque</option>
                                            </select>
                                        </div>
                                        <button onclick="processPayment()" class="btn btn-success w-100">
                                            <i class="fas fa-check"></i> Process Payment
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-transparent p-4 border-bottom">
                                        <h5 style="margin: 0;">Payment Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="invoice-box">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                                <strong>Invoice Amount:</strong>
                                                <span id="summaryInvoiceAmount">₦0</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                                <strong>Payment Amount:</strong>
                                                <span id="summaryPaymentAmount">₦0</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px solid #e5e7eb; color: var(--primary); font-weight: 700;">
                                                <strong>Balance:</strong>
                                                <span id="summaryBalance">₦0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Invoices (Admin) -->
                    <div id="all-invoices" class="section hidden">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 style="color: #1f2937;">All Invoices</h2>
                            <button onclick="openCreateInvoiceModal()" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Invoice
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Student</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="allInvoicesTable">
                                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payments Tracking (Admin) -->
                    <div id="payments-tracking" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">Payment Tracking</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Invoice #</th>
                                        <th>Reference #</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentsTrackingTable">
                                    <tr><td colspan="7" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expenses Management -->
                    <div id="expenses" class="section hidden">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 style="color: #1f2937;">Expense Management</h2>
                            <button onclick="openAddExpenseModal()" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Expense
                            </button>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" id="expenseStartDate" class="form-control" onchange="loadExpenses()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" id="expenseEndDate" class="form-control" onchange="loadExpenses()">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="expensesTable">
                                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Financial Reports -->
                    <div id="financial-reports" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">Financial Reports</h2>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" id="reportStartDate" class="form-control" onchange="loadFinancialReport()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" id="reportEndDate" class="form-control" onchange="loadFinancialReport()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button onclick="downloadFinancialReport()" class="btn btn-success w-100">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                            </div>
                        </div>
                        <div id="financialReportContent">Loading...</div>
                    </div>

                    <!-- Campaigns Management -->
                    <div id="campaigns" class="section hidden">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 style="color: #1f2937;">Marketing Campaigns</h2>
                            <button onclick="openCreateCampaignModal()" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Campaign
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Campaign Name</th>
                                        <th>Type</th>
                                        <th>Budget</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="campaignsTable">
                                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Leads Management -->
                    <div id="leads" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">Lead Management</h2>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Select Campaign</label>
                                <select id="leadscampaignSelect" class="form-select" onchange="loadLeads()">
                                    <option value="">Choose a campaign...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button onclick="openAddLeadModal()" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Add Lead
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Lead Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Conversion Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="leadsTable">
                                    <tr><td colspan="6" class="text-center">Select a campaign</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Campaign Analytics -->
                    <div id="analytics" class="section hidden">
                        <h2 style="margin-bottom: 30px; color: #1f2937;">Campaign Analytics</h2>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Select Campaign</label>
                                <select id="analyticsCampaignSelect" class="form-select" onchange="loadCampaignAnalytics()">
                                    <option value="">Choose a campaign...</option>
                                </select>
                            </div>
                        </div>
                        <div id="analytics--content" class="metric-box">
<div class="metric">
<div class="metric-label">Budget</div>
<div class="metric-value" id="analyticsBudget">₦0</div>
</div>
<div class="metric">
<div class="metric-label">Total Leads</div>
<div class="metric-value" id="analyticsLeads">0</div>
</div>
<div class="metric">
<div class="metric-label">Conversions</div>
<div class="metric-value" id="analyticsConversions">0</div>
</div>
<div class="metric">
<div class="metric-label">Conversion Rate</div>
<div class="metric-value" id="analyticsRate">0%</div>
</div>
<div class="metric">
<div class="metric-label">Total Revenue</div>
<div class="metric-value" id="analyticsRevenue">₦0</div>
</div>
<div class="metric">
<div class="metric-label">ROI</div>
<div class="metric-value" id="analyticsROI">0%</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Student</label>
                    <select id="invoiceStudentSelect" class="form-select">
                        <option value="">Select student...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" id="invoiceAmount" class="form-control" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea id="invoiceDescription" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" id="invoiceDueDate" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createInvoice()">Create</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select id="expenseCategory" class="form-select">
                        <option value="">Select category...</option>
                        <option value="Salaries">Salaries</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" id="expenseAmount" class="form-control" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea id="expenseDescription" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" id="expenseDate" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addExpense()">Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Campaign</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Campaign Name</label>
                    <input type="text" id="campaignName" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Campaign Type</label>
                    <select id="campaignType" class="form-select">
                        <option value="social_media">Social Media</option>
                        <option value="email">Email</option>
                        <option value="referral">Referral</option>
                        <option value="paid_ads">Paid Ads</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Budget</label>
                    <input type="number" id="campaignBudget" class="form-control" placeholder="0.00">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" id="campaignStartDate" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" id="campaignEndDate" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCampaign()">Create</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Lead</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Lead Name</label>
                    <input type="text" id="leadName" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="leadEmail" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="tel" id="leadPhone" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addLead()">Add</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE = '/SMS/api/finance.php';
    let currentUser = null;
    let allStudents = [];

    window.addEventListener('load', async function() {
        await loadUserData();
        setDefaultDates();
        await loadDashboard();
        // Add this line to initialize payment amount listener
        document.getElementById('paymentAmount').addEventListener('input', updatePaymentSummary);
    });

    async function loadUserData() {
        try {
            const response = await fetch('/SMS/api/auth.php?action=current-user', { credentials: 'include' });
            const data = await response.json();
            if (data.success) {
                currentUser = data.user;
                document.getElementById('userNameDisplay').textContent = 'Welcome, ' + currentUser.full_name;
                
                if (['admin', 'staff'].includes(currentUser.role)) {
                    document.getElementById('adminSection').classList.remove('hidden');
                    document.getElementById('studentSection').style.display = 'none';
                    await loadAllStudents();
                } else {
                    document.getElementById('adminSection').style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error loading user:', error);
        }
    }

    function setDefaultDates() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        document.getElementById('expenseStartDate').valueAsDate = firstDay;
        document.getElementById('expenseEndDate').valueAsDate = lastDay;
        document.getElementById('reportStartDate').valueAsDate = firstDay;
        document.getElementById('reportEndDate').valueAsDate = lastDay;
    }

    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        alertContainer.innerHTML = '<div class="alert ' + alertClass + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        setTimeout(() => alertContainer.innerHTML = '', 5000);
    }

    function switchToSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        sections.forEach(s => s.classList.add('hidden'));
        navLinks.forEach(l => l.classList.remove('active'));
        
        document.getElementById(sectionId).classList.remove('hidden');
        event.target.closest('.nav-link').classList.add('active');

        if (sectionId === 'my-invoices') loadMyInvoices();
        else if (sectionId === 'my-payments') loadMyPayments();
        else if (sectionId === 'payment-portal') loadOutstandingInvoices();
        else if (sectionId === 'all-invoices') loadAllInvoices();
        else if (sectionId === 'payments-tracking') loadPaymentsTracking();
        else if (sectionId === 'expenses') loadExpenses();
        else if (sectionId === 'campaigns') loadCampaigns();
        else if (sectionId === 'leads') {
            loadCampaignsForLeads();
            document.getElementById('leadsTable').innerHTML = '<tr><td colspan="6" class="text-center">Select a campaign</td></tr>';
        }
        else if (sectionId === 'analytics') {
            loadCampaignsForAnalytics();
            // Clear previous analytics data
            document.getElementById('analyticsBudget').textContent = '₦0';
            document.getElementById('analyticsLeads').textContent = '0';
            document.getElementById('analyticsConversions').textContent = '0';
            document.getElementById('analyticsRate').textContent = '0%';
            document.getElementById('analyticsRevenue').textContent = '₦0';
            document.getElementById('analyticsROI').textContent = '0%';
        }
    }

    function goBack() {
        window.location.href = '/SMS/index.php';
    }

    async function apiCall(action, method, data = null) {
        try {
            let url = API_BASE + '?action=' + action;
            
            // For GET requests, append data as query parameters
            if (method === 'GET' && data) {
                const params = new URLSearchParams();
                for (const key in data) {
                    if (data[key] !== null && data[key] !== undefined) {
                        params.append(key, data[key]);
                    }
                }
                url += '&' + params.toString();
            }
            
            const options = {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            };
            
            // For POST/PUT requests, put data in body
            if (method !== 'GET' && data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            const text = await response.text();
            return text ? JSON.parse(text) : { success: false };
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'API Error: ' + error.message };
        }
    }

    // Dashboard Functions
    async function loadDashboard() {
        const result = await apiCall('dashboard-stats', 'GET');
        if (result.success && result.data) {
            if (currentUser.role === 'student') {
                document.getElementById('totalRevenue').textContent = '₦' + result.data.total_amount.toLocaleString();
                document.getElementById('totalExpenses').textContent = '₦' + result.data.paid_amount.toLocaleString();
                document.getElementById('pendingCount').textContent = '₦' + result.data.outstanding_amount.toLocaleString();
            } else {
                document.getElementById('totalRevenue').textContent = '₦' + result.data.total_revenue.toLocaleString();
                document.getElementById('totalExpenses').textContent = '₦' + result.data.total_expenses.toLocaleString();
                document.getElementById('pendingCount').textContent = result.data.pending_invoices;
                document.getElementById('activeCampaigns').textContent = result.data.active_campaigns;
            }
        }
        await loadRecentInvoices();
    }

    async function loadRecentInvoices() {
        let result;
        if (currentUser.role === 'student') {
            result = await apiCall('my-invoices', 'GET');
        } else {
            result = await apiCall('all-invoices', 'GET');
        }
        
        const container = document.getElementById('recentInvoices');
        if (result.success && result.data && result.data.length > 0) {
            container.innerHTML = result.data.slice(0, 5).map(inv => 
                '<div class="invoice-box"><strong>' + inv.invoice_number + '</strong> - ₦' + inv.amount.toLocaleString() + 
                ' <span class="badge badge-status badge-' + inv.status + '">' + inv.status + '</span></div>'
            ).join('');
        } else {
            container.innerHTML = '<p class="text-muted">No invoices found</p>';
        }
    }
    // Add function to update payment summary
    function updatePaymentSummary() {
        const invoiceAmount = parseFloat(document.getElementById('summaryInvoiceAmount').textContent.replace('₦', '').replace(/,/g, '')) || 0;
        const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
        
        document.getElementById('summaryPaymentAmount').textContent = '₦' + paymentAmount.toLocaleString();
        
        const balance = invoiceAmount - paymentAmount;
        document.getElementById('summaryBalance').textContent = '₦' + balance.toLocaleString();
        
        // Update balance color
        const balanceElement = document.getElementById('summaryBalance');
        balanceElement.style.color = balance === 0 ? 'var(--success)' : 'var(--danger)';
    }

    // Invoice Functions
    async function loadMyInvoices() {
        const result = await apiCall('my-invoices', 'GET');
        const container = document.getElementById('myInvoicesTable');
        if (result.success && result.data) {
            container.innerHTML = result.data.map(inv => 
                '<tr><td>' + inv.invoice_number + '</td><td>₦' + inv.amount.toLocaleString() + 
                '</td><td>' + new Date(inv.due_date).toLocaleDateString() + 
                '</td><td><span class="badge badge-status badge-' + inv.status + '">' + inv.status + '</span></td>' +
                '<td><button class="btn btn-sm btn-primary" onclick="switchToSection(\'payment-portal\')">Pay</button></td></tr>'
            ).join('');
        }
    }

    async function loadAllInvoices() {
        const result = await apiCall('all-invoices', 'GET');
        const container = document.getElementById('allInvoicesTable');
        if (result.success && result.data) {
            container.innerHTML = result.data.map(inv => 
                '<tr><td>' + inv.invoice_number + '</td><td>' + inv.full_name + 
                '</td><td>₦' + inv.amount.toLocaleString() + 
                '</td><td>' + new Date(inv.due_date).toLocaleDateString() + 
                '</td><td><span class="badge badge-status badge-' + inv.status + '">' + inv.status + '</span></td>' +
                '<td><button class="btn btn-sm btn-info" onclick="updateInvoiceStatus(' + inv.id + ')">Update</button></td></tr>'
            ).join('');
        }
    }

    async function loadAllStudents() {
        try {
            const response = await fetch('/SMS/api/academic.php?action=student-enrollments', { credentials: 'include' });
            // Load all users instead - create endpoint if needed
            const result = await apiCall('all-invoices', 'GET');
            if (result.success && result.data) {
                const students = [...new Map(result.data.map(inv => [inv.student_id, inv])).values()];
                const select = document.getElementById('invoiceStudentSelect');
                select.innerHTML = '<option value="">Select student...</option>' + 
                    result.data.map(inv => '<option value="' + inv.student_id + '">' + inv.full_name + '</option>').join('');
            }
        } catch (error) {
            console.error('Error loading students:', error);
        }
    }

    function openCreateInvoiceModal() {
        new bootstrap.Modal(document.getElementById('createInvoiceModal')).show();
    }

    async function createInvoice() {
        const studentId = document.getElementById('invoiceStudentSelect').value;
        const amount = document.getElementById('invoiceAmount').value;
        const description = document.getElementById('invoiceDescription').value;
        const dueDate = document.getElementById('invoiceDueDate').value;

        if (!studentId || !amount) {
            showAlert('All fields required');
            return;
        }

        const result = await apiCall('create-invoice', 'POST', {
            student_id: parseInt(studentId),
            amount: parseFloat(amount),
            description: description,
            due_date: dueDate
        });

        if (result.success) {
            showAlert('Invoice created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createInvoiceModal')).hide();
            document.getElementById('invoiceStudentSelect').value = '';
            document.getElementById('invoiceAmount').value = '';
            document.getElementById('invoiceDescription').value = '';
            loadAllInvoices();
        } else {
            showAlert(result.message);
        }
    }
    async function loadAllStudents() {
        try {
            const result = await apiCall('all-students', 'GET');
            if (result.success && result.data) {
                const select = document.getElementById('invoiceStudentSelect');
                select.innerHTML = '<option value="">Select student...</option>' + 
                    result.data.map(student => 
                        '<option value="' + student.id + '">' + 
                        student.full_name + 
                        (student.email ? ' (' + student.email + ')' : '') +
                        (student.student_id ? ' - ID: ' + student.student_id : '') +
                        '</option>'
                    ).join('');
            } else {
                showAlert('Failed to load students');
            }
        } catch (error) {
            console.error('Error loading students:', error);
            showAlert('Error loading students');
        }
    }

    async function updateInvoiceStatus(invoiceId) {
        const newStatus = prompt('Enter new status (pending/paid/overdue/cancelled):');
        if (!newStatus) return;

        const result = await apiCall('update-invoice-status', 'PUT', {
            invoice_id: invoiceId,
            status: newStatus
        });

        if (result.success) {
            showAlert('Status updated', 'success');
            loadAllInvoices();
        } else {
            showAlert(result.message);
        }
    }

    // Payment Functions
    async function loadMyPayments() {
        const result = await apiCall('my-payments', 'GET');
        const container = document.getElementById('myPaymentsTable');
        if (result.success && result.data) {
            container.innerHTML = result.data.map(pay => 
                '<tr><td>' + pay.reference_number + '</td><td>₦' + pay.amount.toLocaleString() + 
                '</td><td>' + pay.payment_method + '</td><td>' + new Date(pay.payment_date).toLocaleDateString() + 
                '</td><td><span class="badge bg-success">' + pay.status + '</span></td>' +
                '<td><button class="btn btn-sm btn-info" onclick="downloadReceipt(' + pay.id + ')">Receipt</button></td></tr>'
            ).join('');
        }
    }

    async function loadPaymentsTracking() {
        const result = await apiCall('payments-tracking', 'GET');
        const container = document.getElementById('paymentsTrackingTable');
        if (result.success && result.data) {
            if (result.data.length === 0) {
                container.innerHTML = '<tr><td colspan="7" class="text-center">No payments found</td></tr>';
                return;
            }
            container.innerHTML = result.data.map(pay => 
                '<tr><td>' + (pay.full_name || 'N/A') + '</td>' +
                '<td>' + (pay.invoice_number || 'N/A') + '</td>' +
                '<td>' + (pay.reference_number || 'N/A') + '</td>' +
                '<td>₦' + (pay.amount ? pay.amount.toLocaleString() : '0') + '</td>' +
                '<td>' + (pay.payment_method || 'N/A') + '</td>' +
                '<td>' + (pay.payment_date ? new Date(pay.payment_date).toLocaleDateString() : 'N/A') + '</td>' +
                '<td><span class="badge bg-success">' + (pay.status || 'N/A') + '</span></td></tr>'
            ).join('');
        } else {
            container.innerHTML = '<tr><td colspan="7" class="text-center">Error loading payments</td></tr>';
        }
    }
    // Load outstanding invoices for payment portal
    async function loadOutstandingInvoices() {
        const result = await apiCall('outstanding-invoices', 'GET');
        const select = document.getElementById('invoiceSelect');
        
        if (result.success && result.data) {
            if (result.data.length === 0) {
                select.innerHTML = '<option value="">No outstanding invoices</option>';
                return;
            }
            
            select.innerHTML = '<option value="">Choose an invoice...</option>' + 
                result.data.map(inv => 
                    '<option value="' + inv.id + '" data-amount="' + inv.amount + '">' + 
                    inv.invoice_number + ' - ₦' + inv.amount.toLocaleString() + 
                    (inv.full_name ? ' (' + inv.full_name + ')' : '') + '</option>'
                ).join('');
        } else {
            select.innerHTML = '<option value="">Error loading invoices</option>';
        }
    }


    async function processPayment() {
        const invoiceId = document.getElementById('invoiceSelect').value;
        const amount = document.getElementById('paymentAmount').value;
        const method = document.getElementById('paymentMethod').value;

        if (!invoiceId || !amount) {
            showAlert('Please fill all fields');
            return;
        }

        const result = await apiCall('process-payment', 'POST', {
            invoice_id: parseInt(invoiceId),
            amount: parseFloat(amount),
            payment_method: method
        });

        if (result.success) {
            showAlert('Payment processed successfully', 'success');
            document.getElementById('invoiceSelect').value = '';
            document.getElementById('paymentAmount').value = '';
            loadDashboard();
        } else {
            showAlert(result.message);
        }
    }

    async function downloadReceipt(paymentId) {
        const result = await apiCall('payment-receipt', 'GET');
        if (result.success && result.data) {
            const receipt = result.data;
            let content = 'PAYMENT RECEIPT\n\n';
            content += 'Reference: ' + receipt.reference_number + '\n';
            content += 'Amount: ₦' + receipt.amount + '\n';
            content += 'Date: ' + new Date(receipt.payment_date).toLocaleDateString() + '\n';
            content += 'Status: ' + receipt.status + '\n';
            
            downloadFile(content, 'receipt.txt');
        }
    }
    // Update the loadInvoiceAmount function
    async function loadInvoiceAmount() {
        const invoiceId = document.getElementById('invoiceSelect').value;
        if (!invoiceId) {
            document.getElementById('summaryInvoiceAmount').textContent = '₦0';
            document.getElementById('summaryBalance').textContent = '₦0';
            return;
        }

        const selectedOption = document.getElementById('invoiceSelect').selectedOptions[0];
        const invoiceAmount = selectedOption.getAttribute('data-amount') || 0;
        
        document.getElementById('summaryInvoiceAmount').textContent = '₦' + parseFloat(invoiceAmount).toLocaleString();
        document.getElementById('paymentAmount').value = invoiceAmount;
        updatePaymentSummary();
    }
    // Expense Functions
    async function loadExpenses() {
        const startDate = document.getElementById('expenseStartDate').value;
        const endDate = document.getElementById('expenseEndDate').value;

        const result = await apiCall('expenses', 'GET');
        const container = document.getElementById('expensesTable');
        if (result.success && result.data) {
            container.innerHTML = result.data.map(exp => 
                '<tr><td>' + exp.category + '</td><td>₦' + exp.amount.toLocaleString() + 
                '</td><td>' + exp.description + '</td><td>' + new Date(exp.expense_date).toLocaleDateString() + 
                '</td><td><button class="btn btn-sm btn-danger" onclick="deleteExpense(' + exp.id + ')">Delete</button></td></tr>'
            ).join('');
        }
    }

    function openAddExpenseModal() {
        document.getElementById('expenseDate').valueAsDate = new Date();
        new bootstrap.Modal(document.getElementById('addExpenseModal')).show();
    }

    async function addExpense() {
        const category = document.getElementById('expenseCategory').value;
        const amount = document.getElementById('expenseAmount').value;
        const description = document.getElementById('expenseDescription').value;
        const date = document.getElementById('expenseDate').value;

        if (!category || !amount || !date) {
            showAlert('All fields required');
            return;
        }

        const result = await apiCall('add-expense', 'POST', {
            category: category,
            amount: parseFloat(amount),
            description: description,
            expense_date: date
        });

        if (result.success) {
            showAlert('Expense added successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addExpenseModal')).hide();
            document.getElementById('expenseCategory').value = '';
            document.getElementById('expenseAmount').value = '';
            document.getElementById('expenseDescription').value = '';
            loadExpenses();
        } else {
            showAlert(result.message);
        }
    }

    async function deleteExpense(expenseId) {
        if (!confirm('Delete this expense?')) return;
        
        const deleteUrl = API_BASE + '?action=delete-expense&expense_id=' + expenseId;
        const response = await fetch(deleteUrl, { method: 'DELETE', credentials: 'include' });
        const data = await response.json();

        if (data.success) {
            showAlert('Expense deleted', 'success');
            loadExpenses();
        }
    }

    // Financial Report Functions
    async function loadFinancialReport() {
        const startDate = document.getElementById('reportStartDate').value;
        const endDate = document.getElementById('reportEndDate').value;

        const result = await apiCall('financial-summary', 'GET');
        if (result.success && result.data) {
            const data = result.data;
            const html = '<div class="metric-box">' +
                '<div class="metric"><div class="metric-label">Revenue</div><div class="metric-value">₦' + data.revenue.toLocaleString() + '</div></div>' +
                '<div class="metric"><div class="metric-label">Expenses</div><div class="metric-value">₦' + data.expenses.toLocaleString() + '</div></div>' +
                '<div class="metric"><div class="metric-label">Net Profit</div><div class="metric-value">₦' + data.net_profit.toLocaleString() + '</div></div>' +
                '<div class="metric"><div class="metric-label">Pending</div><div class="metric-value">₦' + data.pending_invoices.toLocaleString() + '</div></div>' +
                '</div>';
            document.getElementById('financialReportContent').innerHTML = html;
        }
    }

    function downloadFinancialReport() {
        let content = 'FINANCIAL SUMMARY REPORT\n';
        content += 'Generated: ' + new Date().toLocaleDateString() + '\n\n';
        content += 'Revenue: ₦' + (document.querySelector('[id="metric-value"]').textContent || '0') + '\n';
        downloadFile(content, 'financial_report.txt');
    }

    // Campaign Functions
    async function loadCampaigns() {
        const result = await apiCall('campaigns', 'GET');
        const container = document.getElementById('campaignsTable');
        if (result.success && result.data) {
            container.innerHTML = result.data.map(camp => 
                '<tr><td>' + camp.campaign_name + '</td><td>' + camp.campaign_type + 
                '</td><td>₦' + camp.budget.toLocaleString() + '</td><td>' + new Date(camp.start_date).toLocaleDateString() + ' to ' + new Date(camp.end_date).toLocaleDateString() + 
                '</td><td><span class="badge bg-success">' + camp.status + '</span></td>' +
                '<td><button class="btn btn-sm btn-info" onclick="viewCampaignDetails(' + camp.id + ')">View</button></td></tr>'
            ).join('');
        }
    }

    function openCreateCampaignModal() {
        document.getElementById('campaignStartDate').valueAsDate = new Date();
        new bootstrap.Modal(document.getElementById('createCampaignModal')).show();
    }

    async function createCampaign() {
        const name = document.getElementById('campaignName').value;
        const type = document.getElementById('campaignType').value;
        const budget = document.getElementById('campaignBudget').value;
        const startDate = document.getElementById('campaignStartDate').value;
        const endDate = document.getElementById('campaignEndDate').value;

        if (!name || !budget || !startDate || !endDate) {
            showAlert('All fields required');
            return;
        }

        const result = await apiCall('create-campaign', 'POST', {
            campaign_name: name,
            campaign_type: type,
            budget: parseFloat(budget),
            start_date: startDate,
            end_date: endDate
        });

        if (result.success) {
            showAlert('Campaign created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createCampaignModal')).hide();
            document.getElementById('campaignName').value = '';
            document.getElementById('campaignBudget').value = '';
            loadCampaigns();
        } else {
            showAlert(result.message);
        }
    }

    // Fix the loadCampaignsForLeads function
    async function loadCampaignsForLeads() {
        const result = await apiCall('campaigns-dropdown', 'GET');
        if (result.success && result.data) {
            const select = document.getElementById('leadscampaignSelect');
            select.innerHTML = '<option value="">Choose a campaign...</option>' + 
                result.data.map(c => 
                    '<option value="' + c.id + '">' + c.campaign_name + '</option>'
                ).join('');
        } else {
            showAlert('Failed to load campaigns');
        }
    }

    // Fix the loadLeads function - it was calling wrong endpoint
    async function loadLeads() {
        const campaignId = document.getElementById('leadscampaignSelect').value;
        if (!campaignId) {
            document.getElementById('leadsTable').innerHTML = '<tr><td colspan="6" class="text-center">Select a campaign</td></tr>';
            return;
        }

        const result = await apiCall('campaign-leads', 'GET', { campaign_id: campaignId });
        const container = document.getElementById('leadsTable');
        
        if (result.success && result.data) {
            if (result.data.length === 0) {
                container.innerHTML = '<tr><td colspan="6" class="text-center">No leads found for this campaign</td></tr>';
                return;
            }
            
            container.innerHTML = result.data.map(lead => 
                '<tr><td>' + (lead.lead_name || 'N/A') + '</td>' +
                '<td>' + (lead.lead_email || 'N/A') + '</td>' +
                '<td>' + (lead.lead_phone || 'N/A') + '</td>' +
                '<td><span class="badge ' + getLeadStatusClass(lead.status) + '">' + (lead.status || 'new') + '</span></td>' +
                '<td>' + (lead.conversion_date ? new Date(lead.conversion_date).toLocaleDateString() : '-') + '</td>' +
                '<td>' +
                    '<button class="btn btn-sm btn-success" onclick="convertLead(' + lead.id + ')" ' + 
                    (lead.status === 'converted' ? 'disabled' : '') + '>' +
                    '<i class="fas fa-check"></i> Convert</button>' +
                '</td></tr>'
            ).join('');
        } else {
            container.innerHTML = '<tr><td colspan="6" class="text-center">Error loading leads</td></tr>';
        }
    }
    // Add helper function for lead status badges
    function getLeadStatusClass(status) {
        switch(status) {
            case 'new': return 'bg-info';
            case 'contacted': return 'bg-warning';
            case 'qualified': return 'bg-primary';
            case 'converted': return 'bg-success';
            default: return 'bg-secondary';
        }
    }

    function openAddLeadModal() {
        new bootstrap.Modal(document.getElementById('addLeadModal')).show();
    }

    // Fix the addLead function to include campaign_id
    async function addLead() {
        const campaignId = document.getElementById('leadscampaignSelect').value;
        const name = document.getElementById('leadName').value;
        const email = document.getElementById('leadEmail').value;
        const phone = document.getElementById('leadPhone').value;

        if (!campaignId || !name || !email) {
            showAlert('Campaign, name, and email are required');
            return;
        }

        const result = await apiCall('add-lead', 'POST', {
            campaign_id: parseInt(campaignId),
            lead_name: name,
            lead_email: email,
            lead_phone: phone
        });

        if (result.success) {
            showAlert('Lead added successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addLeadModal')).hide();
            document.getElementById('leadName').value = '';
            document.getElementById('leadEmail').value = '';
            document.getElementById('leadPhone').value = '';
            loadLeads();
        } else {
            showAlert(result.message);
        }
    }

    // Fix the convertLead function to match your database structure
    async function convertLead(leadId) {
        const conversionValue = prompt('Enter conversion value (₦):');
        if (!conversionValue || isNaN(conversionValue) || parseFloat(conversionValue) <= 0) {
            showAlert('Please enter a valid conversion amount');
            return;
        }

        const result = await apiCall('record-conversion', 'POST', {
            lead_id: leadId,
            conversion_value: parseFloat(conversionValue)
        });
        
        if (result.success) {
            showAlert('Lead converted successfully!', 'success');
            // Reload leads for current campaign
            loadLeads();
            // Update analytics if on that section
            if (document.getElementById('analyticsCampaignSelect').value) {
                loadCampaignAnalytics();
            }
        } else {
            showAlert(result.message || 'Failed to convert lead');
        }
    }

    // Fix the loadCampaignsForAnalytics function
    async function loadCampaignsForAnalytics() {
        const result = await apiCall('campaigns-dropdown', 'GET');
        if (result.success && result.data) {
            const select = document.getElementById('analyticsCampaignSelect');
            select.innerHTML = '<option value="">Choose a campaign...</option>' + 
                result.data.map(c => 
                    '<option value="' + c.id + '">' + c.campaign_name + '</option>'
                ).join('');
        } else {
            showAlert('Failed to load campaigns');
        }
    }

    // Fix the loadCampaignAnalytics function
    async function loadCampaignAnalytics() {
        const campaignId = document.getElementById('analyticsCampaignSelect').value;
        if (!campaignId) {
            // Clear analytics display
            document.getElementById('analyticsBudget').textContent = '₦0';
            document.getElementById('analyticsLeads').textContent = '0';
            document.getElementById('analyticsConversions').textContent = '0';
            document.getElementById('analyticsRate').textContent = '0%';
            document.getElementById('analyticsRevenue').textContent = '₦0';
            document.getElementById('analyticsROI').textContent = '0%';
            return;
        }

        const result = await apiCall('campaign-analytics', 'GET', { campaign_id: campaignId });
        if (result.success && result.data) {
            const data = result.data;
            document.getElementById('analyticsBudget').textContent = '₦' + data.budget.toLocaleString();
            document.getElementById('analyticsLeads').textContent = data.total_leads.toLocaleString();
            document.getElementById('analyticsConversions').textContent = data.conversions.toLocaleString();
            document.getElementById('analyticsRate').textContent = data.conversion_rate.toFixed(1) + '%';
            document.getElementById('analyticsRevenue').textContent = '₦' + data.conversion_value.toLocaleString();
            document.getElementById('analyticsROI').textContent = data.roi.toFixed(1) + '%';
            
            // Optional: Show campaign name in a header
            const analyticsSection = document.getElementById('analytics');
            const existingHeader = analyticsSection.querySelector('.campaign-analytics-header');
            if (existingHeader) existingHeader.remove();
            
            const header = document.createElement('h4');
            header.className = 'campaign-analytics-header mt-3';
            header.style.color = 'var(--primary)';
            header.innerHTML = '<i class="fas fa-chart-line"></i> ' + data.campaign_name + 
                            ' <small class="text-muted">(' + (data.period || '') + ')</small>';
            document.querySelector('#analytics .metric-box').parentNode.insertBefore(header, document.querySelector('#analytics .metric-box'));
        } else {
            showAlert(result.message || 'Failed to load analytics');
        }
    }

    function downloadStatement() {
        let content = 'FINANCIAL STATEMENT\n\n';
        content += 'Date: ' + new Date().toLocaleDateString() + '\n';
        content += 'Student: ' + currentUser.full_name + '\n\n';
        downloadFile(content, 'statement.txt');
    }

    function downloadFile(content, filename) {
        const element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
        element.setAttribute('download', filename);
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
</script>