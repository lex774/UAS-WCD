-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2025 at 12:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tugasakhir`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `expected_salary` decimal(10,2) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `status` enum('menunggu','direview','terseleksi','interview','lolos','tidak_terseleksi','tidak_lolos') NOT NULL DEFAULT 'menunggu',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `job_id`, `cover_letter`, `cv_file`, `expected_salary`, `availability`, `additional_info`, `status`, `applied_at`, `updated_at`) VALUES
(1, 2, 1, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\r\nUt enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\nDuis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.\r\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\r\nAdditional Information\r\nOrigins: Lorem Ipsum is derived from a work by Cicero, written in 45 BC, and has been used as placeholder text since the 16th century. It allows designers to focus on layout without being distracted by meaningful content. \r\n1\r\n', 'user/uploads/cv/cv_2_1751866913.pdf', 100.00, '1_week', 'lorem ipsum ', 'direview', '2025-07-07 05:41:53', '2025-07-08 07:54:55'),
(2, 1, 6, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\r\nUt enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\nDuis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.\r\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\r\nAdditional Information\r\nOrigins: Lorem Ipsum is derived from a work by Cicero, written in 45 BC, and has been used as placeholder text since the 16th century. It allows designers to focus on layout without being distracted by meaningful content. \r\n1\r\nUsage: It is widely used in the graphic design, publishing, and web development industries to fill spaces in layouts and demonstrate how text will look in a design. \r\n', 'user/uploads/cv/cv_1_1751872837.pdf', 500.00, 'immediate', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\r\n', 'direview', '2025-07-07 07:20:37', '2025-07-08 07:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `application_tracking`
--

CREATE TABLE `application_tracking` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `status` enum('applied','under_review','shortlisted','interview_scheduled','interviewed','accepted','contract_received','contract_signed','work_started','milestone_1_completed','milestone_2_completed','milestone_3_completed','work_completed','payment_requested','payment_received','project_closed') DEFAULT 'applied',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `brief_description` varchar(200) DEFAULT NULL,
  `full_description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `company_email` varchar(100) DEFAULT NULL,
  `company_logo_url` varchar(255) DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('TERBUKA','TERTUTUP') NOT NULL DEFAULT 'TERBUKA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `brief_description`, `full_description`, `category`, `location`, `payment_type`, `amount`, `currency`, `requirements`, `company_name`, `company_email`, `company_logo_url`, `posted_by`, `created_at`, `status`) VALUES
(1, 'Graphic Design ', 'Jobdesc nya adalah membuat logo sederhanan', 'Jobdesc nya adalah membuat logo sederhanan dengan tampilan menarik dengan tambahan unsur nusantara ', 'design', 'Bandung', 'fixed-price', 1.00, 'IDR', 'Mahir menggunakan corel ', 'PT Nusantara Sejahtera', NULL, 'https://logo/indo.id', 1, '2025-07-04 04:42:10', 'TERBUKA'),
(6, 'Content Creator ', 'Bertanggung jawab membuat konsep, eskeskusi konten, dan laporan analisis perkembangan account sosial media ', 'Bertanggung jawab membuat konsep konten media cetak atau video kreatif, eskeskusi konten, dan laporan analisis perkembangan account sosial media ', 'Digital Marketing', 'Jakarta', 'fixed-price', 300.00, 'IDR', 'Mahir menggunakan tools editing \\r\\nBerpikir inovtif dan kreatif \\r\\n\\r\\n', 'PT Harapan Indah', 'anisa.tsuroyya@cakrawala.ac.id', 'user/uploads/logo/logo_1751871201_5891.png', 2, '2025-07-07 06:53:21', 'TERBUKA');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(47, 2, 'Status lamaran Anda telah diperbarui menjadi: Direview', 0, '2025-07-08 07:54:55');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'IDR',
  `payment_type` enum('full_payment','milestone_payment','partial_payment') DEFAULT 'full_payment',
  `status` enum('pending','sent','received','cancelled') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_milestones`
--

CREATE TABLE `project_milestones` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `milestone_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','overdue') DEFAULT 'pending',
  `completion_percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `specializations` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `profile_picture`, `bio`, `specializations`, `created_at`) VALUES
(1, 'Anya  Gevanya', 'anisa.tsuroyya@cakrawala.ac.id', '$2y$10$ICH.RrhxWgQgp/WC7hFcT.sYnKXk0U2rH1gbvq/jUgRfTm1IWXgc6', NULL, '', '', '2025-07-03 10:02:31'),
(2, 'Anisa Mufida', 'anisamufidaa12@gmail.com', '$2y$10$2Lb.z9SNrD3gCBw5A36ViejMMA/7ieHsJ8GxQcz.zUW6JgD8C.D/W', 'uploads/profile_2_1751778840.jpg', '', '', '2025-07-05 07:14:07'),
(3, 'jhon doe', 'jhondoe123@gmail.com', '$2y$10$VZup67T.6ZxzQzE/5h.Fx.87ekMBn1MHDyl1GfR5DBGAJGU6YvzRe', 'uploads/profile_3_1751727884.png', '', '', '2025-07-05 15:03:44'),
(4, 'Farah', 'farahani12@gmail.com', '$2y$10$a7WH0e7eKForYSc2TcRCfuJSIGi6RfHqPpEQbSmM6ChbH7FgK9k6m', NULL, NULL, NULL, '2025-07-08 10:38:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `application_tracking`
--
ALTER TABLE `application_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `application_tracking`
--
ALTER TABLE `application_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_milestones`
--
ALTER TABLE `project_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`);

--
-- Constraints for table `application_tracking`
--
ALTER TABLE `application_tracking`
  ADD CONSTRAINT `application_tracking_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`);

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`);

--
-- Constraints for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD CONSTRAINT `project_milestones_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `project_milestones_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
