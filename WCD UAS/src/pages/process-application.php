<?php
require_once '../db/connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $expected_salary = isset($_POST['expected_salary']) ? mysqli_real_escape_string($conn, $_POST['expected_salary']) : null;
    $availability = isset($_POST['availability']) ? mysqli_real_escape_string($conn, $_POST['availability']) : null;
    $additional_info = isset($_POST['additional_info']) ? mysqli_real_escape_string($conn, $_POST['additional_info']) : null;
    
    // Check if user is the job poster (prevent self-application)
    $job_query = mysqli_query($conn, "SELECT posted_by FROM jobs WHERE id = '$job_id'");
    $job = mysqli_fetch_assoc($job_query);
    
    if ($job && $job['posted_by'] == $user_id) {
        header('Location: job-details.php?id=' . $job_id . '&error=cannot_apply_own_job');
        exit();
    }
    
    // Validate required fields
    if (empty($description) || strlen($description) < 50) {
        header('Location: apply-job.php?id=' . $job_id . '&error=invalid_description');
        exit();
    }
    
    if (strlen($description) > 2000) {
        header('Location: apply-job.php?id=' . $job_id . '&error=description_too_long');
        exit();
    }
    
    // Handle CV file upload
    $cv_file = null;
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $allowed_extensions = ['pdf', 'doc', 'docx', 'txt'];
        $file_extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            $upload_dir = '../../user/uploads/cv/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $cv_filename = 'cv_' . $user_id . '_' . time() . '.' . $file_extension;
            $cv_path = $upload_dir . $cv_filename;
            
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $cv_path)) {
                $cv_file = 'user/uploads/cv/' . $cv_filename;
            } else {
                header('Location: apply-job.php?id=' . $job_id . '&error=upload_failed');
                exit();
            }
        } else {
            header('Location: apply-job.php?id=' . $job_id . '&error=invalid_file_type');
            exit();
        }
    } else {
        header('Location: apply-job.php?id=' . $job_id . '&error=no_cv_file');
        exit();
    }
    
    // Check if user already applied for this job
    $existing_application = mysqli_query($conn, "SELECT * FROM applications WHERE user_id = '$user_id' AND job_id = '$job_id'");
    if (mysqli_num_rows($existing_application) > 0) {
        header('Location: job-details.php?id=' . $job_id . '&error=already_applied');
        exit();
    }
    
    // Insert application
    $insert_query = "INSERT INTO applications (user_id, job_id, description, cv_file, status, applied_at) 
                     VALUES ('$user_id', '$job_id', '$description', '$cv_file', 'pending', NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        $application_id = mysqli_insert_id($conn);
        
        // Insert additional application data if provided
        if ($expected_salary || $availability || $additional_info) {
            $additional_data = [];
            
            if ($expected_salary) {
                $additional_data[] = "expected_salary = '$expected_salary'";
            }
            
            if ($availability) {
                $additional_data[] = "availability = '$availability'";
            }
            
            if ($additional_info) {
                $additional_data[] = "additional_info = '$additional_info'";
            }
            
            if (!empty($additional_data)) {
                $update_query = "UPDATE applications SET " . implode(', ', $additional_data) . " WHERE id = '$application_id'";
                mysqli_query($conn, $update_query);
            }
        }
        
        // Send notification to job poster (optional)
        $job_query = mysqli_query($conn, "SELECT j.*, u.email as poster_email, u.name as poster_name 
                                         FROM jobs j 
                                         JOIN users u ON j.posted_by = u.id 
                                         WHERE j.id = '$job_id'");
        $job = mysqli_fetch_assoc($job_query);
        
        if ($job) {
            // You can add email notification here
            // sendApplicationNotification($job['poster_email'], $job['poster_name'], $job['title']);
        }
        
        // Redirect with success message
        header('Location: job-details.php?id=' . $job_id . '&success=application_submitted');
        exit();
    } else {
        header('Location: apply-job.php?id=' . $job_id . '&error=submission_failed');
        exit();
    }
} else {
    // If not POST request, redirect to job list
    header('Location: work-list.php');
    exit();
}

// Function to send email notification (optional)
function sendApplicationNotification($poster_email, $poster_name, $job_title) {
    $subject = "Lamaran Baru untuk Pekerjaan: " . $job_title;
    $message = "Halo $poster_name,\n\n";
    $message .= "Anda menerima lamaran baru untuk pekerjaan: $job_title\n";
    $message .= "Silakan login ke dashboard untuk melihat detail lamaran.\n\n";
    $message .= "Salam,\nTim LocalLink";
    
    $headers = "From: noreply@locallink.com\r\n";
    $headers .= "Reply-To: noreply@locallink.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($poster_email, $subject, $message, $headers);
}
?> 