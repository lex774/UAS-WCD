<?php
session_start();
include_once '../db/connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['job_title'];
    $brief = $_POST['brief_description'];
    $full = $_POST['full_description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $payment_type = $_POST['payment_type'];
    $amount = $_POST['amount'];
    $currency = $_POST['currency'];
    $requirements = $_POST['requirements'];
    $company = $_POST['company_name'];
    $company_email = $_POST['company_email'];
    $logo = null;
    if (isset($_FILES['company_logo_file']) && $_FILES['company_logo_file']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['company_logo_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $upload_dir = '../../user/uploads/logo/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $logo_filename = 'logo_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $logo_path = $upload_dir . $logo_filename;
            if (move_uploaded_file($_FILES['company_logo_file']['tmp_name'], $logo_path)) {
                $logo = 'user/uploads/logo/' . $logo_filename;
            } else {
                $error = 'Gagal mengunggah logo perusahaan.';
            }
        } else {
            $error = 'Format file logo tidak didukung (hanya JPG, PNG, GIF).';
        }
    }
    if (empty($title) || empty($brief) || empty($full) || empty($category) || empty($location) || empty($payment_type) || empty($amount) || empty($currency) || empty($requirements) || empty($company) || empty($company_email)) {
        $error = 'Semua field wajib diisi kecuali logo.';
    }

    if (empty($error)) {
        $user_id = $_SESSION['user_id'];
        
        // Escape semua input untuk mencegah SQL injection
        $title = mysqli_real_escape_string($conn, $title);
        $brief = mysqli_real_escape_string($conn, $brief);
        $full = mysqli_real_escape_string($conn, $full);
        $category = mysqli_real_escape_string($conn, $category);
        $location = mysqli_real_escape_string($conn, $location);
        $payment_type = mysqli_real_escape_string($conn, $payment_type);
        $amount = mysqli_real_escape_string($conn, $amount);
        $currency = mysqli_real_escape_string($conn, $currency);
        $requirements = mysqli_real_escape_string($conn, $requirements);
        $company = mysqli_real_escape_string($conn, $company);
        $company_email = mysqli_real_escape_string($conn, $company_email);
        
        $query = "INSERT INTO jobs (title, brief_description, full_description, category, location, payment_type, amount, currency, requirements, company_name, company_logo_url, company_email, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssssssssi", $title, $brief, $full, $category, $location, $payment_type, $amount, $currency, $requirements, $company, $logo, $company_email, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $job_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                header('Location: post-job-success.php?id=' . $job_id);
                exit();
            } else {
                $error = 'Gagal memasang lowongan: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = 'Gagal menyiapkan query: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasang Lowongan Baru - LocalFlow</title>
    <link rel="stylesheet" href="../styles/post-job.css">
</head>
<body>
    <!-- Main Content -->
    <main class="main-content">
        <a href="home-user.php" class="button button-outline mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            Kembali ke Beranda
        </a>
        <div class="container">
            <h1>Pasang Lowongan Baru</h1>
            <p>Isi detail di bawah ini untuk menemukan talenta yang tepat untuk proyek Anda.</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="job-title">Judul Pekerjaan</label>
                <input type="text" id="job-title" name="job_title" placeholder="misal: Dibutuhkan Web Developer Berpengalaman" required>
                <label for="brief-description">Deskripsi Singkat (maks 200 karakter)</label>
                <textarea id="brief-description" name="brief_description" rows="3" placeholder="Ringkasan singkat tentang pekerjaan." required></textarea>
                <label for="full-description">Deskripsi Lengkap</label>
                <textarea id="full-description" name="full_description" rows="5" placeholder="Informasi detail tentang pekerjaan, tanggung jawab, dan hasil yang diharapkan." required></textarea>
                <div class="input-group">
                    <div class="input-half">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <option value="" disabled selected>Pilih kategori</option>
                            <option value="Web Development">Pengembangan Web</option>
                            <option value="Graphic Design">Desain Grafis</option>
                            <option value="Content Writing">Penulisan Konten</option>
                            <option value="Digital Marketing">Pemasaran Digital</option>
                            <option value="Video Editing">Editing Video</option>
                        </select>
                    </div>
                    <div class="input-half">
                        <label for="location">Lokasi</label>
                        <input type="text" id="location" name="location" placeholder="misal: Jakarta, Indonesia atau Remote" required>
                    </div>
                </div>
                <div class="input-group">
                    <div class="input-third">
                        <label for="payment-type">Tipe Pembayaran</label>
                        <select id="payment-type" name="payment_type" required>
                            <option value="" disabled selected>Pilih tipe pembayaran</option>
                            <option value="fixed-price">Harga Tetap</option>
                            <option value="hourly">Per Jam</option>
                        </select>
                    </div>
                    <div class="input-third">
                        <label for="amount">Jumlah</label>
                        <input type="number" id="amount" name="amount" placeholder="misal: 500" required>
                    </div>
                    <div class="input-third">
                        <label for="currency">Mata Uang</label>
                        <input type="text" id="currency" name="currency" placeholder="IDR atau USD" required>
                    </div>
                </div>
                <label for="requirements">Persyaratan (satu per baris)</label>
                <textarea id="requirements" name="requirements" rows="3" placeholder="misal: Menguasai React\nKomunikasi yang baik\nWajib menyertakan portofolio" required></textarea>
                <label for="company-name">Nama Perusahaan</label>
                <input type="text" id="company-name" name="company_name" placeholder="Nama Perusahaan Anda" required>
                <label for="company-email">Email Perusahaan/HRD</label>
                <input type="email" id="company-email" name="company_email" placeholder="hrd@perusahaan.com" required>
                <label for="company-logo-file">Logo Perusahaan (Opsional, JPG/PNG/GIF)</label>
                <div id="logoUploadContainer" class="file-upload-container" tabindex="0">
                    <div class="file-upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <div class="file-upload-text">Klik atau seret file ke sini untuk mengunggah logo</div>
                    <div class="file-upload-hint">Hanya file gambar (JPG, PNG, GIF) maksimal 2MB</div>
                    <input type="file" id="company-logo-file" name="company_logo_file" class="file-input" accept="image/*">
                    <div id="logoUploadError" class="upload-error" style="display:none;"></div>
                    <div id="logoFilePreview" class="file-preview">
                        <div class="file-info">
                            <span class="file-icon">🖼️</span>
                            <div class="file-details">
                                <span id="logoFileName" class="file-name"></span>
                                <span id="logoFileSize" class="file-size"></span>
                            </div>
                            <button type="button" class="file-remove" onclick="removeLogoFile()">Hapus</button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Pasang Lowongan</button>
            </form>
        </div>
    </main>
    <!-- Footer -->
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2025 Quick Lance. Seluruh Hak Cipta Dilindungi.</p>
        </div>
    </footer>
    <link rel="stylesheet" href="../styles/apply-job.css">
    <script src="../scripts/post-job.js"></script>
</body>
</html> 