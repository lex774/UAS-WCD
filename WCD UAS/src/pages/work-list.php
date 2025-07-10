<?php
session_start();
include_once '../db/connection.php';
$query = "SELECT * FROM jobs ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Ambil data user jika sudah login
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT name, email, profile_picture FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($user_query);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Tersedia - LocalLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/jobs.css">
    <style>
    /* Header Styles */
    .main-nav {
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: #ffffff;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: var(--shadow-md);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        text-decoration: none;
    }

    .brand .logo {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 0.25rem;
    }

    .nav-content ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        gap: 2rem;
    }

    .nav-link {
        text-decoration: none;
        color: #4b5563;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out;
        border-radius: 0.25rem;
    }

    .nav-link:hover {
        color: var(--primary-color);
    }

    .nav-link.active {
        color: var(--primary-color);
        font-weight: 600;
        background-color: #eff6ff;
    }

    /* Profile Menu Styles */
    .profile-menu {
        position: relative;
        display: inline-block;
    }

    .profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        object-fit: cover;
    }

    .profile-avatar:hover {
        opacity: 0.8;
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

    .profile-menu:hover .dropdown-menu,
    .dropdown-menu:hover {
        display: block;
    }

    @keyframes fadeInDropdown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        color: #374151;
        text-decoration: none;
        transition: background-color 0.2s ease-in-out;
        border-radius: 0.25rem;
        margin: 0.25rem;
    }

    .dropdown-item:hover {
        background-color: var(--muted-background);
        color: var(--primary-color);
    }

    .dropdown-item .dropdown-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    /* Ensure avatar icon is centered and sized like in edit-profile */
    .profile-menu .fa-user-circle.profile-avatar {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2.5rem;
        color: #bdbdbd;
        width: 40px;
        height: 40px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-menu span[style*="relative"] {
        width: 40px;
        height: 40px;
        display: inline-block;
        position: relative;
        vertical-align: middle;
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
                    <li><a href="home-user.php" class="nav-link">Beranda</a></li>
                    <li><a href="work-list.php" class="nav-link">Cari Pekerjaan</a></li>
                    <li><a href="post-job.php" class="nav-link">Pasang Lowongan</a></li>
                </ul>
            </div>
            <div class="profile-menu">
                <?php if ($user && !empty($user['profile_picture']) && file_exists(__DIR__ . '/../../user/' . $user['profile_picture'])): ?>
                    <img src="<?php echo '../../user/' . htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" class="profile-avatar" id="profileAvatar" style="width:40px;height:40px;border-radius:50%;cursor:pointer;object-fit:cover;">
                <?php else: ?>
                    <span style="position:relative;display:inline-block;width:40px;height:40px;vertical-align:middle;">
                        <i class="fa fa-user-circle profile-avatar" id="profileAvatar" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:2.5rem;color:#bdbdbd;width:40px;height:40px;cursor:pointer;"></i>
                    </span>
                <?php endif; ?>
                <div class="dropdown-menu" id="profileDropdown" style="display:none;position:absolute;right:0;top:60px;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,0.12);border-radius:0.5rem;min-width:160px;z-index:1001;">
                    <a href="../../user/page/dashboard.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13h2v-2a7 7 0 0 1 14 0v2h2"/><rect width="18" height="8" x="3" y="13" rx="2"/></svg>
                        Dashboard
                    </a>
                    <a href="../../user/page/edit-profile.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
                        Edit Profil
                    </a>
                    <a href="../../user/page/settings.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0-.33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09A1.65 1.65 0 0 0 11 3.09V3a2 2 0 0 1 4 0v.09c0 .66.26 1.3.73 1.77.47.47 1.11.73 1.77.73h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06-.06a1.65 1.65 0 0 0-.33 1.82v.09c0 .66-.26 1.3-.73 1.77-.47.47-1.11.73-1.77.73h-.09a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82V3a2 2 0 0 1 4 0v.09z"/></svg>
                        Pengaturan
                    </a>
                    <a href="../../user/page/logout.php" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 text-center">
            <h1 style="color: var(--primary-color);" class="font-headline text-4xl font-bold">Lowongan Tersedia</h1>
            <p class="text-muted-foreground mt-2">Temukan peluang kerja lepas yang sesuai untukmu.</p>
        </div>
        <div class="mb-8 p-6 bg-card rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="search-jobs" class="block text-sm font-medium mb-1">Cari Pekerjaan</label>
                    <div class="relative">
                        <input 
                            id="search-jobs"
                            type="text"
                            placeholder="Kata kunci, judul..."
                            class="input pl-10"
                        />
                    </div>
                </div>
                <div>
                    <label for="category-filter" class="block text-sm font-medium mb-1">Kategori</label>
                    <select id="category-filter" class="select-trigger">
                        <option value="all">Semua Kategori</option>
                        <option value="Web Development">Pengembangan Web</option>
                        <option value="Graphic Design">Desain Grafis</option>
                        <option value="Content Writing">Penulisan Konten</option>
                        <option value="Digital Marketing">Pemasaran Digital</option>
                        <option value="Video Editing">Editing Video</option>
                    </select>
                </div>
                <div>
                    <label for="location-filter" class="block text-sm font-medium mb-1">Lokasi</label>
                    <select id="location-filter" class="select-trigger">
                        <option value="all">Semua Lokasi</option>
                        <option value="Remote">Jarak Jauh</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Surabaya">Surabaya</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="job-list">
            <?php while ($job = mysqli_fetch_assoc($result)): ?>
            <div class="job-card" data-category="<?php echo htmlspecialchars($job['category']); ?>" data-location="<?php echo htmlspecialchars($job['location']); ?>">
                <h3 class="job-title"><a href="job-details.php?id=<?php echo $job['id']; ?>" class="job-link"><?php echo htmlspecialchars($job['title']); ?></a></h3>
                <p class="job-company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                <p class="job-description"><?php echo htmlspecialchars($job['brief_description']); ?></p>
                <div class="job-meta">
                    <span class="badge badge-secondary"><?php echo htmlspecialchars($job['category']); ?></span>
                    <span class="badge badge-outline"><?php echo htmlspecialchars($job['location']); ?></span>
                    <span class="badge badge-outline"><?php echo htmlspecialchars($job['currency']); ?><?php echo htmlspecialchars($job['amount']); ?><?php echo $job['payment_type'] == 'hourly' ? '/jam' : '/proyek'; ?></span>
                </div>
                <a href="job-details.php?id=<?php echo $job['id']; ?>" class="view-details-button">Lihat Detail</a>
            </div>
            <?php endwhile; ?>
        </div>
        <aside class="related-jobs-section">
            <h2 style="color: var(--primary-color);" class="related-jobs-title">Lowongan Terkait yang Mungkin Cocok</h2>
            <div class="related-jobs-grid">
                <!-- Contoh statis, bisa diubah ke dinamis jika perlu -->
            </div>
        </aside>
    </div>
    <footer>
    <div class="footer-bottom">
        <p>&copy; 2025 LocalLink. Hak Cipta Dilindungi.</p>
    </div>
    </footer>
    <script src="work-list.js"></script>
</body>
</html> 