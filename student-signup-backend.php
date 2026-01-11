<?php
// Clean output buffer to prevent any stray output
ob_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Clear any output that might have been generated
ob_clean();

header('Content-Type: application/json');

$response = array();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response['status'] = 'error';
        $response['message'] = 'Method not allowed';
        echo json_encode($response);
        exit();
    }

    // Check if all required fields are present
    $required_fields = ['fname', 'email', 'phone', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $response['status'] = 'error';
            $response['message'] = 'All fields are required. Missing: ' . $field;
            echo json_encode($response);
            exit();
        }
    }

include("assets/config.php");

if (!isset($conn) || !$conn) {
    $response['status'] = 'error';
    $response['message'] = 'Database connection error';
    echo json_encode($response);
    exit();
}

// Sanitize inputs
$fullname = mysqli_real_escape_string($conn, trim($_POST['fname']));
// Split fullname into first and last name
$name_parts = explode(' ', $fullname, 2);
$fname = $name_parts[0];
$lname = isset($name_parts[1]) ? $name_parts[1] : '';
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit();
}

// Validate phone number (at least 9 digits)
if (!preg_match('/^[0-9]{9,}$/', $phone)) {
    $response['status'] = 'error';
    $response['message'] = 'Phone number must be at least 9 digits';
    echo json_encode($response);
    exit();
}

// Check password match
if ($password !== $confirm_password) {
    $response['status'] = 'error';
    $response['message'] = 'Passwords do not match';
    echo json_encode($response);
    exit();
}

// Validate password strength (minimum 6 characters)
if (strlen($password) < 6) {
    $response['status'] = 'error';
    $response['message'] = 'Password must be at least 6 characters long';
    echo json_encode($response);
    exit();
}

// Check if email already exists
$checkEmailSql = "SELECT email FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $checkEmailSql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $response['status'] = 'error';
    $response['message'] = 'Email already registered';
    mysqli_stmt_close($stmt);
    echo json_encode($response);
    exit();
}
mysqli_stmt_close($stmt);

// Generate unique student ID
$student_id = 'S' . time();

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Use default date of birth
$dob_formatted = '01-01-2000';

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Insert into students table with default values for optional fields
    $insertStudentSql = "INSERT INTO students (id, fname, lname, father, gender, class, section, dob, phone, email, address, city, zip, state, request_date, request_time, request) 
                         VALUES (?, ?, ?, '', 'Not Specified', '', '', ?, ?, ?, '', '', '', '', CURDATE(), CURTIME(), 'approved')";
    $stmt1 = mysqli_prepare($conn, $insertStudentSql);
    
    if (!$stmt1) {
        throw new Exception('Error preparing student insert statement');
    }
    
    mysqli_stmt_bind_param($stmt1, "ssssss", $student_id, $fname, $lname, $dob_formatted, $phone, $email);
    
    if (!mysqli_stmt_execute($stmt1)) {
        throw new Exception('Error inserting student record');
    }
    mysqli_stmt_close($stmt1);
    
    // Insert into users table
    $insertUserSql = "INSERT INTO users (id, email, password_hash, role, theme) VALUES (?, ?, ?, 'student', 'light')";
    $stmt2 = mysqli_prepare($conn, $insertUserSql);
    
    if (!$stmt2) {
        throw new Exception('Error preparing user insert statement');
    }
    
    mysqli_stmt_bind_param($stmt2, "sss", $student_id, $email, $password_hash);
    
    if (!mysqli_stmt_execute($stmt2)) {
        throw new Exception('Error inserting user record');
    }
    mysqli_stmt_close($stmt2);
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Set session variables for automatic login
    $_SESSION['uid'] = $student_id;
    
    $response['status'] = 'success';
    $response['message'] = 'Account created successfully! Redirecting to student panel...';
    $response['redirect'] = 'student_panel/index.php';
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $response['status'] = 'error';
    $response['message'] = 'Registration failed: ' . $e->getMessage();
}

mysqli_close($conn);
echo json_encode($response);
ob_end_flush();

} catch (Exception $e) {
    // Catch any unexpected errors
    ob_clean();
    $response['status'] = 'error';
    $response['message'] = 'Unexpected error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
