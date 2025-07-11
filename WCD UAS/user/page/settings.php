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

// Ambil data rekening dan nama bank
$rekening = '';
$bank_name = '';
$rekening_query = mysqli_query($conn, "SELECT bank_account, bank_name FROM users WHERE id='$user_id'");
if ($rekening_query) {
    $rekening_row = mysqli_fetch_assoc($rekening_query);
    $rekening = $rekening_row && $rekening_row['bank_account'] !== null ? $rekening_row['bank_account'] : '';
    $bank_name = $rekening_row && $rekening_row['bank_name'] !== null ? $rekening_row['bank_name'] : '';
}
// Proses simpan rekening dan nama bank
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bank_account'], $_POST['bank_name'])) {
    $new_rekening = trim($_POST['bank_account']);
    $new_bank_name = trim($_POST['bank_name']);
    mysqli_query($conn, "UPDATE users SET bank_account='" . mysqli_real_escape_string($conn, $new_rekening) . "', bank_name='" . mysqli_real_escape_string($conn, $new_bank_name) . "' WHERE id='$user_id'");
    $rekening = $new_rekening;
    $bank_name = $new_bank_name;
    echo '<script>alert(\'Data bank berhasil disimpan!\');window.location.href=window.location.href;</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengaturan - Quick Lance</title>
  <link rel="stylesheet" href="../style/style-setting.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> 
  <style>
    .input-dropdown {
      width: 100%;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      border: 1.5px solid #cbd5e1;
      background: #f9fafb;
      font-size: 1.08rem;
      color: #222;
      transition: border 0.18s, box-shadow 0.18s;
      margin-bottom: 0.7rem;
    }
    .input-dropdown:focus {
      border-color: #6366f1;
      outline: none;
      box-shadow: 0 0 0 2px #6366f133;
    }
  </style>
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
          <p class="card-description">Kelola metode pembayaran yang terhubung.</p>
          <form method="post" style="margin-bottom:1rem;">
            <label for="bank_name" style="font-weight:600; color:#6366f1; margin-bottom:0.3rem; display:block;">Nama Bank</label>
            <select id="bank_name" name="bank_name" required class="input-dropdown">
              <option value="">Pilih Bank</option>
              <option value="BCA" <?= $bank_name=="BCA"?'selected':'' ?>>BCA</option>
              <option value="BRI" <?= $bank_name=="BRI"?'selected':'' ?>>BRI</option>
              <option value="BNI" <?= $bank_name=="BNI"?'selected':'' ?>>BNI</option>
              <option value="Mandiri" <?= $bank_name=="Mandiri"?'selected':'' ?>>Mandiri</option>
              <option value="CIMB" <?= $bank_name=="CIMB"?'selected':'' ?>>CIMB</option>
              <option value="BTN" <?= $bank_name=="BTN"?'selected':'' ?>>BTN</option>
              <option value="Danamon" <?= $bank_name=="Danamon"?'selected':'' ?>>Danamon</option>
              <option value="Permata" <?= $bank_name=="Permata"?'selected':'' ?>>Permata</option>
              <option value="Maybank" <?= $bank_name=="Maybank"?'selected':'' ?>>Maybank</option>
              <option value="Lainnya" <?= $bank_name=="Lainnya"?'selected':'' ?>>Lainnya</option>
            </select>
            <div style="font-size:0.97rem;color:#64748b;margin-bottom:0.7rem;">Pilih bank Anda. Jika tidak ada di daftar, pilih "Lainnya" dan isi manual di bawah.</div>
            <div style="display:flex;align-items:center;gap:0.7rem;background:#f9fafb;border:1.5px solid #cbd5e1;border-radius:0.5rem;padding:0.5rem 1rem 0.5rem 0.8rem;">
              <span style="color:#6366f1;font-size:1.3rem;"><i class="bi bi-bank2"></i></span>
              <input type="text" id="bank_account" name="bank_account" value="<?= htmlspecialchars($rekening ?? '') ?>" placeholder="Masukkan nomor rekening Anda" required style="border:none;outline:none;background:transparent;font-size:1.08rem;width:100%;padding:0.5rem 0;" maxlength="50">
            </div>
            <div style="font-size:0.97rem;color:#64748b;margin:0.3rem 0 1rem 0;">Pastikan nomor rekening benar agar pembayaran tidak gagal.</div>
            <button type="submit" class="card-button" style="margin-top:0.5rem;">Simpan Data Bank</button>
          </form>
          <?php if ($rekening && $bank_name): ?>
            <p class="card-message"><b>Bank:</b> <?= htmlspecialchars($bank_name) ?> <br><b>Rekening:</b> <?= htmlspecialchars($rekening) ?></p>
          <?php elseif ($rekening): ?>
            <p class="card-message"><b>Rekening:</b> <?= htmlspecialchars($rekening) ?></p>
          <?php else: ?>
            <p class="card-message">Belum ada rekening yang disimpan.</p>
          <?php endif; ?>
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
        <p>&copy; 2025 Quick Lance. Seluruh Hak Cipta Dilindungi.</p>
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