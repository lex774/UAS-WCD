<?php
require_once '../db/connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get job details
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: work-list.php');
    exit();
}

$job_id = mysqli_real_escape_string($conn, $_GET['id']);
$job_query = mysqli_query($conn, "SELECT * FROM jobs WHERE id = '$job_id'");
$job = mysqli_fetch_assoc($job_query);

if (!$job) {
    header('Location: work-list.php');
    exit();
}

// Check if user is the job poster (prevent self-application)
if ($job['posted_by'] == $user_id) {
    header('Location: job-details.php?id=' . $job_id . '&error=cannot_apply_own_job');
    exit();
}

// Get user details
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Check if user already applied for this job
$existing_application = mysqli_query($conn, "SELECT * FROM applications WHERE user_id = '$user_id' AND job_id = '$job_id'");
if (mysqli_num_rows($existing_application) > 0) {
    header('Location: job-details.php?id=' . $job_id . '&error=already_applied');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $expected_salary = isset($_POST['expected_salary']) ? mysqli_real_escape_string($conn, $_POST['expected_salary']) : null;
    $availability = isset($_POST['availability']) ? mysqli_real_escape_string($conn, $_POST['availability']) : null;
    $additional_info = isset($_POST['additional_info']) ? mysqli_real_escape_string($conn, $_POST['additional_info']) : null;
    
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
            }
        }
    }
    
    // Validate required fields
    if (empty($description) || strlen($description) < 50) {
        $error = "Deskripsi harus minimal 50 karakter.";
    } elseif (strlen($description) > 2000) {
        $error = "Deskripsi tidak boleh lebih dari 2000 karakter.";
    } elseif (!$cv_file) {
        $error = "CV harus diupload.";
    } else {
        // Insert application
        $insert_query = "INSERT INTO applications (user_id, job_id, cover_letter, cv_file, expected_salary, availability, additional_info, status, applied_at) 
                         VALUES ('$user_id', '$job_id', '$description', '$cv_file', " . 
                         ($expected_salary ? "'$expected_salary'" : "NULL") . ", " .
                         ($availability ? "'$availability'" : "NULL") . ", " .
                         ($additional_info ? "'$additional_info'" : "NULL") . ", " .
                         "'pending', NOW())";
        
        if (mysqli_query($conn, $insert_query)) {
            header('Location: job-details.php?id=' . $job_id . '&success=application_submitted');
            exit();
        } else {
            $error = "Gagal mengirim lamaran: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamar Pekerjaan - <?php echo htmlspecialchars($job['title']); ?> | LocalLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../user/style/dashboard.css">
    <link rel="stylesheet" href="../styles/apply-job.css">
</head>
<body>
    <div class="container">
        <a href="job-details.php?id=<?php echo $job_id; ?>" class="button button-outline mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            Kembali
        </a>
        <div class="application-form">
            <div class="form-header">
                <h1><i class="fa fa-paper-plane"></i> Lamar Pekerjaan</h1>
                <p>Lengkapi form di bawah ini untuk melamar pekerjaan ini</p>
            </div>
            
            <div class="form-content">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fa fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Job Summary -->
                <div class="job-summary">
                    <h3><i class="fa fa-briefcase"></i> Detail Pekerjaan</h3>
                    <div class="job-details">
                        <div class="job-detail">
                            <i class="fa fa-tag"></i>
                            <span><strong>Posisi:</strong> <?php echo htmlspecialchars($job['title']); ?></span>
                        </div>
                        <div class="job-detail">
                            <i class="fa fa-building"></i>
                            <span><strong>Perusahaan:</strong> <?php echo htmlspecialchars($job['company_name']); ?></span>
                        </div>
                        <div class="job-detail">
                            <i class="fa fa-map-marker-alt"></i>
                            <span><strong>Lokasi:</strong> <?php echo htmlspecialchars($job['location']); ?></span>
                        </div>
                        <div class="job-detail">
                            <i class="fa fa-money-bill-wave"></i>
                            <span><strong>Gaji:</strong> <?php echo number_format($job['amount'], 0, ',', '.'); ?> <?php echo $job['currency']; ?></span>
                        </div>
                        <div class="job-detail">
                            <i class="fa fa-clock"></i>
                            <span><strong>Jenis Pembayaran:</strong> <?php echo htmlspecialchars($job['payment_type']); ?></span>
                        </div>
                        <div class="job-detail">
                            <i class="fa fa-calendar"></i>
                            <span><strong>Diposting:</strong> <?php echo date('d M Y', strtotime($job['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- User Information -->
                <div class="user-info">
                    <h4><i class="fa fa-user"></i> Informasi Anda</h4>
                    <div class="user-details">
                        <div class="user-detail">
                            <i class="fa fa-user-circle"></i>
                            <span><strong>Nama:</strong> <?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                        <div class="user-detail">
                            <i class="fa fa-envelope"></i>
                            <span><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="user-detail">
                            <i class="fa fa-calendar-alt"></i>
                            <span><strong>Bergabung:</strong> <?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $job_id; ?>" enctype="multipart/form-data">
                    <!-- CV Upload Section -->
                    <div class="form-section">
                        <h3><i class="fa fa-file-upload"></i> Upload CV/Resume</h3>
                        <div class="form-group">
                            <label for="cv_file" class="form-label">
                                CV/Resume <span class="required">*</span>
                            </label>
                            <div class="file-upload-container" id="fileUploadContainer">
                                <input type="file" id="cv_file" name="cv_file" class="file-input" accept=".pdf,.doc,.docx,.txt" required>
                                <div class="file-upload-icon">
                                    <i class="fa fa-cloud-upload-alt"></i>
                                </div>
                                <div class="file-upload-text">
                                    <strong>Klik untuk memilih file</strong> atau drag & drop
                                </div>
                                <div class="file-upload-hint">
                                    Format yang didukung: PDF, DOC, DOCX, TXT (Max: 5MB)
                                </div>
                            </div>
                            <div class="file-preview" id="filePreview">
                                <div class="file-info">
                                    <div class="file-icon">
                                        <i class="fa fa-file-alt"></i>
                                    </div>
                                    <div class="file-details">
                                        <div class="file-name" id="fileName"></div>
                                        <div class="file-size" id="fileSize"></div>
                                    </div>
                                    <button type="button" class="file-remove" onclick="removeFile()">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="upload-error" id="uploadError"></div>
                            <div class="upload-success" id="uploadSuccess"></div>
                        </div>
                    </div>
                    
                    <!-- Description Section -->
                    <div class="form-section">
                        <h3><i class="fa fa-edit"></i> Deskripsi Diri</h3>
                        <div class="form-group">
                            <label for="description" class="form-label">
                                Deskripsi Diri <span class="required">*</span>
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                class="form-input form-textarea" 
                                placeholder="Jelaskan mengapa Anda cocok untuk posisi ini, pengalaman yang relevan, dan mengapa Anda tertarik dengan perusahaan ini..."
                                required
                                maxlength="2000"
                            ></textarea>
                            <div class="character-count">
                                <span id="char-count">0</span> / 2000 karakter
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information Section -->
                    <div class="form-section">
                        <h3><i class="fa fa-info-circle"></i> Informasi Tambahan</h3>
                        <div class="form-group">
                            <label for="expected_salary" class="form-label">
                                Gaji yang Diharapkan
                            </label>
                            <input 
                                type="number" 
                                id="expected_salary" 
                                name="expected_salary" 
                                class="form-input" 
                                placeholder="Masukkan gaji yang Anda harapkan (opsional)"
                                min="0"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="availability" class="form-label">
                                Ketersediaan Mulai Kerja
                            </label>
                            <select id="availability" name="availability" class="form-input">
                                <option value="">Pilih ketersediaan</option>
                                <option value="immediate">Segera</option>
                                <option value="1_week">1 minggu</option>
                                <option value="2_weeks">2 minggu</option>
                                <option value="1_month">1 bulan</option>
                                <option value="negotiable">Dapat dinegosiasikan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="additional_info" class="form-label">
                                Informasi Tambahan
                            </label>
                            <textarea 
                                id="additional_info" 
                                name="additional_info" 
                                class="form-input" 
                                placeholder="Informasi tambahan yang ingin Anda sampaikan (opsional)"
                                rows="3"
                                maxlength="500"
                            ></textarea>
                            <div class="character-count">
                                <span id="additional-char-count">0</span> / 500 karakter
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i>
                            Kirim Lamaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2025 LocalLink. Seluruh Hak Cipta Dilindungi.</p>
        </div>
    </footer>
    <script src="../scripts/apply-job.js"></script>
</body>
</html> 