<?php
require_once '../db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_FILES['payment_proof'])) {
    $app_id = intval($_POST['application_id']);
    // Cek apakah user adalah employer (pemilik job)
    $app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, j.posted_by FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id='$app_id'"));
    if (!$app || $app['posted_by'] != $user_id) {
        http_response_code(403);
        exit('Forbidden');
    }
    // Proses upload file
    $file = $_FILES['payment_proof'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $targetDir = '../user/uploads/payment/';
    // Pastikan folder ada
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            http_response_code(500);
            exit('Gagal membuat folder upload: ' . $targetDir);
        }
    }
    if (!is_writable($targetDir)) {
        http_response_code(500);
        exit('Folder tidak bisa ditulis: ' . $targetDir);
    }
    $filename = 'payment_' . $app_id . '_' . time() . '.' . $ext;
    $target = $targetDir . $filename;
    error_log('USER: ' . $user_id . ' | APP: ' . $app_id . ' | POSTED_BY: ' . $app['posted_by']);
    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Update aplikasi: simpan bukti dan status
        $q = mysqli_query($conn, "UPDATE applications SET payment_proof='$filename', work_status='waiting_worker_confirmation' WHERE id='$app_id'");
        if (!$q) {
            error_log('UPDATE ERROR: ' . mysqli_error($conn));
        }
        echo 'success';
    } else {
        http_response_code(500);
        echo 'Upload failed';
    }
} else {
    http_response_code(400);
    echo 'Bad request';
} 