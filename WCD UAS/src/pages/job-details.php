<?php
session_start();
include_once '../db/connection.php';

// Ambil ID pekerjaan dari URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    header('Location: work-list.php');
    exit();
}

// Ambil detail pekerjaan dari database
$query = "SELECT j.*, u.name as posted_by_name, u.email as posted_by_email, u.profile_picture as posted_by_picture 
          FROM jobs j 
          LEFT JOIN users u ON j.posted_by = u.id 
          WHERE j.id = $job_id";
$result = mysqli_query($conn, $query);
$job = mysqli_fetch_assoc($result);

// Inisialisasi variabel display untuk menghindari error undefined
$category_display = !empty($job['category']) ? $job['category'] : '-';
$payment_display = !empty($job['payment_type']) ? $job['payment_type'] : '-';
$created_date = !empty($job['created_at']) ? date('d M Y', strtotime($job['created_at'])) : '-';

// Inisialisasi requirements_array jika ada field requirements
$requirements_array = [];
if (!empty($job['requirements'])) {
    $requirements_array = preg_split('/\r\n|\r|\n|,/', $job['requirements']);
    $requirements_array = array_filter(array_map('trim', $requirements_array));
}

if (!$job) {
    header('Location: work-list.php');
    exit();
}

// Ambil data user jika sudah login
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT name, email, profile_picture FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($user_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Job Details</title>
    <link rel="stylesheet" href="../styles/job-details.css">
    <style>
    /* Badge Status Styling - Konsisten dengan dashboard */
    .badge-status {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        display: inline-block;
        margin-right: 0.5rem;
        transition: all 0.2s ease-in-out;
    }

    .badge-open {
        background-color: #6366f1;
        color: #ffffff;
    }

    .badge-closed {
        background-color: #6b7280;
        color: #ffffff;
    }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <a href="work-list.php" class="button button-outline button-back mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M15 18l-6-6 6-6"/></svg>
            Kembali ke Daftar Pekerjaan
        </a>
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <div class="lg:col-span-2">
                <div class="card shadow-lg mb-8">
                    <div class="card-header">
                        <img 
                            src="<?php echo !empty($job['company_logo_url']) ? htmlspecialchars($job['company_logo_url']) : 'https://placehold.co/64x64/6366f1/white/png'; ?>" 
                            alt="<?php echo htmlspecialchars($job['company_name'] ?: 'Company'); ?> logo"
                            width="64"
                            height="64"
                            class="company-logo"
                        />
                        <h1 class="font-headline text-3xl font-bold tracking-tight"><?php echo htmlspecialchars($job['title']); ?></h1>
                        <p class="text-lg text-muted-foreground"><?php echo htmlspecialchars($job['company_name'] ?: 'Perusahaan'); ?></p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="badge badge-secondary"><?php echo htmlspecialchars($category_display); ?></span>
                            <span class="badge badge-outline flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg> 
                                <?php echo htmlspecialchars($job['location']); ?>
                            </span>
                            <span class="badge badge-outline flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> 
                                <?php echo htmlspecialchars($payment_display); ?>
                            </span>
                            <span class="badge badge-outline flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> 
                                Diposting pada <?php echo $created_date; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-content">
                        <hr class="separator my-6" />
                        
                        <?php if (!empty($job['brief_description'])): ?>
                        <h2 class="font-headline text-xl font-semibold mb-3">Deskripsi Singkat</h2>
                        <p class="text-foreground-90 leading-relaxed mb-6">
                            <?php echo htmlspecialchars($job['brief_description']); ?>
                        </p>
                        <hr class="separator my-6" />
                        <?php endif; ?>
                        
                        <h2 class="font-headline text-xl font-semibold mb-3">Deskripsi Pekerjaan</h2>
                        <p class="text-foreground-90 whitespace-pre-line leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($job['full_description'])); ?>
                        </p>
                        
                        <?php if (!empty($requirements_array)): ?>
                        <hr class="separator my-6" />
                        <h2 class="font-headline text-xl font-semibold mb-3">Persyaratan</h2>
                        <ul class="list-disc list-inside space-y-1 text-foreground-90">
                            <?php foreach ($requirements_array as $requirement): ?>
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon mr-2 text-primary flex-shrink-0 mt-0.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> 
                                <span><?php echo htmlspecialchars(trim($requirement)); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        
                        <hr class="separator my-6" />
                        
                        <?php
                        // Check if user is logged in and if they already applied
                        $already_applied = false;
                        $is_job_poster = false;
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            $check_application = mysqli_query($conn, "SELECT * FROM applications WHERE user_id = '$user_id' AND job_id = '$job_id'");
                            $already_applied = mysqli_num_rows($check_application) > 0;
                            $is_job_poster = ($job['posted_by'] == $user_id);
                        }
                        ?>
                        
                        <?php if (isset($_GET['success']) && $_GET['success'] === 'application_submitted'): ?>
                            <div class="success-message">
                                <i class="fa fa-check-circle"></i>
                                Lamaran Anda berhasil dikirim! Kami akan menghubungi Anda segera.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error']) && $_GET['error'] === 'already_applied'): ?>
                            <div class="error-message">
                                <i class="fa fa-exclamation-circle"></i>
                                Anda sudah melamar pekerjaan ini sebelumnya.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error']) && $_GET['error'] === 'cannot_apply_own_job'): ?>
                            <div class="error-message">
                                <i class="fa fa-exclamation-circle"></i>
                                Anda tidak dapat melamar pekerjaan yang Anda posting sendiri.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="apply-section">
                                <a href="../../user/page/login.php" class="button button-lg w-full sm:w-auto text-lg">
                                    <i class="fa fa-sign-in-alt"></i>
                                    Login untuk Melamar
                                </a>
                                <p class="text-xs text-muted-foreground mt-2">Anda harus login terlebih dahulu untuk melamar pekerjaan ini.</p>
                            </div>
                        <?php elseif ($is_job_poster): ?>
                            <div class="apply-section">
                                <div class="error-message">
                                    <i class="fa fa-exclamation-circle"></i>
                                    Anda tidak dapat melamar pekerjaan yang Anda posting sendiri.
                                </div>
                                <a href="../../user/page/dashboard.php" class="button button-outline button-lg w-full sm:w-auto text-lg mt-4">
                                    <i class="fa fa-dashboard"></i>
                                    Lihat Dashboard
                                </a>
                            </div>
                        <?php elseif ($already_applied): ?>
                            <div class="applied-section">
                                <div class="success-message">
                                    <i class="fa fa-check-circle"></i>
                                    Anda sudah melamar pekerjaan ini
                                </div>
                                <a href="../../user/page/dashboard.php" class="button button-outline button-lg w-full sm:w-auto text-lg mt-4">
                                    <i class="fa fa-dashboard"></i>
                                    Lihat Status Lamaran
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="apply-section">
                                <a href="apply-job.php?id=<?php echo $job_id; ?>" class="button button-lg w-full sm:w-auto text-lg">
                                    <i class="fa fa-paper-plane"></i>
                                    Lamar Pekerjaan Ini
                                </a>
                                <p class="text-xs text-muted-foreground mt-2">Klik tombol di atas untuk mengisi form lamaran.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6 mt-8 lg:mt-0">
                <div class="card shadow-md mb-8">
                    <div class="card-header">
                        <h2 class="card-title font-headline text-xl">Tentang Pengirim</h2>
                    </div>
                    <div class="card-content flex flex-col items-center text-center sm:flex-row sm:text-left sm:items-start gap-4">
                        <div class="avatar">
                            <?php if (!empty($job['posted_by_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($job['posted_by_picture']); ?>" alt="<?php echo htmlspecialchars($job['posted_by_name']); ?> Avatar" class="avatar-image" />
                            <?php else: ?>
                                <div class="avatar-fallback"><?php echo strtoupper(substr($job['posted_by_name'], 0, 2)); ?></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($job['posted_by_name']); ?></h3>
                            <p class="text-sm text-muted-foreground"><?php echo htmlspecialchars($job['posted_by_email']); ?></p>
                            <a href="../../user/page/profile.php?id=<?php echo $job['posted_by']; ?>" class="button button-outline button-sm mt-2">
                                Lihat Profil
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-md mb-8">
                    <div class="card-header">
                        <h2 class="card-title font-headline text-xl">Ringkasan Pekerjaan</h2>
                    </div>
                    <div class="card-content space-y-2 text-sm">
                        <div class="flex items-center">
                            <strong class="mr-2">Status:</strong>
                            <span class="badge badge-<?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'open' : 'closed'; ?> badge-status">
                                <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'Terbuka' : 'Tertutup'; ?>
                            </span>
                        </div>
                        <p><strong>Kategori:</strong> <?php echo htmlspecialchars($category_display); ?></p>
                        <p><strong>Lokasi:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                        <p><strong>Pembayaran:</strong> <?php echo htmlspecialchars($payment_display); ?></p>
                        <?php if (!empty($job['company_name'])): ?>
                        <p><strong>Perusahaan:</strong> <?php echo htmlspecialchars($job['company_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2025 Quick Lance. Seluruh Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html> 