<?php
// Test database connection and check if announcements table exists
include("../assets/config.php");

header('Content-Type: application/json');

// Check if announcements table exists
$checkTable = "SHOW TABLES LIKE 'announcements'";
$result = mysqli_query($conn, $checkTable);

if (mysqli_num_rows($result) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Announcements table exists',
        'table_exists' => true
    ]);
    
    // Check table structure
    $descTable = "DESCRIBE announcements";
    $descResult = mysqli_query($conn, $descTable);
    
    echo "\n\nTable structure:\n";
    while ($row = mysqli_fetch_assoc($descResult)) {
        echo json_encode($row) . "\n";
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Announcements table does not exist! Please run the database migration.',
        'table_exists' => false,
        'hint' => 'Run the SQL file: database/school_enhancement_part3.sql'
    ]);
}

mysqli_close($conn);
?>
