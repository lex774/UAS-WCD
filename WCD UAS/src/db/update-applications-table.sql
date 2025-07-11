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

-- Add work_status column to separate selection status from work progress status
ALTER TABLE `applications` ADD COLUMN `work_status` ENUM('not_started', 'in_progress', 'completed', 'reviewed', 'paid') DEFAULT 'not_started' AFTER `status`;

-- Update existing records to set work_status based on current status
UPDATE `applications` SET `work_status` = 'not_started' WHERE `status` IN ('menunggu', 'direview', 'terseleksi', 'interview', 'tidak_terseleksi', 'tidak_lolos');
UPDATE `applications` SET `work_status` = 'in_progress' WHERE `status` = 'lolos';

-- Add index for work_status
CREATE INDEX idx_applications_work_status ON applications(work_status);

-- Tabel notifikasi untuk pekerja
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
); 