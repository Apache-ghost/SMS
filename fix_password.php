<?php
// Fix Parent Password - Run this once to update the password
require_once 'assets/config.php';

echo "<!DOCTYPE html><html><head><title>Fix Password</title>";
echo "<style>body{font-family:Arial;padding:40px;background:#f5f5f5;text-align:center;}";
echo ".success{color:#28a745;background:#d4edda;padding:20px;margin:20px auto;border-radius:10px;max-width:600px;font-size:18px;}";
echo ".error{color:#dc3545;background:#f8d7da;padding:20px;margin:20px auto;border-radius:10px;max-width:600px;font-size:18px;}";
echo "h1{color:#333;}</style></head><body>";

echo "<h1>🔧 Fix Parent Password</h1>";

// Generate new password hash for '123'
$newPassword = '123';
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password
$sql = "UPDATE parent_users SET password = ? WHERE email = 'parent@gmail.com'";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $newHash);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected = mysqli_stmt_affected_rows($stmt);
        
        if ($affected > 0) {
            echo "<div class='success'>";
            echo "✅ <strong>SUCCESS!</strong><br><br>";
            echo "Password updated successfully!<br><br>";
            echo "<strong>Login Details:</strong><br>";
            echo "Email: parent@gmail.com<br>";
            echo "Password: 123<br><br>";
            echo "New Hash: " . substr($newHash, 0, 40) . "...<br><br>";
            echo "<a href='login.php' style='color:#fff;background:#667eea;padding:15px 30px;text-decoration:none;border-radius:8px;font-size:16px;'>Go to Login Page</a>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "⚠️ No rows updated.<br><br>";
            echo "The email 'parent@gmail.com' might not exist in the database.<br><br>";
            echo "Please import: database/fix_parent_login.sql first";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>";
        echo "❌ Error executing update: " . mysqli_error($conn);
        echo "</div>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<div class='error'>";
    echo "❌ Error preparing statement: " . mysqli_error($conn);
    echo "</div>";
}

// Verify the update
echo "<hr style='margin:40px auto;max-width:600px;'>";
echo "<h2>Verification:</h2>";

$checkSql = "SELECT parent_user_id, email, password FROM parent_users WHERE email = 'parent@gmail.com'";
$result = mysqli_query($conn, $checkSql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    echo "<div style='background:#d1ecf1;color:#0c5460;padding:20px;margin:20px auto;border-radius:10px;max-width:600px;'>";
    echo "<strong>Database Check:</strong><br>";
    echo "Account ID: " . $row['parent_user_id'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
    echo "Password Hash: " . substr($row['password'], 0, 40) . "...<br><br>";
    
    // Test password verification
    if (password_verify('123', $row['password'])) {
        echo "<span style='color:#28a745;font-size:20px;font-weight:bold;'>✓ Password '123' VERIFIED!</span>";
    } else {
        echo "<span style='color:#dc3545;font-size:20px;font-weight:bold;'>✗ Password verification failed</span>";
    }
    echo "</div>";
}

mysqli_close($conn);

echo "<p style='color:#666;margin-top:40px;'>You can delete this file after use: fix_password.php</p>";
echo "</body></html>";
?>
