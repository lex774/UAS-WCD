<?php
require_once '../db/connection.php';
session_start();

// Debug logging
error_log("update-application-status.php accessed at " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session");
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}
$user_id = $_SESSION['user_id'];
error_log("User ID: " . $user_id);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['status'])) {
    error_log("Invalid request method or missing parameters");
    http_response_code(400);
    echo 'Bad Request';
    exit();
}
$app_id = intval($_POST['id']);
$status = $_POST['status'];
$allowed_status = ['menunggu','direview','terseleksi','tidak_terseleksi','interview','lolos','tidak_lolos'];
error_log("POST status: $status");
error_log("Allowed status: " . implode(',', $allowed_status));
if (!in_array($status, $allowed_status)) {
    error_log("Invalid status: " . $status);
    http_response_code(400);
    echo 'Status tidak valid';
    exit();
}
// Cek apakah user adalah pemilik job dari aplikasi ini
$q = mysqli_query($conn, "SELECT a.id, j.posted_by FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id='$app_id'");
if (!$q) {
    error_log("Database query failed: " . mysqli_error($conn));
    http_response_code(500);
    echo 'Database error';
    exit();
}
$row = mysqli_fetch_assoc($q);
error_log("Query result: " . print_r($row, true));
if (!$row || $row['posted_by'] != $user_id) {
    error_log("Unauthorized access - row: " . print_r($row, true) . ", user_id: " . $user_id);
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}
// Update status
$u = mysqli_query($conn, "UPDATE applications SET status='$status' WHERE id='$app_id'");
if ($u) {
    error_log("Status updated to: $status for app_id: $app_id");
    // Ambil user_id pelamar
    $q2 = mysqli_query($conn, "SELECT user_id FROM applications WHERE id='$app_id'");
    $row2 = mysqli_fetch_assoc($q2);
    if ($row2) {
        $notif_msg = 'Status lamaran Anda telah diperbarui menjadi: ' . ucwords(str_replace('_',' ', $status));
        mysqli_query($conn, "INSERT INTO notifications (user_id, message) VALUES ('{$row2['user_id']}', '$notif_msg')");
    }
    echo 'success';
} else {
    error_log("Failed to update status: " . mysqli_error($conn));
    http_response_code(500);
    echo 'Gagal update status: ' . mysqli_error($conn);
} 