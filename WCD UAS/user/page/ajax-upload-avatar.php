<?php
session_start();
require_once '../../src/db/connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user_id = $_SESSION['user_id'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['avatar']['tmp_name'];
    $file_name = $_FILES['avatar']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($file_ext, $allowed_ext)) {
        $new_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $upload_path = $upload_dir . $new_name;
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $profile_picture = 'uploads/' . $new_name;
            mysqli_query($conn, "UPDATE users SET profile_picture='$profile_picture' WHERE id='$user_id'");
            $url = '/user/uploads/' . $new_name;
            echo json_encode(['success' => true, 'url' => $url]);
            exit;
        } else {
            error_log('UPLOAD ERROR: ' . $file_tmp . ' => ' . $upload_path);
            echo json_encode(['error' => 'Gagal mengupload file. Pastikan folder uploads bisa ditulis.']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Ekstensi file tidak didukung.']);
        exit;
    }
}
echo json_encode(['error' => 'Tidak ada file yang diupload.']);
