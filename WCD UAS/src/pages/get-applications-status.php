<?php
require_once '../db/connection.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT a.id, a.status FROM applications a WHERE a.user_id='$user_id'");
$applications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $applications[$row['id']] = $row['status'];
}
echo json_encode(['success' => true, 'applications' => $applications]); 