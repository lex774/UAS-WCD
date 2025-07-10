<?php
require_once '../db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$job_id = isset($input['job_id']) ? (int)$input['job_id'] : 0;

if ($job_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    exit();
}

// Pastikan user adalah pemilik job
$check = mysqli_query($conn, "SELECT * FROM jobs WHERE id='$job_id' AND posted_by='$user_id'");
if (!$check || mysqli_num_rows($check) === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

// Hapus semua aplikasi terkait (cascade manual jika belum ON DELETE CASCADE)
mysqli_query($conn, "DELETE FROM applications WHERE job_id='$job_id'");
// Hapus job
$delete = mysqli_query($conn, "DELETE FROM jobs WHERE id='$job_id'");

if ($delete) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete job']);
} 