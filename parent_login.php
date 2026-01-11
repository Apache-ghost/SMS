<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal Login - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/animations.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Decorative Elements */
        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float-up 15s infinite;
        }

        .bubble:nth-child(1) { left: 10%; width: 80px; height: 80px; animation-delay: 0s; }
        .bubble:nth-child(2) { left: 30%; width: 60px; height: 60px; animation-delay: 2s; }
        .bubble:nth-child(3) { left: 50%; width: 100px; height: 100px; animation-delay: 4s; }
        .bubble:nth-child(4) { left: 70%; width: 70px; height: 70px; animation-delay: 6s; }
        .bubble:nth-child(5) { left: 85%; width: 90px; height: 90px; animation-delay: 8s; }

        @keyframes float-up {
            0% {
                bottom: -100px;
                opacity: 0;
                transform: translateX(0) rotate(0deg);
            }
            50% {
                opacity: 0.3;
            }
            100% {
                bottom: 100vh;
                opacity: 0;
                transform: translateX(100px) rotate(360deg);
            }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            animation: slideInUp 0.6s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s infinite;
        }

        .login-header .icon i {
            font-size: 40px;
            color: white;
        }

        .login-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating input {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-floating label {
            padding: 12px 15px;
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e0e0e0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .login-footer a:hover {
            color: #764ba2;
        }

        .alert {
            border-radius: 10px;
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .role-badge {
            display: inline-block;
            padding: 5px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Floating Bubbles -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="icon">
                    <i class='bx bxs-user-circle'></i>
                </div>
                <span class="role-badge">PARENT PORTAL</span>
                <h2>Welcome Back!</h2>
                <p>Access your child's information and school updates</p>
            </div>

            <?php
            session_start();
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle"></i> ' . $_SESSION['error_message'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
                unset($_SESSION['error_message']);
            }
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bx bx-check-circle"></i> ' . $_SESSION['success_message'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
                unset($_SESSION['success_message']);
            }
            ?>

            <form action="assets/parentPortalHandler.php" method="POST" id="loginForm">
                <input type="hidden" name="action" value="login">
                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="name@example.com" required value="parent@gmail.com">
                    <label for="email"><i class='bx bx-envelope'></i> Email Address</label>
                </div>

                <div class="input-group mb-3">
                    <div class="form-floating flex-grow-1">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required value="123" style="border-right: none;">
                        <label for="password"><i class='bx bx-lock-alt'></i> Password</label>
                    </div>
                    <span class="input-group-text" onclick="togglePassword()">
                        <i class='bx bx-show' id="toggleIcon"></i>
                    </span>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class='bx bx-log-in'></i> Login to Portal
                </button>
            </form>

            <div class="login-footer">
                <p class="mb-2"><a href="forgotPassword.php">Forgot Password?</a></p>
                <p class="text-muted">Login as: 
                    <a href="login.php">Student</a> | 
                    <a href="login.php">Teacher</a> | 
                    <a href="login.php">Admin</a>
                </p>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="text-white text-decoration-none">
                <i class='bx bx-home'></i> Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                toggleIcon.classList.remove('bx-show');
                toggleIcon.classList.add('bx-hide');
            } else {
                password.type = 'password';
                toggleIcon.classList.remove('bx-hide');
                toggleIcon.classList.add('bx-show');
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
