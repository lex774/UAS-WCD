-- Applications Table
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expected_salary` decimal(10,2) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `job_id` (`job_id`),
  KEY `status` (`status`),
  UNIQUE KEY `unique_application` (`user_id`, `job_id`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_3` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_applications_user_status ON applications(user_id, status);
CREATE INDEX idx_applications_job_status ON applications(job_id, status);
CREATE INDEX idx_applications_applied_at ON applications(applied_at);

-- Insert sample data (optional)
INSERT INTO `applications` (`user_id`, `job_id`, `description`, `cv_file`, `status`, `expected_salary`, `availability`, `additional_info`) VALUES
(1, 1, 'Saya sangat tertarik dengan posisi ini dan memiliki pengalaman yang relevan dalam bidang yang sama. Saya yakin dapat memberikan kontribusi yang signifikan untuk perusahaan Anda.', 'user/uploads/cv/cv_1_1234567890.pdf', 'pending', 5000000.00, 'immediate', 'Saya dapat mulai bekerja segera dan memiliki fleksibilitas waktu yang baik.'),
(2, 1, 'Dengan pengalaman 3 tahun di bidang yang sama, saya siap untuk mengambil tantangan baru ini. Saya memiliki kemampuan komunikasi yang baik dan dapat bekerja dalam tim.', 'user/uploads/cv/cv_2_1234567891.pdf', 'pending', 4500000.00, '2_weeks', 'Saya memiliki portofolio yang dapat saya tunjukkan saat interview.'),
(1, 2, 'Saya memiliki passion dalam desain UI/UX dan telah mengerjakan berbagai proyek yang berhasil. Saya percaya dapat memberikan solusi kreatif untuk kebutuhan perusahaan.', 'user/uploads/cv/cv_1_1234567892.pdf', 'accepted', 6000000.00, '1_month', 'Saya memiliki sertifikasi UI/UX Design dan familiar dengan tools seperti Figma, Adobe XD, dan Sketch.'); 