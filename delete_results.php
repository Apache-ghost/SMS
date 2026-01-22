<?php
session_start();
require './assets/config.php';

// Check if admin
if (!isset($_SESSION['uid'])) {
    die("Please login as admin first");
}

$userCheck = mysqli_query($conn, "SELECT role FROM users WHERE id = '{$_SESSION['uid']}'");
$user = mysqli_fetch_assoc($userCheck);
if ($user['role'] != 'admin') {
    die("Admin access only");
}

echo "<h1>🗑️ Delete All Exam Results</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; }
    button { padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    button:hover { background: #c82333; }
</style>";

if (isset($_POST['delete_all'])) {
    $result = mysqli_query($conn, "DELETE FROM exam_results");
    if ($result) {
        $affected = mysqli_affected_rows($conn);
        echo "<div class='success'>✅ Deleted $affected result(s) successfully!</div>";
        echo "<p><a href='admin_panel/exams.php'>Go back to Admin Panel</a></p>";
    } else {
        echo "<div class='error'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
} else {
    // Show current results
    $countQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM exam_results");
    $count = mysqli_fetch_assoc($countQuery)['count'];
    
    echo "<p>Current results in database: <strong>$count</strong></p>";
    
    if ($count > 0) {
        echo "<div class='error'>";
        echo "<h3>⚠️ Warning: This will delete ALL exam results!</h3>";
        echo "<p>This action cannot be undone.</p>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='delete_all' onclick='return confirm(\"Are you sure you want to delete ALL results?\")'>🗑️ Delete All Results</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<p>No results to delete.</p>";
    }
    
    echo "<p><a href='admin_panel/exams.php'>Go back to Admin Panel</a></p>";
}
?>
