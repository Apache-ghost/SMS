<?php
// Set proper headers for HTML without restrictive CSP
header('Content-Type: text/html; charset=utf-8');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1f2937;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .btn-register:hover {
            background: var(--primary);
            color: white;
        }

        .login-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 13px;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .spinner-border {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }

        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            border: none;
        }

        .dashboard-nav {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-content {
            padding: 30px;
            background: #f9fafb;
            min-height: 100vh;
        }

        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .module-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 5px solid var(--primary);
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .module-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .module-card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        .nav-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-link {
            padding: 10px 15px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Login View -->
    <div id="loginView" class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>ERP System</h1>
                <p>Academic | Finance | Human Resources</p>
            </div>
            <div class="login-body">
                <div id="alertContainer"></div>
                <div class="nav-tabs" role="tablist">
                    <button class="nav-link active" id="loginTab" onclick="switchTab('login')" type="button" role="tab">Login</button>
                    <button class="nav-link" id="registerTab" onclick="switchTab('register')" type="button" role="tab">Register</button>
                </div>
                <div class="tab-content active" id="login" role="tabpanel">
                    <form id="loginForm" onsubmit="handleLogin(event)">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <input type="email" id="loginEmail" class="form-control" placeholder="your@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <input type="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn-login">
                            <span id="loginBtnText">Login</span>
                        </button>
                    </form>
                </div>
                <div class="tab-content" id="register" role="tabpanel">
                    <form id="registerForm" onsubmit="handleRegister(event)">
                        <div class="form-group">
                            <label for="registerName">Full Name</label>
                            <input type="text" id="registerName" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="form-group">
                            <label for="registerEmail">Email Address</label>
                            <input type="email" id="registerEmail" class="form-control" placeholder="your@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="registerPassword">Password</label>
                            <input type="password" id="registerPassword" class="form-control" placeholder="Min 8 characters" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label for="registerRole">User Role</label>
                            <select id="registerRole" class="form-control" required>
                                <option value="">Select a role</option>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-login">Create Account</button>
                    </form>
                </div>
            </div>
            <div class="login-footer">
                <p>© 2025 ERP System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Dashboard View -->
    <div id="dashboardView" style="display: none;">
        <div class="dashboard-nav">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin: 0; color: #1f2937;">ERP System Dashboard</h2>
                </div>
                <div>
                    <span id="userGreeting" style="color: #6b7280; font-size: 14px;"></span>
                    <button onclick="handleLogout()" class="btn btn-outline-danger btn-sm" style="margin-left: 15px;">Logout</button>
                </div>
            </div>
        </div>
        <div class="dashboard-content">
            <div class="welcome-card">
                <h3>Welcome to ERP System</h3>
                <p id="welcomeMessage" style="color: #6b7280; margin: 0;"></p>
            </div>

            <div class="module-grid">
                <div class="module-card" onclick="navigateToModule('academic')">
                    <h3>📚 Academic Module</h3>
                    <p>Manage courses, enrollments, academic programs, and student records.</p>
                </div>
                <div class="module-card" onclick="navigateToModule('finance')">
                    <h3>💰 Finance & Marketing</h3>
                    <p>Handle financial transactions, budgets, and marketing campaigns.</p>
                </div>
                <div class="module-card" onclick="navigateToModule('hr')">
                    <h3>👥 Administration & HR</h3>
                    <p>Manage employees, leave requests, and administrative tasks.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // API endpoint
        const API_BASE = '/SMS/api/auth.php';

        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            alertContainer.innerHTML = '<div class="alert ' + alertClass + '">' + message + '</div>';
            setTimeout(function() {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function switchTab(tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            const navLinks = document.querySelectorAll('.nav-link');
            
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            navLinks.forEach(function(link) {
                link.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');
        }

        async function apiCall(action, method, data) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include'
                };

                if (data) {
                    options.body = JSON.stringify(data);
                }

                const url = API_BASE + '?action=' + action;
                const response = await fetch(url, options);
                
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }

                const text = await response.text();
                if (!text) {
                    throw new Error('Empty response from server');
                }

                return JSON.parse(text);
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, message: 'API Error: ' + error.message };
            }
        }

        async function handleLogin(event) {
            event.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const btn = event.target.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border"></span>Logging in...';

            const result = await apiCall('login', 'POST', { email: email, password: password });

            if (result.success) {
                showAlert('Login successful! Redirecting...', 'success');
                setTimeout(function() {
                    loadDashboard();
                }, 1000);
            } else {
                showAlert(result.message || 'Login failed');
                btn.disabled = false;
                btn.innerHTML = 'Login';
            }
        }

        async function handleRegister(event) {
            event.preventDefault();
            const full_name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const role = document.getElementById('registerRole').value;
            const btn = event.target.querySelector('button');
            btn.disabled = true;

            const result = await apiCall('register', 'POST', { 
                full_name: full_name, 
                email: email, 
                password: password, 
                role: role 
            });

            if (result.success) {
                showAlert('Registration successful! Please login.', 'success');
                document.getElementById('registerForm').reset();
                setTimeout(function() {
                    switchTab('login');
                }, 1500);
            } else {
                showAlert(result.message || 'Registration failed');
                btn.disabled = false;
            }
        }

        async function loadDashboard() {
            const result = await apiCall('current-user', 'GET', null);

            if (result.success) {
                const user = result.user;
                document.getElementById('loginView').style.display = 'none';
                document.getElementById('dashboardView').style.display = 'block';
                document.getElementById('userGreeting').textContent = 'Welcome, ' + user.full_name + '!';
                document.getElementById('welcomeMessage').textContent = 'You are logged in as a ' + user.role + '. Access the modules below to manage your operations.';
            } else {
                console.log('Not authenticated');
            }
        }

        async function handleLogout() {
            await apiCall('logout', 'POST', null);
            document.getElementById('dashboardView').style.display = 'none';
            document.getElementById('loginView').style.display = 'flex';
            document.getElementById('loginForm').reset();
            document.getElementById('registerForm').reset();
            showAlert('Logged out successfully', 'success');
        }

        function navigateToModule(module) {
            
            if (module === 'academic') {
                window.location.href = 'academic.html';
            } else if (module === 'finance') {
                window.location.href = 'finance.php';
            } else if (module === 'hr') {
                window.location.href = 'hr_dashboard.html';
            }
        }

        // Check if already logged in on page load
        window.addEventListener('load', loadDashboard);
    </script>
</body>
</html>