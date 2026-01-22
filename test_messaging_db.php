<?php
// Database Structure Test - Run this to check messaging system setup
// Access: http://localhost/school/test_messaging_db.php

include("assets/config.php");

echo "<!DOCTYPE html><html><head><title>Messaging System Test</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";
echo "</head><body><h1>Messaging System Database Test</h1>";

// Test 1: Check if table exists
echo "<h2>1. Check parent_messages table</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'parent_messages'");
if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ Table 'parent_messages' exists</p>";
} else {
    echo "<p class='error'>❌ Table 'parent_messages' does NOT exist</p>";
    echo "<p>Create it using the SQL in database/parent_portal_setup.sql</p>";
    exit;
}

// Test 2: Check table structure
echo "<h2>2. Check table columns</h2>";
$result = mysqli_query($conn, "DESCRIBE parent_messages");
$columns = [];
echo "<table><tr><th>Column Name</th><th>Type</th><th>Status</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td class='success'>✅</td></tr>";
}
echo "</table>";

// Check for required columns
$required = ['message_id', 'sender_id', 'sender_type', 'subject', 'message', 'created_at'];
$optional = ['message_body', 'recipient_id', 'recipient_type', 'sent_date', 'receiver_id', 'receiver_type'];

echo "<h3>Required Columns:</h3><ul>";
foreach ($required as $col) {
    if (in_array($col, $columns)) {
        echo "<li class='success'>✅ $col</li>";
    } else {
        echo "<li class='error'>❌ $col (MISSING - CRITICAL!)</li>";
    }
}
echo "</ul>";

echo "<h3>Optional Columns (for compatibility):</h3><ul>";
foreach ($optional as $col) {
    if (in_array($col, $columns)) {
        echo "<li class='success'>✅ $col</li>";
    } else {
        echo "<li class='warning'>⚠️ $col (recommended)</li>";
    }
}
echo "</ul>";

// Test 3: Check message_attachments table
echo "<h2>3. Check message_attachments table</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'message_attachments'");
if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ Table 'message_attachments' exists</p>";
} else {
    echo "<p class='warning'>⚠️ Table 'message_attachments' does not exist (optional feature)</p>";
}

// Test 4: Count messages
echo "<h2>4. Current Messages</h2>";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_messages");
$row = mysqli_fetch_assoc($result);
echo "<p>Total messages in database: <strong>{$row['count']}</strong></p>";

if ($row['count'] > 0) {
    echo "<h3>Recent Messages:</h3>";
    $sql = "SELECT 
                message_id,
                sender_type,
                COALESCE(recipient_type, receiver_type, 'unknown') as recipient_type,
                subject,
                COALESCE(created_at, NOW()) as date
            FROM parent_messages 
            ORDER BY COALESCE(sent_date, created_at) DESC 
            LIMIT 5";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo "<table><tr><th>ID</th><th>From</th><th>To</th><th>Subject</th><th>Date</th></tr>";
        while ($msg = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$msg['message_id']}</td>";
            echo "<td>{$msg['sender_type']}</td>";
            echo "<td>{$msg['recipient_type']}</td>";
            echo "<td>{$msg['subject']}</td>";
            echo "<td>{$msg['date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>Error loading messages: " . mysqli_error($conn) . "</p>";
    }
}

// Test 5: Check parent_guardian table
echo "<h2>5. Check parent_guardian table</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'parent_guardian'");
if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ Table 'parent_guardian' exists</p>";
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM parent_guardian");
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total guardians: <strong>{$row['count']}</strong></p>";
} else {
    echo "<p class='error'>❌ Table 'parent_guardian' does NOT exist</p>";
}

// Test 6: Check students table
echo "<h2>6. Check students table</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'students'");
if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ Table 'students' exists</p>";
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total students: <strong>{$row['count']}</strong></p>";
} else {
    echo "<p class='error'>❌ Table 'students' does NOT exist</p>";
}

// Test 7: Test INSERT query
echo "<h2>7. Test Message Insert</h2>";
echo "<p>Testing if we can insert a message with both old and new column names...</p>";

$test_sql = "INSERT INTO parent_messages 
    (subject, message, message_body, sender_id, sender_type, 
     receiver_id, receiver_type, recipient_id, recipient_type,
     student_id, sent_date, created_at) 
VALUES 
    ('TEST', 'Test message', 'Test message', 'test', 'parent', 
     'admin', 'admin', 'admin', 'admin',
     'test', NOW(), NOW())";

// Don't actually run it, just test if it would work
if (mysqli_query($conn, "EXPLAIN " . str_replace("INSERT", "SELECT", $test_sql))) {
    echo "<p class='success'>✅ Insert query structure is valid</p>";
} else {
    echo "<p class='error'>❌ Insert query has issues: " . mysqli_error($conn) . "</p>";
    echo "<p><strong>Solution:</strong> Run the SQL update from database/messaging_quick_fix.sql</p>";
}

// Summary
echo "<hr><h2>📋 Summary</h2>";
echo "<p><strong>If you see errors above, run this SQL in phpMyAdmin:</strong></p>";
echo "<pre style='background:#f4f4f4;padding:15px;border:1px solid #ddd;'>";
echo "-- Run in phpMyAdmin to fix issues\n";
echo "ALTER TABLE `parent_messages` \n";
echo "  ADD COLUMN IF NOT EXISTS `message_body` TEXT AFTER `message`,\n";
echo "  ADD COLUMN IF NOT EXISTS `recipient_id` VARCHAR(40) AFTER `receiver_id`,\n";
echo "  ADD COLUMN IF NOT EXISTS `recipient_type` ENUM('teacher', 'parent', 'admin') AFTER `receiver_type`,\n";
echo "  ADD COLUMN IF NOT EXISTS `sent_date` DATETIME AFTER `created_at`;\n\n";
echo "UPDATE `parent_messages` \n";
echo "SET \n";
echo "  `message_body` = `message`,\n";
echo "  `recipient_id` = `receiver_id`,\n";
echo "  `recipient_type` = `receiver_type`,\n";
echo "  `sent_date` = `created_at`\n";
echo "WHERE `message_body` IS NULL OR `message_body` = '';";
echo "</pre>";

echo "<p style='margin-top:30px;'><strong>Delete this file after testing for security!</strong></p>";
echo "</body></html>";

mysqli_close($conn);
?>
