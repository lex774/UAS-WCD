<?php
require_once '../../src/db/connection.php';
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Terima data dari AJAX request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['job_id']) || !isset($input['new_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$job_id = intval($input['job_id']);
$new_status = $input['new_status'] === 'TERTUTUP' ? 'TERTUTUP' : 'TERBUKA';

// Pastikan user hanya bisa update job miliknya
$check_query = mysqli_query($conn, "SELECT id FROM jobs WHERE id='$job_id' AND posted_by='$user_id'");
if (mysqli_num_rows($check_query) === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Job not found or unauthorized']);
    exit();
}

// Update status
$update_query = mysqli_query($conn, "UPDATE jobs SET status='$new_status' WHERE id='$job_id'");

if ($update_query) {
    echo json_encode([
        'success' => true, 
        'message' => 'Status updated successfully',
        'new_status' => $new_status,
        'new_status_text' => $new_status === 'TERBUKA' ? 'Terbuka' : 'Tertutup',
        'new_button_text' => $new_status === 'TERBUKA' ? 'Tutup Lowongan' : 'Buka Lowongan',
        'new_button_class' => $new_status === 'TERBUKA' ? 'btn-status-close' : 'btn-status-open'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?> 