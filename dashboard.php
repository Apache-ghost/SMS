<?php
// FILE: /SMS/dashboard.php
// Main dashboard that ALL logged-in users see after login

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'student';
$user_email = $_SESSION['email'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f9fafb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px 30px;
            margin-bottom: 30px;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .user-role {
            font-size: 12px;
            color: #9ca3af;
            margin: 0;
        }

        .btn-logout {
            background: var(--danger);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .main-container {
            padding: 0 30px 30px 30px;
        }

        .welcome-section {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .welcome-section h1 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 700;
        }

        .welcome-section p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .module-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .module-header {
            padding: 30px;
            color: white;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .module-header i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .module-header h3 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .module-body {
            padding: 25px;
            text-align: center;
        }

        .module-body p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .btn-module {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            display: inline-block;
        }

        .btn-module:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
            color: white;
        }

        /* Academic Module Color */
        .module-card.academic .module-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .module-card.academic .btn-module {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        /* Finance Module Color */
        .module-card.finance .module-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .module-card.finance .btn-module {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* HR Module Color */
        .module-card.hr .module-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .module-card.hr .btn-module {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        /* Admin Controls Section */
        .admin-section {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-top: 4px solid var(--danger);
        }

        .admin-section h2 {
            color: #1f2937;
            margin-bottom: 30px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .admin-btn {
            background: white;
            border: 2px solid #e5e7eb;
            padding: 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: #6b7280;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .admin-btn:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: #fef2f2;
            transform: translateY(-5px);
        }

        .admin-btn i {
            font-size: 32px;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 15px;
            }

            .main-container {
                padding: 0 15px 15px 15px;
            }

            .welcome-section {
                padding: 25px;
            }

            .welcome-section h1 {
                font-size: 24px;
            }

            .modules-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .admin-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="navbar-content">
            <h1 class="navbar-brand">
                <i class="fas fa-graduation-cap"></i> ERP System
            </h1>
            <div class="user-section">
                <div class="user-info">
                    <p class="user-name"><?php echo htmlspecialchars($user_full_name); ?></p>
                    <p class="user-role"><?php echo ucfirst($user_role); ?></p>
                </div>
                <button class="btn-logout" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($user_full_name); ?>!</h1>
            <p>
                <?php 
                if ($user_role === 'admin') {
                    echo 'You have full access to all modules. Select a module below or use the Administrative Controls section to manage the system.';
                } else {
                    echo 'Select a module below to access the Academic, Finance, or HR & Administration systems.';
                }
                ?>
            </p>
        </div>

        <!-- Modules Grid -->
        <div class="modules-grid">
            <!-- Academic Module -->
            <div class="module-card academic">
                <div class="module-header">
                    <i class="fas fa-book"></i>
                    <h3>📚 Academic Module</h3>
                </div>
                <div class="module-body">
                    <p>Manage courses, enrollments, grades, and academic programs</p>
                    <a href="academic.html" class="btn-module">
                        Access Module <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Finance Module -->
            <div class="module-card finance">
                <div class="module-header">
                    <i class="fas fa-money-bill"></i>
                    <h3>💰 Finance & Marketing</h3>
                </div>
                <div class="module-body">
                    <p>Handle invoices, payments, expenses, and marketing campaigns</p>
                    <a href="finance.php" class="btn-module">
                        Access Module <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- HR Module -->
            <div class="module-card hr">
                <div class="module-header">
                    <i class="fas fa-users"></i>
                    <h3>👥 HR & Administration</h3>
                </div>
                <div class="module-body">
                    <p>Manage employees, payroll, leaves, and assets</p>
                    <a href="hr_dashboard.html" class="btn-module">
                        Access Module <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Admin Controls (Only for Admins) -->
        <?php if ($user_role === 'admin'): ?>
        <div class="admin-section">
            <h2>
                <i class="fas fa-shield-alt"></i> Administrative Controls
            </h2>
            <div class="admin-grid">
                <a href="admin/dashboard.php" class="admin-btn">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
                <a href="admin/dashboard.php?section=reports" class="admin-btn">
                    <i class="fas fa-chart-bar"></i>
                    System Reports
                </a>
                <a href="admin/dashboard.php?section=settings" class="admin-btn">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                <a href="admin/dashboard.php?section=logs" class="admin-btn">
                    <i class="fas fa-history"></i>
                    Audit Logs
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        async function handleLogout() {
            try {
                const response = await fetch('/SMS/api/auth.php?action=logout', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                if (response.ok) {
                    window.location.href = '/SMS/index.php';
                }
            } catch (error) {
                console.error('Error:', error);
                // Fallback logout
                window.location.href = '/SMS/index.php';
            }
        }
    </script>
</body>
</html>