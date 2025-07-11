<?php
require_once '../db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT name, email, profile_picture FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalLink</title>
    <link rel="stylesheet" href="../styles/app.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
    <style>
.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 60px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-radius: 0.5rem;
    min-width: 180px;
    z-index: 1001;
    padding: 0.5rem 0;
}
.dropdown-menu.show {
    display: block;
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.7rem 1.2rem;
    color: #333;
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.15s;
    border: none;
    background: none;
    cursor: pointer;
}
.dropdown-item:hover {
    background: #f3f4f6;
    color: #6366f1;
}
.dropdown-item i {
    font-size: 1.2rem;
    min-width: 1.2rem;
    text-align: center;
}
</style>
</head>
<body>
    <header class="main-nav">
        <div class="header-content container">
             <div class="brand">
                <img src="../../asset/logo.png" alt="LocalLink Logo" class="logo">
             </div>
            <div class="nav-content container">
                <ul>
                    <li><a href="home-user.php" class="nav-link">Beranda</a></li>
                    <li><a href="work-list.php" class="nav-link">Cari Pekerjaan</a></li>
                    <li><a href="post-job.php" class="nav-link">Pasang Lowongan</a></li>
                </ul>
            </div>
            <div class="profile-menu" style="display:flex;align-items:center;gap:1.2rem;position:relative;">
                <?php if ($user && !empty($user['profile_picture']) && file_exists(__DIR__ . '/../../user/' . $user['profile_picture'])): ?>
                    <img src="<?php echo '../../user/' . htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" class="profile-avatar" id="profileAvatar" style="width:40px;height:40px;border-radius:50%;cursor:pointer;object-fit:cover;">
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="../../user/page/dashboard.php" class="dropdown-item">
                            <i class="fa fa-gauge"></i> Dashboard
                        </a>
                        <a href="../../user/page/edit-profile.php" class="dropdown-item">
                            <i class="fa fa-user-edit"></i> Edit Profil
                        </a>
                        <a href="../../user/page/settings.php" class="dropdown-item">
                            <i class="fa fa-gear"></i> Pengaturan
                        </a>
                        <a href="../../user/page/logout.php" class="dropdown-item">
                            <i class="fa fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                <?php else: ?>
                    <span style="position:relative;display:inline-block;width:40px;height:40px;vertical-align:middle;">
                        <i class="fa fa-user-circle profile-avatar" id="profileAvatar" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:2.5rem;color:#bdbdbd;width:40px;height:40px;cursor:pointer;"></i>
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="../../user/page/dashboard.php" class="dropdown-item">
                                <i class="fa fa-gauge"></i> Dashboard
                            </a>
                            <a href="../../user/page/edit-profile.php" class="dropdown-item">
                                <i class="fa fa-user-edit"></i> Edit Profil
                            </a>
                            <a href="../../user/page/settings.php" class="dropdown-item">
                                <i class="fa fa-gear"></i> Pengaturan
                            </a>
                            <a href="../../user/page/logout.php" class="dropdown-item">
                                <i class="fa fa-sign-out-alt"></i> Keluar
                            </a>
                        </div>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="container main-content">
        <section class="hero-section" id="hero">
            <h1 class="hero-title">
                Selamat Datang di <span class="text-primary">Quick Lance</span>
            </h1>
            <p class="hero-description">
                Platform pekerjaan sementara yang <b>mudah, inklusif, dan tanpa seleksi rumit</b>.<br>
                Temukan atau tawarkan pekerjaan sederhana, bantu sesama, dan dapatkan penghasilan dengan cepat.
            </p>
            <p class="hero-tagline">
                "Setiap orang berhak mendapat kesempatan bekerja. Quick Lance, untuk semua."
            </p>
        </section>
        <section class="hero">
            <div class="hero-buttons">
                <a href="work-list.php" class="button button-lg" id="cta-findwork">
                    Cari Pekerjaan
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="icon ml-2">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
                <a href="post-job.php" class="button button-lg button-secondary" id="cta-postjob">
                    Pasang Lowongan
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="icon ml-2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </a>
            </div>
        </section>
    </section>

    <section class="features-section" id="features">
        <h2 class="section-title">Fitur Unggulan Quick Lance</h2>
        <div class="features-grid">
            <div class="feature-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-lg icon-primary mx-auto mb-4"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <h3 class="feature-title">Temukan Peluang</h3>
                <p class="feature-description">
                    Jelajahi berbagai pekerjaan lokal yang sesuai dengan keahlian dan minat Anda.
                </p>
            </div>
            <div class="feature-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-lg icon-primary mx-auto mb-4"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <h3 class="feature-title">Terhubung Secara Lokal</h3>
                <p class="feature-description">
                    Temukan pekerja lepas berbakat atau klien terpercaya di sekitar Anda.
                </p>
            </div>
            <div class="feature-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-lg icon-primary mx-auto mb-4"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <h3 class="feature-title">Perluas Jaringan</h3>
                <p class="feature-description">
                    Bangun profil profesional Anda, tunjukkan hasil kerja, dan dapatkan lebih banyak peluang.
                </p>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <h2>Apa Kata Mereka?</h2>
        <div class="testimonials-list">
            <blockquote class="testimonial-card">
                "Saya bisa dapat penghasilan tambahan tanpa ribet. Quick Lance sangat membantu!"<br>
                <span class="testimonial-author">- Rina, Mahasiswa</span>
            </blockquote>
            <blockquote class="testimonial-card">
                "Mudah digunakan, dan saya bisa langsung menemukan orang yang mau membantu pekerjaan rumah saya."
                <br><span class="testimonial-author">- Pak Budi, Pengguna</span>
            </blockquote>
            <blockquote class="testimonial-card">
                "Saya merekomendasikan Quick Lance ke teman-teman saya. Prosesnya cepat dan transparan!"
                <br><span class="testimonial-author">- Siti, Freelancer</span>
            </blockquote>
            <blockquote class="testimonial-card">
                "Berkat Quick Lance, saya bisa menemukan pekerjaan harian yang sesuai jadwal kuliah saya. Sangat fleksibel!"
                <br><span class="testimonial-author">- Andi, Mahasiswa</span>
            </blockquote>
        </div>
    </section>

    <section class="how-it-works-section" id="howitworks">
            <h2 class="section-title">Bagaimana Cara Kerjanya?</h2>
            <div class="how-it-works-content">
                <div class="how-it-works-image">
                    <img 
                        src="../../asset/illustrasi.jpg" 
                        alt="Ilustrasi orang saling terhubung" 
                    >
                </div>
                <ul class="how-list">
  <li class="how-card">
    <span class="how-badge">1</span>
    <div>
      <div class="how-card-title">Daftar & Lengkapi Profil</div>
      <div class="how-card-desc">Buat akun dan lengkapi profil Anda untuk mulai menggunakan platform.</div>
    </div>
  </li>
  <li class="how-card">
    <span class="how-badge">2</span>
    <div>
      <div class="how-card-title">Cari atau Pasang Lowongan</div>
      <div class="how-card-desc">Temukan pekerjaan atau pasang lowongan sesuai kebutuhan Anda.</div>
    </div>
  </li>
  <li class="how-card">
    <span class="how-badge">3</span>
    <div>
      <div class="how-card-title">Lamar atau Terima Pelamar</div>
      <div class="how-card-desc">Lamar pekerjaan yang diminati atau terima pelamar yang sesuai.</div>
    </div>
  </li>
  <li class="how-card">
    <span class="how-badge">4</span>
    <div>
      <div class="how-card-title">Selesaikan & Dapatkan Pembayaran</div>
      <div class="how-card-desc">Selesaikan pekerjaan dan dapatkan pembayaran dengan aman.</div>
    </div>
  </li>
</ul>
            </div>
    </section>
    <footer class="footer">
        <div class="footer-container">
            <!-- Brand -->
            <div class="footer-brand">
                <img src="../../asset/logo.png" alt="Logo" class="footer-logo">
                <p>Platform yang menyediakan peluang kerja sementara untuk semua.</p>
            </div>
    
            <!-- Quick Links -->
            <div class="footer-section">
                <h4>Menu Cepat</h4>
                <ul>
                    <li><a href="home-user.php">Beranda</a></li>
                    <li><a href="work-list.php">Cari Pekerjaan</a></li>
                    <li><a href="post-job.php">Pasang Lowongan</a></li>
                </ul>
            </div>
    
            <!-- Follow Us -->
            <div class="footer-section with-logo">
                <h4>Ikuti Kami</h4>
                <ul class="social-icons">
                    <li><a href="#"><i class="fab fa-facebook-f"></i> Facebook</a></li>
                    <li><a href="#"><i class="fab fa-linkedin-in"></i> LinkedIn</a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                </ul>
            </div>
    
            <!-- Contact Us -->
            <div class="footer-section">
                <h4>Kontak Kami</h4>
                <ul>
                    <li>Email: info@lokalink.id</li>
                    <li>Telepon: +62 812 3456</li>
                    <li>Jl. Raya No.1, Jakarta Selatan</li>
                </ul>
            </div>
        </div>
    
        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; 2025 Quick Lance. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script>
    // Dropdown profile logic
    document.addEventListener('DOMContentLoaded', function() {
      const profileAvatar = document.getElementById('profileAvatar');
      const profileDropdown = document.getElementById('profileDropdown');
      if (profileAvatar && profileDropdown) {
        profileAvatar.addEventListener('click', function(e) {
          e.stopPropagation();
          profileDropdown.classList.toggle('show');
        });
      }
      document.addEventListener('click', function(e) {
        if (profileDropdown) profileDropdown.classList.remove('show');
      });
      if (profileDropdown) profileDropdown.onclick = function(e) { e.stopPropagation(); };
    });
    </script>
    <script src="home.js"></script>
</body>
</html> 