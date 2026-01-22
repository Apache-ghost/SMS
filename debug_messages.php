<?php
// DEBUG SCRIPT - Check what's happening with messages
session_start();
include("assets/config.php");

echo "<!DOCTYPE html><html><head><title>Debug Messages</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;} pre{background:#fff;padding:15px;border:1px solid #ddd;overflow:auto;} h2{color:#333;border-bottom:2px solid #007bff;padding-bottom:10px;} .success{color:green;} .error{color:red;}</style>";
echo "</head><body>";

echo "<h1>🔍 Message System Debug</h1>";

// 1. Check Session
echo "<h2>1. Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$userType = $_SESSION['role'] ?? 'NOT SET';
$userId = $_SESSION['id'] ?? $_SESSION['guardian_id'] ?? 'NOT SET';

echo "<p><strong>Detected User Type:</strong> $userType</p>";
echo "<p><strong>Detected User ID:</strong> $userId</p>";

// 2. Check all messages in database
echo "<h2>2. All Messages in Database</h2>";
$sql = "SELECT 
    message_id,
    sender_id,
    sender_type,
    COALESCE(recipient_id, receiver_id) as recipient_id,
    COALESCE(recipient_type, receiver_type) as recipient_type,
    subject,
    SUBSTRING(COALESCE(message_body, message), 1, 50) as preview,
    COALESCE(sent_date, created_at) as date
FROM parent_messages 
ORDER BY COALESCE(sent_date, created_at) DESC 
LIMIT 10";

$result = mysqli_query($conn, $sql);
if ($result) {
    echo "<table border='1' cellpadding='8' cellspacing='0' style='background:#fff;border-collapse:collapse;'>";
    echo "<tr style='background:#007bff;color:#fff;'><th>ID</th><th>From Type</th><th>From ID</th><th>To Type</th><th>To ID</th><th>Subject</th><th>Preview</th><th>Date</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $highlight = ($row['recipient_type'] == 'admin') ? "style='background:#ffffcc;'" : "";
        echo "<tr $highlight>";
        echo "<td>{$row['message_id']}</td>";
        echo "<td><strong>{$row['sender_type']}</strong></td>";
        echo "<td>{$row['sender_id']}</td>";
        echo "<td><strong>{$row['recipient_type']}</strong></td>";
        echo "<td>{$row['recipient_id']}</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>{$row['preview']}</td>";
        echo "<td>{$row['date']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✅ Found " . mysqli_num_rows($result) . " messages</p>";
    echo "<p><strong>Yellow highlighted rows</strong> should appear in admin panel</p>";
} else {
    echo "<p class='error'>❌ Query failed: " . mysqli_error($conn) . "</p>";
}

// 3. Test Admin Query
echo "<h2>3. Test Admin Query</h2>";
if ($userType == 'admin' || isset($_SESSION['id'])) {
    $testSql = "SELECT m.*, 
                   COALESCE(m.message_body, m.message) as message,
                   COALESCE(m.recipient_id, m.receiver_id) as recipient_id,
                   COALESCE(m.recipient_type, m.receiver_type) as recipient_type,
                   COALESCE(m.sent_date, m.created_at) as sent_date
            FROM parent_messages m
            WHERE (COALESCE(m.recipient_type, m.receiver_type) = 'admin' OR m.sender_type = 'admin')
            AND COALESCE(m.is_archived, 0) = 0 
            ORDER BY COALESCE(m.sent_date, m.created_at) DESC";
    
    echo "<p><strong>Query for admin:</strong></p>";
    echo "<pre style='background:#ffffcc;'>$testSql</pre>";
    
    $result = mysqli_query($conn, $testSql);
    if ($result) {
        $count = mysqli_num_rows($result);
        echo "<p class='success'>✅ Admin query returned: <strong>$count messages</strong></p>";
        
        if ($count > 0) {
            echo "<table border='1' cellpadding='8' cellspacing='0' style='background:#fff;border-collapse:collapse;'>";
            echo "<tr style='background:#28a745;color:#fff;'><th>ID</th><th>From</th><th>To</th><th>Subject</th></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['message_id']}</td>";
                echo "<td>{$row['sender_type']}</td>";
                echo "<td>{$row['recipient_type']}</td>";
                echo "<td>{$row['subject']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p class='error'>❌ Admin query failed: " . mysqli_error($conn) . "</p>";
    }
}

// 4. Check students
echo "<h2>4. Check Students</h2>";
$result = mysqli_query($conn, "SELECT id, fname, lname, class FROM students ORDER BY fname LIMIT 10");
if ($result) {
    $count = mysqli_num_rows($result);
    echo "<p>Found <strong>$count</strong> students:</p>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>{$row['fname']} {$row['lname']} (ID: {$row['id']}, Class: {$row['class']})</li>";
    }
    echo "</ul>";
}

// 5. Check parent_guardian
echo "<h2>5. Check Parent/Guardian</h2>";
$result = mysqli_query($conn, "SELECT guardian_id, fname, lname, relation FROM parent_guardian LIMIT 10");
if ($result) {
    $count = mysqli_num_rows($result);
    echo "<p>Found <strong>$count</strong> guardians:</p>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>{$row['fname']} {$row['lname']} ({$row['relation']}) - ID: {$row['guardian_id']}</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h2>✅ Actions to Take:</h2>";
echo "<ol>";
echo "<li>If you see messages with recipient_type='admin' in section 2, but section 3 shows 0 messages, the query filter is wrong</li>";
echo "<li>If section 3 shows messages but admin panel doesn't, check admin session in section 1</li>";
echo "<li>Try logging out and back in to admin panel</li>";
echo "<li>Check browser console (F12) for JavaScript errors</li>";
echo "</ol>";

echo "<p><a href='admin_panel/messages.php' style='padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;display:inline-block;margin:10px 5px;'>Go to Admin Messages</a>";
echo "<a href='parent_panel/messages.php' style='padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;display:inline-block;margin:10px 5px;'>Go to Parent Messages</a></p>";

echo "</body></html>";
mysqli_close($conn);
?>
