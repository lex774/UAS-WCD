<?php
require_once '../db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['new_status'])) {
    $app_id = intval($_POST['app_id']);
    $new_status = $_POST['new_status'];
    $allowed = ['dikerjakan','sudah_dikerjakan','reviewed','sudah_direview','waiting_worker_confirmation','paid'];
    
    if (in_array($new_status, $allowed)) {
        // Map new_status to work_status
        $work_status_map = [
            'dikerjakan' => 'in_progress',
            'sudah_dikerjakan' => 'completed',
            'reviewed' => 'reviewed',
            'sudah_direview' => 'sudah_direview',
            'waiting_worker_confirmation' => 'waiting_worker_confirmation',
            'paid' => 'paid'
        ];
        
        $work_status = $work_status_map[$new_status];

        // Ambil data aplikasi
        $app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE id='$app_id'"));
        if (!$app) {
            http_response_code(404);
            exit('Application not found');
        }

        if ($new_status === 'reviewed' || $new_status === 'sudah_direview' || $new_status === 'waiting_worker_confirmation') {
            // Hanya employer yang boleh update status ini
            $job = mysqli_fetch_assoc(mysqli_query($conn, "SELECT posted_by FROM jobs WHERE id='{$app['job_id']}'"));
            if ($job && $job['posted_by'] == $user_id) {
                $q = mysqli_query($conn, "UPDATE applications SET work_status='$work_status' WHERE id='$app_id'");
            } else {
                http_response_code(403);
                exit('Forbidden');
            }
        } else {
            // Hanya pekerja yang boleh update progress
            if ($app['user_id'] == $user_id) {
                $q = mysqli_query($conn, "UPDATE applications SET work_status='$work_status' WHERE id='$app_id'");
            } else {
                http_response_code(403);
                exit('Forbidden');
            }
        }

        if (isset($q) && $q) {
            echo 'success';
        } else {
            http_response_code(500);
            echo 'DB error';
        }
    } else {
        http_response_code(400);
        echo 'Invalid status';
    }
} else {
    http_response_code(400);
    echo 'Bad request';
} 