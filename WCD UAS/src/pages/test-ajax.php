<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'AJAX test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'test' => $_GET['test'] ?? 'no test parameter'
]);
?> 