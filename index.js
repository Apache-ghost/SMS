
let usersEmail = '';


const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#password");

togglePassword.addEventListener("click", function () {
    // toggle the type attribute
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    
    // toggle the icon
    this.classList.toggle("bi-eye-slash-fill");
});

document.getElementById('login-form').addEventListener('submit', submitForm);

var errorbox = document.querySelector('.alert-box');
var error_msg = document.getElementById("error-msg");
var weakPswrd = document.getElementById('weakPasswordFeedback');
var notMatch = document.getElementById('passwordNotSame');


document.getElementById('loginEmail').addEventListener('keyup', ()=>{
    errorbox.style.display = 'none';
    error_msg.innerHTML = '';
});
document.getElementById('password').addEventListener('keyup', ()=>{
    errorbox.style.display = 'none';
    error_msg.innerHTML = '';
});
document.getElementById('forgotEmail').addEventListener('keyup', ()=>{
    errorbox.style.display = 'none';
    error_msg.innerHTML = '';
});
document.getElementById('otpCode').addEventListener('keyup', ()=>{
    errorbox.style.display = 'none';
    error_msg.innerHTML = '';
});
document.getElementById('newpassword').addEventListener('keyup', ()=>{
    errorbox.style.display = 'none';
    error_msg.innerHTML = '';
    weakPswrd.style.display = 'none';
    notMatch.style.display = 'none';
});
document.getElementById('confirmpassword').addEventListener('keyup', ()=>{
    errorbox.style.display = 'none';
    error_msg.innerHTML = '';
    weakPswrd.style.display = 'none';
    notMatch.style.display = 'none';
});


function submitForm(event) {
    event.preventDefault();

    var formData = new FormData(event.target);

    fetch('login-backend.php', {
        method: 'POST',
        body: formData
    })
    .then(response =>{
        if (!response.ok) {
            if (response.status === 500) {                
                window.location.href = './errors/internal_server_error.html';
            } 
            throw new Error('HTTP error');
        }    
        return response.json();
    })
    .then(data => {
        if(data.status === 'NO_CONNECTION'){
            window.location.href = '../errors/error.html';
            return;
        }
    
        error_msg.classList.remove('alert-danger', 'alert-success');
        error_msg.classList.add(data.status === "success" ? 'alert-success' : 'alert-danger');
        error_msg.innerHTML = data.status === "success" ? 'success' : '' + data.message;
    
        errorbox.style.display = 'block';
    
        if (data.role === "admin") {
            window.location.href = 'admin_panel/dashboard.php';
        } else if (data.role === "owner") {
            window.location.href = 'owner_panel/index.php';
        } else if (data.role === "teacher") {
            window.location.href = 'teacher_panel/dashboard.php';
        } else if (data.role === "student") {
            window.location.href = 'student_panel/index.php';
        } else if (data.role === "parent") {
            window.location.href = 'parent_panel/dashboard.php';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorbox.style.display = 'block';
        error_msg.classList.remove('alert-success');
        error_msg.classList.add('alert-danger');
        error_msg.innerHTML = 'An error occurred. Please try again.';
    });
}

document.getElementById('forgotpassword').addEventListener('click', function(){
    hideLoginForm(true);
    hideVerifyOtpForm(true);
    hideforgotPasswordForm(false);
});


document.getElementById('backToLogin').addEventListener('click', function(){
    hideforgotPasswordForm(true);
    hideVerifyOtpForm(true);
    hideLoginForm(false);
});

document.getElementById('backToforgotPasswordForm').addEventListener('click', ()=>{
    hideLoginForm(true);
    hideVerifyOtpForm(true);
    hideforgotPasswordForm(false);
});



function hideLoginForm(hide){
    document.querySelector('.alert-box').style.display = 'none';

    document.getElementById('login-form').reset();
    document.getElementById('forgotPassword-form').reset();
    document.getElementById('otpVarification-form').reset();
    document.getElementById('createNewPassword-form').reset();
    if(document.getElementById('signup-form')){
        document.getElementById('signup-form').reset();
    }
    if(hide){
        document.getElementById('login-form').style.display = 'none';
    }else{
        document.getElementById('login-form').style.display = 'block';
        document.getElementById('board-title').innerHTML = 'Login';
        // Hide signup form when showing login
        if(document.getElementById('signup-form')){
            document.getElementById('signup-form').style.display = 'none';
        }
    }
}
function hideforgotPasswordForm(hide){

    document.getElementById('login-form').reset();
    document.getElementById('otpVarification-form').reset();
    document.getElementById('createNewPassword-form').reset();

    document.querySelector('.alert-box').style.display = 'none';
    if(hide){
        document.getElementById('forgotPassword-form').style.display = 'none';
    }else{
        document.getElementById('forgotPassword-form').style.display = 'block';
        document.getElementById('board-title').innerHTML = 'Forgot Password';
    }
}
function hideVerifyOtpForm(hide){

    document.getElementById('login-form').reset();
    document.getElementById('otpVarification-form').reset();
    document.getElementById('createNewPassword-form').reset();

    document.querySelector('.alert-box').style.display = 'none';
    if(hide){
        document.getElementById('otpVarification-form').style.display = 'none';
    }else{
        document.getElementById('otpVarification-form').style.display = 'block';
        document.getElementById('board-title').innerHTML = 'OTP Verfication';
    }
}

function hideCreateNewPasswordForm(hide){
    document.getElementById('login-form').reset();
    document.getElementById('otpVarification-form').reset();
    document.getElementById('forgotPassword-form').reset();

    document.querySelector('.alert-box').style.display = 'none';
    if(hide){
        document.getElementById('createNewPassword-form').style.display = 'none';
    }else{
        document.getElementById('createNewPassword-form').style.display = 'block';
        document.getElementById('board-title').innerHTML = 'Create New Password';
    }
}

document.getElementById('forgotPassword-form').addEventListener('submit', (event)=>{
    event.preventDefault();
    
    let email = document.getElementById('forgotEmail').value;

    document.querySelector('.alert-box').style.display = 'none';
    document.getElementById('forgotEmail').disabled = true;
    document.getElementById('backToLogin').style.display = 'none';
    document.getElementById('sendCodeBtn').disabled = true;
    document.getElementById('sendCodeBtn').innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span role="status">&nbsp;Processing...</span>';
    
    let sendData = new FormData();
    sendData.append('email', email);
    
    fetch("forgotPassword.php", {
        method: 'POST',
        body: sendData,
    })
    .then(response => response.json())
    .then(data => {
        
        document.getElementById('backToLogin').style.display = 'block';
        document.getElementById('forgotEmail').disabled = false;
        document.getElementById('sendCodeBtn').disabled = false;
        document.getElementById('sendCodeBtn').innerHTML = 'Send Code';

        

        if(data['status'] === 'success'){
            hideLoginForm(true);
            hideCreateNewPasswordForm(true);
            hideforgotPasswordForm(true);
            
            hideVerifyOtpForm(false);
            document.getElementById('otpDisabledEmail').value = data['email'] + '';
            document.getElementById('otpDisabledEmail').disabled = true;
            usersEmail = data['email'];
        }else{
            document.getElementById("error-msg").innerHTML = data['message'];
            document.querySelector('.alert-box').style.display = 'block';
        }

        })
        .catch(error => {
            console.error("Error:", error);
        });

    
});

document.getElementById("otpVarification-form").addEventListener('submit', (event)=>{
    event.preventDefault();

    document.querySelector('.alert-box').style.display = 'none';
    document.getElementById('verifyCodeBtn').innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span role="status">&nbsp;Processing...</span>';

    document.getElementById('backToforgotPasswordForm').style.display = 'none';
    document.getElementById('resendOTP').style.display = 'none';
    document.getElementById('otpCode').disabled = true;
    document.getElementById('verifyCodeBtn').disabled = true;

    let email = document.getElementById('otpDisabledEmail').value;
    let otp = document.getElementById('otpCode').value;



    let sendData = new FormData();
    sendData.append('email', email);
    sendData.append('otp', otp);

    fetch("forgotPassword.php", {
        method: 'POST',
        body: sendData,
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('verifyCodeBtn').innerHTML = "Verify Code";
        
        document.getElementById('backToforgotPasswordForm').style.display = 'block';
        document.getElementById('resendOTP').style.display = 'block';
        document.getElementById('otpCode').disabled = false;
        document.getElementById('verifyCodeBtn').disabled = false;

        if(data['status'] === 'success'){
           hideLoginForm(true);
           hideVerifyOtpForm(true);
           hideforgotPasswordForm(true);


           hideCreateNewPasswordForm(false);
        }else{
            document.getElementById("error-msg").innerHTML = data['message'];
            document.querySelector('.alert-box').style.display = 'block';
        }

        })
        .catch(error => {
            console.error("Error:", error);
        });

});

document.getElementById('resendOTP').addEventListener('click', function(){
    document.querySelector('.alert-box').style.display = 'none';
    document.getElementById('verifyCodeBtn').innerHTML = '<div class="spinner-border  spinner-border-sm" role="status"><span class="visually-hidden"></span></div>&nbsp;Sending...';

    document.getElementById('backToforgotPasswordForm').style.display = 'none';
    document.getElementById('resendOTP').style.display = 'none';
    document.getElementById('otpCode').disabled = true;
    document.getElementById('verifyCodeBtn').disabled = true;

    let email = document.getElementById('otpDisabledEmail').value;
    
    let sendData = new FormData();
    sendData.append('email', email);

    fetch("forgotPassword.php", {
        method: 'POST',
        body: sendData,
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('verifyCodeBtn').innerHTML = 'Verify Code';

        document.getElementById('resendOTP').innerHTML = 'OTP sended';
        setTimeout(()=>{
            document.getElementById('resendOTP').innerHTML = 'resend OTP?';
        }, 2000);
        
        document.getElementById('backToforgotPasswordForm').style.display = 'block';
        document.getElementById('resendOTP').style.display = 'block';
        document.getElementById('otpCode').disabled = false;
        document.getElementById('verifyCodeBtn').disabled = false;


        if(data['status'] === 'success'){
            hideLoginForm(true);
            hideCreateNewPasswordForm(true);
            hideforgotPasswordForm(true);
            
            hideVerifyOtpForm(false);
            document.getElementById('otpDisabledEmail').value = data['email'] + '';
            document.getElementById('otpDisabledEmail').disabled = true;
            usersEmail = data['email'];
        }else{
            document.getElementById("error-msg").innerHTML = data['message'];
            document.querySelector('.alert-box').style.display = 'block';
        }

        })
        .catch(error => {
            console.error("Error:", error);
        });
});

document.getElementById('createNewPassword-form').addEventListener('submit', (event)=>{
    event.preventDefault();


    var errorBox = document.querySelector('.alert-box');
    var error_message = document.getElementById("error-msg");

    if(isStrongPassword()){
        let newPassword = document.getElementById('newpassword').value;
        let confirmPassword = document.getElementById('confirmpassword').value;

        if(newPassword === confirmPassword){
            document.getElementById('weakPasswordFeedback').style.display = 'none';
            document.getElementById('passwordNotSame').style.display = 'none';

            document.getElementById('newpassword').disabled = true;
            document.getElementById('confirmpassword').disabled = true;
            document.getElementById('changePasswordBtn').disabled = true;
            
            let sendData = new FormData();
            sendData.append('email', usersEmail);
            sendData.append('password', newPassword);
        
            fetch("forgotPassword.php", {
                method: 'POST',
                body: sendData,
            })
            .then(response => response.json())
            .then(data => {
              
                
        
                if(data['status'] === 'update_success'){
                    error_message.classList.remove('alert-danger', 'alert-success');
                    error_message.classList.add('alert-success');
                    error_message.innerHTML = data['message'];
                    errorBox.style.display = 'block';
                   
                   
                   setTimeout(() => {
                    error_message.classList.remove('alert-danger', 'alert-success');
                    error_message.classList.add('alert-danger');
                    error_message.innerHTML = '';
                    errorBox.style.display = 'none';

                    window.location.href = './';
                   }, 1000);
                  
                   
                }else{

                    document.getElementById('newpassword').disabled = false;
                    document.getElementById('confirmpassword').disabled = false;
                    document.getElementById('changePasswordBtn').disabled = false;

                    error_message.classList.remove('alert-danger', 'alert-success');
                    error_message.classList.add('alert-danger');
                    document.getElementById("error-msg").innerHTML = data['message'];
                    errorBox.style.display = 'block';
                }
        
                })
                .catch(error => {
                    console.error("Error:", error);
                });
            

        }else{
            document.getElementById('weakPasswordFeedback').style.display = 'none';
            document.getElementById('passwordNotSame').style.display = 'block';
        }
    }

    let newPassword = document.getElementById('newpassword').value;
    let confirmPassword = document.getElementById('confirmpassword').value;

    let sendData = new FormData();
    sendData.append("newpassword", newPassword);
    sendData.append("confirmpassword", confirmPassword);


});



function isStrongPassword() {
    document.getElementById('passwordNotSame').style.display = 'none';

      var password = document.getElementById('newpassword').value;
      var weakBadge = document.getElementById('weakPasswordFeedback');

      var minLength = 8;

      // Check password length
      if (!(password.length >= minLength)) {
          weakBadge.innerHTML = "Password must contain minimum 8 characters!";
          weakBadge.style.display = 'block';
          return false;
      }
      else if (!(password.match(/([A-Z])/))) {
        weakBadge.innerHTML = "Password must contain atleast one uppercase letter.";
        weakBadge.style.display = 'block';
        return false;
      }
      else if (!(password.match(/([a-z])/))) {
        weakBadge.innerHTML = "Password must contain atleast one lowercase letter.";
        weakBadge.style.display = 'block';
        return false;
      }
      else if (!(password.match(/([0-9])/))) {
        weakBadge.innerHTML = "Password must contain atleast one number.";
        weakBadge.style.display = 'block';
        return false;
      }
      else if (!(password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))) {
        weakBadge.innerHTML = "Password must contain atleast one special character.";
        weakBadge.style.display = 'block';
        return false;
      }
      else{
        weakBadge.innerHTML = "";
        weakBadge.style.display = 'none';
        return true;
      }

     
    }


    document.getElementById('showPasswords').addEventListener('click', function(){

        let newPassword = document.getElementById('newpassword');
        let confirmPassword = document.getElementById('confirmpassword');
        let label = document.getElementById('showPasswordLabel');

        if(this.checked){
            newPassword.setAttribute('type', 'text');
            confirmPassword.setAttribute('type', 'text');
            label.innerHTML = 'Hide password';
        }else{
            newPassword.setAttribute('type', 'password');
            confirmPassword.setAttribute('type', 'password');
            label.innerHTML = 'Show password';
        }
    });


// ========================================
// STUDENT SIGNUP FUNCTIONALITY
// ========================================

// Wait for DOM to be fully loaded before attaching signup event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up signup functionality...');
    
    // Show signup form - wrapped in check to ensure element exists
    const showSignupBtn = document.getElementById('showSignup');
    if (showSignupBtn) {
        console.log('showSignup button found, attaching click listener');
        showSignupBtn.addEventListener('click', function(e){
            e.preventDefault(); // Prevent default link behavior
            console.log('Signup button clicked!'); // Debug log
            hideLoginForm(true);
            hideforgotPasswordForm(true);
            hideVerifyOtpForm(true);
            hideCreateNewPasswordForm(true);
            showSignupForm(true);
            document.getElementById('board-title').innerHTML = 'Student Sign Up';
            console.log('Signup form should now be visible'); // Debug log
        });
    } else {
        console.error('showSignup button not found!');
    }

    // Back to login from signup
    const backToLoginBtn = document.getElementById('backToLoginFromSignup');
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function(e){
            e.preventDefault(); // Prevent default behavior
            showSignupForm(false);
            hideLoginForm(false);
            document.getElementById('board-title').innerHTML = 'Login';
            console.log('Back to login'); // Debug log
        });
    }
});

// Toggle signup password visibility
const toggleSignupPassword = document.querySelector("#toggleSignupPassword");
const signupPassword = document.querySelector("#signupPassword");

if (toggleSignupPassword) {
    toggleSignupPassword.addEventListener("click", function () {
        const type = signupPassword.getAttribute("type") === "password" ? "text" : "password";
        signupPassword.setAttribute("type", type);
        this.classList.toggle("bi-eye-slash-fill");
    });
}

// Show/hide signup form
function showSignupForm(show){
    console.log('showSignupForm called with:', show);
    
    const alertBox = document.querySelector('.alert-box');
    const passwordMismatch = document.getElementById('signupPasswordMismatch');
    const signupForm = document.getElementById('signup-form');
    
    if (alertBox) alertBox.style.display = 'none';
    if (passwordMismatch) passwordMismatch.style.display = 'none';
    
    if (!signupForm) {
        console.error('signup-form element not found!');
        return;
    }
    
    if(show){
        signupForm.style.display = 'block';
        console.log('Signup form display set to block');
    }else{
        signupForm.style.display = 'none';
        signupForm.reset();
        console.log('Signup form hidden');
    }
}

// Real-time password match validation
const signupConfirmPasswordField = document.getElementById('signupConfirmPassword');
if (signupConfirmPasswordField) {
    signupConfirmPasswordField.addEventListener('keyup', function(){
        const password = document.getElementById('signupPassword').value;
        const confirmPassword = this.value;
        const mismatchMsg = document.getElementById('signupPasswordMismatch');
        
        if(confirmPassword !== '' && password !== confirmPassword){
            mismatchMsg.style.display = 'block';
        } else {
            mismatchMsg.style.display = 'none';
        }
    });
}

// Clear error messages on input
const signupInputs = ['signupFullName', 'signupEmail', 'signupPhone', 'signupPassword', 'signupConfirmPassword'];

signupInputs.forEach(inputId => {
    const element = document.getElementById(inputId);
    if(element) {
        element.addEventListener('keyup', () => {
            const errorbox = document.querySelector('.alert-box');
            const error_msg = document.getElementById('error-msg');
            if (errorbox) errorbox.style.display = 'none';
            if (error_msg) error_msg.innerHTML = '';
        });
    }
});

// Handle signup form submission
const signupForm = document.getElementById('signup-form');
if (signupForm) {
    signupForm.addEventListener('submit', function(event){
        event.preventDefault();
        
        const password = document.getElementById('signupPassword').value;
        const confirmPassword = document.getElementById('signupConfirmPassword').value;
        const errorbox = document.querySelector('.alert-box');
        const error_msg = document.getElementById('error-msg');
        
        // Validate password match
        if(password !== confirmPassword){
            if (errorbox) errorbox.style.display = 'block';
            if (error_msg) {
                error_msg.classList.remove('alert-success');
                error_msg.classList.add('alert-danger');
                error_msg.innerHTML = 'Passwords do not match!';
            }
            return;
        }
        
        // Validate password strength
        if(password.length < 6){
            if (errorbox) errorbox.style.display = 'block';
            if (error_msg) {
                error_msg.classList.remove('alert-success');
                error_msg.classList.add('alert-danger');
                error_msg.innerHTML = 'Password must be at least 6 characters long!';
            }
            return;
        }
        
        // Show loading state
        const signupBtn = document.getElementById('signupBtn');
        const originalText = signupBtn.innerHTML;
        signupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        signupBtn.disabled = true;
        
        const formData = new FormData(event.target);
        
        // Debug: log form data
        console.log('Form data being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        fetch('student-signup-backend.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);
            console.log('Response headers:', response.headers.get('content-type'));
            
            return response.text(); // Get response as text first
        })
        .then(text => {
            console.log('Raw response length:', text.length);
            console.log('Raw response:', text);
            
            if (!text || text.trim() === '') {
                console.error('Empty response received from server');
                signupBtn.innerHTML = originalText;
                signupBtn.disabled = false;
                if (errorbox) errorbox.style.display = 'block';
                if (error_msg) {
                    error_msg.classList.remove('alert-success');
                    error_msg.classList.add('alert-danger');
                    error_msg.innerHTML = 'Server returned empty response. Check if Apache/PHP is running.';
                }
                return;
            }
            
            // Try to parse as JSON
            try {
                const data = JSON.parse(text);
                signupBtn.innerHTML = originalText;
                signupBtn.disabled = false;
                
                if (error_msg && errorbox) {
                    error_msg.classList.remove('alert-danger', 'alert-success');
                    error_msg.classList.add(data.status === "success" ? 'alert-success' : 'alert-danger');
                    error_msg.innerHTML = data.message;
                    errorbox.style.display = 'block';
                }
                
                if (data.status === "success") {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                signupBtn.innerHTML = originalText;
                signupBtn.disabled = false;
                if (errorbox) errorbox.style.display = 'block';
                if (error_msg) {
                    error_msg.classList.remove('alert-success');
                    error_msg.classList.add('alert-danger');
                    error_msg.innerHTML = 'Server error. Please check console for details.';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            signupBtn.innerHTML = originalText;
            signupBtn.disabled = false;
            if (errorbox) errorbox.style.display = 'block';
            if (error_msg) {
                error_msg.classList.remove('alert-success');
                error_msg.classList.add('alert-danger');
                error_msg.innerHTML = 'An error occurred. Please try again.';
            }
        });
    });
}
