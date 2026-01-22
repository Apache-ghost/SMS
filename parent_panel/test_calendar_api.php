<?php
session_start();
require '../assets/config.php';

// Test as parent
$_SESSION['role'] = 'parent';
$_SESSION['parent_id'] = '1';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Calendar Test</title>
</head>
<body>
    <h1>Testing Calendar API</h1>
    
    <h3>Test 1: Get All Events</h3>
    <button onclick="testGetEvents()">Test Get Events</button>
    <pre id="eventsResult"></pre>
    
    <h3>Test 2: Get Upcoming Events</h3>
    <button onclick="testUpcomingEvents()">Test Upcoming Events</button>
    <pre id="upcomingResult"></pre>
    
    <h3>Test 3: Check Database Table</h3>
    <?php
    $sql = "SELECT COUNT(*) as count FROM school_calendar WHERE is_cancelled = 0";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total events in database: " . $row['count'] . "</p>";
    
    $sql2 = "SELECT * FROM school_calendar WHERE is_cancelled = 0 LIMIT 5";
    $result2 = mysqli_query($conn, $sql2);
    echo "<h4>Sample Events:</h4><pre>";
    while ($event = mysqli_fetch_assoc($result2)) {
        print_r($event);
        echo "\n\n";
    }
    echo "</pre>";
    ?>
    
    <script>
    function testGetEvents() {
        fetch('../assets/manageCalendar.php?action=get_events')
            .then(r => r.json())
            .then(data => {
                document.getElementById('eventsResult').textContent = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                document.getElementById('eventsResult').textContent = 'Error: ' + err;
            });
    }
    
    function testUpcomingEvents() {
        fetch('../assets/manageCalendar.php?action=get_upcoming_events')
            .then(r => r.json())
            .then(data => {
                document.getElementById('upcomingResult').textContent = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                document.getElementById('upcomingResult').textContent = 'Error: ' + err;
            });
    }
    </script>
</body>
</html>