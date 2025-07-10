<?php
require_once '../db/connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit();
}

$user_id = $_SESSION['user_id'];

// Get CV file path from request
$cv_file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($cv_file)) {
    http_response_code(400);
    echo 'Invalid file parameter';
    exit();
}

// Security check: ensure the file path is within uploads directory
$uploads_dir = '../../user/uploads/cv/';
$file_path = realpath($uploads_dir . basename($cv_file));

if (!$file_path || strpos($file_path, realpath($uploads_dir)) !== 0) {
    http_response_code(403);
    echo 'Access denied';
    exit();
}

// Check if file exists
if (!file_exists($file_path)) {
    http_response_code(404);
    echo 'File not found';
    exit();
}

// Check if user has permission to download this CV
$application_query = mysqli_query($conn, "SELECT * FROM applications WHERE cv_file = '$cv_file' AND user_id = '$user_id'");
if (mysqli_num_rows($application_query) === 0) {
    // Check if user is the job poster
    $job_query = mysqli_query($conn, "SELECT j.posted_by FROM applications a 
                                      JOIN jobs j ON a.job_id = j.id 
                                      WHERE a.cv_file = '$cv_file'");
    $job = mysqli_fetch_assoc($job_query);
    
    if (!$job || $job['posted_by'] != $user_id) {
        http_response_code(403);
        echo 'Access denied';
        exit();
    }
}

// Get file info
$file_info = pathinfo($file_path);
$file_extension = strtolower($file_info['extension']);

// Set appropriate content type
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'txt' => 'text/plain'
];

$content_type = $content_types[$file_extension] ?? 'application/octet-stream';

// Set headers for download
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($file_path);
exit();
?> 