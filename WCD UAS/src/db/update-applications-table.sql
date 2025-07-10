-- Ubah kolom status pada tabel applications agar mendukung status lamaran yang lebih lengkap
ALTER TABLE `applications`
MODIFY COLUMN `status` ENUM('menunggu','direview','terseleksi','tidak_terseleksi','interview','lolos','tidak_lolos') NOT NULL DEFAULT 'menunggu';

-- (Opsional) Update data lama ke value baru jika ada
UPDATE `applications` SET `status` = 'menunggu' WHERE `status` = 'pending';
UPDATE `applications` SET `status` = 'lolos' WHERE `status` = 'accepted';
UPDATE `applications` SET `status` = 'tidak_lolos' WHERE `status` = 'rejected';
UPDATE `applications` SET `status` = 'menunggu' WHERE `status` = 'withdrawn'; 

-- Pastikan semua status lamaran valid (tidak NULL/kosong)
UPDATE applications SET status='menunggu' WHERE status IS NULL OR status=''; 

-- Tabel notifikasi untuk pekerja
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
); 