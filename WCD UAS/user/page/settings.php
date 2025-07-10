<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../../src/db/connection.php';
$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT name, email FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengaturan - LocalFlow</title>
  <link rel="stylesheet" href="../style/style-setting.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> 
</head>
<body>

  <!-- Header -->
  <header>
  </header>

  <!-- Main Content -->
  <main class="main container">
    <a href="../../user/page/dashboard.php" class="button button-outline mb-6">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
          <path d="m15 18-6-6 6-6"/>
      </svg>
      Kembali ke Dashboard
  </a>
    <section class="settings-section">
      <h1 class="settings-title"> Pengaturan</h1>
      <p></p>
      <!-- Sidebar Navigation -->
      <aside class="sidebar">
        <h2 class="general">Umum</h2>
        <p class="general-description">Perbarui informasi dan preferensi pribadi Anda.</p>
        <ul class="sidebar-links">
          <li><a href="#account-info" class="sidebar-link"><i class="bi bi-person-fill" style="font-size: 23px;"></i>Akun</a></li>
          <li><a href="#appearance" class="sidebar-link"><i class="bi bi-palette-fill" style="font-size: 21px;"></i>Tampilan</a></li>
        </ul>
      </aside>

      <!-- Content Cards -->
      <div class="content-cards">
        <!-- Account Information Card -->
        <div id="account-info" class="card">
          <h2 class="card-title"></i>Informasi Akun</h2>
          <p class="card-description">Lihat dan edit detail pribadi Anda.</p>
          <div class="card-details">
            <p><strong>Nama:</strong> <span id="setting-name"><?php echo htmlspecialchars($user['name']); ?></span></p>
            <p><strong>Email:</strong> <span id="setting-email"><?php echo htmlspecialchars($user['email']); ?></span></p>
          </div>
          <a href="../../user/page/edit-profile.php">
            <button class="card-button">Edit Profil</button>
          </a>
        </div>

        <!-- Appearance Card -->
        <div id="appearance" class="card">
          <h2 class="card-title"></i>Tampilan</h2>
          <p class="card-description">Kustomisasi tampilan dan nuansa aplikasi.</p>
          <div class="card-details">
            <p><strong>Tema:</strong></p>
            <div class="theme-selector-wrapper">
              <select class="theme-selector">
                <option value="system">Sistem</option>
                <option value="light">Terang</option>
                <option value="dark">Gelap</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Payment Methods Card -->
        <div class="card">
          <h2 class="card-title"></i>Metode Pembayaran</h2>
          <p class="card-description">Kelola metode pembayaran yang terhubung. (Placeholder)</p>
          <p class="card-message">Belum ada metode pembayaran yang dikonfigurasi.</p>
          <button class="card-button">Tambah Metode Pembayaran</button>
        </div>

        <!-- Security Card -->
        <div class="card">
          <h2 class="card-title"></i>Keamanan</h2>
          <p class="card-description">Kelola pengaturan keamanan akun Anda. (Placeholder)</p>
          <div class="card-details">
            <p><strong>Ubah Kata Sandi:</strong></p>
            <button class="card-button">Ubah Kata Sandi</button>
          </div>
          <div class="card-details">
            <p><strong>Autentikasi Dua Faktor:</strong> Belum diaktifkan.</p>
            <button class="card-button">Aktifkan 2FA</button>
          </div>
        </div>

        <!-- Legal Card -->
        <div class="card">
          <h2 class="card-title">Legal</h2>
          <p class="card-description">Lihat syarat dan kebijakan kami.</p>
          <ul class="legal-links">
            <li><a href="terms-service.php">Syarat Layanan</a></li>
            <li><a href="privacy.php">Kebijakan Privasi</a></li>
          </ul>
        </div>

        <!-- Sign Out Card -->
        <div class="card sign-out-card">
            <h2 class="card-title"> <i class="bi bi-box-arrow-right custom-icon"></i>Keluar Akun</h2>
            <p class="card-message">Apakah Anda yakin ingin keluar dari akun Anda?</p>
            <a href="logout.php" class="card-button sign-out-button"> Keluar
            </a>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer-bottom">
    <div>
      <hr>
      <div>
        <p>&copy; 2025 LocalLink. Seluruh Hak Cipta Dilindungi.</p>
        </div>
      <ul class="footer-links">
        <li><a href="terms-service.php">Syarat Layanan</a></li>
        <li><a href="privacy.php">Kebijakan Privasi</a></li>
      </ul>
    </div>
  </footer>

  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"  crossorigin="anonymous"></script>
  <!-- Custom JavaScript -->
  <script src="../script/script-user.js"></script>
</body>
</html> 