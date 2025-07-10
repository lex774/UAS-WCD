<?php
require_once '../../src/db/connection.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'error'=>'Unauthorized']);
    exit();
}
$user_id = $_SESSION['user_id'];
if (isset($_GET['all']) && $_GET['all'] == '1') {
    // Hapus semua notifikasi user
    mysqli_query($conn, "DELETE FROM notifications WHERE user_id='$user_id'");
    echo json_encode(['success'=>true]);
    exit();
}
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM notifications WHERE id='$id' AND user_id='$user_id'");
    echo json_encode(['success'=>true]);
    exit();
}
echo json_encode(['success'=>false, 'error'=>'No action']); 