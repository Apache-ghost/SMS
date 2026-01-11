<?php
// Auto-Fix Parent Guardian Link
require_once 'assets/config.php';

echo "<!DOCTYPE html><html><head><title>Auto Fix</title>";
echo "<style>body{font-family:Arial;padding:40px;background:#f5f5f5;text-align:center;}";
echo ".success{color:#28a745;background:#d4edda;padding:20px;margin:20px auto;border-radius:10px;max-width:600px;font-size:18px;}";
echo ".error{color:#dc3545;background:#f8d7da;padding:20px;margin:20px auto;border-radius:10px;max-width:600px;font-size:18px;}";
echo "h1{color:#333;}</style></head><body>";

echo "<h1>🔧 Auto-Fix Guardian Link</h1>";

// Step 1: Get a valid guardian ID
$getGuardianSql = "SELECT id, gname, relation FROM student_guardian LIMIT 1";
$guardianResult = mysqli_query($conn, $getGuardianSql);
$guardian = mysqli_fetch_assoc($guardianResult);

if ($guardian) {
    echo "<div class='success'>";
    echo "Found guardian:<br>";
    echo "ID: " . $guardian['id'] . "<br>";
    echo "Name: " . $guardian['gname'] . "<br>";
    echo "Relation: " . $guardian['relation'] . "<br>";
    echo "</div>";
    
    // Step 2: Update parent_users with correct guardian_id
    $updateSql = "UPDATE parent_users SET guardian_id = ? WHERE email = 'parent@gmail.com'";
    $stmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($stmt, "s", $guardian['id']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='success'>";
        echo "✅ <strong>SUCCESS!</strong><br><br>";
        echo "Guardian link updated!<br>";
        echo "Parent account is now linked to: " . $guardian['gname'] . "<br><br>";
        echo "<strong>Now you can login:</strong><br>";
        echo "Email: parent@gmail.com<br>";
        echo "Password: 123<br><br>";
        echo "<a href='login.php' style='color:#fff;background:#667eea;padding:15px 30px;text-decoration:none;border-radius:8px;font-size:16px;'>Go to Login Page</a>";
        echo "</div>";
        
        // Verify the fix
        echo "<hr style='margin:40px auto;max-width:600px;'>";
        echo "<h2>Verification:</h2>";
        
        $testSql = "SELECT pu.parent_user_id, pu.guardian_id, pu.email, sg.gname 
                    FROM parent_users pu
                    JOIN student_guardian sg ON pu.guardian_id = sg.id
                    WHERE pu.email = 'parent@gmail.com' AND pu.is_active = 1";
        $testResult = mysqli_query($conn, $testSql);
        $testRow = mysqli_fetch_assoc($testResult);
        
        if ($testRow) {
            echo "<div class='success'>";
            echo "✓ JOIN query works!<br>";
            echo "Email: " . $testRow['email'] . "<br>";
            echo "Guardian ID: " . $testRow['guardian_id'] . "<br>";
            echo "Guardian Name: " . $testRow['gname'] . "<br><br>";
            echo "<span style='color:#28a745;font-size:24px;font-weight:bold;'>✓✓✓ READY TO LOGIN! ✓✓✓</span>";
            echo "</div>";
        }
        
    } else {
        echo "<div class='error'>";
        echo "❌ Error updating: " . mysqli_error($conn);
        echo "</div>";
    }
    
} else {
    echo "<div class='error'>";
    echo "❌ No guardians found in database!<br>";
    echo "You need to add students with guardians first.";
    echo "</div>";
}

mysqli_close($conn);

echo "<p style='color:#666;margin-top:40px;'>You can delete these files after login works:<br>fix_password.php, debug_login.php, auto_fix.php</p>";
echo "</body></html>";
?>
