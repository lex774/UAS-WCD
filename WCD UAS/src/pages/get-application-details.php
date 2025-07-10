<?php
require_once '../db/connection.php';
session_start();

// Debug: Log request
error_log("get-application-details.php called with GET params: " . print_r($_GET, true));

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    if (isset($_GET['as']) && $_GET['as'] === 'json') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        echo '<div class="error-message">Anda harus login untuk melihat detail lamaran.</div>';
    }
    exit();
}

$user_id = $_SESSION['user_id'];
error_log("User ID: " . $user_id);

if (!isset($_GET['id'])) {
    error_log("No application ID provided");
    if (isset($_GET['as']) && $_GET['as'] === 'json') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID lamaran tidak ditemukan']);
    } else {
        echo '<div class="error-message">ID lamaran tidak ditemukan.</div>';
    }
    exit();
}

$app_id = intval($_GET['id']);
error_log("Application ID: " . $app_id);

$app_query = mysqli_query($conn, "SELECT a.*, j.title as job_title, j.company_name, j.posted_by, j.id as job_id FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id='$app_id'");

if (!$app_query) {
    error_log("Database query failed: " . mysqli_error($conn));
    echo '<div class="error-message">Database error: ' . mysqli_error($conn) . '</div>';
    exit();
}

$app = mysqli_fetch_assoc($app_query);
if (!$app) {
    error_log("Application not found for ID: " . $app_id);
    if (isset($_GET['as']) && $_GET['as'] === 'json') {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lamaran tidak ditemukan']);
    } else {
        echo '<div class="error-message">Lamaran tidak ditemukan untuk ID: ' . $app_id . '</div>';
    }
    exit();
}

error_log("Application found: " . print_r($app, true));

if ($app['user_id'] != $user_id && $app['posted_by'] != $user_id) {
    error_log("Access denied - user_id: $user_id, app_user_id: " . $app['user_id'] . ", posted_by: " . $app['posted_by']);
    if (isset($_GET['as']) && $_GET['as'] === 'json') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Anda tidak berhak melihat detail lamaran ini']);
    } else {
        echo '<div class="error-message">Anda tidak berhak melihat detail lamaran ini.</div>';
    }
    exit();
}

// Jika request dari dashboard user (AJAX), kembalikan JSON
if (isset($_GET['as']) && $_GET['as'] === 'json') {
    $response = [
        'success' => true,
        'application' => [
            'id' => $app['id'],
            'job_id' => $app['job_id'],
            'job_title' => htmlspecialchars($app['job_title']),
            'company_name' => htmlspecialchars($app['company_name']),
            'cover_letter' => nl2br(htmlspecialchars($app['cover_letter'])),
            'cv_file' => $app['cv_file'],
            'cv_filename' => basename($app['cv_file']),
            'status' => $app['status'],
            'applied_date' => date('d M Y', strtotime($app['applied_at'])),
            'expected_salary' => $app['expected_salary'],
            'availability' => $app['availability'],
            'additional_info' => $app['additional_info'] ? nl2br(htmlspecialchars($app['additional_info'])) : null
        ]
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Jika dari daftar pelamar (company/HRD), kembalikan HTML
error_log("Returning HTML for application details");

// Output detail lamaran (HTML)
echo '<div class="preview-lamaran-card">';
echo '<div class="lamaran-header">';
// Query nama & email pelamar
$user_query = mysqli_query($conn, "SELECT name, email FROM users WHERE id='".$app['user_id']."'");
$user = mysqli_fetch_assoc($user_query);
echo '<div class="pelamar-info"><i class="fa fa-user"></i> <b>Nama:</b> '.htmlspecialchars($user['name']).' <span style="margin-left:1.5rem;"><i class="fa fa-envelope"></i> <b>Email:</b> '.htmlspecialchars($user['email']).'</span></div>';
echo '<div class="lamaran-meta"><i class="fa fa-calendar"></i> <b>Tanggal Melamar:</b> '.date('d M Y', strtotime($app['applied_at'])).' <span style="margin-left:1.5rem;"><i class="fa fa-briefcase"></i> <b>Posisi:</b> '.htmlspecialchars($app['job_title']).'</span></div>';
echo '<div class="lamaran-status"><b>Status:</b> <span class="badge badge-status badge-'.htmlspecialchars($app['status']).'">'.htmlspecialchars($app['status']).'</span></div>';
echo '</div>';
// Cover Letter
if (!empty($app['cover_letter'])) {
    echo '<div class="lamaran-section"><h4><i class="fa fa-file-alt"></i> Cover Letter</h4><div class="lamaran-content">'.nl2br(htmlspecialchars($app['cover_letter'])).'</div></div>';
}
// Info Tambahan
if (!empty($app['expected_salary']) || !empty($app['availability']) || !empty($app['additional_info'])) {
    echo '<div class="lamaran-section"><h4><i class="fa fa-info-circle"></i> Info Tambahan</h4><ul style="margin:0 0 0 1.2em;">';
    if (!empty($app['expected_salary'])) echo '<li><b>Expected Salary:</b> '.htmlspecialchars($app['expected_salary']).'</li>';
    if (!empty($app['availability'])) echo '<li><b>Availability:</b> '.htmlspecialchars($app['availability']).'</li>';
    if (!empty($app['additional_info'])) echo '<li><b>Info Lain:</b> '.nl2br(htmlspecialchars($app['additional_info'])).'</li>';
    echo '</ul></div>';
}
// CV
if (!empty($app['cv_file'])) {
    $cv_url = '../../'.$app['cv_file'];
    echo '<div class="lamaran-section"><h4><i class="fa fa-file-pdf"></i> CV/Resume</h4>';
    echo '<a href="'.$cv_url.'" class="btn btn-primary btn-xs" target="_blank" style="margin-top:0.5rem;"><i class="fa fa-download"></i> Download CV</a>';
    echo '</div>';
}
echo '</div>';
?> 
<style>
/* Hapus baris berikut agar kembali ke default sebelum perbaikan scroll bar double */
/* .preview-lamaran-content, .preview-lamaran-modal {
    overflow: unset !important;
    max-height: unset !important;
} */
.preview-lamaran-card {
    background: #fff;
    border-radius: 1.1rem;
    box-shadow: 0 4px 24px rgba(99,102,241,0.10), 0 1.5px 6px rgba(0,0,0,0.04);
    padding: 2rem 2.2rem 1.5rem 2.2rem;
    margin: 1.2rem 0 1.5rem 0;
    position: relative;
    min-width: 260px;
    max-width: 98%;
    margin-left: auto;
    margin-right: auto;
}
@media (max-width: 700px) {
    .preview-lamaran-card { padding: 1.1rem 0.5rem 1rem 0.5rem; }
}
</style> 