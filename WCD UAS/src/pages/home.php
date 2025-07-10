<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalLink</title>
    <link rel="stylesheet" href="../styles/app.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
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
                    <li><a href="home.php" class="nav-link">Beranda</a></li>
                    <li><a href="../../user/page/register.php" class="nav-link">Cari Pekerjaan</a></li>
                    <li><a href="../../user/page/register.php" class="nav-link">Pasang Lowongan</a></li>
                </ul>
                
            </div>
            <div class="auth-buttons">
                <a href="../../user/page/register.php" class="button button-sm button-secondary">Sign In</a>
                <a href="../../user/page/login.php" class="button button-sm">Log In</a>
            </div>
        </div>
    </header>

    <section class="container main-content">
        <section class="hero-section" id="hero">
            <h1 class="hero-title">
                Selamat Datang di <span class="text-primary">LocalLink</span>
            </h1>
            <p class="hero-description">
                Platform pekerjaan sementara yang <b>mudah, inklusif, dan tanpa seleksi rumit</b>.<br>
                Temukan atau tawarkan pekerjaan sederhana, bantu sesama, dan dapatkan penghasilan dengan cepat.
            </p>
            <p class="hero-tagline">
                "Setiap orang berhak mendapat kesempatan bekerja. LocalLink, untuk semua."
            </p>
        </section>
        <section class="hero">
            <div class="hero-buttons">
                <a href="../../user/page/register.php" class="button button-lg" id="cta-findwork">
                    Cari Pekerjaan
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="icon ml-2">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
                <a href="../../user/page/register.php" class="button button-lg button-secondary" id="cta-postjob">
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
        <h2 class="section-title">Fitur Unggulan LocalLink</h2>
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
                "Saya bisa dapat penghasilan tambahan tanpa ribet. LocalLink sangat membantu!"<br>
                <span class="testimonial-author">- Rina, Mahasiswa</span>
            </blockquote>
            <blockquote class="testimonial-card">
                "Mudah digunakan, dan saya bisa langsung menemukan orang yang mau membantu pekerjaan rumah saya."
                <br><span class="testimonial-author">- Pak Budi, Pengguna</span>
            </blockquote>
            <blockquote class="testimonial-card">
                "Saya merekomendasikan LocalLink ke teman-teman saya. Prosesnya cepat dan transparan!"
                <br><span class="testimonial-author">- Siti, Freelancer</span>
            </blockquote>
            <blockquote class="testimonial-card">
                "Berkat LocalLink, saya bisa menemukan pekerjaan harian yang sesuai jadwal kuliah saya. Sangat fleksibel!"
                <br><span class="testimonial-author">- Andi, Mahasiswa</span>
            </blockquote>
        </div>
    </section>

    <section class="how-it-works-section" id="howitworks">
            <h2 class="section-title">Bagaimana Cara Kerjanya?</h2>
            <div class="how-it-works-content">
                <div class="how-it-works-image">
                    <img 
                        src="https://placehold.co/600x400.png" 
                        alt="Ilustrasi orang saling terhubung" 
                    >
                </div>
                <div class="how-it-works-steps">
                    <div class="step">
                        <h4 class="step-title">1. Daftar & Buat Profil</h4>
                        <p class="step-description">Bergabung dengan LocalLink hanya dalam beberapa menit. Buat profil untuk menonjolkan keahlian Anda atau jelaskan kebutuhan pekerjaan Anda.</p>
                    </div>
                    <div class="step">
                        <h4 class="step-title">2. Pasang atau Cari Pekerjaan</h4>
                        <p class="step-description">Pasang lowongan pekerjaan secara mudah atau cari peluang kerja yang sesuai keahlian dan lokasi Anda.</p>
                    </div>
                    <div class="step">
                        <h4 class="step-title">3. Terhubung & Kolaborasi</h4>
                        <p class="step-description">Komunikasi aman, sepakati syarat, dan mulai bekerja sama. LocalLink memudahkan kolaborasi tanpa hambatan.</p>
                    </div>
                </div>
            </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <!-- Brand -->
            <div class="footer-brand">
                <img src="https://via.placeholder.com/40"  alt="Logo LocalLink" class="footer-logo">
                <h3>LocalLink</h3>
                <p>Platform yang menyediakan peluang kerja sementara untuk semua.</p>
            </div>
    
            <!-- Quick Links -->
            <div class="footer-section">
                <h4>Menu Cepat</h4>
                <ul>
                    <li><a href="home.php">Beranda</a></li>
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
            <p>&copy; 2025 LocalLink. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="home.js"></script>
</body>
</html> 