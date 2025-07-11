<?php
include_once '../../src/db/connection.php';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    echo '<h2>Profil tidak ditemukan.</h2>';
    exit();
}
// Fetch user data (now with new fields)
$query = mysqli_query($conn, "SELECT name, email, profile_picture, bio, specializations, linkedin_url, instagram_url, facebook_url, twitter_url, education, portfolio_url, cv_url FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
if (!$user) {
    echo '<h2>Profil tidak ditemukan.</h2>';
    exit();
}
// Fetch jobs posted
$jobs_posted = mysqli_query($conn, "SELECT * FROM jobs WHERE posted_by='$user_id' ORDER BY created_at DESC");
// Fetch jobs applied/accepted
$jobs_applied = mysqli_query($conn, "SELECT a.*, j.title, j.company_name, j.id as job_id, j.company_email FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.user_id='$user_id' ORDER BY a.applied_at DESC");
// Fetch received reviews
$reviews_received = mysqli_query($conn, "SELECT r.*, j.title, u.name as reviewer_name FROM reviews r JOIN jobs j ON r.job_id = j.id JOIN users u ON r.reviewer_id = u.id WHERE r.reviewee_id='$user_id' ORDER BY r.created_at DESC");
// Count stats
$count_jobs_posted = mysqli_num_rows($jobs_posted);
$count_jobs_applied = mysqli_num_rows($jobs_applied);
$count_reviews = mysqli_num_rows($reviews_received);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?php echo htmlspecialchars($user['name']); ?> | LocalLink</title>
    <link rel="stylesheet" href="../style/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fb; }
        .profile-main { max-width: 900px; margin: 2.5rem auto; padding: 0 1rem; }
        .profile-header { display: flex; flex-direction: column; align-items: center; background: #fff; border-radius: 1.2rem; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 2.5rem 2rem 2rem 2rem; margin-bottom: 2.5rem; }
        .profile-avatar { margin-bottom: 1.2rem; }
        .avatar { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb; background: #f3f4f6; }
        .profile-name { font-size: 2rem; font-weight: 700; margin-bottom: 0.3rem; }
        .profile-email { color: #888; font-size: 1.05rem; margin-bottom: 0.7rem; }
        .profile-bio { color: #555; margin-bottom: 0.7rem; text-align: left; }
        .profile-spec { color: #555; margin-bottom: 0.7rem; }
        .profile-placeholder { color: #bbb; font-style: italic; }
        .profile-stats { display: flex; gap: 2.5rem; margin-top: 1.2rem; }
        .stat-card { background: #f3f4f6; border-radius: 0.7rem; padding: 1rem 1.5rem; text-align: center; min-width: 120px; }
        .stat-count { font-size: 1.4rem; font-weight: 700; color: #6366f1; }
        .stat-label { font-size: 1rem; color: #555; }
        .profile-sections { display: flex; flex-wrap: wrap; gap: 2rem; }
        .profile-section-card { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 2rem 1.5rem; flex: 1 1 270px; min-width: 270px; }
        .section-title { font-size: 1.2rem; font-weight: 600; color: #6366f1; margin-bottom: 1.2rem; }
        .job-list, .review-list { list-style: none; padding: 0; margin: 0; }
        .job-card, .review-card { background: #f8f9fb; border-radius: 0.7rem; padding: 1rem 1.2rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(99,102,241,0.04); }
        .job-title { font-weight: 600; color: #333; }
        .company-name { color: #6366f1; font-weight: 500; margin-left: 0.5rem; }
        .badge-status { border-radius: 0.5rem; padding: 0.1rem 0.5rem; font-size: 0.92rem; font-weight: 600; margin-left: 0.3rem; min-width: 0; }
        .badge-open { background: #6366f1; color: #fff; }
        .badge-closed { background: #e5e7eb; color: #888; }
        .rating { color: #fbbf24; font-size: 1.1rem; margin-left: 0.5rem; }
        .review-meta { color: #888; font-size: 0.95rem; margin-bottom: 0.3rem; }
        .review-comment { color: #444; margin-top: 0.3rem; }
        .profile-extra { display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 2.5rem; }
        .profile-extra-card { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 1.5rem 1.2rem; flex: 1 1 270px; min-width: 270px; }
        .social-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.7rem; }
        .social-list li { display: flex; align-items: center; gap: 0.7rem; }
        .social-list a { color: #6366f1; text-decoration: none; font-weight: 500; }
        .social-list a:hover { text-decoration: underline; }
        .cv-link, .portfolio-link { color: #6366f1; font-weight: 500; text-decoration: none; }
        .cv-link:hover, .portfolio-link:hover { text-decoration: underline; }
        @media (max-width: 900px) { .profile-sections, .profile-extra { flex-direction: column; gap: 1.5rem; } .profile-header { padding: 2rem 0.7rem; } }
    </style>
</head>
<body>
    <div class="profile-main">
        <!-- Profile Header: Match dashboard style -->
        <section class="profile-section" style="margin-bottom: 2.5rem;">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
                        <img src="<?php echo '../' . htmlspecialchars($user['profile_picture']); ?>" alt="Avatar" class="avatar">
                    <?php else: ?>
                        <i class="fa fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name" id="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="profile-bio" id="profile-bio">
                        <?php if (!empty($user['bio'])): ?>
                            <span style="font-style:italic; color:#555;"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></span>
                        <?php else: ?>
                            <span class="profile-placeholder" style="font-style:normal; color:#aaa; display:inline-block; margin:0; padding:0; text-align:left;">Belum menambahkan bio.</span>
                        <?php endif; ?>
                    </p>
                    <p class="profile-spec" id="profile-spec">
                        <strong>Spesialisasi:</strong> <?php echo !empty($user['specializations']) ? htmlspecialchars($user['specializations']) : '<span class="profile-placeholder">Belum menambahkan spesialisasi.</span>'; ?>
                    </p>
                </div>
            </div>
        </section>
        <div class="profile-extra">
            <div class="profile-extra-card">
                <div class="section-title"><i class="fa fa-address-book"></i> Kontak & Sosial Media</div>
                <ul class="social-list">
                    <li><i class="fa fa-envelope"></i> <span><?php echo htmlspecialchars($user['email']); ?></span></li>
                    <?php if (!empty($user['linkedin_url'])): ?>
                        <li><i class="fab fa-linkedin"></i> <a href="<?php echo htmlspecialchars($user['linkedin_url']); ?>" target="_blank">LinkedIn</a></li>
                    <?php endif; ?>
                    <?php if (!empty($user['instagram_url'])): ?>
                        <li><i class="fab fa-instagram"></i> <a href="<?php echo htmlspecialchars($user['instagram_url']); ?>" target="_blank">Instagram</a></li>
                    <?php endif; ?>
                    <?php if (!empty($user['facebook_url'])): ?>
                        <li><i class="fab fa-facebook"></i> <a href="<?php echo htmlspecialchars($user['facebook_url']); ?>" target="_blank">Facebook</a></li>
                    <?php endif; ?>
                    <?php if (!empty($user['twitter_url'])): ?>
                        <li><i class="fab fa-twitter"></i> <a href="<?php echo htmlspecialchars($user['twitter_url']); ?>" target="_blank">Twitter</a></li>
                    <?php endif; ?>
                    <?php if (empty($user['linkedin_url']) && empty($user['instagram_url']) && empty($user['facebook_url']) && empty($user['twitter_url'])): ?>
                        <li><span class="profile-placeholder">Belum menambahkan sosial media.</span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="profile-extra-card">
                <div class="section-title"><i class="fa fa-graduation-cap"></i> Riwayat Pendidikan</div>
                <?php if (!empty($user['education'])): ?>
                    <div style="white-space:pre-line;color:#444;line-height:1.6;"><?php echo nl2br(htmlspecialchars($user['education'])); ?></div>
                <?php else: ?>
                    <span class="profile-placeholder">Belum menambahkan riwayat pendidikan.</span>
                <?php endif; ?>
            </div>
            <div class="profile-extra-card">
                <div class="section-title"><i class="fa fa-folder-open"></i> Portfolio & CV</div>
                <?php if (!empty($user['portfolio_url'])): ?>
                    <div><i class="fa fa-globe"></i> <a href="<?php echo htmlspecialchars($user['portfolio_url']); ?>" class="portfolio-link" target="_blank">Lihat Portfolio</a></div>
                <?php endif; ?>
                <?php if (!empty($user['cv_url'])): ?>
                    <div style="margin-top:0.7rem;"><i class="fa fa-file-pdf"></i> <a href="<?php echo htmlspecialchars($user['cv_url']); ?>" class="cv-link" target="_blank">Download CV</a></div>
                <?php endif; ?>
                <?php if (empty($user['portfolio_url']) && empty($user['cv_url'])): ?>
                    <span class="profile-placeholder">Belum menambahkan portfolio atau CV.</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-sections">
            <div class="profile-section-card">
                <div class="section-title"><i class="fa fa-briefcase"></i> Pekerjaan Diposting</div>
                <?php if ($count_jobs_posted === 0): ?>
                    <p style="color:#aaa;">Belum memposting pekerjaan apapun.</p>
                <?php else: ?>
                    <ul class="job-list">
                    <?php mysqli_data_seek($jobs_posted, 0); while ($job = mysqli_fetch_assoc($jobs_posted)): ?>
                        <li class="job-card">
                            <span class="job-title"><?php echo htmlspecialchars($job['title']); ?></span>
                            <span class="badge-status <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'badge-open' : 'badge-closed'; ?>">
                                <?php echo ($job['status'] ?? 'TERBUKA') === 'TERBUKA' ? 'Terbuka' : 'Tertutup'; ?>
                            </span>
                            <div style="color:#888;font-size:0.97rem;margin-top:0.2rem;">
                                <?php echo date('d M Y', strtotime($job['created_at'])); ?>
                            </div>
                            <div class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></div>
                            <a href="../../src/pages/job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-outline btn-sm" style="margin-top:0.5rem;">Lihat Detail</a>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="profile-section-card">
                <div class="section-title"><i class="fa fa-clipboard-check"></i> Pekerjaan Dilamar</div>
                <?php if ($count_jobs_applied === 0): ?>
                    <p style="color:#aaa;">Belum melamar pekerjaan apapun.</p>
                <?php else: ?>
                    <ul class="job-list">
                    <?php mysqli_data_seek($jobs_applied, 0); while ($app = mysqli_fetch_assoc($jobs_applied)): ?>
                        <li class="job-card">
                            <span class="job-title"><?php echo htmlspecialchars($app['title']); ?></span>
                            <span class="company-name"><?php echo htmlspecialchars($app['company_name']); ?></span>
                            <span class="badge-status badge-open" style="margin-left:0.5rem;">
                                <?php echo strtoupper($app['status']); ?>
                            </span>
                            <div style="color:#888;font-size:0.97rem;margin-top:0.2rem;">
                                Dilamar: <?php echo date('d M Y', strtotime($app['applied_at'])); ?>
                            </div>
                            <a href="../../src/pages/job-details.php?id=<?php echo $app['job_id']; ?>" class="btn btn-outline btn-sm" style="margin-top:0.5rem;">Lihat Detail</a>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="profile-section-card">
                <div class="section-title"><i class="fa fa-star"></i> Penilaian Diterima</div>
                <?php if ($count_reviews === 0): ?>
                    <p style="color:#aaa;">Belum menerima penilaian apapun.</p>
                <?php else: ?>
                    <ul class="review-list">
                    <?php mysqli_data_seek($reviews_received, 0); while ($rev = mysqli_fetch_assoc($reviews_received)): ?>
                        <li class="review-card">
                            <div class="review-meta">
                                <strong><?php echo htmlspecialchars($rev['reviewer_name']); ?></strong> pada <span><?php echo htmlspecialchars($rev['title']); ?></span>
                                <span class="rating">
                                    <?php for ($i=0; $i<$rev['rating']; $i++) echo '★'; ?><?php for ($i=$rev['rating']; $i<5; $i++) echo '☆'; ?>
                                </span>
                                <span style="margin-left:0.7rem;color:#bbb;font-size:0.93rem;"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></span>
                            </div>
                            <div class="review-comment"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></div>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer-bottom" style="text-align:center;margin-top:2rem;padding-top:1rem;border-top:1px solid #e5e7eb;color:#9ca3af;font-size:0.875rem;">
            <p>&copy; 2025 Quick Lance. Seluruh Hak Cipta Dilindungi.</p>
        </div>
    </footer>
    <script>
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
</script>
</body>
</html> 