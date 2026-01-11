<?php
// Test Parent Login Authentication
// This script verifies if the parent account exists and password works
// Access: http://localhost/school/test_parent_login.php

require_once 'assets/config.php';

echo "<!DOCTYPE html><html><head><title>Parent Login Test</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:#28a745;background:#d4edda;padding:10px;margin:10px 0;border-radius:5px;}";
echo ".error{color:#dc3545;background:#f8d7da;padding:10px;margin:10px 0;border-radius:5px;}";
echo ".info{color:#0c5460;background:#d1ecf1;padding:10px;margin:10px 0;border-radius:5px;}";
echo "h2{color:#333;}</style></head><body>";

echo "<h1>🔍 Parent Login Diagnostic Test</h1>";

// Test 1: Check if parent_users table exists
echo "<h2>Test 1: Check parent_users table</h2>";
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'parent_users'");
if (mysqli_num_rows($tableCheck) > 0) {
    echo "<div class='success'>✓ Table 'parent_users' exists</div>";
    
    // Test 2: Count records
    $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_users");
    $countRow = mysqli_fetch_assoc($countResult);
    echo "<div class='info'>Total records in parent_users: " . $countRow['count'] . "</div>";
    
    // Test 3: Check for parent@gmail.com
    echo "<h2>Test 2: Check parent@gmail.com account</h2>";
    $stmt = mysqli_prepare($conn, "SELECT parent_user_id, guardian_id, email, password, is_active FROM parent_users WHERE email = ?");
    $email = 'parent@gmail.com';
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='success'>✓ Account 'parent@gmail.com' exists</div>";
        echo "<div class='info'>";
        echo "Parent User ID: " . $row['parent_user_id'] . "<br>";
        echo "Guardian ID: " . $row['guardian_id'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Is Active: " . ($row['is_active'] ? 'Yes' : 'No') . "<br>";
        echo "Password Hash: " . substr($row['password'], 0, 30) . "...<br>";
        echo "</div>";
        
        // Test 4: Verify password
        echo "<h2>Test 3: Verify password '123'</h2>";
        $testPassword = '123';
        if (password_verify($testPassword, $row['password'])) {
            echo "<div class='success'>✓ Password '123' is CORRECT!</div>";
            echo "<div class='success' style='font-size:18px;font-weight:bold;'>✓✓✓ AUTHENTICATION SHOULD WORK! ✓✓✓</div>";
        } else {
            echo "<div class='error'>✗ Password '123' does NOT match the stored hash</div>";
            echo "<div class='info'>Stored hash: " . $row['password'] . "</div>";
            
            // Generate new hash
            $newHash = password_hash('123', PASSWORD_DEFAULT);
            echo "<div class='info'>Try updating with this hash:<br>";
            echo "<code>UPDATE parent_users SET password = '$newHash' WHERE email = 'parent@gmail.com';</code></div>";
        }
        
    } else {
        echo "<div class='error'>✗ Account 'parent@gmail.com' NOT FOUND!</div>";
        echo "<div class='info'>Please run: database/fix_parent_login.sql</div>";
    }
    
} else {
    echo "<div class='error'>✗ Table 'parent_users' does NOT exist!</div>";
    echo "<div class='info'>Please import: database/parent_portal_setup.sql or database/fix_parent_login.sql</div>";
}

// Test 5: Check student_guardian table
echo "<h2>Test 4: Check student_guardian table</h2>";
$guardianCheck = mysqli_query($conn, "SELECT COUNT(*) as count FROM student_guardian");
if ($guardianCheck) {
    $guardianRow = mysqli_fetch_assoc($guardianCheck);
    if ($guardianRow['count'] > 0) {
        echo "<div class='success'>✓ Found " . $guardianRow['count'] . " guardian records</div>";
        
        // Show first guardian
        $firstGuardian = mysqli_query($conn, "SELECT id, gname, relation FROM student_guardian LIMIT 1");
        if ($gRow = mysqli_fetch_assoc($firstGuardian)) {
            echo "<div class='info'>Sample Guardian:<br>";
            echo "ID: " . $gRow['id'] . "<br>";
            echo "Name: " . $gRow['gname'] . "<br>";
            echo "Relation: " . $gRow['relation'] . "</div>";
        }
    } else {
        echo "<div class='error'>✗ No guardian records found</div>";
        echo "<div class='info'>You need to add students with guardians first</div>";
    }
} else {
    echo "<div class='error'>✗ Table 'student_guardian' error: " . mysqli_error($conn) . "</div>";
}

echo "<hr><h2>📋 Next Steps:</h2>";
echo "<ol>";
echo "<li>If all tests pass, try logging in at: <a href='login.php'>login.php</a></li>";
echo "<li>Email: <strong>parent@gmail.com</strong></li>";
echo "<li>Password: <strong>123</strong></li>";
echo "<li>Or use dedicated parent login: <a href='parent_login.php'>parent_login.php</a></li>";
echo "</ol>";

echo "</body></html>";

mysqli_close($conn);
?>
