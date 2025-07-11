<?php
require_once '../db/connection.php';
session_start();

if (!isset($_GET['app_id'])) {
    echo 'Aplikasi tidak ditemukan.';
    exit;
}
$app_id = intval($_GET['app_id']);
$query = mysqli_query($conn, "SELECT a.*, u.name, u.bank_account, u.bank_name, j.title, j.company_name, j.posted_by FROM applications a JOIN users u ON a.user_id = u.id JOIN jobs j ON a.job_id = j.id WHERE a.id='$app_id'");
$app = mysqli_fetch_assoc($query);

if (!$app) {
    echo "Data tidak ditemukan.";
    exit;
}

$is_worker = ($_SESSION['user_id'] == $app['user_id']);
$is_employer = ($_SESSION['user_id'] == $app['posted_by']);

$work_status = strtolower($app['work_status'] ?? 'not_started');
$payment_status = isset($app['payment_status']) ? strtolower($app['payment_status']) : '';

function stepClass($step, $status, $payment_status) {
    $steps = [
        'lolos' => 1,
        'dikerjakan' => 2,
        'sudah_dikerjakan' => 3,
        'pembayaran' => 4,
        'menunggu_konfirmasi_pekerja' => 5,
        'sudah_dibayar' => 6
    ];
    $current = $steps[$status] ?? 1;
    $idx = $steps[$step];
    return ($idx < $current) ? 'step completed' : (($idx == $current) ? 'step active' : 'step');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pekerjaan</title>
    <link rel="stylesheet" href="../styles/tracking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <a href="applicants-list.php?job_id=<?= $app['job_id'] ?>" class="back-btn">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar Pelamar
        </a>
        <h2>Tracking Pekerjaan untuk <?= htmlspecialchars($app['name']) ?></h2>
        <!-- Progress Card Start -->
<div class="progress-card">
  <div class="job-header">
    <h2><?= htmlspecialchars($app['job_title'] ?? $app['title']) ?> <span class="company"><?= htmlspecialchars($app['company_name']) ?></span></h2>
    <span class="badge badge-status badge-<?= $work_status ?><?php if($work_status=='reviewed') echo ' badge-reviewed'; ?>">
      <?php
        $status_display = [
          'not_started' => 'Lolos',
          'in_progress' => 'Sedang Dikerjakan',
          'completed' => 'Pekerjaan Selesai',
          'reviewed' => 'Review Pekerjaan',
          'sudah_direview' => 'Sudah di Review',
          'waiting_worker_confirmation' => 'Menunggu Konfirmasi Pekerja',
          'paid' => 'Sudah Dibayar'
        ];
        echo $status_display[$work_status] ?? 'Lolos';
      ?>
    </span>
  </div>
  <div class="progress-stepper">
    <div class="progress-step <?= $work_status=='not_started'?'active':($work_status!='not_started'?'completed':'') ?>">
      <i class="fa fa-check"></i><br>Lolos
    </div>
    <div class="progress-step <?= $work_status=='in_progress'?'active':(in_array($work_status,['completed','reviewed','waiting_worker_confirmation','paid'])?'completed':'') ?>">
      <i class="fa fa-play"></i><br>Sedang Dikerjakan
    </div>
    <div class="progress-step <?= $work_status=='completed'?'active':(in_array($work_status,['reviewed','waiting_worker_confirmation','paid'])?'completed':'') ?>">
      <i class="fa fa-flag-checkered"></i><br>Pekerjaan Selesai
    </div>
    <div class="progress-step <?= $work_status=='reviewed'?'active':($work_status=='sudah_direview'?'completed':(in_array($work_status,['waiting_worker_confirmation','paid'])?'completed':'') ) ?>">
      <i class="fa fa-star"></i><br>Review Pekerjaan
    </div>
    <div class="progress-step <?= $work_status=='sudah_direview'?'active':(in_array($work_status,['waiting_worker_confirmation','paid'])?'completed':'') ?>">
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
    <?php if ($work_status=='not_started' && $is_worker): ?>
      <button class="btn btn-primary" onclick="updateProgress(<?= $app['id'] ?>, 'dikerjakan')">Mulai Dikerjakan</button>
    <?php elseif ($work_status=='in_progress' && $is_worker): ?>
      <button class="btn btn-primary" onclick="updateProgress(<?= $app['id'] ?>, 'sudah_dikerjakan')">Pekerjaan Selesai</button>
    <?php elseif ($work_status=='completed' && $is_employer): ?>
      <button class="btn btn-primary" onclick="reviewPekerjaan(<?= $app['id'] ?>)">Review Pekerjaan</button>
    <?php elseif ($work_status=='reviewed' && $is_employer): ?>
      <button class="btn btn-primary" onclick="updateProgress(<?= $app['id'] ?>, 'sudah_direview')">Sudah di Review</button>
    <?php elseif ($work_status=='sudah_direview' && $is_employer): ?>
      <button class="btn btn-primary" onclick='openPaymentModal(<?= json_encode((string)$app['id']) ?>, <?= json_encode($app['bank_account'] ?? "") ?>, <?= json_encode($app['bank_name'] ?? "") ?>)'>Lakukan Pembayaran</button>
    <?php elseif ($work_status=='paid' && $is_employer): ?>
      <button class="btn btn-info" onclick="openRatingModal(<?= $app['id'] ?>)">Beri Penilaian</button>
    <?php elseif ($work_status=='paid'): ?>
      <span class="badge badge-pembayaran">Pembayaran Sudah Dilakukan</span>
      <a href="../<?= $app['payment_proof'] ?>" class="btn btn-secondary" target="_blank">Lihat Bukti Pembayaran</a>
      <?php if ($is_worker): ?>
        <button class="btn btn-success" onclick="konfirmasiPembayaran(<?= $app['id'] ?>)">Konfirmasi Pembayaran</button>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="progress-log">
    <ul>
      <li><?= date('d M Y', strtotime($app['updated_at'] ?? $app['applied_at'])) ?> - Status: <?= $status_display[$work_status] ?? 'Lolos' ?></li>
      <?php if ($work_status == 'reviewed'): ?>
        <li><i class="fa fa-star"></i> Sudah di-review oleh employer.</li>
      <?php endif; ?>
    </ul>
  </div>
</div>
<!-- Progress Card End -->
        <div style="margin-top:2rem;">
        <?php if ($work_status == 'not_started'): ?>
            <div class="info-card info-welcome">
                <i class="fa fa-user-check"></i>
                <div class="info-text">
                    <strong>Pekerja telah diterima.</strong><br>
                    Menunggu mulai pengerjaan.
                </div>
            </div>
        <?php elseif ($work_status == 'in_progress'): ?>
            <div class="info-card info-progress">
                <i class="fa fa-briefcase"></i>
                <div class="info-text">
                    <strong>Pekerjaan sedang berlangsung.</strong><br>
                    Pantau progres secara berkala.
                </div>
            </div>
        <?php elseif ($work_status == 'completed'): ?>
            <div class="info-card info-completed">
                <i class="fa fa-flag-checkered"></i>
                <div class="info-text">
                    <strong>Pekerja sudah menyelesaikan tugasnya.</strong><br>
                    Silakan review hasil pekerjaan.
                </div>
            </div>
        <?php elseif ($work_status == 'reviewed'): ?>
            <div class="info-card info-reviewed">
                <i class="fa fa-star"></i>
                <div class="info-text">
                    <strong>Tinjau hasil pekerjaan.</strong><br>
                    Jika sudah sesuai, klik 'Sudah di Review'.
                </div>
            </div>
        <?php elseif ($work_status == 'waiting_worker_confirmation'): ?>
            <div class="info-card info-payment">
                <i class="fa fa-money-bill"></i>
                <div class="info-text">
                    <strong>Pembayaran sudah dilakukan.</strong><br>
                    Tunggu konfirmasi dari pekerja.
                </div>
            </div>
        <?php elseif ($work_status == 'paid'): ?>
            <div class="info-card info-paid">
                <i class="fa fa-money-bill-wave"></i>
                <div class="info-text">
                    <strong>Pembayaran sudah dikonfirmasi.</strong><br>
                    Silakan beri penilaian untuk pekerja.
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <!-- Modal Pembayaran -->
    <div id="paymentModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closePaymentModal()">&times;</span>
        <h4 style="margin-bottom:1.2rem;font-size:1.18rem;font-weight:700;">Transfer Pembayaran</h4>
        <div style="margin-bottom:0.7rem;display:flex;align-items:center;gap:0.7rem;">
          <span style="color:#6366f1;font-size:1.3rem;"><i class="bi bi-bank2"></i></span>
          <div style="font-size:1.08rem;color:#222;">
            <b>Bank:</b> <span id="workerBankName"><?= htmlspecialchars($app['bank_name'] ?? '-') ?></span><br>
            <b>No Rekening:</b> <span id="workerBankAccount" style="font-family:monospace;font-size:1.08rem;color:#6366f1;"></span>
          </div>
        </div>
        <div style="font-size:0.97rem;color:#64748b;margin-bottom:1.1rem;">Transfer ke rekening di atas, lalu upload bukti pembayaran.</div>
        <form id="paymentProofForm" enctype="multipart/form-data">
          <input type="hidden" id="paymentAppId" name="application_id" value="<?= $app['id'] ?>">
          <label style="font-weight:600;color:#6366f1;margin-bottom:0.8rem;display:block;">Upload Bukti Transfer:</label>
          
          <!-- Custom File Upload Container -->
          <div class="file-upload-container" id="paymentFileContainer" style="border:2px dashed #d1d5db;border-radius:0.5rem;padding:1.5rem;text-align:center;background:#f9fafb;transition:all 0.2s ease;cursor:pointer;margin-bottom:1rem;">
            <div class="file-upload-icon" style="font-size:2.5rem;color:#6366f1;margin-bottom:0.8rem;">
              <i class="fa fa-cloud-upload-alt"></i>
            </div>
            <div class="file-upload-text" style="color:#6b7280;margin-bottom:0.5rem;font-weight:500;">
              <strong>Klik untuk memilih file</strong> atau drag & drop
            </div>
            <div class="file-upload-hint" style="font-size:0.875rem;color:#9ca3af;">
              Format yang didukung: JPG, PNG, PDF (Maksimal 2MB)
            </div>
            <input type="file" name="payment_proof" accept="image/*,application/pdf" required style="display:none;" id="paymentProofFile">
          </div>
          
          <!-- File Preview -->
          <div class="file-preview" id="paymentFilePreview" style="margin-top:0.5rem;padding:1rem;background:white;border:1px solid #e5e7eb;border-radius:0.375rem;display:none;">
            <div class="file-info" style="display:flex;align-items:center;gap:0.5rem;">
              <div class="file-icon" style="font-size:1.5rem;color:#6366f1;">
                <i class="fa fa-file-alt"></i>
              </div>
              <div class="file-details" style="flex:1;">
                <div class="file-name" id="paymentFileName" style="font-weight:500;color:#374151;"></div>
                <div class="file-size" id="paymentFileSize" style="font-size:0.875rem;color:#6b7280;"></div>
              </div>
              <button type="button" class="file-remove" onclick="removePaymentFile()" style="background:none;border:none;color:#ef4444;cursor:pointer;padding:0.25rem;border-radius:0.25rem;transition:background-color 0.2s ease;">
                <i class="fa fa-times"></i>
              </button>
            </div>
          </div>
          
          <button type="submit" class="btn btn-success" style="width:100%;font-size:1.08rem;padding:0.7rem 0;border-radius:2rem;">Kirim Bukti Pembayaran</button>
        </form>
      </div>
    </div>
    <!-- Modal Penilaian -->
    <div id="ratingModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closeRatingModal()">&times;</span>
        <h4>Beri Penilaian untuk Pekerja</h4>
        <form id="ratingForm">
          <input type="hidden" name="application_id" value="<?= $app['id'] ?>"> 
          <label>Rating:</label>
          <select name="rating" required>
            <option value="">Pilih rating</option>
            <option value="5">5 - Sangat Baik</option>
            <option value="4">4 - Baik</option>
            <option value="3">3 - Cukup</option>
            <option value="2">2 - Kurang</option>
            <option value="1">1 - Buruk</option>
          </select>
          <label>Komentar:</label>
          <textarea name="comment" required></textarea>
          <button type="submit" class="btn btn-primary">Kirim Penilaian</button>
        </form>
      </div>
    </div>
    <div id="notif" class="notif" style="display:none;position:fixed;top:2rem;right:2rem;background:#10b981;color:#fff;padding:1rem 1.5rem;border-radius:0.7rem;z-index:10000;font-weight:600;font-size:1.05rem;box-shadow:0 2px 8px rgba(16,185,129,0.13);animation:fadeInNotif 0.3s;"></div>
    <script src="../scripts/tracking.js"></script>
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
        body: 'app_id=' + encodeURIComponent(appId) + '&new_status=completed'
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