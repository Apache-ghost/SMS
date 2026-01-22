<?php
error_reporting(0);
ini_set('display_errors', 0);

include('config.php');

header('Content-Type: application/json');

// Get unique classes and sections
$sql = "SELECT DISTINCT class, section FROM students ORDER BY class ASC, section ASC";
$result = @mysqli_query($conn, $sql);

$classData = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $classData[] = $row;
    }
}

echo json_encode($classData);

mysqli_close($conn);
?>