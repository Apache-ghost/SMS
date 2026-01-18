<?php
// Test if file_path column exists in curriculum_master table
include('../assets/config.php');
header('Content-Type: application/json');

$result = mysqli_query($conn, "DESCRIBE curriculum_master");
$columns = [];
$hasFilePathColumn = false;

while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    if ($row['Field'] === 'file_path') {
        $hasFilePathColumn = true;
    }
}

echo json_encode([
    'has_column' => $hasFilePathColumn,
    'all_columns' => $columns,
    'message' => $hasFilePathColumn ? 'file_path column exists' : 'file_path column missing'
]);
?>
