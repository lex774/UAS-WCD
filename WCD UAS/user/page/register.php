<?php
require_once '../../src/db/connection.php';
$error = '';
$success = isset($_GET['register']) && $_GET['register'] === 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: login.php?register=success');
                exit();
            } else {
                $error = 'Gagal mendaftar. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Lance - Daftar Akun</title>
    <link rel="stylesheet" href="../style/style-register.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="register-bg d-flex align-items-center justify-content-center">
    <div class="signup-container shadow-lg">
        <div class="text-center mb-4">
            <i class="fa fa-briefcase fa-3x mb-2" style="color:#6366f1;"></i>
            <h2 class="fw-bold mb-1">Buat Akun Quick Lance</h2>
            <p class="text-muted">Gabung dan mulai temukan atau tawarkan pekerjaan sederhana, bantu sesama, dan dapatkan penghasilan dengan cepat.</p>
        </div>
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle me-2"></i>
                <span><?php echo $error; ?></span>
            </div>
            <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <form method="POST" class="mt-3">
            <div class="mb-3">
                <label for="name" class="form-label small">Nama Lengkap</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Nama lengkap" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label small">Email</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control form-control-sm" id="email" name="email" placeholder="email@contoh.com" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label small">Password</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control form-control-sm" id="password" name="password" placeholder="Password" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm-password" class="form-label small">Konfirmasi Password</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control form-control-sm" id="confirm-password" name="confirm-password" placeholder="Konfirmasi Password" required>
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label small" for="terms">Saya setuju dengan <a href="../../user/page/terms-service.php" class="text-primary text-decoration-none">syarat & ketentuan</a></label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">Daftar</button>
            <div class="text-center mt-3">
                <span class="text-muted">Atau daftar dengan</span>
                <div class="social-login mt-2">
                    <a href="#" class="social-btn bg-danger text-white me-2"><i class="fab fa-google"></i></a>
                    <a href="#" class="social-btn bg-primary text-white me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn bg-dark text-white"><i class="fab fa-apple"></i></a>
                </div>
            </div>
            <div class="text-center mt-4">
                <p>Sudah punya akun? <a href="login.php" class="text-decoration-none text-primary">Masuk</a></p>
            </div>
        </form>
    </div>
    <!-- Modal Success Register -->
    <?php if ($success): ?>
    <div class="modal fade show" id="registerSuccessModal" tabindex="-1" aria-labelledby="registerSuccessLabel" aria-modal="true" role="dialog" style="display:block; background:rgba(0,0,0,0.3);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title w-100 text-center" id="registerSuccessLabel"><i class="fa fa-check-circle text-success me-2"></i>Registrasi Berhasil!</h5>
          </div>
          <div class="modal-body text-center">
            <p class="mb-3">Selamat, akun Anda berhasil dibuat.<br>Silakan login untuk mulai menggunakan LocalLink.</p>
            <a href="login.php" class="btn btn-primary w-100">Login Sekarang</a>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.body.style.overflow = 'hidden';
      setTimeout(function(){
        window.location.href = 'login.php';
      }, 6000);
    </script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/script-register.js"></script>
</body>
</html> 