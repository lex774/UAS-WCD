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
$query = mysqli_query($conn, "SELECT name, email, profile_picture, bio, specializations FROM users WHERE id='$user_id'");
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
                $update = mysqli_query($conn, "UPDATE users SET name='$name', email='$email' $set_password $set_profile_picture $set_bio $set_spec WHERE id='$user_id'");
                if ($update) {
                    $success = 'Profil berhasil diperbarui.';
                    $_SESSION['user_name'] = $name;
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['bio'] = $bio;
                    $user['specializations'] = $specializations;
                    $user['profile_picture'] = $profile_picture;
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
});
</script>
<body>
<div class="container">
    <section class="edit-profile-section">
        <h2>Edit Profil Anda</h2>
        <p>Perbarui informasi pribadi Anda. Perubahan akan diterapkan di seluruh platform.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($success && $success !== 'Foto profil berhasil dihapus.'): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="avatar-upload" style="display:flex;align-items:center;gap:1.2rem;margin-bottom:2rem;">
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div class="avatar-preview" style="position:relative;">
                        <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars('../' . $user['profile_picture']); ?>" alt="Pratinjau Avatar" class="avatar-image" id="avatarFullPreview" style="cursor:pointer;">
                        <?php else: ?>
                            <i class="fa fa-user-circle" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:6rem;color:#bdbdbd;z-index:1;"></i>
                        <?php endif; ?>
                        <span class="avatar-size"></span>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:0.7rem;align-items:flex-start;">
                    <label for="avatar" class="lihat-foto-btn" style="cursor:pointer;display:flex;align-items:center;gap:0.4rem;background:none;border:none;padding:0;font-size:1rem;color:var(--primary-color);transition:color 0.2s;">
                        <span>Lihat Foto</span>
                        <i class="fa fa-arrow-right arrow-hover" style="font-size:1.3rem;transition:transform 0.2s, color 0.2s;"></i>
                    </label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none;">
                    <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])): ?>
                        <button type="submit" name="delete_avatar" value="1" style="background:none;border:none;color:var(--primary-color);padding:0;font-size:1rem;cursor:pointer;">Hapus</button>
                    <?php endif; ?>
                </div>
            </div>
            <style>
            .lihat-foto-btn:hover .arrow-hover {
                color: #4f46e5;
                transform: translateX(6px);
            }
            </style>
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="input" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="Masukkan nama lengkap Anda" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Masukkan email Anda" required>
            </div>
            <div class="form-group">
                <label for="bio">Bio (Opsional, maks 500 karakter)</label>
                <textarea id="bio" name="bio" class="textarea" maxlength="500" placeholder="Ceritakan sedikit tentang diri dan keahlian Anda..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="specializations">Spesialisasi (Opsional, pisahkan dengan koma)</label>
                <input type="text" id="specializations" name="specializations" class="input" value="<?php echo htmlspecialchars($user['specializations'] ?? ''); ?>" placeholder="misal: Desain Web, Penulisan Konten, Plumbing">
            </div>
            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
            </div>
        </form>
    </section>
</div>
<footer>
    <div class="footer-bottom">
        <p>&copy; 2025 LocalLink. Seluruh Hak Cipta Dilindungi.</p>
    </div>
</footer>
<!-- Modal Fullscreen Avatar -->
<div id="avatarModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
    <span id="closeAvatarModal" style="position:absolute;top:30px;right:40px;font-size:2.5rem;color:#fff;cursor:pointer;z-index:10001;">&times;</span>
    <img id="avatarModalImg" src="" alt="Foto Profil" style="max-width:90vw;max-height:80vh;border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,0.25);border:4px solid #fff;">
</div>
</body>
</html> 