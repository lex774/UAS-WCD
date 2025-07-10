<?php
require_once '../../src/db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "SELECT name, email, profile_picture, bio, specializations FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
$jobs_posted = mysqli_query($conn, "SELECT * FROM jobs WHERE posted_by='$user_id' ORDER BY created_at DESC");
$jobs_applied = mysqli_query($conn, "SELECT a.*, j.title, j.company_name, j.id as job_id, j.company_email FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.user_id='$user_id' ORDER BY a.applied_at DESC");
$reviews_received = mysqli_query($conn, "SELECT r.*, j.title, u.name as reviewer_name FROM reviews r JOIN jobs j ON r.job_id = j.id JOIN users u ON r.reviewer_id = u.id WHERE r.reviewee_id='$user_id' ORDER BY r.created_at DESC");

// Ambil notifikasi untuk user
$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id='$user_id' AND is_read=0 ORDER BY created_at DESC");
$notifications = [];
while ($n = mysqli_fetch_assoc($notif_query)) {
    $notifications[] = $n;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Halaman Profil - LocalFlow</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/jobs.css">
    <style>
.badge-status-menunggu {
    background: #f3f4f6; color: #6366f1; border: 1.5px solid #6366f1;
}
.badge-status-direview {
    background: #f0f9ff; color: #0ea5e9; border: 1.5px solid #0ea5e9;
}
.badge-status-terseleksi {
    background: #f0fdf4; color: #22c55e; border: 1.5px solid #22c55e;
}
.badge-status-interview {
    background: #fef9c3; color: #eab308; border: 1.5px solid #eab308;
}
.badge-status-lolos {
    background: #f0fdf4; color: #16a34a; border: 1.5px solid #16a34a;
}
.badge-status-tidak_terseleksi, .badge-status-tidak_lolos {
    background: #fef2f2; color: #ef4444; border: 1.5px solid #ef4444;
}
.badge-status {
    border-radius: 0.6rem;
    padding: 0.38rem 1.1rem;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    margin-left: 0.3rem;
}
.btn-status-detail.badge-open {
    background: #f3f4f6; color: #6366f1; border-color: #6366f1;
}
.btn-status-detail.badge-closed {
    background: #fef2f2; color: #ef4444; border-color: #ef4444;
}
.btn-status-detail:hover, .btn-status-detail:focus {
    filter: brightness(0.97) contrast(1.1);
    box-shadow: 0 2px 8px rgba(99,102,241,0.10);
    transform: translateY(-2px) scale(1.03);
    text-decoration: none;
}
.badge-status,
.btn-status-detail {
    padding: 0.22rem 0.8rem !important;
    font-size: 0.92rem !important;
    border-radius: 0.45rem !important;
}
.preview-lamaran-modal, .preview-lamaran-content {
    font-size: 0.97rem !important;
    line-height: 1.5;
    color: #333;
}
.preview-lamaran-modal h3, .preview-lamaran-content h3 {
    font-size: 1.08rem !important;
    margin-bottom: 0.7rem;
}
.preview-lamaran-modal p, .preview-lamaran-content p {
    font-size: 0.97rem !important;
    margin-bottom: 0.5rem;
}
.preview-modal-close {
    position: absolute;
    top: 12px;
    right: 18px;
    cursor: pointer;
    font-size: 1.7rem;
    color: #6366f1;
    background: none;
    border: none;
    z-index: 2;
    transition: color 0.2s;
}
.preview-modal-close:hover {
    color: #ef4444;
    background: none !important;
    transform: none !important;
    box-shadow: none !important;
}
.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 60px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-radius: 0.5rem;
    min-width: 160px;
    z-index: 1001;
    animation: fadeInDropdown 0.2s ease-in-out;
}
.dropdown-menu.show {
    display: block;
}
</style>
</head>
<body>
<header class="main-nav">
    <div class="header-content container">
         <div class="brand">
            <img src="https://placehold.co/40x40/6366f1/white/png"  alt="LocalLink Logo" class="logo">
            <span class="app-name">LocalLink</span>
         </div>
        <div class="nav-content container">
            <ul>
                <li><a href="../../src/pages/home-user.php" class="nav-link">Beranda</a></li>
                <li><a href="../../src/pages/work-list.php" class="nav-link">Cari Pekerjaan</a></li>
                <li><a href="../../src/pages/post-job.php" class="nav-link">Pasang Lowongan</a></li>
            </ul>
        </div>
        <div class="profile-menu" style="display:flex;align-items:center;gap:1.2rem;position:relative;">
            <span id="notifIcon" class="notif-bell" style="cursor:pointer;position:relative;font-size:1.5rem;color:#6366f1;">
                <i class="fa fa-bell"></i>
                <span id="notifCount" class="notif-badge" style="display:<?php echo count($notifications) ? 'inline-block' : 'none'; ?>;">
                    <?php echo count($notifications); ?>
                </span>
            </span>
            <?php if ($user && !empty($user['profile_picture']) && file_exists(__DIR__ . '/../../user/' . $user['profile_picture'])): ?>
                <img src="<?php echo '../../user/' . htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" class="profile-avatar" id="profileAvatar" style="width:40px;height:40px;border-radius:50%;cursor:pointer;object-fit:cover;">
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="../../user/page/dashboard.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13h2v-2a7 7 0 0 1 14 0v2h2"/><rect width="18" height="8" x="3" y="13" rx="2"/></svg>
                        Dashboard
                    </a>
                    <a href="../../user/page/edit-profile.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
                        Edit Profil
                    </a>
                    <a href="../../user/page/settings.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0-.33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09A1.65 1.65 0 0 0 11 3.09V3a2 2 0 0 1 4 0v.09c0 .66.26 1.3.73 1.77.47.47 1.11.73 1.77.73h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06-.06a1.65 1.65 0 0 0-.33-1.82v.09c0 .66-.26 1.3-.73 1.77-.47.47-1.11.73-1.77.73h-.09a1.65 1.65 0 0 0-1.82.33l-.06-.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82V3a2 2 0 0 1 4 0v.09z"/></svg>
                        Pengaturan
                    </a>
                    <a href="../../user/page/logout.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                        Keluar
                    </a>
                </div>
            <?php else: ?>
                <span style="position:relative;display:inline-block;width:40px;height:40px;vertical-align:middle;">
                    <i class="fa fa-user-circle profile-avatar" id="profileAvatar" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:2.5rem;color:#bdbdbd;width:40px;height:40px;cursor:pointer;"></i>
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="../../user/page/dashboard.php" class="dropdown-item">
                            <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13h2v-2a7 7 0 0 1 14 0v2h2"/><rect width="18" height="8" x="3" y="13" rx="2"/></svg>
                            Dashboard
                        </a>
                        <a href="../../user/page/edit-profile.php" class="dropdown-item">
                            <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
                            Edit Profil
                        </a>
                        <a href="../../user/page/settings.php" class="dropdown-item">
                            <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0-.33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09A1.65 1.65 0 0 0 11 3.09V3a2 2 0 0 1 4 0v.09c0 .66.26 1.3.73 1.77.47.47 1.11.73 1.77.73h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06-.06a1.65 1.65 0 0 0-.33-1.82v.09c0 .66-.26 1.3-.73 1.77-.47.47-1.11.73-1.77.73h-.09a1.65 1.65 0 0 0-1.82.33l-.06-.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82V3a2 2 0 0 1 4 0v.09z"/></svg>
                            Pengaturan
                        </a>
                        <a href="../../user/page/logout.php" class="dropdown-item">
                            <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                            Keluar
                        </a>
                    </div>
                </span>
            <?php endif; ?>
            <div id="notifPanel" class="notif-panel" style="display:none;z-index:1102;">
                <button id="notifCloseBtn" style="position:absolute;top:10px;right:14px;background:none;border:none;font-size:1.3rem;color:#6366f1;cursor:pointer;z-index:1200;" title="Tutup"><i class="fa fa-times"></i></button>
                <div class="notif-panel-header" style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 1rem 0.5rem 1rem;border-bottom:1px solid #eee;font-weight:600;">
                    <span>Notifikasi</span>
                    <button id="notifMarkAll" class="notif-markall" style="background:none;border:none;color:#6366f1;cursor:pointer;font-size:0.95rem;">Tandai semua sudah dibaca</button>
                </div>
                <ul id="notifList" style="list-style:none;margin:0;padding:0;max-height:320px;overflow-y:auto;">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notif): ?>
                        <li class="notif-item" id="notif-<?php echo $notif['id']; ?>" style="display:flex;align-items:flex-start;gap:0.7rem;padding:0.7rem 1rem;border-bottom:1px solid #f3f4f6;">
                            <span style="color:#6366f1;"><i class="fa fa-bell"></i></span>
                            <div class="notif-msg" style="flex:1;color:#333;">
                                <?php echo htmlspecialchars($notif['message']); ?>
                                <div class="notif-time" style="font-size:0.85em;color:#aaa;margin-top:2px;">
                                    <?php echo date('d M Y H:i', strtotime($notif['created_at'])); ?>
                                </div>
                            </div>
                            <button class="notif-delete" onclick="deleteNotif(<?php echo $notif['id']; ?>)" style="background:none;border:none;color:#ef4444;font-size:1.1rem;cursor:pointer;margin-left:0.5rem;" title="Hapus"><i class="fa fa-times"></i></button>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="padding:1.2rem;text-align:center;color:#aaa;">Tidak ada notifikasi baru.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</header>
    <div class="container">
        <!-- Profile Section -->
        <section class="profile-section" style="margin-bottom: 2rem;">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
                        <img src="<?php echo '../' . htmlspecialchars($user['profile_picture']); ?>" alt="Avatar" class="avatar" onclick="showAvatarModal('<?php echo '../' . htmlspecialchars($user['profile_picture']); ?>');">
                    <?php else: ?>
                        <i class="fa fa-user-circle" onclick="window.location.href='edit-profile.php'"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name" id="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="profile-bio" id="profile-bio" style="margin-top:8px;color:#555;">
                        <?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : '<span class="profile-placeholder">Belum menambahkan bio.</span>'; ?>
                    </p>
                    <p class="profile-spec" id="profile-spec" style="margin-top:4px;color:#555;">
                        <strong>Spesialisasi:</strong> <?php echo !empty($user['specializations']) ? htmlspecialchars($user['specializations']) : '<span class="profile-placeholder">Belum menambahkan spesialisasi.</span>'; ?>
                    </p>
                </div>
                <div class="profile-actions">
                    <a href="edit-profile.php" class="btn btn-primary" >Edit Profil</a>
                </div>
                <div class="profile-actions">
                    <a href="settings.php" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                            <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319-.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                          </svg>
                    </a>
                </div>
            </div>
        </section>
        <!-- User Statistics -->
        <section class="stats-section">
            <div class="stat-item" data-target="jobs-posted">
                <p class="stat-label">Pekerjaan Diposting</p>
            </div>
            <div class="stat-item" data-target="platform-work">
                <p class="stat-label">Pekerjaan Platform</p>
            </div>
            <div class="stat-item" data-target="ratings-received">
                <p class="stat-label">Penilaian Diterima</p>
            </div>
        </section>
        <!-- Pekerjaan Diposting -->
        <div id="jobs-posted" class="card-content hidden">
            <h4>Pekerjaan yang Anda Posting</h4>
            <?php if (mysqli_num_rows($jobs_posted) === 0): ?>
                <p>Anda belum memposting pekerjaan apapun.</p>
            <?php else: ?>
                <ul class="job-list">
                <?php while ($job = mysqli_fetch_assoc($jobs_posted)): ?>
                    <li class="job-card" data-job-id="<?php echo $job['id']; ?>">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                </div>
                                <button type="button" 
                                        class="btn btn-status <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'btn-status-close' : 'btn-status-open'; ?>"
                                        onclick="updateJobStatus(<?php echo $job['id']; ?>, '<?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'TERTUTUP' : 'TERBUKA'; ?>')">
                                    <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'Tutup Lowongan' : 'Buka Lowongan'; ?>
                                </button>
                            </div>
                            <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.7rem;">
    <strong style="color: #555; font-size: 0.9rem;">Status:</strong>
    <span class="badge badge-status <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'badge-open' : 'badge-closed'; ?>">
        <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'Terbuka' : 'Tertutup'; ?>
    </span>
    <a href="../../src/pages/job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-status-detail <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'badge-open' : 'badge-closed'; ?>" style="margin-left:0.2rem;display:inline-flex;align-items:center;gap:0.4rem;font-weight:600;padding:0.38rem 1.1rem;font-size:1rem;border-radius:0.6rem;border:1.5px solid;box-shadow:0 1px 4px rgba(0,0,0,0.04);text-decoration:none;">
        <i class="fa fa-eye"></i> Lihat Detail
    </a>
</div>
                        </div>
                        <div class="meta">
                            <span><?php echo date('d M Y', strtotime($job['created_at'])); ?></span>
                            <span><?php echo htmlspecialchars($job['company_name']); ?></span>
                        </div>
                        <div class="job-card-actions" style="display: flex; flex-direction: row; gap: 0.5rem; align-items: center; margin-top: 0.7rem;">
                            <a href="../../src/pages/applicants-list.php?job_id=<?php echo $job['id']; ?>" class="btn btn-outline btn-xs job-action-btn">
                                <i class="fa fa-users"></i> Lihat Pelamar
                            </a>
                            <button class="btn btn-delete-job job-action-btn" onclick="deleteJob(<?php echo $job['id']; ?>, this)" title="Hapus Lowongan" style="margin-left:auto;">
                                <i class="fa fa-trash delete-icon"></i>
                            </button>
                        </div>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
        <!-- Pekerjaan Platform -->
        <div id="platform-work" class="card-content hidden">
            <h4>Pekerjaan yang Anda Lamar</h4>
            <?php if (mysqli_num_rows($jobs_applied) === 0): ?>
                <p>Anda belum melamar pekerjaan apapun.</p>
            <?php else: ?>
                <ul class="job-list">
                <?php while ($app = mysqli_fetch_assoc($jobs_applied)): ?>
                    <li class="job-card" data-app-id="<?php echo $app['id']; ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($app['title']); ?></strong>
                            <span class="badge badge-outline"><?php echo htmlspecialchars($app['company_name']); ?></span>
                            <span class="badge badge-status badge-status-<?php echo htmlspecialchars($app['status']); ?>">
    <?php echo $status_icons[$app['status']] ?? ''; ?> <?php echo $status_label_map[$app['status']] ?? strtoupper($app['status']); ?>
</span>
                        </div>
                        <div class="meta">
                            <span>Lamaran: <?php echo date('d M Y', strtotime($app['applied_at'])); ?></span>
                            <?php if (!empty($app['description'])): ?>
                                <span class="description-preview">
                                    <?php echo substr(htmlspecialchars($app['description']), 0, 100) . (strlen($app['description']) > 100 ? '...' : ''); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="application-actions">
                            <button class="btn btn-outline btn-sm" onclick="viewApplicationDetails(<?php echo $app['id']; ?>)">
                                <i class="fa fa-eye"></i> Lihat Lamaran
                            </button>
                            <?php if (!empty($app['company_email'])): ?>
                                <button class="btn-contact-hrd" style="margin-left: 0.5rem;" onclick="showEmailModal('<?php echo htmlspecialchars($app['company_email']); ?>')">
                                    <i class="fa fa-envelope"></i> Contact HRD
                                </button>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
        <!-- Penilaian Diterima -->
        <div id="ratings-received" class="card-content hidden">
            <h4>Penilaian yang Anda Terima</h4>
            <?php if (mysqli_num_rows($reviews_received) === 0): ?>
                <p>Anda belum menerima penilaian apapun.</p>
            <?php else: ?>
                <ul class="review-list">
                <?php while ($rev = mysqli_fetch_assoc($reviews_received)): ?>
                    <li class="review-card">
                        <div>
                            <strong><?php echo htmlspecialchars($rev['reviewer_name']); ?></strong>
                            <span class="badge badge-outline"><?php echo htmlspecialchars($rev['title']); ?></span>
                            <span class="rating">
                                <?php for ($i=0; $i<$rev['rating']; $i++) echo '★'; ?><?php for ($i=$rev['rating']; $i<5; $i++) echo '☆'; ?>
                            </span>
                        </div>
                        <div class="meta">
                            <span><?php echo date('d M Y', strtotime($rev['created_at'])); ?></span>
                        </div>
                        <div class="comment"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></div>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if (!empty($notifications)): ?>
<div class="notif" style="display:none;"></div>
<?php endif; ?>
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2025 LocalLink. Seluruh Hak Cipta Dilindungi.</p>
        </div>
    </footer>
    <!-- Modal Fullscreen Avatar -->
    <div id="avatarModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
        <span id="closeAvatarModal" style="position:absolute;top:30px;right:40px;font-size:2.5rem;color:#fff;cursor:pointer;z-index:10001;">&times;</span>
        <img id="avatarModalImg" src="" alt="Foto Profil" style="max-width:90vw;max-height:80vh;border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,0.25);border:4px solid #fff;">
    </div>
    <div id="emailModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;">
        <div style="background:#fff;padding:2rem 2.5rem;border-radius:1rem;max-width:90vw;min-width:300px;position:relative;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
            <span id="closeEmailModal" style="position:absolute;top:12px;right:18px;cursor:pointer;font-size:1.7rem;">&times;</span>
            <h4 style="margin-bottom:1rem;">Email HRD</h4>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <span id="modalEmail" style="font-weight:bold;font-size:1.1rem;"></span>
                <button id="copyEmailBtn" class="btn btn-outline btn-sm" style="padding:0.2rem 0.7rem;">Copy Email</button>
            </div>
            <div id="copyNotif" style="color:green;margin-top:0.7rem;display:none;">Email berhasil disalin!</div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
  const profileAvatar = document.getElementById('profileAvatar');
  const profileDropdown = document.getElementById('profileDropdown');
  if (profileAvatar && profileDropdown) {
    profileAvatar.addEventListener('click', function(e) {
      e.stopPropagation();
      profileDropdown.classList.toggle('show');
    });
  }
  // Toggle panel notifikasi
  const notifIcon = document.getElementById('notifIcon');
  const notifPanel = document.getElementById('notifPanel');
  const notifCloseBtn = document.getElementById('notifCloseBtn');
  const notifMarkAll = document.getElementById('notifMarkAll');
  notifIcon.onclick = function(e) {
    e.stopPropagation();
    notifPanel.style.display = (notifPanel.style.display === 'block') ? 'none' : 'block';
    if (profileDropdown) profileDropdown.classList.remove('show');
  };
  if (notifCloseBtn) {
    notifCloseBtn.onclick = function(e) {
      e.stopPropagation();
      notifPanel.style.display = 'none';
    };
  }
  if (notifMarkAll) {
    notifMarkAll.onclick = function(e) {
      e.stopPropagation();
      fetch('ajax-delete-notif.php?all=1', {method:'POST'})
        .then(res=>res.json()).then(data=>{
          if(data.success) {
            document.getElementById('notifList').innerHTML = '<li style="padding:1.2rem;text-align:center;color:#aaa;">Tidak ada notifikasi baru.</li>';
            updateNotifCount();
          }
        });
    };
  }
  document.addEventListener('click', function(e) {
    notifPanel.style.display = 'none';
    if (profileDropdown) profileDropdown.classList.remove('show');
  });
  notifPanel.onclick = function(e) { e.stopPropagation(); };
  if (profileDropdown) profileDropdown.onclick = function(e) { e.stopPropagation(); };
});
// Hapus notifikasi satuan
function deleteNotif(id) {
  fetch('ajax-delete-notif.php?id='+id, {method:'POST'})
    .then(res=>res.json()).then(data=>{
      if(data.success) {
        const el = document.getElementById('notif-'+id);
        if(el) el.remove();
        updateNotifCount();
      }
    });
}
function updateNotifCount() {
  const notifCount = document.getElementById('notifCount');
  const notifList = document.getElementById('notifList');
  const count = notifList.querySelectorAll('.notif-item').length;
  notifCount.textContent = count;
  notifCount.style.display = count ? 'inline-block' : 'none';
}
function showAvatarModal(imgUrl) {
  const modal = document.getElementById('avatarModal');
  const modalImg = document.getElementById('avatarModalImg');
  const closeBtn = document.getElementById('closeAvatarModal');
  modalImg.src = imgUrl;
  modal.style.display = 'flex';
  closeBtn.onclick = function() {
    modal.style.display = 'none';
  };
  modal.onclick = function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  };
}
    </script>
    <script src="../../user/script/script-user.js"></script>
</body>
</html> 