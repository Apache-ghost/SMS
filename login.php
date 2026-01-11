<?php
error_reporting(0);
session_start();

if (isset($_SESSION['uid'])) {
  $uid = $_SESSION['uid'];
  include('assets/config.php');

  $query = "SELECT `role` FROM `users` WHERE `users`.`id`=?";
  $stmt = mysqli_prepare($conn, $query);

  mysqli_stmt_bind_param($stmt, "s", $uid);
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_array($result);

  mysqli_stmt_close($stmt);

  if ($row && isset($row['role'])) {
    if ($row['role'] == "admin") {
      header('Location: admin_panel/dashboard.php');
      exit();
    } else if ($row['role'] == "owner") {
      header('Location: owner_panel/index.php');
      exit();
    } else if ($row['role'] == "teacher") {
      header('Location: teacher_panel/dashboard.php');
      exit();
    } else if ($row['role'] == "student") {
      header('Location: student_panel/index.php');
      exit();
    }
  }
}

// Check if parent is logged in
if (isset($_SESSION['parent_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'parent') {
  header('Location: parent_panel/dashboard.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <title>School Management</title>
  <!-- Fontawesome CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="css/decorative-elements.css">
  <link rel="stylesheet" href="css/text-enhancements.css">
  <link rel="stylesheet" href="login-form-style.css">
  <link rel="icon" type="image/x-icon" href="images/1.png">
</head>

<body>
  <!-- Premium Background Elements -->
  <div class="floating-bubbles">
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
  </div>
  
  <div class="gradient-orb gradient-orb-1"></div>
  <div class="gradient-orb gradient-orb-2"></div>
  <div class="gradient-orb gradient-orb-3"></div>
  
  <div class="container">
    <input type="checkbox" id="flip">
    <div class="cover">
      <div class="front">
        <img src="images/loginimage.jpg" alt="">
        <div class="text">
          <span class="text-1">SCHOOL MANAGEMENT SYSTEM<br></span>
          <span class="text-2">Plan serve program</span>
        </div>
      </div>

    </div>
    <div class="forms">
      <div class="form-content">
        <div class="login-form">

          <div class="title" id='board-title'>Login</div>

          <div class="alert-box">
            <div class="alert alert-danger text-center mt-3" role="alert" id="error-msg">

            </div>
          </div>

          <form action="index.php" id="login-form" method="post">
            <div class="input-boxes">
              <div class="input-box">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" id='loginEmail' required>
              </div>
              <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter your password" id="password" required>
                <i class="bi bi-eye-fill" style="margin-left:auto;margin-right: 6px;" id="togglePassword"></i>
              </div>
              <div class="text"><a id="forgotpassword">Forgot password?</a></div>
              <div class="button input-box">
                <button type="submit" class="btn">
                  Submit
                </button>
              </div>
              <div class="text signup-link" style="margin-top: 15px; text-align: center;">
                Don't have an account? <a href="javascript:void(0);" id="showSignup" onclick="showStudentSignup()" style="font-weight: 600; color: #667eea; cursor: pointer;">Sign up as Student</a>
              </div>
            </div>
          </form>


          <!-- Student Signup Form -->
          <form action="index.php" id="signup-form" method="post" style="display:none;">

            <div class="input-boxes">
              <div class="input-box">
                <i class="fas fa-user"></i>
                <input type="text" name="fname" placeholder="Full Name" id='signupFullName' required>
              </div>
              
              <div class="input-box">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" id='signupEmail' required>
              </div>
              
              <div class="input-box">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" placeholder="Phone Number (at least 9 digits)" id='signupPhone' pattern="[0-9]{9,}" required>
              </div>
              
              <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Create Password (min 6 characters)" id="signupPassword" required>
                <i class="bi bi-eye-fill" style="margin-left:auto;margin-right: 6px;" id="toggleSignupPassword"></i>
              </div>
              
              <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" id="signupConfirmPassword" required>
              </div>

              <div class="invalid-feedback" id='signupPasswordMismatch' style="display: none; color: #dc3545; font-size: 13px; margin-top: -10px; margin-bottom: 10px;">
                Passwords do not match!
              </div>

              <div class="text" style="margin-bottom: 20px;display:flex">
                <a href="javascript:void(0);" id="backToLoginFromSignup" onclick="backToLoginFromSignupForm()" style="cursor: pointer;">Already have an account? Login</a>
              </div>

              <div class="button input-box">
                <button type="submit" id='signupBtn'>
                  Create Account
                </button>
              </div>

            </div>
          </form>


          <!-- forgot password gui -->
          <form action="index.php" id="forgotPassword-form" method="post" style="display:none;">

            <div class="input-boxes">
              <div class="input-box">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="forgotEmail" placeholder="Enter your email" required>
              </div>

              <div class="text" style="margin-bottom: 20px;display:flex">
                <a id="backToLogin">back to login?</a>
              </div>

              <div class="button input-box">
                <button type="submit" id='sendCodeBtn'>
                  Send Code
                </button>
              </div>

            </div>
          </form>

          <form id="otpVarification-form" method="post" style="display:none;">

            <div class="input-boxes">
              <div class="input-box">
                <i class="fas fa-envelope"></i>
                <input type="text" name="email" value="some value" id="otpDisabledEmail">
              </div>

              <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="text" name="otp" placeholder="Enter code" id="otpCode" required>
              </div>

              <div class="text" style="margin-bottom: 20px;display:flex">
                <a id="backToforgotPasswordForm">back</a>
                <a id="resendOTP" style='margin-left: auto;'>resend OTP?</a>
              </div>

              <div class="button input-box">
                <button type="submit" id='verifyCodeBtn'>
                  Verify Code
                </button>
              </div>

            </div>
          </form>


          <form id="createNewPassword-form" method="post" style="display:none;">

            <div class="input-boxes">
              <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="newpassword" id='newpassword' placeholder='Enter new password' required>
              </div>

              <div class="invalid-feedback" id='weakPasswordFeedback'></div>

              <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirmpassword" id='confirmpassword' placeholder='Confirm password' required>
              </div>

              <div class="invalid-feedback" id='passwordNotSame'>
                New password and confirm password are not same!
              </div>

              <div class="form-check mt-3 ">
                <input class="form-check-input" type="checkbox" value="" id="showPasswords">
                <label class="form-check-label" for="showPasswords" id='showPasswordLabel'>
                  Show password
                </label>
              </div>

              <div class="button input-box">
                <button type="submit" id='changePasswordBtn'>
                  Change password
                </button>
              </div>

            </div>
          </form>

          <!-- end of forgot password gui -->


        </div>

      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="index.js"></script>
  
  <script>
    // Simple inline function to show signup form - guaranteed to work
    function showStudentSignup() {
      console.log('showStudentSignup called');
      
      // Hide login form
      var loginForm = document.getElementById('login-form');
      if (loginForm) loginForm.style.display = 'none';
      
      // Hide other forms
      var forgotForm = document.getElementById('forgotPassword-form');
      if (forgotForm) forgotForm.style.display = 'none';
      
      var otpForm = document.getElementById('otpVarification-form');
      if (otpForm) otpForm.style.display = 'none';
      
      var newPwdForm = document.getElementById('createNewPassword-form');
      if (newPwdForm) newPwdForm.style.display = 'none';
      
      // Show signup form
      var signupForm = document.getElementById('signup-form');
      if (signupForm) {
        signupForm.style.display = 'block';
        console.log('Signup form displayed');
      } else {
        console.error('signup-form not found!');
      }
      
      // Update title
      var title = document.getElementById('board-title');
      if (title) title.innerHTML = 'Student Sign Up';
      
      // Hide alert box
      var alertBox = document.querySelector('.alert-box');
      if (alertBox) alertBox.style.display = 'none';
    }
    
    // Back to login function
    function backToLoginFromSignupForm() {
      var signupForm = document.getElementById('signup-form');
      if (signupForm) {
        signupForm.style.display = 'none';
        signupForm.reset();
      }
      
      var loginForm = document.getElementById('login-form');
      if (loginForm) loginForm.style.display = 'block';
      
      var title = document.getElementById('board-title');
      if (title) title.innerHTML = 'Login';
    }
  </script>


</body>

</html>