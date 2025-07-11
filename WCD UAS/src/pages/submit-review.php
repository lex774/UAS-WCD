<?php
require_once '../db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}
$reviewer_id = $_SESSION['user_id'];
$reviewee_id = isset($_POST['reviewee_id']) ? intval($_POST['reviewee_id']) : 0;
$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
if ($reviewee_id <= 0 || $job_id <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    http_response_code(400);
    echo 'Data tidak valid';
    exit();
}
// Cek reviewer adalah HRD (pemilik job)
$q = mysqli_query($conn, "SELECT posted_by FROM jobs WHERE id='$job_id'");
$row = mysqli_fetch_assoc($q);
if (!$row || $row['posted_by'] != $reviewer_id) {
    http_response_code(403);
    echo 'Anda bukan pemilik pekerjaan ini';
    exit();
}
// Cek status aplikasi harus 'lolos'
$app = mysqli_query($conn, "SELECT status FROM applications WHERE job_id='$job_id' AND user_id='$reviewee_id'");
$app_row = mysqli_fetch_assoc($app);
if (!$app_row || strtolower($app_row['status']) !== 'lolos') {
    http_response_code(400);
    echo 'Status aplikasi belum selesai (lolos)';
    exit();
}
// Cek sudah pernah review?
$review_check = mysqli_query($conn, "SELECT id FROM reviews WHERE reviewer_id='$reviewer_id' AND reviewee_id='$reviewee_id' AND job_id='$job_id'");
if ($review_check && mysqli_num_rows($review_check) > 0) {
    http_response_code(400);
    echo 'Sudah pernah memberi penilaian';
    exit();
}
// Simpan review
$ins = mysqli_query($conn, "INSERT INTO reviews (reviewer_id, reviewee_id, job_id, rating, comment) VALUES ('$reviewer_id', '$reviewee_id', '$job_id', '$rating', '".mysqli_real_escape_string($conn, $comment)."')");
if ($ins) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Gagal menyimpan penilaian';
} 