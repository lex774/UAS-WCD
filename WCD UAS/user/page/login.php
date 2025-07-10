<?php
session_start();
include_once '../../src/db/connection.php';

$error = '';

// Proses login jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $query = mysqli_query($conn, "SELECT id, name, password FROM users WHERE email='$email'");
        if ($row = mysqli_fetch_assoc($query)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Email tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalLink - Login</title>
    <!-- Style CSS -->
    <link rel="stylesheet" href="../style/style-register.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="register-bg d-flex align-items-center justify-content-center">
    <div class="signup-container shadow-lg">
        <div class="text-center mb-4">
            <i class="fa fa-briefcase fa-3x mb-2" style="color:#6366f1;"></i>
            <h2 class="fw-bold mb-1">Masuk ke LocalLink</h2>
            <p class="text-muted">Akses akunmu dengan Log-in disini</p>
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
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label small" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">Masuk</button>
            <div class="text-center mt-3">
                <span class="text-muted">Belum punya akun?</span>
                <a href="register.php" class="text-decoration-none text-primary ms-1">Daftar</a>
            </div>
            <div class="text-center mt-2">
                <a href="#" class="small text-decoration-none">Lupa password?</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/script-login.js"></script>
</body>
</html> 