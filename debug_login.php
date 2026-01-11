<?php
// Debug Parent Login - Shows exactly what's happening
require_once 'assets/config.php';

echo "<!DOCTYPE html><html><head><title>Debug Login</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#fff;}";
echo ".box{background:#2d2d2d;padding:15px;margin:10px 0;border-left:4px solid #4CAF50;border-radius:5px;}";
echo ".error{border-color:#f44336;}</style></head><body>";

echo "<h1>🔍 Debug Parent Login</h1>";

$email = 'parent@gmail.com';
$password = '123';

echo "<div class='box'>";
echo "<strong>Testing with:</strong><br>";
echo "Email: $email<br>";
echo "Password: $password<br>";
echo "</div>";

// Step 1: Check parent_users table
echo "<h2>Step 1: Check parent_users table</h2>";
$sql1 = "SELECT parent_user_id, guardian_id, email, password, is_active FROM parent_users WHERE email = ?";
$stmt1 = mysqli_prepare($conn, $sql1);
mysqli_stmt_bind_param($stmt1, "s", $email);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
$row1 = mysqli_fetch_assoc($result1);

if ($row1) {
    echo "<div class='box'>";
    echo "✓ Found in parent_users:<br>";
    echo "parent_user_id: " . $row1['parent_user_id'] . "<br>";
    echo "guardian_id: " . $row1['guardian_id'] . "<br>";
    echo "is_active: " . $row1['is_active'] . "<br>";
    echo "Password hash: " . substr($row1['password'], 0, 40) . "...<br>";
    
    if (password_verify($password, $row1['password'])) {
        echo "<span style='color:#4CAF50;font-weight:bold;'>✓ Password MATCHES!</span><br>";
    } else {
        echo "<span style='color:#f44336;font-weight:bold;'>✗ Password DOES NOT MATCH!</span><br>";
    }
    echo "</div>";
    
    // Step 2: Check if guardian exists
    echo "<h2>Step 2: Check student_guardian table</h2>";
    $guardianId = $row1['guardian_id'];
    $sql2 = "SELECT id, gname, relation FROM student_guardian WHERE id = ?";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "s", $guardianId);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $row2 = mysqli_fetch_assoc($result2);
    
    if ($row2) {
        echo "<div class='box'>";
        echo "✓ Found guardian:<br>";
        echo "id: " . $row2['id'] . "<br>";
        echo "name: " . $row2['gname'] . "<br>";
        echo "relation: " . $row2['relation'] . "<br>";
        echo "</div>";
    } else {
        echo "<div class='box error'>";
        echo "✗ Guardian NOT FOUND!<br>";
        echo "Looking for ID: $guardianId<br>";
        echo "This is the PROBLEM!<br>";
        echo "</div>";
    }
    
    // Step 3: Test the JOIN query (what login-backend.php uses)
    echo "<h2>Step 3: Test the actual login query (with JOIN)</h2>";
    $sql3 = "SELECT pu.parent_user_id, pu.guardian_id, pu.password, sg.gname 
             FROM parent_users pu
             JOIN student_guardian sg ON pu.guardian_id = sg.id
             WHERE pu.email = ? AND pu.is_active = 1";
    $stmt3 = mysqli_prepare($conn, $sql3);
    mysqli_stmt_bind_param($stmt3, "s", $email);
    mysqli_stmt_execute($stmt3);
    $result3 = mysqli_stmt_get_result($stmt3);
    $row3 = mysqli_fetch_assoc($result3);
    
    if ($row3) {
        echo "<div class='box'>";
        echo "✓ JOIN query works!<br>";
        echo "parent_user_id: " . $row3['parent_user_id'] . "<br>";
        echo "guardian_id: " . $row3['guardian_id'] . "<br>";
        echo "guardian_name: " . $row3['gname'] . "<br>";
        
        if (password_verify($password, $row3['password'])) {
            echo "<span style='color:#4CAF50;font-size:20px;font-weight:bold;'>✓✓✓ LOGIN SHOULD WORK! ✓✓✓</span><br>";
        }
        echo "</div>";
    } else {
        echo "<div class='box error'>";
        echo "✗ JOIN query FAILED!<br>";
        echo "This means guardian_id in parent_users doesn't match id in student_guardian<br>";
        echo "<br><strong>SOLUTION:</strong><br>";
        echo "Run this SQL to fix:<br>";
        echo "<code style='background:#000;padding:10px;display:block;margin:10px 0;'>";
        echo "UPDATE parent_users SET guardian_id = (SELECT id FROM student_guardian LIMIT 1) WHERE email = 'parent@gmail.com';";
        echo "</code>";
        echo "</div>";
    }
    
} else {
    echo "<div class='box error'>";
    echo "✗ parent@gmail.com NOT FOUND in parent_users table!<br>";
    echo "Import database/fix_parent_login.sql first.";
    echo "</div>";
}

mysqli_close($conn);
echo "</body></html>";
?>
