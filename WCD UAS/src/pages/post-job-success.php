<?php
// src/pages/post-job-success.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($job_id <= 0) {
    header('Location: work-list.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Berhasil Dipasang - LocalLink</title>
    <link rel="stylesheet" href="../styles/post-job.css">
    <style>
    .success-container {
        max-width: 480px;
        margin: 4rem auto 0 auto;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(99,102,241,0.08);
        padding: 2.5rem 2rem 2rem 2rem;
        text-align: center;
    }
    .success-icon {
        font-size: 3.5rem;
        color: #22c55e;
        margin-bottom: 1rem;
    }
    .success-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #15803d;
    }
    .success-desc {
        color: #555;
        margin-bottom: 1.5rem;
    }
    .button.button-outline {
        margin-top: 1rem;
        margin-right: 0.5rem;
    }
    .button.button-secondary {
        margin-top: 1rem;
        background: var(--primary-color);
        color: #fff;
        border: 1px solid var(--primary-color);
        text-decoration: none;
        padding: 0.6rem 1.2rem;
        border-radius: 0.4rem;
        font-size: 1rem;
        transition: background 0.2s;
    }
    .button.button-secondary:hover {
        background: #4f46e5;
        border-color: #4f46e5;
    }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✔️</div>
        <div class="success-title">Lowongan Berhasil Dipasang!</div>
        <div class="success-desc">Lowongan Anda sudah berhasil dipublikasikan.<br>Anda dapat melihat detail dan mengelola lowongan ini di halaman berikut.</div>
        <a href="job-details.php?id=<?php echo $job_id; ?>" class="button button-outline">Lihat Detail Lowongan</a>
        <a href="home.php" class="button button-secondary">Kembali ke Beranda</a>
    </div>
</body>
</html> 