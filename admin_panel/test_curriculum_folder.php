<?php
// Test if curriculumUploads folder exists and is writable
header('Content-Type: application/json');

$uploadPath = '../curriculumUploads/';
$exists = file_exists($uploadPath) && is_dir($uploadPath);
$writable = $exists && is_writable($uploadPath);

echo json_encode([
    'exists' => $exists,
    'writable' => $writable,
    'path' => realpath($uploadPath),
    'message' => $exists && $writable ? 'Folder ready for uploads' : 'Folder has issues'
]);
?>
