<?php
// FILE: /SMS/index.php
// Login and Registration page only

session_start();

// If user already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
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

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
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

        .login-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <h1>ERP System</h1>
                <p>Academic | Finance | Human Resources</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Tabs Navigation -->
                <div class="nav-tabs" role="tablist">
                    <button class="nav-link active" id="loginTab" onclick="switchTab('login')" type="button" role="tab">Login</button>
                    <button class="nav-link" id="registerTab" onclick="switchTab('register')" type="button" role="tab">Register</button>
                </div>

                <!-- Login Tab -->
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

                <!-- Register Tab -->
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
                                <option value="lecturer">Lecturer</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-login">Create Account</button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                <p>© 2025 ERP System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/SMS/api/auth.php';

        function switchTab(tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            const navLinks = document.querySelectorAll('.nav-link');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            navLinks.forEach(link => link.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');
        }

        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            alertContainer.innerHTML = '<div class="alert ' + alertClass + '">' + message + '</div>';
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        async function handleLogin(event) {
            event.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const btn = event.target.querySelector('button');
            const btnText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span id="loginBtnText">Logging in...</span>';

            try {
                const response = await fetch(API_BASE + '?action=login', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/SMS/dashboard.php';
                    }, 1000);
                } else {
                    showAlert(result.message || 'Login failed');
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = btnText;
            }
        }

        async function handleRegister(event) {
            event.preventDefault();
            const full_name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const role = document.getElementById('registerRole').value;
            const btn = event.target.querySelector('button');
            const btnText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = 'Creating account...';

            try {
                const response = await fetch(API_BASE + '?action=register', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ full_name, email, password, role })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Registration successful! Please login.', 'success');
                    document.getElementById('registerForm').reset();
                    setTimeout(() => {
                        switchTab('login');
                    }, 1500);
                } else {
                    showAlert(result.message || 'Registration failed');
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = btnText;
            }
        }
    </script>
</body>
</html>