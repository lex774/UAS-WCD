<?php
require_once '../../src/db/connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$query = mysqli_query($conn, "SELECT name, email, profile_picture, bio, specializations, linkedin_url, instagram_url, facebook_url, twitter_url, education, portfolio_url, cv_url FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses hapus foto profil
    if (isset($_POST['delete_avatar'])) {
        if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])) {
            unlink(__DIR__ . '/../' . $user['profile_picture']);
        }
        mysqli_query($conn, "UPDATE users SET profile_picture=NULL WHERE id='$user_id'");
        $user['profile_picture'] = null;
        $success = 'Foto profil berhasil dihapus.';
    } else {
        $name = trim($_POST['name'] ?? $user['name']);
        $email = trim($_POST['email'] ?? $user['email']);
        $bio = trim($_POST['bio'] ?? $user['bio']);
        $specializations = trim($_POST['specializations'] ?? $user['specializations']);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';
        $profile_picture = $user['profile_picture'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_name = $_FILES['avatar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed_ext)) {
                $new_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $upload_path = $upload_dir . $new_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $profile_picture = 'uploads/' . $new_name;
                } else {
                    $error = 'Gagal mengupload foto.';
                }
            } else {
                $error = 'Ekstensi file tidak didukung.';
            }
        }
        $linkedin_url = trim($_POST['linkedin_url'] ?? $user['linkedin_url']);
        $instagram_url = trim($_POST['instagram_url'] ?? $user['instagram_url']);
        $facebook_url = trim($_POST['facebook_url'] ?? $user['facebook_url']);
        $twitter_url = trim($_POST['twitter_url'] ?? $user['twitter_url']);
        $education = trim($_POST['education'] ?? $user['education']);
        $portfolio_url = trim($_POST['portfolio_url'] ?? $user['portfolio_url']);
        $cv_url = $user['cv_url'] ?? null;
        // Handle CV upload
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['cv_file']['tmp_name'];
            $file_name = $_FILES['cv_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['pdf'];
            if (in_array($file_ext, $allowed_ext)) {
                $new_name = 'cv_' . $user_id . '_' . time() . '.' . $file_ext;
                $upload_dir = __DIR__ . '/../uploads/cv/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $upload_path = $upload_dir . $new_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $cv_url = 'uploads/cv/' . $new_name;
                } else {
                    $error = 'Gagal mengupload CV.';
                }
            } else {
                $error = 'CV harus berupa file PDF.';
            }
        }
        if ($name === '' || $email === '') {
            $error = 'Nama dan email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif ($password !== '' && $password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id!='$user_id'");
            if (mysqli_num_rows($check) > 0) {
                $error = 'Email sudah digunakan user lain.';
            } else {
                $set_password = '';
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $set_password = ", password='$hash'";
                }
                $set_profile_picture = $profile_picture ? ", profile_picture='$profile_picture'" : '';
                $set_bio = ", bio='" . mysqli_real_escape_string($conn, $bio) . "'";
                $set_spec = ", specializations='" . mysqli_real_escape_string($conn, $specializations) . "'";
                $set_linkedin = ", linkedin_url='" . mysqli_real_escape_string($conn, $linkedin_url) . "'";
                $set_instagram = ", instagram_url='" . mysqli_real_escape_string($conn, $instagram_url) . "'";
                $set_facebook = ", facebook_url='" . mysqli_real_escape_string($conn, $facebook_url) . "'";
                $set_twitter = ", twitter_url='" . mysqli_real_escape_string($conn, $twitter_url) . "'";
                $set_education = ", education='" . mysqli_real_escape_string($conn, $education) . "'";
                $set_portfolio = ", portfolio_url='" . mysqli_real_escape_string($conn, $portfolio_url) . "'";
                $set_cv = $cv_url ? ", cv_url='" . mysqli_real_escape_string($conn, $cv_url) . "'" : '';
                $update = mysqli_query($conn, "UPDATE users SET name='$name', email='$email' $set_password $set_profile_picture $set_bio $set_spec $set_linkedin $set_instagram $set_facebook $set_twitter $set_education $set_portfolio $set_cv WHERE id='$user_id'");
                if ($update) {
                    $success = 'Profil berhasil diperbarui.';
                    $_SESSION['user_name'] = $name;
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['bio'] = $bio;
                    $user['specializations'] = $specializations;
                    $user['profile_picture'] = $profile_picture;
                    $user['linkedin_url'] = $linkedin_url;
                    $user['instagram_url'] = $instagram_url;
                    $user['facebook_url'] = $facebook_url;
                    $user['twitter_url'] = $twitter_url;
                    $user['education'] = $education;
                    $user['portfolio_url'] = $portfolio_url;
                    $user['cv_url'] = $cv_url;
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Gagal memperbarui profil.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="../style/edit-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('avatar');
    const preview = document.querySelector('.avatar-image');
    const icon = document.querySelector('.fa-user-circle');
    const form = document.querySelector('form');

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            // Preview instan
            const reader = new FileReader();
            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.style.display = 'block';
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(file);

            // Upload AJAX ke server & update database
            const formData = new FormData();
            formData.append('avatar', file);

            fetch('ajax-upload-avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.url) {
                    preview.src = data.url; // Ganti src dengan url file di server
                    preview.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(() => alert('Gagal upload foto.'));
        } else {
            preview.src = '';
            preview.style.display = 'none';
            if (icon) icon.style.display = 'block';
        }
    });

    const avatarImg = document.getElementById('avatarFullPreview');
    const avatarModal = document.getElementById('avatarModal');
    const avatarModalImg = document.getElementById('avatarModalImg');
    const closeAvatarModal = document.getElementById('closeAvatarModal');
    if (avatarImg) {
        avatarImg.addEventListener('click', function() {
            avatarModal.style.display = 'flex';
            avatarModalImg.src = avatarImg.src;
            document.body.style.overflow = 'hidden';
        });
    }
    if (closeAvatarModal) {
        closeAvatarModal.onclick = function() {
            avatarModal.style.display = 'none';
            document.body.style.overflow = '';
        };
    }
    avatarModal.onclick = function(e) {
        if (e.target === avatarModal) {
            avatarModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Custom file input for CV
    const cvInput = document.getElementById('cv_file');
    const cvText = document.getElementById('cv-file-text');
    const cvInfo = document.getElementById('cv-file-info');
    if (cvInput && cvText) {
        cvInput.addEventListener('change', function(e) {
            if (cvInput.files && cvInput.files[0]) {
                cvText.textContent = cvInput.files[0].name;
                cvInfo.textContent = 'File terpilih: ' + cvInput.files[0].name;
            } else {
                cvText.textContent = 'Pilih file CV (PDF)';
                cvInfo.textContent = '';
            }
        });
    }
});
</script>
<body>
<div class="container">
    <h2 style="font-size:1.7rem;font-weight:700;margin:2.5rem 0 1.5rem 0;text-align:left;color:#6366f1;">Edit Profil</h2>
    <form method="POST" enctype="multipart/form-data" style="max-width:900px;margin:0 auto;display:flex;flex-direction:column;gap:1.5rem;">
        <!-- Card: Personal Info (full width) -->
        <div class="edit-card profile-card-hover" style="background:#fff;box-shadow:0 4px 16px rgba(99,102,241,0.07);border-radius:1.2rem;padding:2rem 1.5rem;display:flex;align-items:center;gap:1.5rem;margin-bottom:0;transition:box-shadow 0.2s,transform 0.2s;">
            <div class="avatar-upload" style="margin-bottom:0;">
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div class="avatar-preview" style="position:relative;width:80px;height:80px;border-radius:50%;overflow:hidden;border:2px solid #e5e7eb;background:#f3f4f6;">
                        <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars('../' . $user['profile_picture']); ?>" alt="Pratinjau Avatar" class="avatar-image" id="avatarFullPreview" style="cursor:pointer;width:80px;height:80px;object-fit:cover;">
                        <?php else: ?>
                            <i class="fa fa-user-circle" style="font-size:3.5rem;color:#bdbdbd;"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:0.5rem;align-items:flex-start;margin-left:1rem;">
                    <label for="avatar" class="lihat-foto-btn" style="cursor:pointer;display:flex;align-items:center;gap:0.4rem;background:none;border:none;padding:0;font-size:1rem;color:var(--primary-color);transition:color 0.2s;">
                        <span>Ganti Foto</span>
                        <i class="fa fa-arrow-right arrow-hover" style="font-size:1.1rem;transition:transform 0.2s, color 0.2s;"></i>
                    </label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none;">
                    <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
                        <button type="submit" name="delete_avatar" value="1" style="background:none;border:none;color:var(--primary-color);padding:0;font-size:1rem;cursor:pointer;">Hapus Foto</button>
                    <?php endif; ?>
                </div>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;gap:1rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="name"><i class="fa fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="input" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="Masukkan nama lengkap Anda" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="email"><i class="fa fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" class="input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Masukkan email Anda" required>
                </div>
            </div>
        </div>
        <!-- All other cards full width, stacked -->
        <div class="edit-card profile-card-hover" style="background:#fff;box-shadow:0 4px 16px rgba(99,102,241,0.07);border-radius:1.2rem;padding:2rem 1.5rem;transition:box-shadow 0.2s,transform 0.2s;">
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="bio"><i class="fa fa-info-circle"></i> Bio <span style="color:#bbb;font-weight:400;">(Opsional, maks 500 karakter)</span></label>
                <textarea id="bio" name="bio" class="textarea" maxlength="500" placeholder="Ceritakan sedikit tentang diri dan keahlian Anda..." style="min-height:80px;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label for="specializations"><i class="fa fa-certificate"></i> Spesialisasi <span style="color:#bbb;font-weight:400;">(Pisahkan dengan koma)</span></label>
                <input type="text" id="specializations" name="specializations" class="input" value="<?php echo htmlspecialchars($user['specializations'] ?? ''); ?>" placeholder="misal: Desain Web, Penulisan Konten, Plumbing">
            </div>
        </div>
        <div class="edit-card profile-card-hover" style="background:#fff;box-shadow:0 4px 16px rgba(99,102,241,0.07);border-radius:1.2rem;padding:2rem 1.5rem;transition:box-shadow 0.2s,transform 0.2s;">
            <h3 style="font-size:1.08rem;font-weight:600;color:#6366f1;margin-bottom:1.2rem;"><i class="fa fa-address-book"></i> Kontak & Sosial Media</h3>
            <div style="display:grid;grid-template-columns:1fr;gap:1.2rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="linkedin_url"><i class="fab fa-linkedin"></i> LinkedIn</label>
                    <input type="url" id="linkedin_url" name="linkedin_url" class="input" value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="instagram_url"><i class="fab fa-instagram"></i> Instagram</label>
                    <input type="url" id="instagram_url" name="instagram_url" class="input" value="<?php echo htmlspecialchars($user['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/username">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="facebook_url"><i class="fab fa-facebook"></i> Facebook</label>
                    <input type="url" id="facebook_url" name="facebook_url" class="input" value="<?php echo htmlspecialchars($user['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/username">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="twitter_url"><i class="fab fa-twitter"></i> Twitter</label>
                    <input type="url" id="twitter_url" name="twitter_url" class="input" value="<?php echo htmlspecialchars($user['twitter_url'] ?? ''); ?>" placeholder="https://twitter.com/username">
                </div>
            </div>
        </div>
        <div class="edit-card profile-card-hover" style="background:#fff;box-shadow:0 4px 16px rgba(99,102,241,0.07);border-radius:1.2rem;padding:2rem 1.5rem;transition:box-shadow 0.2s,transform 0.2s;">
            <h3 style="font-size:1.08rem;font-weight:600;color:#6366f1;margin-bottom:1.2rem;"><i class="fa fa-graduation-cap"></i> Riwayat Pendidikan</h3>
            <div class="form-group" style="margin-bottom:0;">
                <textarea id="education" name="education" class="textarea" maxlength="1000" placeholder="Contoh: S1 Teknik Informatika, Universitas ABC, 2018-2022\nSMA 1 Kota XYZ, 2015-2018" style="min-height:70px;"><?php echo htmlspecialchars($user['education'] ?? ''); ?></textarea>
            </div>
        </div>
        <div class="edit-card profile-card-hover" style="background:#fff;box-shadow:0 4px 16px rgba(99,102,241,0.07);border-radius:1.2rem;padding:2rem 1.5rem;transition:box-shadow 0.2s,transform 0.2s;">
            <h3 style="font-size:1.08rem;font-weight:600;color:#6366f1;margin-bottom:1.2rem;"><i class="fa fa-folder-open"></i> Portfolio & CV</h3>
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="portfolio_url"><i class="fa fa-globe"></i> Link Portfolio</label>
                <input type="url" id="portfolio_url" name="portfolio_url" class="input" value="<?php echo htmlspecialchars($user['portfolio_url'] ?? ''); ?>" placeholder="https://portfolio.com/username">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label for="cv_file"><i class="fa fa-file-pdf"></i> Upload CV (PDF)</label>
                <div class="file-upload-container" style="margin-bottom:0.5rem;">
                    <label for="cv_file" class="file-upload-label" style="display:flex;align-items:center;gap:0.7rem;cursor:pointer;padding:0.7rem 1.2rem;background:#f3f4f6;border-radius:0.5rem;border:1.5px dashed #6366f1;font-weight:500;color:#6366f1;transition:background 0.2s;">
                        <span class="file-upload-icon"><i class="fa fa-upload"></i></span>
                        <span class="file-upload-text" id="cv-file-text">Pilih file CV (PDF)</span>
                    </label>
                    <input type="file" id="cv_file" name="cv_file" class="file-input" accept=".pdf" style="display:none;">
                </div>
                <div id="cv-file-info" class="file-info" style="color:#6366f1;font-size:0.97rem;margin-bottom:0.3rem;"></div>
                <?php if (!empty($user['cv_url'])): ?>
                    <div style="margin-top:0.5rem;"><a href="../<?php echo htmlspecialchars($user['cv_url']); ?>" target="_blank" style="color:#6366f1;font-weight:500;"><i class="fa fa-download"></i> Download CV Saat Ini</a></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="action-buttons" style="margin-top:0.5rem;display:flex;gap:1rem;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-top:1.2rem;"><?php echo $error; ?></div>
        <?php elseif ($success && $success !== 'Foto profil berhasil dihapus.'): ?>
            <div class="alert alert-success" style="margin-top:1.2rem;"><?php echo $success; ?></div>
        <?php endif; ?>
    </form>
</div>
<style>
.edit-card.profile-card-hover:hover {
    box-shadow: 0 8px 32px rgba(99,102,241,0.13), 0 2px 8px rgba(99,102,241,0.09);
    transform: translateY(-2px) scale(1.01);
}
</style>
<footer>
    <div class="footer-bottom">
        <p>&copy; 2025 Quick Lance. Seluruh Hak Cipta Dilindungi.</p>
    </div>
</footer>
<!-- Modal Fullscreen Avatar -->
<div id="avatarModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
    <span id="closeAvatarModal" style="position:absolute;top:30px;right:40px;font-size:2.5rem;color:#fff;cursor:pointer;z-index:10001;">&times;</span>
    <img id="avatarModalImg" src="" alt="Foto Profil" style="max-width:90vw;max-height:80vh;border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,0.25);border:4px solid #fff;">
</div>
</body>
</html> 