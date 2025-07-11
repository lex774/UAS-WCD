<?php
require_once '../db/connection.php';
session_start();

// Debug session
error_log("Session data in applicants-list.php: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../user/page/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
error_log("User ID in applicants-list.php: " . $user_id);

// Validasi job_id
if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    echo '<p>Job ID tidak ditemukan.</p>';
    exit();
}
$job_id = intval($_GET['job_id']);

// Ambil data job
$job_query = mysqli_query($conn, "SELECT * FROM jobs WHERE id='$job_id'");
$job = mysqli_fetch_assoc($job_query);
if (!$job) {
    echo '<p>Pekerjaan tidak ditemukan.</p>';
    exit();
}
// Cek apakah user adalah pemilik job
if ($job['posted_by'] != $user_id) {
    echo '<p>Anda tidak berhak mengakses halaman ini.</p>';
    exit();
}

// Ambil daftar pelamar
$applicants = mysqli_query($conn, "SELECT a.*, u.name, u.email FROM applications a JOIN users u ON a.user_id = u.id WHERE a.job_id='$job_id' ORDER BY a.applied_at DESC");

$status_seleksi = [
    'menunggu',
    'direview',
    'terseleksi',
    'interview',
    'lolos',
    'tidak_terseleksi',
    'tidak_lolos'
];
$status_pekerjaan = [
    'dikerjakan',
    'sudah_dikerjakan',
    'pekerjaan_selesai',
    'menunggu_review',
    'pekerjaan_sesuai',
    'menunggu_pembayaran',
    'pembayaran_sudah_dilakukan',
    'menunggu_konfirmasi',
    'completed'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelamar - <?php echo htmlspecialchars($job['title']); ?> | LocalLink</title>
    <link rel="stylesheet" href="../../user/style/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(90deg, #6366f1 60%, #8b5cf6 100%);
            color: #fff;
            border: none;
            border-radius: 0.6rem;
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(99,102,241,0.13);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        }
        .back-btn:hover {
            background: linear-gradient(90deg, #4f46e5 60%, #6366f1 100%);
            box-shadow: 0 4px 16px rgba(99,102,241,0.18);
            transform: translateY(-2px) scale(1.03);
            color: #fff;
        }
        .applicants-list-cards {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .applicant-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(99,102,241,0.07), 0 1.5px 6px rgba(0,0,0,0.04);
            padding: 2rem 2rem 1.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            position: relative;
        }
        .applicant-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .applicant-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #23272f;
        }
        .applicant-email {
            font-size: 1rem;
            color: #6366f1;
            background: #f0f4ff;
            border-radius: 0.4rem;
            padding: 0.2rem 0.7rem;
        }
        .applicant-meta {
            font-size: 0.98rem;
            color: #6b7280;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .applicant-status {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        .applicant-actions {
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }
        .applicant-actions select {
            min-width: 150px;
        }
        @media (max-width: 700px) {
            .applicant-card { padding: 1.2rem 0.7rem 1rem 0.7rem; }
            .applicant-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .applicant-actions { flex-direction: column; align-items: stretch; }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 4vh 0;
            min-height: 100vh;
            box-sizing: border-box;
            transition: opacity 0.25s;
            opacity: 0;
        }
        .modal.show {
            display: flex !important;
            opacity: 1;
            animation: fadeInModal 0.3s;
        }
        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background: #fff;
            padding: 2rem 2.5rem;
            border-radius: 1.2rem;
            max-width: 600px;
            min-width: 320px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            animation: slideInModal 0.3s;
        }
        @keyframes slideInModal {
            from { transform: translateY(40px) scale(0.97); opacity: 0.7; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .modal-close {
            position: absolute;
            top: 12px;
            right: 18px;
            cursor: pointer;
            font-size: 1.7rem;
            color: #6366f1;
            background: none;
            border: none;
            z-index: 2;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #ef4444;
            background: #fef2f2;
            border-radius: 50%;
            transform: scale(1.13);
            box-shadow: 0 2px 8px rgba(239,68,68,0.10);
        }
        @media (max-width: 700px) {
            .modal-content { padding: 1.2rem 0.7rem; max-width: 98vw; }
        }
        /* Debug styles */
        .debug-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .error-message {
            background: #fef2f2;
            border: 1px solid #ef4444;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            color: #dc2626;
            font-weight: 600;
        }
        .notif {
            display: none;
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: #10b981;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 0.7rem;
            z-index: 10000;
            font-weight: 600;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(16,185,129,0.13);
            animation: fadeInNotif 0.3s;
        }
        @keyframes fadeInNotif {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .applicant-status-row {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 0.2rem;
            flex-wrap: wrap;
        }
        .applicant-status-label {
            font-size: 0.98rem;
            color: #6366f1;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0;
        }
        .applicant-status-label .fa {
            color: #6366f1;
        }
        .status-action-group .btn-status-menunggu {
            background: #f3f4f6; color: #6366f1; border: 1.5px solid #6366f1;
        }
        .status-action-group .btn-status-direview {
            background: #f0f9ff; color: #0ea5e9; border: 1.5px solid #0ea5e9;
        }
        .status-action-group .btn-status-terseleksi {
            background: #f0fdf4; color: #22c55e; border: 1.5px solid #22c55e;
        }
        .status-action-group .btn-status-interview {
            background: #fef9c3; color: #eab308; border: 1.5px solid #eab308;
        }
        .status-action-group .btn-status-lolos {
            background: #f0fdf4; color: #16a34a; border: 1.5px solid #16a34a;
        }
        .status-action-group .btn-status-tidak_terseleksi, .status-action-group .btn-status-tidak_lolos {
            background: #fef2f2; color: #ef4444; border: 1.5px solid #ef4444;
        }
        .status-action-group button {
            border-radius: 0.6rem;
            padding: 0.45rem 1.1rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            cursor: pointer;
            outline: none;
            margin-bottom: 0.2rem;
        }
        .status-action-group button:hover, .status-action-group button:focus {
            filter: brightness(0.97) contrast(1.1);
            box-shadow: 0 2px 8px rgba(99,102,241,0.10);
            transform: translateY(-2px) scale(1.03);
        }
        .status-action-group button:active {
            filter: brightness(0.93);
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }
        /* Ensure buttons are clickable */
        .status-action-group button {
            position: relative;
            z-index: 10;
            pointer-events: auto !important;
        }
        .status-action-group {
            position: relative;
            z-index: 5;
        }
        .progress-action-card {
            margin-top: 1rem;
            padding: 1.1rem 1.5rem;
            background: #f8fafc;
            border-radius: 0.7rem;
            box-shadow: 0 2px 8px rgba(99,102,241,0.07);
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: flex-end;
        }
        .progress-action-card .btn {
            font-size: 1rem;
            padding: 0.6rem 1.3rem;
        }
        .sub-progress-card {
            margin-top: 1.1rem;
            background: #f0f9ff;
            border: 1.5px solid #0ea5e9;
            border-radius: 0.8rem;
            box-shadow: 0 2px 8px rgba(14,165,233,0.07);
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1.2rem;
            animation: fadeIn 0.4s;
        }
        .sub-progress-content {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            width: 100%;
        }
        .sub-progress-icon {
            font-size: 2.2rem;
            color: #0ea5e9;
            background: #e0f2fe;
            border-radius: 50%;
            padding: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sub-progress-text {
            flex: 1;
            font-size: 1.08rem;
            color: #0369a1;
        }
        .sub-progress-btn {
            font-size: 1rem;
            padding: 0.7rem 1.5rem;
            border-radius: 0.6rem;
            background: #0ea5e9;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(14,165,233,0.09);
            transition: background 0.2s;
        }
        .sub-progress-btn:hover {
            background: #0369a1;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../../user/page/dashboard.php" class="back-btn"><i class="fa fa-arrow-left"></i> Kembali ke Dashboard</a>
        <h2>Daftar Pelamar</h2>
        <h3><?php echo htmlspecialchars($job['title']); ?> <span style="color:#6366f1;">(<?php echo htmlspecialchars($job['company_name']); ?>)</span></h3>
        
        <!-- Tombol Aksi (Pemberi Kerja) -->
        <?php while ($app = mysqli_fetch_assoc($applicants)): ?>
            <?php
            // Cek apakah sudah ada review untuk pelamar ini pada job ini
            $reviewed = false;
            $review_check = mysqli_query($conn, "SELECT id FROM reviews WHERE reviewer_id='$user_id' AND reviewee_id='{$app['user_id']}' AND job_id='$job_id'");
            if ($review_check && mysqli_num_rows($review_check) > 0) $reviewed = true;
            ?>
            <div class="applicant-card" data-app-id="<?php echo $app['id']; ?>">
                <div class="applicant-header">
                    <span class="applicant-name"><i class="fa fa-user"></i> <?php echo htmlspecialchars($app['name']); ?></span>
                    <span class="applicant-email"><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($app['email']); ?></span>
                </div>
                <div class="applicant-meta">
                    <span><i class="fa fa-calendar"></i> Melamar: <?php echo date('d M Y', strtotime($app['applied_at'])); ?></span>
                    <div class="applicant-status-row">
                        <?php
                        $status_label = '';
                        $status_db = strtolower(trim($app['status']));
                        switch($status_db) {
                            case 'menunggu': $status_label = 'Menunggu'; break;
                            case 'direview': $status_label = 'Direview'; break;
                            case 'terseleksi': $status_label = 'Terseleksi'; break;
                            case 'tidak_terseleksi': $status_label = 'Tidak Terseleksi'; break;
                            case 'interview': $status_label = 'Interview'; break;
                            case 'lolos': $status_label = 'Lolos'; break;
                            case 'tidak_lolos': $status_label = 'Tidak Lolos'; break;
                            default: $status_label = '';
                        }
                        $status_order = ['menunggu','direview','terseleksi','interview','lolos','tidak_terseleksi','tidak_lolos'];
                        $next_status = [
                            'menunggu' => ['direview','tidak_terseleksi'],
                            'direview' => ['terseleksi','tidak_terseleksi'],
                            'terseleksi' => ['interview','tidak_terseleksi'],
                            'interview' => ['lolos','tidak_lolos'],
                            'lolos' => [],
                            'tidak_terseleksi' => [],
                            'tidak_lolos' => []
                        ];
                        $status_label_map = [
                            'menunggu' => 'Menunggu',
                            'direview' => 'Direview',
                            'terseleksi' => 'Terseleksi',
                            'interview' => 'Interview',
                            'lolos' => 'Lolos',
                            'tidak_terseleksi' => 'Tidak Terseleksi',
                            'tidak_lolos' => 'Tidak Lolos'
                        ];
                        ?>
                        <span class="applicant-status-label"><i class="fa fa-info-circle"></i> Status Lamaran: <span class="badge badge-status badge-<?php echo htmlspecialchars($status_db); ?>"><?php echo $status_label; ?></span></span>
                        <div class="status-action-group" style="display:inline-flex;gap:0.5rem;margin-left:1.2rem;flex-wrap:wrap;">
                            <?php
                            $status_icons = [
                                'menunggu' => '<i class="fa fa-hourglass-half"></i>',
                                'direview' => '<i class="fa fa-search"></i>',
                                'terseleksi' => '<i class="fa fa-check-circle"></i>',
                                'interview' => '<i class="fa fa-comments"></i>',
                                'lolos' => '<i class="fa fa-trophy"></i>',
                                'tidak_terseleksi' => '<i class="fa fa-times-circle"></i>',
                                'tidak_lolos' => '<i class="fa fa-times-circle"></i>'
                            ];
                            ?>
                            <?php foreach ($status_label_map as $s => $label): ?>
                                <?php if ($s !== $status_db): ?>
                                    <button class="btn btn-outline btn-sm btn-status-<?php echo $s; ?>" onclick="updateStatus(<?php echo $app['id']; ?>, '<?php echo $s; ?>')" style="font-weight:600;" title="Ubah status ke <?php echo $label; ?>" data-debug="button-<?php echo $s; ?>">
                                        <?php echo $status_icons[$s]; ?> <?php echo $label; ?>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php if (strtolower($app['status']) === 'lolos'): ?>
                    <div class="sub-progress-card">
                        <div class="sub-progress-content">
                            <div class="sub-progress-icon"><i class="fa fa-location-arrow"></i></div>
                            <div class="sub-progress-text">
                                <strong>Pekerjaan sedang berlangsung!</strong><br>
                                Pantau dan lacak progres pekerjaan pelamar ini secara real-time.
                            </div>
                            <button class="btn btn-primary sub-progress-btn" onclick="window.location='tracking-pekerjaan.php?app_id=<?= $app['id'] ?>'">
                                <i class="fa fa-location-arrow"></i> Lacak Progres
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="applicant-actions">
                    <a class="btn btn-outline btn-sm" href="../../user/uploads/cv/<?php echo htmlspecialchars(basename($app['cv_file'])); ?>" target="_blank">
                        <i class="fa fa-file-pdf"></i> Lihat CV
                    </a>
                    <button class="btn btn-outline btn-sm" onclick="showLamaranModalAjax(<?php echo $app['id']; ?>)">
                        <i class="fa fa-eye"></i> Lihat Lamaran
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    </div>
    <div id="lamaranModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeLamaranModal()">&times;</span>
            <h4>Preview Lamaran</h4>
            <div id="lamaranContent">
                <div class="debug-info">Loading application details...</div>
            </div>
        </div>
    </div>
    <div id="notif" class="notif"></div>
    <!-- Modal Review -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeReviewModal()">&times;</span>
            <h4>Beri Penilaian untuk <span id="revieweeName"></span></h4>
            <form id="reviewForm">
                <input type="hidden" name="reviewee_id" id="revieweeId">
                <input type="hidden" name="job_id" id="reviewJobId">
                <div style="margin-bottom:1rem;">
                    <label for="rating">Rating:</label>
                    <select name="rating" id="rating" required style="margin-left:0.7rem;">
                        <option value="">Pilih rating</option>
                        <option value="5">5 - Sangat Baik</option>
                        <option value="4">4 - Baik</option>
                        <option value="3">3 - Cukup</option>
                        <option value="2">2 - Kurang</option>
                        <option value="1">1 - Buruk</option>
                    </select>
                </div>
                <div style="margin-bottom:1rem;">
                    <label for="comment">Komentar:</label><br>
                    <textarea name="comment" id="comment" rows="4" style="width:100%;" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Kirim Penilaian</button>
            </form>
        </div>
    </div>
    <!-- Modal Pembayaran -->
    <div id="paymentModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" onclick="closePaymentModal()">&times;</span>
        <h4>Transfer Pembayaran</h4>
        <p>No Rekening Pekerja: <b id="workerBankAccount"></b></p>
        <form id="paymentProofForm" enctype="multipart/form-data">
          <input type="hidden" name="application_id" id="paymentAppId">
          <label>Upload Bukti Transfer:</label>
          <input type="file" name="payment_proof" accept="image/*,application/pdf" required>
          <button type="submit" class="btn btn-success">Kirim Bukti Pembayaran</button>
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
    <script src="../scripts/applicants-list.js"></script>
    <script>
    // Map status ke label (harus ada di JS agar update badge berjalan)
    const statusLabelMap = {
        'menunggu': 'Menunggu',
        'direview': 'Direview',
        'terseleksi': 'Terseleksi',
        'interview': 'Interview',
        'lolos': 'Lolos',
        'tidak_terseleksi': 'Tidak Terseleksi',
        'tidak_lolos': 'Tidak Lolos'
    };
    
    // Update status lamaran (AJAX) - moved to top to ensure it's defined
    function updateStatus(appId, status) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update-application-status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    showNotif('Status lamaran berhasil diupdate!');
                    var card = document.querySelector('.applicant-card[data-app-id="' + appId + '"]');
                    if (card) {
                        // Update badge
                        var badge = card.querySelector('.badge-status');
                        if (badge) {
                            badge.textContent = statusLabelMap[status] || status;
                            badge.className = 'badge badge-status badge-' + status;
                        }
                        // Update tombol status
                        var group = card.querySelector('.status-action-group');
                        if (group) {
                            var status_label_map = {
                                'menunggu' => 'Menunggu',
                                'direview' => 'Direview',
                                'terseleksi' => 'Terseleksi',
                                'interview' => 'Interview',
                                'lolos' => 'Lolos',
                                'tidak_terseleksi' => 'Tidak Terseleksi',
                                'tidak_lolos' => 'Tidak Lolos'
                            };
                            var status_icons = {
                                'menunggu': '<i class="fa fa-hourglass-half"></i>',
                                'direview': '<i class="fa fa-search"></i>',
                                'terseleksi': '<i class="fa fa-check-circle"></i>',
                                'interview': '<i class="fa fa-comments"></i>',
                                'lolos': '<i class="fa fa-trophy"></i>',
                                'tidak_terseleksi': '<i class="fa fa-times-circle"></i>',
                                'tidak_lolos': '<i class="fa fa-times-circle"></i>'
                            };
                            group.innerHTML = '';
                            Object.keys(status_label_map).forEach(function(s) {
                                if (s !== status) {
                                    var btn = document.createElement('button');
                                    btn.className = 'btn btn-outline btn-sm btn-status-' + s;
                                    btn.style.fontWeight = '600';
                                    btn.title = 'Ubah status ke ' + status_label_map[s];
                                    btn.innerHTML = status_icons[s] + ' ' + status_label_map[s];
                                    btn.onclick = function() { updateStatus(appId, s); };
                                    group.appendChild(btn);
                                }
                            });
                        }
                    }
                } else {
                    showNotif('Gagal update status: ' + xhr.responseText);
                }
            }
        };
        xhr.send('id=' + appId + '&status=' + status);
    }
    
    function showLamaranModalAjax(appId) {
        var modal = document.getElementById('lamaranModal');
        document.getElementById('lamaranContent').innerHTML = '<div class="debug-info">Loading application details for ID: ' + appId + '...</div>';
        modal.classList.add('show');
        fetch('get-application-details.php?id=' + appId)
          .then(res => res.text())
          .then(html => {
            document.getElementById('lamaranContent').innerHTML = html;
          })
          .catch(error => {
            document.getElementById('lamaranContent').innerHTML = '<div class="error-message">Error loading application details: ' + error.message + '</div>';
          });
    }
    function closeLamaranModal() {
        var modal = document.getElementById('lamaranModal');
        modal.classList.remove('show');
        document.getElementById('lamaranContent').innerHTML = '<div class="debug-info">Loading application details...</div>';
    }
    // Notifikasi
    function showNotif(msg) {
        var notif = document.getElementById('notif');
        notif.innerText = msg;
        notif.style.display = 'block';
        notif.style.background = '#10b981';
        notif.style.color = '#fff';
        notif.style.fontWeight = '600';
        notif.style.fontSize = '1.05rem';
        notif.style.borderRadius = '0.7rem';
        notif.style.boxShadow = '0 2px 8px rgba(16,185,129,0.13)';
        setTimeout(()=>{ notif.style.display = 'none'; }, 2500);
    }
    // Modal close on click outside
    window.onclick = function(event) {
        var modal = document.getElementById('lamaranModal');
        if (event.target == modal) closeLamaranModal();
    }

    function openReviewModal(revieweeId, jobId, revieweeName) {
        document.getElementById('revieweeId').value = revieweeId;
        document.getElementById('reviewJobId').value = jobId;
        document.getElementById('revieweeName').textContent = revieweeName;
        document.getElementById('reviewModal').classList.add('show');
    }
    function closeReviewModal() {
        document.getElementById('reviewModal').classList.remove('show');
    }
    document.getElementById('reviewForm').onsubmit = async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        const res = await fetch('submit-review.php', { method: 'POST', body: data });
        const text = await res.text();
        if (text.trim() === 'success') {
            alert('Penilaian berhasil dikirim!');
            closeReviewModal();
            location.reload();
        } else {
            alert('Gagal mengirim penilaian: ' + text);
        }
    };
    </script>
</body>
</html> 