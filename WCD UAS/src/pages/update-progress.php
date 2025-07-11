<?php
require_once '../db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$app_id = isset($_GET['application_id']) ? intval($_GET['application_id']) : 0;
if ($app_id) {
    $query = mysqli_query($conn, "SELECT a.*, j.title, j.company_name FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.user_id='$user_id' AND a.id='$app_id'");
    $apps = [];
    if ($row = mysqli_fetch_assoc($query)) $apps[] = $row;
} else {
    $query = mysqli_query($conn, "SELECT a.*, j.title, j.company_name FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.user_id='$user_id'");
    $apps = [];
    while ($row = mysqli_fetch_assoc($query)) $apps[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Progres Pekerjaan</title>
    <link rel="stylesheet" href="../styles/update-progress.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/tracking.css">
</head>
<body>
    <div class="container">
        <a href="../../user/page/dashboard.php" class="back-btn"><i class="fa fa-arrow-left"></i> Kembali ke Dashboard</a>
        <h2>Update Progres Pekerjaan</h2>
        <?php if (empty($apps)): ?>
            <div class="tracking-card">Tidak ada pekerjaan yang sedang berjalan.</div>
        <?php else: ?>
        <!-- Progress Card Start -->
        <?php foreach ($apps as $app): 
            $work_status = strtolower($app['work_status'] ?? 'not_started');
            $status_display = '';
            switch($work_status) {
                case 'not_started': $status_display = 'Belum Dikerjakan'; break;
                case 'in_progress': $status_display = 'Sedang Dikerjakan'; break;
                case 'completed': $status_display = 'Selesai Dikerjakan'; break;
                case 'reviewed': $status_display = 'Sudah Direview'; break;
                case 'waiting_worker_confirmation': $status_display = 'Menunggu Konfirmasi Pekerja'; break;
                case 'paid': $status_display = 'Sudah Dibayar'; break;
                default: $status_display = 'Belum Dikerjakan';
            }
        ?>
        <div class="progress-card">
          <div class="job-header">
            <h2><?= htmlspecialchars($app['title']) ?> <span class="company"><?= htmlspecialchars($app['company_name']) ?></span></h2>
            <span class="badge badge-status badge-<?= $work_status ?><?php if($work_status=='reviewed') echo ' badge-reviewed'; ?>"><?= $status_display ?></span>
          </div>
          <div class="progress-stepper">
            <div class="progress-step <?= $work_status=='not_started'?'active':($work_status!='not_started'?'completed':'') ?>">
              <i class="fa fa-check"></i><br>Lolos Seleksi
            </div>
            <div class="progress-step <?= $work_status=='in_progress'?'active':(in_array($work_status,['completed','reviewed','waiting_worker_confirmation','paid'])?'completed':'') ?>">
              <i class="fa fa-play"></i><br>Sedang Dikerjakan
            </div>
            <div class="progress-step <?= $work_status=='completed'?'active':(in_array($work_status,['reviewed','waiting_worker_confirmation','paid'])?'completed':'') ?>">
              <i class="fa fa-flag-checkered"></i><br>Pekerjaan Selesai
            </div>
            <div class="progress-step <?= $work_status=='reviewed'?'active':(in_array($work_status,['waiting_worker_confirmation','paid'])?'completed':'') ?>">
              <i class="fa fa-star"></i><br>Review Pekerjaan
              <?php if ($work_status=='reviewed'): ?>
                <span class="badge badge-reviewed" style="display:inline-block;margin-top:0.5rem;font-size:0.95rem;padding:0.25rem 0.8rem;">Sudah Direview</span>
              <?php endif; ?>
            </div>
            <div class="progress-step <?= in_array($work_status,['reviewed','waiting_worker_confirmation','paid']) && $work_status!='reviewed' ? 'completed' : ($work_status=='reviewed'?'active':'') ?>">
              <i class="fa fa-check"></i><br>Sudah di Review
            </div>
            <div class="progress-step <?= $work_status=='waiting_worker_confirmation'?'active':($work_status=='paid'?'completed':'') ?>">
              <i class="fa fa-money-bill"></i><br>Pembayaran
            </div>
            <div class="progress-step <?= $work_status=='paid'?'active completed':'' ?>">
              <i class="fa fa-check-double"></i><br>Konfirmasi Selesai
            </div>
          </div>
          <div class="progress-actions">
            <?php if ($work_status=='not_started'): ?>
              <button class="btn btn-primary" onclick="updateProgress(<?= $app['id'] ?>, 'dikerjakan')">Mulai Dikerjakan</button>
            <?php endif; ?>
            <?php if ($work_status=='in_progress'): ?>
              <button class="btn btn-primary" onclick="updateProgress(<?= $app['id'] ?>, 'sudah_dikerjakan')">Pekerjaan Selesai</button>
            <?php elseif ($work_status=='paid'): ?>
              <span class="badge badge-pembayaran"><i class="fa fa-money-check-alt"></i> Pembayaran Sudah Dilakukan</span>
              <a href="../<?= $app['payment_proof'] ?>" class="btn btn-secondary" target="_blank"><i class="fa fa-file-invoice"></i> Lihat Bukti Pembayaran</a>
              <button class="btn btn-success" onclick="konfirmasiPembayaran(<?= $app['id'] ?>)"><i class="fa fa-check-circle"></i> Konfirmasi Pembayaran</button>
            <?php elseif ($work_status=='waiting_worker_confirmation'): ?>
              <span class="badge badge-info"><i class="fa fa-hourglass-half"></i> Menunggu Konfirmasi Pekerja</span>
              <a href="../<?= $app['payment_proof'] ?>" class="btn btn-secondary" target="_blank"><i class="fa fa-file-invoice"></i> Lihat Bukti Pembayaran</a>
              <button class="btn btn-success" onclick="konfirmasiPembayaran(<?= $app['id'] ?>)"><i class="fa fa-check-circle"></i> Konfirmasi Pembayaran</button>
            <?php endif; ?>
          </div>
          <div class="progress-log">
            <ul>
              <li><?= date('d M Y', strtotime($app['updated_at'] ?? $app['applied_at'])) ?> - Status: <?= $status_display ?></li>
              <?php if ($work_status == 'reviewed'): ?>
                <li><i class="fa fa-star"></i> Sudah di-review oleh employer.</li>
              <?php endif; ?>
            </ul>
          </div>
          <?php if ($work_status=='not_started'): ?>
            <div class="info-card info-welcome">
                <i class="fa fa-user-check"></i>
                <div class="info-text">
                    <strong>Pekerja telah diterima.</strong><br>
                    Menunggu mulai pengerjaan.
                </div>
            </div>
          <?php elseif ($work_status=='in_progress'): ?>
            <div class="info-card info-progress">
                <i class="fa fa-briefcase"></i>
                <div class="info-text">
                    <strong>Pekerjaan sedang dikerjakan oleh pekerja.</strong><br>
                    Tetap semangat dan pantau progres pekerjaan secara berkala!
                </div>
            </div>
          <?php elseif ($work_status=='completed'): ?>
            <div class="info-card info-completed">
                <i class="fa fa-flag-checkered"></i>
                <div class="info-text">
                    <strong>Pekerjaan sudah selesai!</strong><br>
                    Menunggu review dari employer.
                </div>
            </div>
          <?php elseif ($work_status=='reviewed'): ?>
            <div class="info-card info-reviewed">
                <i class="fa fa-star"></i>
                <div class="info-text">
                    <strong>Pekerjaan sudah direview!</strong><br>
                    Pekerjaan Anda telah dinilai oleh employer. Silakan cek hasil review sebelum melanjutkan ke proses pembayaran.
                </div>
            </div>
          <?php elseif ($work_status=='waiting_worker_confirmation'): ?>
            <div class="info-card info-payment">
                <i class="fa fa-money-bill"></i>
                <div class="info-text">
                    <strong>Pembayaran sedang diproses!</strong><br>
                    Employer telah mengirim pembayaran. Silakan cek dan konfirmasi jika sudah diterima.
                </div>
            </div>
          <?php elseif ($work_status=='paid'): ?>
            <div class="info-card info-paid">
                <i class="fa fa-money-bill-wave"></i>
                <div class="info-text">
                    <strong>Pembayaran sudah selesai!</strong><br>
                    Anda dapat memberikan penilaian untuk pekerjaan yang telah selesai.
                </div>
            </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <!-- Progress Card End -->
        <?php endif; ?>
    </div>
    <div id="notif" class="notif" style="display:none;position:fixed;top:2rem;right:2rem;background:#10b981;color:#fff;padding:1rem 1.5rem;border-radius:0.7rem;z-index:10000;font-weight:600;font-size:1.05rem;box-shadow:0 2px 8px rgba(16,185,129,0.13);animation:fadeInNotif 0.3s;"></div>
    <script src="../scripts/update-progress.js"></script>
    <script>
    function showNotif(msg, success=true) {
      var notif = document.getElementById('notif');
      notif.innerText = msg;
      notif.style.display = 'block';
      notif.style.background = success ? '#10b981' : '#ef4444';
      notif.style.color = '#fff';
      notif.style.fontWeight = '600';
      notif.style.fontSize = '1.05rem';
      notif.style.borderRadius = '0.7rem';
      notif.style.boxShadow = '0 2px 8px rgba(16,185,129,0.13)';
      setTimeout(()=>{ notif.style.display = 'none'; }, 2500);
    }
    function konfirmasiPembayaran(appId) {
      fetch('update-progress-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'app_id=' + encodeURIComponent(appId) + '&new_status=paid'
      })
      .then(res => res.text())
      .then(text => {
        if (text.trim() === 'success') {
          showNotif('Pembayaran berhasil dikonfirmasi!', true);
          setTimeout(()=>location.reload(), 1200);
        } else {
          showNotif('Gagal konfirmasi pembayaran: ' + text, false);
        }
      })
      .catch(err => showNotif('Error: ' + err, false));
    }
    </script>
</body>
</html> 