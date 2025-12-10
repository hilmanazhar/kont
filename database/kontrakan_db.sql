-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 12:36 PM
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
-- Database: `kontrakan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `paid_by` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `is_rotation` tinyint(1) DEFAULT 0,
  `receipt_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `paid_by`, `amount`, `description`, `category`, `is_rotation`, `receipt_image`, `created_at`) VALUES
(2, 1, 1000000.00, 'listrik', 'Air', 0, NULL, '2025-12-09 18:43:55'),
(3, 2, 100000.00, 'ajbhfa', 'Air', 0, NULL, '2025-12-09 18:46:43'),
(6, 2, 100000.00, 'Token Listrik', 'Listrik', 0, 'uploads/receipts/receipt_693873f6f32a5_1765307382.png', '2025-12-09 19:09:43'),
(7, 1, 100000.00, 'Token Listrik', 'Listrik', 0, 'uploads/receipts/receipt_693875f8a5878_1765307896.png', '2025-12-09 19:18:16'),
(8, 2, 300000.00, 'makan', 'Titip Makanan', 0, NULL, '2025-12-09 19:21:48'),
(9, 2, 100000.00, 'makan', 'Titip Makanan', 0, NULL, '2025-12-09 19:22:41'),
(10, 1, 1000000.00, 'ksjnvskjndv', 'Titip Makanan', 0, NULL, '2025-12-09 19:32:06'),
(11, 2, 500000.00, 'tes', 'Titip Makanan', 0, NULL, '2025-12-10 03:35:00'),
(12, 1, 1000000.00, 'ksjnvskjndv', 'Titip Makanan', 0, NULL, '2025-12-10 03:36:35'),
(13, 1, 100000.00, 'kajsj', 'Titip Makanan', 0, NULL, '2025-12-10 03:51:19'),
(14, 2, 1000000.00, 'pecelll', 'Titip Makanan', 0, NULL, '2025-12-10 03:57:01'),
(15, 2, 100000.00, 'pecelll', 'Titip Makanan', 0, NULL, '2025-12-10 04:04:25'),
(16, 2, 2030000.00, 'mhb', 'Lainnya', 0, NULL, '2025-12-10 04:10:21'),
(17, 1, 132420.00, 'kajsj', 'Titip Makanan', 0, NULL, '2025-12-10 04:13:27'),
(18, 1, 1000230.00, 'ada', 'Titip Makanan', 0, NULL, '2025-12-10 04:22:50'),
(19, 1, 800000.00, 'dnihbda', 'Titip Makanan', 0, NULL, '2025-12-10 04:28:32'),
(20, 1, 100000.00, 'adfaf', 'Titip Makanan', 0, NULL, '2025-12-10 04:34:17'),
(21, 1, 1000000.00, 'kajsj', 'Titip Makanan', 0, NULL, '2025-12-10 05:09:33'),
(22, 1, 100000.00, 'sadasda', 'Titip Makanan', 0, NULL, '2025-12-10 05:12:40'),
(23, 1, 100000.00, 'Test Tagih', 'Titip Makanan', 0, NULL, '2025-12-10 05:22:40'),
(24, 1, 30000.00, 'ksjnvskjndv', 'Titip Makanan', 0, NULL, '2025-12-10 10:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `expense_splits`
--

CREATE TABLE `expense_splits` (
  `id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_splits`
--

INSERT INTO `expense_splits` (`id`, `expense_id`, `user_id`, `amount`, `is_paid`) VALUES
(8, 2, 6, 200000.00, 0),
(9, 2, 2, 200000.00, 0),
(10, 2, 1, 200000.00, 0),
(11, 2, 5, 200000.00, 0),
(12, 2, 4, 200000.00, 0),
(13, 3, 2, 50000.00, 0),
(14, 3, 1, 50000.00, 0),
(16, 8, 2, 150000.00, 0),
(17, 8, 1, 150000.00, 0),
(18, 9, 2, 50000.00, 0),
(19, 9, 1, 50000.00, 0),
(20, 10, 2, 500000.00, 0),
(21, 10, 1, 500000.00, 0),
(22, 11, 2, 250000.00, 0),
(23, 11, 1, 250000.00, 0),
(24, 12, 2, 500000.00, 0),
(25, 12, 1, 500000.00, 0),
(26, 13, 2, 50000.00, 0),
(27, 13, 1, 50000.00, 0),
(28, 14, 2, 500000.00, 0),
(29, 14, 1, 500000.00, 0),
(30, 15, 2, 50000.00, 0),
(31, 15, 1, 50000.00, 0),
(32, 16, 2, 1015000.00, 0),
(33, 16, 1, 1015000.00, 0),
(34, 17, 2, 66210.00, 0),
(35, 17, 1, 66210.00, 0),
(36, 18, 2, 500115.00, 0),
(37, 18, 1, 500115.00, 0),
(38, 19, 2, 400000.00, 0),
(39, 19, 1, 400000.00, 0),
(40, 20, 2, 50000.00, 0),
(41, 20, 1, 50000.00, 0),
(42, 21, 2, 333334.00, 0),
(43, 21, 1, 333334.00, 0),
(44, 21, 5, 333334.00, 0),
(45, 22, 2, 50000.00, 0),
(46, 22, 1, 50000.00, 0),
(47, 23, 2, 50000.00, 0),
(48, 24, 2, 15000.00, 0),
(49, 24, 1, 15000.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `info_kontrakan`
--

CREATE TABLE `info_kontrakan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `info_kontrakan`
--

INSERT INTO `info_kontrakan` (`id`, `user_id`, `title`, `content`, `image_path`, `created_at`) VALUES
(1, 1, 'tesss', 'dafsgdshdhfyftdafsgdshdhfyftdafsgdshdhfyftdafsgdshdhfyftdafsgdshdhfyftdafsgdshdhfyft', 'uploads/info/info_1765305499_69386c9bb1420.jpg', '2025-12-09 18:38:19'),
(2, 1, 'rewr', 'shfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjsshfdvbkksjs', NULL, '2025-12-09 18:42:03'),
(3, 1, 'whbdwjfbjwbf', 'hbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja bhbsdajkhvb akhv bja b', NULL, '2025-12-09 18:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('expense','settlement','info') DEFAULT 'info',
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `related_id`, `is_read`, `created_at`) VALUES
(1, 6, 'Pengeluaran Baru', 'Hilman nalangin Listrik: listrik sebesar Rp 14.285.715', 'expense', 1, 0, '2025-12-09 18:43:04'),
(2, 2, 'Pengeluaran Baru', 'Hilman nalangin Listrik: listrik sebesar Rp 14.285.715', 'expense', 1, 1, '2025-12-09 18:43:04'),
(3, 5, 'Pengeluaran Baru', 'Hilman nalangin Listrik: listrik sebesar Rp 14.285.715', 'expense', 1, 0, '2025-12-09 18:43:04'),
(4, 7, 'Pengeluaran Baru', 'Hilman nalangin Listrik: listrik sebesar Rp 14.285.715', 'expense', 1, 0, '2025-12-09 18:43:04'),
(5, 4, 'Pengeluaran Baru', 'Hilman nalangin Listrik: listrik sebesar Rp 14.285.715', 'expense', 1, 0, '2025-12-09 18:43:04'),
(6, 3, 'Pengeluaran Baru', 'Hilman nalangin Listrik: listrik sebesar Rp 14.285.715', 'expense', 1, 0, '2025-12-09 18:43:04'),
(7, 6, 'Pengeluaran Baru', 'Hilman nalangin Air: listrik sebesar Rp 200.000', 'expense', 2, 0, '2025-12-09 18:43:55'),
(8, 2, 'Pengeluaran Baru', 'Hilman nalangin Air: listrik sebesar Rp 200.000', 'expense', 2, 1, '2025-12-09 18:43:55'),
(9, 5, 'Pengeluaran Baru', 'Hilman nalangin Air: listrik sebesar Rp 200.000', 'expense', 2, 0, '2025-12-09 18:43:55'),
(10, 4, 'Pengeluaran Baru', 'Hilman nalangin Air: listrik sebesar Rp 200.000', 'expense', 2, 0, '2025-12-09 18:43:55'),
(11, 1, 'Pengeluaran Baru', 'Arkan nalangin Air: ajbhfa sebesar Rp 50.000', 'expense', 3, 1, '2025-12-09 18:46:43'),
(12, 1, 'Pengeluaran Baru', 'Arkan nalangin Titip Makanan: makan sebesar Rp 150.000', 'expense', 8, 1, '2025-12-09 19:21:48'),
(13, 1, 'Pengeluaran Baru', 'Arkan nalangin Titip Makanan: makan sebesar Rp 50.000', 'expense', 9, 1, '2025-12-09 19:22:41'),
(14, 2, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 50.000 ke kamu', 'settlement', 1, 1, '2025-12-09 19:31:32'),
(15, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: ksjnvskjndv sebesar Rp 500.000', 'expense', 10, 1, '2025-12-09 19:32:06'),
(16, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 500.000 ke kamu', 'settlement', 2, 1, '2025-12-09 19:34:11'),
(17, 1, 'Pengeluaran Baru', 'Arkan nalangin Titip Makanan: tes sebesar Rp 250.000', 'expense', 11, 1, '2025-12-10 03:35:00'),
(18, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: ksjnvskjndv sebesar Rp 500.000', 'expense', 12, 1, '2025-12-10 03:36:35'),
(19, 4, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 200.000 ke kamu', 'settlement', 3, 0, '2025-12-10 03:37:47'),
(20, 5, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 200.000 ke kamu', 'settlement', 4, 0, '2025-12-10 03:37:52'),
(21, 6, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 200.000 ke kamu', 'settlement', 5, 0, '2025-12-10 03:37:56'),
(22, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 250.000 ke kamu', 'settlement', 6, 1, '2025-12-10 03:50:08'),
(23, 4, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 400.000 ke kamu', 'settlement', 7, 0, '2025-12-10 03:50:39'),
(24, 5, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 400.000 ke kamu', 'settlement', 8, 0, '2025-12-10 03:50:43'),
(25, 4, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 800.000 ke kamu', 'settlement', 9, 0, '2025-12-10 03:50:48'),
(26, 6, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 400.000 ke kamu', 'settlement', 10, 0, '2025-12-10 03:50:52'),
(27, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: kajsj sebesar Rp 50.000', 'expense', 13, 1, '2025-12-10 03:51:19'),
(28, 2, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 50.000 dari kamu', 'settlement', 11, 1, '2025-12-10 03:56:11'),
(29, 4, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 1.600.000 dari kamu', 'settlement', 12, 0, '2025-12-10 03:56:15'),
(30, 5, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 800.000 dari kamu', 'settlement', 13, 0, '2025-12-10 03:56:19'),
(31, 6, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 800.000 dari kamu', 'settlement', 14, 0, '2025-12-10 03:56:23'),
(32, 1, 'Pengeluaran Baru', 'Arkan nalangin Titip Makanan: pecelll sebesar Rp 500.000', 'expense', 14, 1, '2025-12-10 03:57:01'),
(33, 1, 'Pembayaran Dikonfirmasi', 'Arkan mengkonfirmasi pembayaran Rp 500.000 dari kamu', 'settlement', 15, 1, '2025-12-10 04:04:04'),
(34, 1, 'Pengeluaran Baru', 'Arkan nalangin Titip Makanan: pecelll sebesar Rp 50.000', 'expense', 15, 1, '2025-12-10 04:04:25'),
(35, 2, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 50.000 ke kamu', 'settlement', 16, 1, '2025-12-10 04:09:49'),
(36, 1, 'Pengeluaran Baru', 'Arkan nalangin Lainnya: mhb sebesar Rp 1.015.000', 'expense', 16, 1, '2025-12-10 04:10:21'),
(37, 2, 'Pembayaran Diterima', 'Hilman sudah membayar Rp 1.015.000 ke kamu', 'settlement', 17, 0, '2025-12-10 04:12:58'),
(38, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: kajsj sebesar Rp 66.210', 'expense', 17, 0, '2025-12-10 04:13:27'),
(39, 2, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 66.210 dari kamu', 'settlement', 18, 0, '2025-12-10 04:22:20'),
(40, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: ada sebesar Rp 500.115', 'expense', 18, 0, '2025-12-10 04:22:50'),
(41, 2, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 500.115 dari kamu', 'settlement', 19, 0, '2025-12-10 04:28:04'),
(42, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: dnihbda sebesar Rp 400.000', 'expense', 19, 0, '2025-12-10 04:28:32'),
(43, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 400.000 ke kamu', 'settlement', 20, 1, '2025-12-10 04:33:37'),
(44, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: adfaf sebesar Rp 50.000', 'expense', 20, 0, '2025-12-10 04:34:17'),
(45, 2, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 50.000 dari kamu', 'settlement', 21, 0, '2025-12-10 05:09:14'),
(46, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: kajsj sebesar Rp 333.334', 'expense', 21, 0, '2025-12-10 05:09:33'),
(47, 5, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: kajsj sebesar Rp 333.334', 'expense', 21, 0, '2025-12-10 05:09:33'),
(48, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 100.000 ke kamu', 'settlement', 22, 1, '2025-12-10 05:10:06'),
(49, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 233.334 ke kamu', 'settlement', 23, 1, '2025-12-10 05:10:13'),
(50, 5, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 333.334 dari kamu', 'settlement', 24, 0, '2025-12-10 05:12:16'),
(51, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: sadasda sebesar Rp 50.000', 'expense', 22, 0, '2025-12-10 05:12:40'),
(52, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 50.000 ke kamu', 'settlement', 25, 1, '2025-12-10 05:44:09'),
(53, 1, 'Pembayaran Diterima', 'Arkan sudah membayar Rp 50.000 ke kamu', 'settlement', 26, 1, '2025-12-10 05:44:32'),
(54, 2, 'Pengeluaran Baru', 'Hilman nalangin Titip Makanan: ksjnvskjndv sebesar Rp 15.000', 'expense', 24, 0, '2025-12-10 10:30:21'),
(55, 2, 'Pembayaran Dikonfirmasi', 'Hilman mengkonfirmasi pembayaran Rp 15.000 dari kamu', 'settlement', 27, 0, '2025-12-10 10:31:53');

-- --------------------------------------------------------

--
-- Table structure for table `settlements`
--

CREATE TABLE `settlements` (
  `id` int(11) NOT NULL,
  `from_user` int(11) NOT NULL,
  `to_user` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settlements`
--

INSERT INTO `settlements` (`id`, `from_user`, `to_user`, `amount`, `created_at`) VALUES
(1, 1, 2, 50000.00, '2025-12-09 19:31:32'),
(2, 2, 1, 500000.00, '2025-12-09 19:34:11'),
(3, 1, 4, 200000.00, '2025-12-10 03:37:47'),
(4, 1, 5, 200000.00, '2025-12-10 03:37:52'),
(5, 1, 6, 200000.00, '2025-12-10 03:37:56'),
(6, 2, 1, 250000.00, '2025-12-10 03:50:08'),
(7, 1, 4, 400000.00, '2025-12-10 03:50:39'),
(8, 1, 5, 400000.00, '2025-12-10 03:50:43'),
(9, 1, 4, 800000.00, '2025-12-10 03:50:48'),
(10, 1, 6, 400000.00, '2025-12-10 03:50:52'),
(11, 2, 1, 50000.00, '2025-12-10 03:56:11'),
(12, 4, 1, 1600000.00, '2025-12-10 03:56:15'),
(13, 5, 1, 800000.00, '2025-12-10 03:56:19'),
(14, 6, 1, 800000.00, '2025-12-10 03:56:23'),
(15, 1, 2, 500000.00, '2025-12-10 04:04:04'),
(16, 1, 2, 50000.00, '2025-12-10 04:09:49'),
(17, 1, 2, 1015000.00, '2025-12-10 04:12:58'),
(18, 2, 1, 66210.00, '2025-12-10 04:22:20'),
(19, 2, 1, 500115.00, '2025-12-10 04:28:04'),
(20, 2, 1, 400000.00, '2025-12-10 04:33:37'),
(21, 2, 1, 50000.00, '2025-12-10 05:09:14'),
(22, 2, 1, 100000.00, '2025-12-10 05:10:06'),
(23, 2, 1, 233334.00, '2025-12-10 05:10:13'),
(24, 5, 1, 333334.00, '2025-12-10 05:12:16'),
(25, 2, 1, 50000.00, '2025-12-10 05:44:09'),
(26, 2, 1, 50000.00, '2025-12-10 05:44:32'),
(27, 2, 1, 15000.00, '2025-12-10 10:31:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `phone_wa` varchar(20) DEFAULT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ewallet_type` varchar(50) DEFAULT NULL,
  `ewallet_number` varchar(50) DEFAULT NULL,
  `qris_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `display_name`, `phone_wa`, `role`, `created_at`, `bank_name`, `bank_account`, `ewallet_type`, `ewallet_number`, `qris_image`) VALUES
(1, 'hilman', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Hilman', '085693889022', 'member', '2025-12-09 17:32:06', 'BCA', '516728394', 'OVO', '123454657', 'uploads/qris/qris_1_1765337770.png'),
(2, 'arkan', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Arkan', '085760519922', 'member', '2025-12-09 17:32:06', NULL, NULL, NULL, NULL, NULL),
(3, 'rafli', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Rafli', NULL, 'member', '2025-12-09 17:32:06', NULL, NULL, NULL, NULL, NULL),
(4, 'rafi', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Rafi', NULL, 'member', '2025-12-09 17:32:06', NULL, NULL, NULL, NULL, NULL),
(5, 'kahfi', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Kahfi', NULL, 'member', '2025-12-09 17:32:06', NULL, NULL, NULL, NULL, NULL),
(6, 'alromy', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Al Romy', NULL, 'member', '2025-12-09 17:32:06', NULL, NULL, NULL, NULL, NULL),
(7, 'lutfan', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Lutfan', NULL, 'member', '2025-12-09 17:32:06', NULL, NULL, NULL, NULL, NULL),
(8, 'admin', '$2a$12$Ujhb1PPPz1GodWZJWHFHJ.1ZPrrrcXTA2oMapATIexrf27DBpXBfa', 'Admin Kontrakan', NULL, 'admin', '2025-12-10 10:33:57', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paid_by` (`paid_by`);

--
-- Indexes for table `expense_splits`
--
ALTER TABLE `expense_splits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_id` (`expense_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `info_kontrakan`
--
ALTER TABLE `info_kontrakan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settlements`
--
ALTER TABLE `settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_user` (`from_user`),
  ADD KEY `to_user` (`to_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `expense_splits`
--
ALTER TABLE `expense_splits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `info_kontrakan`
--
ALTER TABLE `info_kontrakan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `settlements`
--
ALTER TABLE `settlements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expense_splits`
--
ALTER TABLE `expense_splits`
  ADD CONSTRAINT `expense_splits_ibfk_1` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expense_splits_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `info_kontrakan`
--
ALTER TABLE `info_kontrakan`
  ADD CONSTRAINT `info_kontrakan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settlements`
--
ALTER TABLE `settlements`
  ADD CONSTRAINT `settlements_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `settlements_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
