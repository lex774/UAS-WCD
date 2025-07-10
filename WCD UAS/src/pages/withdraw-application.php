<?php
require_once '../db/connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$application_id = isset($input['application_id']) ? (int)$input['application_id'] : 0;

if ($application_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

// Check if application exists and belongs to user
$check_query = "SELECT * FROM applications WHERE id = '$application_id' AND user_id = '$user_id'";
$check_result = mysqli_query($conn, $check_query);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Application not found']);
    exit();
}

$application = mysqli_fetch_assoc($check_result);

// Check if application can be withdrawn (only pending applications)
if ($application['status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot withdraw application that is not pending']);
    exit();
}

// Delete the application
$delete_query = "DELETE FROM applications WHERE id = '$application_id' AND user_id = '$user_id'";

if (mysqli_query($conn, $delete_query)) {
    echo json_encode(['success' => true, 'message' => 'Application withdrawn successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to withdraw application']);
}
?> 