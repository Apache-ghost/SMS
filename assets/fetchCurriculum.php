<?php
session_start();
include("config.php");
header('Content-Type: application/json');

$response = array();

try {
    $sql = "SELECT cm.*, 
            (SELECT COUNT(*) FROM curriculum_subjects cs WHERE cs.curriculum_id = cm.curriculum_id) as total_subjects
            FROM curriculum_master cm 
            ORDER BY cm.class ASC, cm.academic_year DESC";
    $result = mysqli_query($conn, $sql);
    
    $curriculums = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $curriculums[] = $row;
    }
    
    $response['status'] = 'success';
    $response['data'] = $curriculums;
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    $response['data'] = [];
}

echo json_encode($response);
?>
