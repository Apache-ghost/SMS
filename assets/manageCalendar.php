<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'parent') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

if ($action == 'get_events') {
    // Get all calendar events visible to parents
    $sql = "SELECT * FROM school_calendar 
            WHERE is_cancelled = 0 
            AND (target_audience = 'all' OR target_audience = 'parent' OR target_audience = 'parents')
            ORDER BY start_date ASC, start_time ASC";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $events = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        echo json_encode(['status' => 'success', 'events' => $events]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

else if ($action == 'get_upcoming_events') {
    // Get upcoming events (next 30 days)
    $today = date('Y-m-d');
    $thirtyDaysLater = date('Y-m-d', strtotime('+30 days'));
    
    $sql = "SELECT * FROM school_calendar 
            WHERE is_cancelled = 0 
            AND (target_audience = 'all' OR target_audience = 'parent' OR target_audience = 'parents')
            AND start_date BETWEEN ? AND ?
            ORDER BY start_date ASC, start_time ASC
            LIMIT 10";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $today, $thirtyDaysLater);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        $events = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        echo json_encode(['status' => 'success', 'events' => $events]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

mysqli_close($conn);
?>