-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2026 at 05:29 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rvm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint UNSIGNED DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `created_at`, `updated_at`) VALUES
(1, 1, 'create_machine', 'machine', 99, '{\"message\": \"Created machine: Test RVM\"}', '2026-05-09 23:13:38', '2026-05-09 23:13:38'),
(2, 1, 'delete_machine', 'machine', 2, '{\"message\": \"Deleted machine: RVM UMPSA Pekan\"}', '2026-05-09 23:14:05', '2026-05-09 23:14:05'),
(3, 1, 'create_machine', 'machine', 3, '{\"message\": \"Created machine: RVM UMPSA Pekan\"}', '2026-05-09 23:14:30', '2026-05-09 23:14:30'),
(4, 1, 'update_machine', 'machine', 1, '{\"message\": \"Updated machine: RVM Machine 1\"}', '2026-05-09 23:16:31', '2026-05-09 23:16:31'),
(5, 1, 'update_machine', 'machine', 1, '{\"message\": \"Updated machine: RVM UMPSA Gambang\"}', '2026-05-09 23:17:06', '2026-05-09 23:17:06'),
(6, 1, 'update_machine', 'machine', 3, '{\"message\": \"Updated machine: RVM UMPSA Pekan\"}', '2026-05-09 23:17:23', '2026-05-09 23:17:23'),
(7, 1, 'update_reward_config', 'system', 0, '{\"message\": \"Updated reward points configuration\"}', '2026-05-09 23:18:14', '2026-05-09 23:18:14'),
(8, 1, 'update_reward_config', 'system', 0, '{\"message\": \"Updated reward points configuration\"}', '2026-05-09 23:18:17', '2026-05-09 23:18:17'),
(9, 1, 'update_reward_config', 'system', 0, '{\"message\": \"Updated reward points configuration\"}', '2026-05-09 23:18:20', '2026-05-09 23:18:20'),
(10, 1, 'update_reward_config', 'system', 0, '{\"message\": \"Updated reward points configuration\"}', '2026-05-09 23:18:22', '2026-05-09 23:18:22'),
(11, 1, 'update_user', 'user', 4, '{\"message\": \"Updated user: TasikLur\"}', '2026-05-09 23:19:07', '2026-05-09 23:19:07'),
(12, 1, 'update_user', 'user', 4, '{\"message\": \"Updated user: TasikLur\"}', '2026-05-09 23:21:00', '2026-05-09 23:21:00'),
(13, 1, 'update_user', 'user', 2, '{\"message\": \"Updated user: Adi\"}', '2026-05-09 23:21:07', '2026-05-09 23:21:07'),
(14, 1, 'reset_bin_alerts', 'system', 0, '{\"message\": \"Reset alerts for 0 machine(s)\"}', '2026-05-10 07:55:45', '2026-05-10 07:55:45'),
(15, 1, 'reset_bin_alerts', 'system', 0, '{\"message\": \"Reset alerts for 0 machine(s)\"}', '2026-05-10 07:55:48', '2026-05-10 07:55:48');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_05_07_000001_add_ai_confidence_to_transactions_table', 2),
(6, '2026_05_07_000002_create_rvm_machines_table', 2),
(7, '2026_05_07_000003_create_rvm_core_tables', 3),
(8, '2026_05_07_000004_add_kiosk_fields_to_qr_sessions', 4),
(9, '2026_05_08_000001_add_session_code_to_qr_sessions_table', 5),
(10, '2026_05_10_000001_add_kiosk_token_to_qr_sessions', 6),
(11, '2026_05_10_000003_fix_rvm_machines_schema', 7);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--


-- --------------------------------------------------------

--
-- Table structure for table `points_history`
--

CREATE TABLE `points_history` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `transaction_id` bigint UNSIGNED DEFAULT NULL,
  `session_id` bigint UNSIGNED DEFAULT NULL,
  `points_change` int NOT NULL,
  `balance_after` int NOT NULL,
  `type` enum('earned','deducted') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `points_history`
--

INSERT INTO `points_history` (`id`, `user_id`, `transaction_id`, `session_id`, `points_change`, `balance_after`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 14, 14, 'earned', 'Recycled 179g of plastic', '2026-05-06 21:25:38', '2026-05-06 21:25:38'),
(2, 3, 2, 1, 10, 24, 'earned', 'Recycled 135g of plastic', '2026-05-06 21:26:05', '2026-05-06 21:26:05'),
(3, 3, 3, 1, 5, 29, 'earned', 'Recycled 97g of glass', '2026-05-06 21:26:32', '2026-05-06 21:26:32'),
(4, 3, 4, 11, -10, 19, 'deducted', 'Invalid item: selected aluminum, detected paper', '2026-05-06 22:41:03', '2026-05-06 22:41:03'),
(5, 3, 5, 11, 11, 30, 'earned', 'Recycled 233g of paper', '2026-05-06 22:41:27', '2026-05-06 22:41:27'),
(6, 3, 6, 11, -10, 20, 'deducted', 'Invalid item: selected glass, detected paper', '2026-05-06 22:41:56', '2026-05-06 22:41:56'),
(7, 3, 7, 12, -10, 10, 'deducted', 'Invalid item: selected plastic, detected aluminum', '2026-05-06 22:44:47', '2026-05-06 22:44:47'),
(8, 3, 8, 12, -10, 0, 'deducted', 'Invalid item: selected paper, detected aluminum', '2026-05-06 22:45:07', '2026-05-06 22:45:07'),
(9, 3, 9, 12, 7, 7, 'earned', 'Recycled 79g of aluminum', '2026-05-06 22:45:29', '2026-05-06 22:45:29'),
(10, 3, 10, 12, -7, 0, 'deducted', 'Invalid item: selected plastic, detected glass', '2026-05-06 22:45:50', '2026-05-06 22:45:50'),
(11, 3, 11, 12, 0, 0, 'deducted', 'Invalid item: selected glass, detected aluminum', '2026-05-06 22:46:17', '2026-05-06 22:46:17'),
(12, 3, 12, 16, 0, 0, 'deducted', 'Invalid item: selected aluminum, detected paper', '2026-05-07 19:37:53', '2026-05-07 19:37:53'),
(13, 3, 13, 16, 0, 0, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-07 19:38:14', '2026-05-07 19:38:14'),
(14, 3, 14, 16, 29, 29, 'earned', 'Recycled 490g of glass', '2026-05-07 19:38:40', '2026-05-07 19:38:40'),
(15, 3, 15, 18, 26, 35, 'earned', 'Recycled 440g of glass', '2026-05-07 22:29:56', '2026-05-07 22:29:56'),
(16, 1, 16, 21, 30, 30, 'earned', 'Recycled 301g of aluminum', '2026-05-07 22:34:16', '2026-05-07 22:34:16'),
(17, 1, 17, 21, -10, 20, 'deducted', 'Invalid item: selected plastic, detected glass', '2026-05-07 22:34:41', '2026-05-07 22:34:41'),
(18, 1, 18, 22, -10, 10, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-07 23:05:20', '2026-05-07 23:05:20'),
(19, 1, 19, 22, 23, 33, 'earned', 'Recycled 291g of plastic', '2026-05-07 23:05:44', '2026-05-07 23:05:44'),
(20, 1, 20, 22, 46, 79, 'earned', 'Recycled 462g of aluminum', '2026-05-07 23:06:10', '2026-05-07 23:06:10'),
(21, 1, 21, 22, -10, 69, 'deducted', 'Invalid item: selected glass, detected unknown', '2026-05-07 23:06:43', '2026-05-07 23:06:43'),
(22, 1, 22, 22, 25, 94, 'earned', 'Recycled 423g of glass', '2026-05-07 23:07:34', '2026-05-07 23:07:34'),
(23, 1, 23, 22, 49, 143, 'earned', 'Recycled 496g of aluminum', '2026-05-07 23:09:36', '2026-05-07 23:09:36'),
(24, 1, 24, 22, 19, 162, 'earned', 'Recycled 249g of plastic', '2026-05-07 23:11:04', '2026-05-07 23:11:04'),
(25, 1, 25, 22, 4, 166, 'earned', 'Recycled 54g of plastic', '2026-05-07 23:11:25', '2026-05-07 23:11:25'),
(26, 1, 26, 22, 3, 169, 'earned', 'Recycled 60g of glass', '2026-05-07 23:11:55', '2026-05-07 23:11:55'),
(27, 1, 27, 22, 21, 190, 'earned', 'Recycled 437g of paper', '2026-05-07 23:12:21', '2026-05-07 23:12:21'),
(28, 1, 28, 22, 8, 198, 'earned', 'Recycled 165g of paper', '2026-05-07 23:12:43', '2026-05-07 23:12:43'),
(29, 1, 29, 22, 20, 218, 'earned', 'Recycled 400g of paper', '2026-05-07 23:13:03', '2026-05-07 23:13:03'),
(30, 1, 30, 22, 3, 221, 'earned', 'Recycled 64g of paper', '2026-05-07 23:13:25', '2026-05-07 23:13:25'),
(31, 1, 31, 22, -10, 211, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-07 23:16:47', '2026-05-07 23:16:47'),
(32, 1, 32, 22, -10, 201, 'deducted', 'Invalid item: selected plastic, detected paper', '2026-05-07 23:17:09', '2026-05-07 23:17:09'),
(33, 1, 33, 22, -10, 191, 'deducted', 'Invalid item: selected glass, detected paper', '2026-05-07 23:18:27', '2026-05-07 23:18:27'),
(34, 1, 34, 23, -10, 181, 'deducted', 'Invalid item: selected aluminum, detected paper', '2026-05-07 23:33:53', '2026-05-07 23:33:53'),
(35, 1, 35, 23, 15, 196, 'earned', 'Recycled 304g of paper', '2026-05-07 23:35:15', '2026-05-07 23:35:15'),
(36, 4, 36, 25, 0, 0, 'deducted', 'Invalid item: selected aluminum, detected unknown', '2026-05-09 08:00:07', '2026-05-09 08:00:07'),
(37, 4, 37, 25, 0, 0, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-09 08:00:32', '2026-05-09 08:00:32'),
(38, 1, 38, 30, -10, 186, 'deducted', 'Invalid item: selected glass, detected unknown', '2026-05-09 09:16:21', '2026-05-09 09:16:21'),
(39, 1, 39, 31, -10, 176, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-09 10:17:52', '2026-05-09 10:17:52'),
(40, 1, 40, 31, -10, 166, 'deducted', 'Invalid item: selected aluminum, detected glass', '2026-05-09 10:18:21', '2026-05-09 10:18:21'),
(41, 1, 41, 32, -10, 156, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-09 10:19:02', '2026-05-09 10:19:02'),
(42, 1, 42, 32, -10, 146, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-09 10:19:22', '2026-05-09 10:19:22'),
(43, 1, 43, 32, 28, 174, 'earned', 'Recycled 356g of plastic', '2026-05-09 10:19:44', '2026-05-09 10:19:44'),
(44, 1, 44, 33, 10, 184, 'earned', 'Recycled 128g of plastic', '2026-05-09 10:20:33', '2026-05-09 10:20:33'),
(45, 1, 45, 35, -10, 174, 'deducted', 'Invalid item: selected aluminum, detected unknown', '2026-05-09 10:27:01', '2026-05-09 10:27:01'),
(46, 1, 46, 35, -10, 164, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 10:27:20', '2026-05-09 10:27:20'),
(47, 1, 47, 35, 20, 184, 'earned', 'Recycled 258g of plastic', '2026-05-09 10:27:40', '2026-05-09 10:27:40'),
(48, 1, 48, 35, -10, 174, 'deducted', 'Invalid item: selected plastic, detected paper', '2026-05-09 10:29:47', '2026-05-09 10:29:47'),
(49, 1, 49, 35, -10, 164, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 10:30:25', '2026-05-09 10:30:25'),
(50, 1, 50, 37, -10, 154, 'deducted', 'Invalid item: selected glass, detected aluminum', '2026-05-09 11:05:26', '2026-05-09 11:05:26'),
(51, 1, 51, 37, -10, 144, 'deducted', 'Invalid item: selected plastic, detected aluminum', '2026-05-09 11:05:49', '2026-05-09 11:05:49'),
(52, 1, 52, 37, 18, 162, 'earned', 'Recycled 188g of aluminum', '2026-05-09 11:06:12', '2026-05-09 11:06:12'),
(53, 1, 53, 38, -10, 152, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 11:06:41', '2026-05-09 11:06:41'),
(54, 1, 54, 38, -10, 142, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:49:13', '2026-05-09 18:49:13'),
(55, 1, 55, 38, -10, 132, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:50:29', '2026-05-09 18:50:29'),
(56, 1, 56, 38, -10, 122, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:51:08', '2026-05-09 18:51:08'),
(57, 4, 57, 39, 0, 0, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:52:28', '2026-05-09 18:52:28'),
(58, 4, 58, 39, 0, 0, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:53:15', '2026-05-09 18:53:15'),
(59, 4, 59, 39, 0, 0, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:53:34', '2026-05-09 18:53:34'),
(60, 4, 60, 40, 0, 0, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:57:04', '2026-05-09 18:57:04'),
(61, 4, 61, 40, 0, 0, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 18:57:23', '2026-05-09 18:57:23'),
(62, 3, 62, 20, -10, 25, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 19:27:50', '2026-05-09 19:27:50'),
(63, 3, 63, 20, -10, 15, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 19:28:37', '2026-05-09 19:28:37'),
(64, 3, 64, 20, -10, 5, 'deducted', 'Invalid item: selected plastic, detected aluminum', '2026-05-09 19:28:56', '2026-05-09 19:28:56'),
(65, 3, 65, 20, 38, 43, 'earned', 'Recycled 483g of plastic', '2026-05-09 19:29:22', '2026-05-09 19:29:22'),
(66, 3, 66, 41, 32, 75, 'earned', 'Recycled 408g of plastic', '2026-05-09 19:31:06', '2026-05-09 19:31:06'),
(67, 3, 67, 42, 28, 103, 'earned', 'Recycled 355g of plastic', '2026-05-09 19:44:59', '2026-05-09 19:44:59'),
(68, 3, 68, 42, -10, 93, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 19:45:29', '2026-05-09 19:45:29'),
(69, 3, 69, 42, 32, 125, 'earned', 'Recycled 401g of plastic', '2026-05-09 19:46:13', '2026-05-09 19:46:13'),
(70, 3, 70, 43, -10, 115, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 20:05:31', '2026-05-09 20:05:31'),
(71, 3, 71, 43, 18, 133, 'earned', 'Recycled 230g of plastic', '2026-05-09 20:05:53', '2026-05-09 20:05:53'),
(72, 3, 72, 44, 19, 152, 'earned', 'Recycled 239g of plastic', '2026-05-09 20:21:32', '2026-05-09 20:21:32'),
(73, 3, 73, 44, 7, 159, 'earned', 'Recycled 98g of plastic', '2026-05-09 20:22:04', '2026-05-09 20:22:04'),
(74, 3, 74, 45, 37, 196, 'earned', 'Recycled 463g of plastic', '2026-05-09 20:39:15', '2026-05-09 20:39:15'),
(75, 3, 75, 48, 5, 201, 'earned', 'Recycled 64g of plastic', '2026-05-09 20:53:09', '2026-05-09 20:53:09'),
(76, 3, 76, 51, -10, 191, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-09 21:49:12', '2026-05-09 21:49:12'),
(77, 3, 77, 51, 21, 212, 'earned', 'Recycled 269g of plastic', '2026-05-09 21:49:33', '2026-05-09 21:49:33'),
(78, 3, 78, 53, 23, 235, 'earned', 'Recycled 294g of plastic', '2026-05-09 21:51:12', '2026-05-09 21:51:12'),
(79, 3, 79, 54, -10, 225, 'deducted', 'Invalid item: selected glass, detected plastic', '2026-05-09 21:52:32', '2026-05-09 21:52:32'),
(80, 1, 80, 38, -10, 112, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-09 21:55:13', '2026-05-09 21:55:13'),
(81, 1, 81, 38, -10, 102, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 21:55:33', '2026-05-09 21:55:33'),
(82, 1, 82, 55, -10, 92, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-09 22:01:44', '2026-05-09 22:01:44'),
(83, 1, 83, 55, -10, 82, 'deducted', 'Invalid item: selected paper, detected aluminum', '2026-05-09 22:02:02', '2026-05-09 22:02:02'),
(84, 1, 84, 56, -10, 72, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 22:02:39', '2026-05-09 22:02:39'),
(85, 1, 85, 57, -10, 62, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 22:03:43', '2026-05-09 22:03:43'),
(86, 1, 86, 58, -10, 52, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-09 22:09:30', '2026-05-09 22:09:30'),
(87, 1, 87, 58, -10, 42, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 22:09:52', '2026-05-09 22:09:52'),
(88, 1, 88, 58, 105, 147, 'earned', 'Recycled 420g of paper', '2026-05-09 22:10:17', '2026-05-09 22:10:17'),
(89, 1, 89, 58, -10, 137, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 22:11:25', '2026-05-09 22:11:25'),
(90, 1, 90, 58, -10, 127, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-09 22:11:55', '2026-05-09 22:11:55'),
(91, 1, 91, 58, -10, 117, 'deducted', 'Invalid item: selected paper, detected aluminum', '2026-05-09 22:12:15', '2026-05-09 22:12:15'),
(92, 1, 92, 58, -10, 107, 'deducted', 'Invalid item: selected paper, detected aluminum', '2026-05-09 22:12:37', '2026-05-09 22:12:37'),
(93, 1, 93, 58, 83, 190, 'earned', 'Recycled 151g of aluminum', '2026-05-09 22:13:02', '2026-05-09 22:13:02'),
(94, 1, 94, 58, -10, 180, 'deducted', 'Invalid item: selected glass, detected unknown', '2026-05-09 22:15:44', '2026-05-09 22:15:44'),
(95, 1, 95, 58, 55, 235, 'earned', 'Recycled 100g of aluminum', '2026-05-09 22:16:06', '2026-05-09 22:16:06'),
(96, 1, 96, 58, 55, 290, 'earned', 'Recycled 100g of aluminum', '2026-05-09 22:16:34', '2026-05-09 22:16:34'),
(97, 1, 97, 58, -10, 280, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-09 22:18:35', '2026-05-09 22:18:35'),
(98, 1, 98, 58, -10, 270, 'deducted', 'Invalid item: selected aluminum, detected unknown', '2026-05-09 22:19:04', '2026-05-09 22:19:04'),
(99, 1, 99, 58, -10, 260, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-09 22:19:26', '2026-05-09 22:19:26'),
(100, 1, 100, 58, -10, 250, 'deducted', 'Invalid item: selected aluminum, detected paper', '2026-05-09 22:19:49', '2026-05-09 22:19:49'),
(101, 1, 101, 58, 37, 287, 'earned', 'Recycled 249g of paper', '2026-05-09 22:20:11', '2026-05-09 22:20:11'),
(102, 1, 102, 58, -10, 277, 'deducted', 'Invalid item: selected glass, detected unknown', '2026-05-09 22:20:48', '2026-05-09 22:20:48'),
(103, 1, 103, 58, -10, 267, 'deducted', 'Invalid item: selected glass, detected plastic', '2026-05-09 22:21:08', '2026-05-09 22:21:08'),
(104, 1, 104, 58, -10, 257, 'deducted', 'Invalid item: selected aluminum, detected unknown', '2026-05-09 22:21:28', '2026-05-09 22:21:28'),
(105, 1, 105, 58, -10, 247, 'deducted', 'Invalid item: selected aluminum, detected plastic', '2026-05-09 22:21:49', '2026-05-09 22:21:49'),
(106, 1, 106, 58, -10, 237, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-09 22:22:18', '2026-05-09 22:22:18'),
(107, 1, 107, 58, -10, 227, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-09 22:22:38', '2026-05-09 22:22:38'),
(108, 1, 108, 60, -10, 217, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 07:32:42', '2026-05-10 07:32:42'),
(109, 1, 109, 60, 10, 227, 'earned', 'Recycled 98g of plastic', '2026-05-10 07:33:04', '2026-05-10 07:33:04'),
(110, 1, 110, 61, -10, 217, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 07:41:40', '2026-05-10 07:41:40'),
(111, 1, 111, 62, -10, 207, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-10 07:44:30', '2026-05-10 07:44:30'),
(112, 1, 112, 64, 10, 217, 'earned', 'Recycled 77g of plastic', '2026-05-10 07:46:17', '2026-05-10 07:46:17'),
(113, 1, 113, 68, -10, 207, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-10 10:56:54', '2026-05-10 10:56:54'),
(114, 1, 114, 69, -10, 197, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 12:58:32', '2026-05-10 12:58:32'),
(115, 1, 115, 69, -10, 187, 'deducted', 'Invalid item: selected plastic, detected glass', '2026-05-10 12:58:52', '2026-05-10 12:58:52'),
(116, 1, 116, 69, -10, 177, 'deducted', 'Invalid item: selected plastic, detected aluminum', '2026-05-10 12:59:13', '2026-05-10 12:59:13'),
(117, 1, 117, 69, -10, 167, 'deducted', 'Invalid item: selected paper, detected aluminum', '2026-05-10 13:00:40', '2026-05-10 13:00:40'),
(118, 1, 118, 69, -10, 157, 'deducted', 'Invalid item: selected aluminum, detected unknown', '2026-05-10 13:01:12', '2026-05-10 13:01:12'),
(119, 1, 119, 69, -10, 147, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 13:01:34', '2026-05-10 13:01:34'),
(120, 1, 120, 69, -10, 137, 'deducted', 'Invalid item: selected plastic, detected aluminum', '2026-05-10 13:01:55', '2026-05-10 13:01:55'),
(121, 1, 121, 69, 10, 147, 'earned', 'Recycled 33g of aluminum', '2026-05-10 13:02:20', '2026-05-10 13:02:20'),
(122, 1, 122, 69, -10, 137, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-10 13:03:55', '2026-05-10 13:03:55'),
(123, 1, 123, 69, -10, 127, 'deducted', 'Invalid item: selected paper, detected unknown', '2026-05-10 13:04:15', '2026-05-10 13:04:15'),
(124, 1, 124, 69, 10, 137, 'earned', 'Recycled 79g of aluminum', '2026-05-10 13:12:04', '2026-05-10 13:12:04'),
(125, 1, 125, 69, 5, 142, 'earned', 'Recycled 163g of paper', '2026-05-10 13:12:29', '2026-05-10 13:12:29'),
(126, 1, 126, 69, -10, 132, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 13:13:02', '2026-05-10 13:13:02'),
(127, 1, 127, 69, -10, 122, 'deducted', 'Invalid item: selected glass, detected plastic', '2026-05-10 13:13:22', '2026-05-10 13:13:22'),
(128, 1, 128, 69, -10, 112, 'deducted', 'Invalid item: selected glass, detected unknown', '2026-05-10 13:13:50', '2026-05-10 13:13:50'),
(129, 1, 129, 69, -10, 102, 'deducted', 'Invalid item: selected plastic, detected glass', '2026-05-10 13:14:34', '2026-05-10 13:14:34'),
(130, 1, 130, 69, -10, 92, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 13:20:45', '2026-05-10 13:20:45'),
(131, 1, 131, 69, -10, 82, 'deducted', 'Invalid item: selected plastic, detected aluminum', '2026-05-10 13:21:07', '2026-05-10 13:21:07'),
(132, 1, 132, 69, 10, 92, 'earned', 'Recycled 96g of plastic', '2026-05-10 13:22:18', '2026-05-10 13:22:18'),
(133, 1, 133, 69, 5, 97, 'earned', 'Recycled 164g of paper', '2026-05-10 13:22:56', '2026-05-10 13:22:56'),
(134, 1, 134, 69, 10, 107, 'earned', 'Recycled 60g of aluminum', '2026-05-10 13:23:18', '2026-05-10 13:23:18'),
(135, 1, 135, 69, 5, 112, 'earned', 'Recycled 56g of glass', '2026-05-10 13:23:48', '2026-05-10 13:23:48'),
(136, 1, 136, 69, -10, 102, 'deducted', 'Invalid item: selected plastic, detected unknown', '2026-05-10 13:24:19', '2026-05-10 13:24:19'),
(137, 1, 137, 69, -10, 92, 'deducted', 'Invalid item: selected paper, detected plastic', '2026-05-10 13:24:42', '2026-05-10 13:24:42'),
(138, 1, 138, 70, 10, 102, 'earned', 'Recycled 78g of plastic', '2026-05-10 13:30:13', '2026-05-10 13:30:13'),
(139, 1, 139, 70, 10, 112, 'earned', 'Recycled 30g of plastic', '2026-05-10 13:30:39', '2026-05-10 13:30:39'),
(140, 1, 140, 70, 5, 117, 'earned', 'Recycled 195g of paper', '2026-05-10 13:31:03', '2026-05-10 13:31:03'),
(141, 1, 141, 70, 10, 127, 'earned', 'Recycled 42g of plastic', '2026-05-10 13:46:03', '2026-05-10 13:46:03'),
(142, 1, 142, 70, 10, 137, 'earned', 'Recycled 74g of plastic', '2026-05-10 13:46:24', '2026-05-10 13:46:24'),
(143, 1, 143, 70, 10, 147, 'earned', 'Recycled 57g of plastic', '2026-05-10 13:47:33', '2026-05-10 13:47:33'),
(144, 1, 144, 70, 10, 157, 'earned', 'Recycled 66g of plastic', '2026-05-10 13:49:06', '2026-05-10 13:49:06'),
(145, 1, 145, 70, 10, 167, 'earned', 'Recycled 30g of plastic', '2026-05-10 13:49:48', '2026-05-10 13:49:48'),
(146, 1, 146, 70, 10, 177, 'earned', 'Recycled 35g of plastic', '2026-05-10 13:50:28', '2026-05-10 13:50:28'),
(147, 1, 147, 70, 10, 187, 'earned', 'Recycled 47g of plastic', '2026-05-10 13:50:56', '2026-05-10 13:50:56'),
(148, 1, 148, 70, 10, 197, 'earned', 'Recycled 83g of plastic', '2026-05-10 13:55:38', '2026-05-10 13:55:38'),
(149, 1, 149, 70, 10, 207, 'earned', 'Recycled 29g of plastic', '2026-05-10 13:56:01', '2026-05-10 13:56:01'),
(150, 1, 150, 70, 10, 217, 'earned', 'Recycled 62g of plastic', '2026-05-10 13:56:21', '2026-05-10 13:56:21'),
(151, 1, 151, 70, 10, 227, 'earned', 'Recycled 40g of plastic', '2026-05-10 13:56:41', '2026-05-10 13:56:41'),
(152, 1, 152, 70, 5, 232, 'earned', 'Recycled 428g of paper', '2026-05-10 14:04:43', '2026-05-10 14:04:43'),
(153, 1, 153, 70, 5, 237, 'earned', 'Recycled 83g of glass', '2026-05-10 14:05:21', '2026-05-10 14:05:21'),
(154, 1, 154, 70, 10, 247, 'earned', 'Recycled 31g of plastic', '2026-05-10 14:05:44', '2026-05-10 14:05:44'),
(155, 1, 155, 70, 5, 252, 'earned', 'Recycled 17g of glass', '2026-05-10 14:06:06', '2026-05-10 14:06:06'),
(156, 1, 156, 70, 10, 262, 'earned', 'Recycled 22g of plastic', '2026-05-10 14:06:33', '2026-05-10 14:06:33'),
(157, 1, 157, 71, 10, 272, 'earned', 'Recycled 94g of plastic', '2026-05-10 14:08:40', '2026-05-10 14:08:40'),
(158, 1, 158, 71, 10, 282, 'earned', 'Recycled 47g of aluminum', '2026-05-10 14:09:13', '2026-05-10 14:09:13'),
(159, 1, 159, 71, 10, 292, 'earned', 'Recycled 18g of aluminum', '2026-05-10 14:11:29', '2026-05-10 14:11:29'),
(160, 1, 160, 71, 10, 302, 'earned', 'Recycled 27g of aluminum', '2026-05-10 14:15:46', '2026-05-10 14:15:46'),
(161, 1, 161, 71, 39, 341, 'earned', 'Recycled 396g of paper', '2026-05-10 14:16:22', '2026-05-10 14:16:22'),
(162, 1, 162, 71, 14, 355, 'earned', 'Recycled 140g of paper', '2026-05-10 14:16:57', '2026-05-10 14:16:57'),
(163, 1, 163, 71, 235, 590, 'earned', 'Recycled 475g of glass', '2026-05-10 14:17:21', '2026-05-10 14:17:21'),
(164, 1, 164, 71, 90, 680, 'earned', 'Recycled 450g of glass', '2026-05-10 14:18:13', '2026-05-10 14:18:13'),
(165, 1, 165, 71, 27, 707, 'earned', 'Recycled 277g of glass', '2026-05-10 14:18:36', '2026-05-10 14:18:36'),
(166, 1, 166, 71, 15, 722, 'earned', 'Recycled 36g of plastic', '2026-05-10 14:19:05', '2026-05-10 14:19:05'),
(167, 1, 167, 71, 8, 730, 'earned', 'Recycled 83g of glass', '2026-05-10 14:21:39', '2026-05-10 14:21:39'),
(168, 1, 168, 71, 9, 739, 'earned', 'Recycled 94g of glass', '2026-05-10 14:29:44', '2026-05-10 14:29:44'),
(169, 1, 169, 71, 28, 767, 'earned', 'Recycled 43g of aluminum', '2026-05-10 14:30:07', '2026-05-10 14:30:07'),
(170, 1, 170, 71, 20, 787, 'earned', 'Recycled 48g of plastic', '2026-05-10 14:30:31', '2026-05-10 14:30:31'),
(171, 1, 171, 71, 22, 809, 'earned', 'Recycled 224g of paper', '2026-05-10 14:31:57', '2026-05-10 14:31:57'),
(172, 1, 172, 71, 14, 823, 'earned', 'Recycled 20g of aluminum', '2026-05-10 14:32:34', '2026-05-10 14:32:34'),
(173, 1, 173, 71, 0, 823, 'earned', 'Recycled 9g of plastic', '2026-05-10 14:33:12', '2026-05-10 14:33:12'),
(174, 1, 174, 71, 17, 840, 'earned', 'Recycled 176g of glass', '2026-05-10 14:33:35', '2026-05-10 14:33:35'),
(175, 1, 175, 71, 6, 846, 'earned', 'Recycled 65g of paper', '2026-05-10 14:34:09', '2026-05-10 14:34:09'),
(176, 1, 176, 71, 30, 876, 'earned', 'Recycled 305g of paper', '2026-05-10 14:34:41', '2026-05-10 14:34:41'),
(177, 1, 177, 71, 47, 923, 'earned', 'Recycled 475g of glass', '2026-05-10 14:35:03', '2026-05-10 14:35:03'),
(178, 1, 178, 71, 44, 967, 'earned', 'Recycled 447g of glass', '2026-05-10 14:35:27', '2026-05-10 14:35:27'),
(179, 1, 179, 71, 28, 995, 'earned', 'Recycled 41g of aluminum', '2026-05-10 14:35:55', '2026-05-10 14:35:55'),
(180, 1, 180, 71, 29, 1024, 'earned', 'Recycled 297g of paper', '2026-05-10 14:36:24', '2026-05-10 14:36:24'),
(181, 1, 181, 71, 7, 1031, 'earned', 'Recycled 15g of aluminum', '2026-05-10 14:39:26', '2026-05-10 14:39:26'),
(182, 1, 182, 71, 16, 1047, 'earned', 'Recycled 168g of paper', '2026-05-10 14:43:59', '2026-05-10 14:43:59'),
(183, 1, 183, 71, 38, 1085, 'earned', 'Recycled 382g of glass', '2026-05-10 14:47:22', '2026-05-10 14:47:22'),
(184, 1, 184, 71, 35, 1120, 'earned', 'Recycled 356g of paper', '2026-05-10 14:51:41', '2026-05-10 14:51:41'),
(185, 1, 185, 71, 11, 1131, 'earned', 'Recycled 117g of paper', '2026-05-10 14:52:42', '2026-05-10 14:52:42'),
(186, 1, 186, 71, 14, 1145, 'earned', 'Recycled 26g of aluminum', '2026-05-10 14:53:12', '2026-05-10 14:53:12'),
(187, 1, 187, 72, 7, 1152, 'earned', 'Recycled 79g of glass', '2026-05-10 16:20:00', '2026-05-10 16:20:00'),
(188, 4, 188, 40, 7, 7, 'earned', 'Recycled 75g of paper', '2026-05-10 16:21:38', '2026-05-10 16:21:38'),
(189, 4, 189, 73, 33, 33, 'earned', 'Recycled 336g of paper', '2026-05-10 16:23:48', '2026-05-10 16:23:48'),
(190, 4, 190, 74, 25, 58, 'earned', 'Recycled 252g of paper', '2026-05-10 16:24:57', '2026-05-10 16:24:57'),
(191, 4, 191, 75, 14, 72, 'earned', 'Recycled 20g of aluminum', '2026-05-10 16:26:39', '2026-05-10 16:26:39'),
(192, 4, 192, 76, 14, 86, 'earned', 'Recycled 28g of aluminum', '2026-05-10 16:27:45', '2026-05-10 16:27:45'),
(193, 4, 193, 77, 14, 100, 'earned', 'Recycled 20g of aluminum', '2026-05-10 16:30:56', '2026-05-10 16:30:56'),
(194, 4, 194, 78, 28, 128, 'earned', 'Recycled 44g of aluminum', '2026-05-10 16:37:01', '2026-05-10 16:37:01'),
(195, 4, 195, 79, 37, 165, 'earned', 'Recycled 377g of paper', '2026-05-10 16:38:03', '2026-05-10 16:38:03'),
(196, 1, 196, 80, 20, 1172, 'earned', 'Recycled 45g of plastic', '2026-05-11 09:36:30', '2026-05-11 09:36:30'),
(197, 1, 197, 80, 29, 1201, 'earned', 'Recycled 294g of glass', '2026-05-11 09:36:56', '2026-05-11 09:36:56'),
(198, 1, 198, 80, 21, 1222, 'earned', 'Recycled 33g of aluminum', '2026-05-11 09:37:19', '2026-05-11 09:37:19'),
(199, 1, 199, 80, 15, 1237, 'earned', 'Recycled 157g of glass', '2026-05-11 09:38:44', '2026-05-11 09:38:44'),
(200, 6, 200, 81, 28, 28, 'earned', 'Recycled 43g of aluminum', '2026-05-11 09:51:06', '2026-05-11 09:51:06'),
(201, 6, 201, 81, 14, 42, 'earned', 'Recycled 26g of aluminum', '2026-05-11 09:55:26', '2026-05-11 09:55:26'),
(202, 1, 202, 83, 33, 1270, 'earned', 'Recycled 334g of glass', '2026-05-11 10:01:34', '2026-05-11 10:01:34'),
(203, 3, 203, 84, 28, 253, 'earned', 'Recycled 42g of aluminum', '2026-05-11 10:11:18', '2026-05-11 10:11:18'),
(204, 1, 204, 85, 7, 1277, 'earned', 'Recycled 78g of paper', '2026-05-11 10:15:59', '2026-05-11 10:15:59'),
(205, 1, 205, 85, 7, 1284, 'earned', 'Recycled 12g of aluminum', '2026-05-11 10:16:23', '2026-05-11 10:16:23'),
(206, 1, 206, 85, 41, 1325, 'earned', 'Recycled 416g of glass', '2026-05-11 10:17:20', '2026-05-11 10:17:20'),
(207, 1, 207, 85, 14, 1339, 'earned', 'Recycled 24g of aluminum', '2026-05-11 10:17:50', '2026-05-11 10:17:50'),
(208, 1, 208, 85, 5, 1344, 'earned', 'Recycled 12g of plastic', '2026-05-11 10:18:40', '2026-05-11 10:18:40'),
(209, 1, 209, 85, 28, 1372, 'earned', 'Recycled 49g of aluminum', '2026-05-11 10:19:06', '2026-05-11 10:19:06'),
(210, 4, 210, 86, 45, 210, 'earned', 'Recycled 453g of paper', '2026-05-11 14:56:57', '2026-05-11 14:56:57'),
(211, 4, 211, 86, 10, 220, 'earned', 'Recycled 21g of plastic', '2026-05-11 14:58:07', '2026-05-11 14:58:07'),
(212, 4, 212, 86, 20, 240, 'earned', 'Recycled 47g of plastic', '2026-05-11 14:58:33', '2026-05-11 14:58:33'),
(213, 4, 213, 86, 7, 247, 'earned', 'Recycled 19g of aluminum', '2026-05-11 14:59:37', '2026-05-11 14:59:37'),
(214, 4, 214, 86, 5, 252, 'earned', 'Recycled 15g of plastic', '2026-05-11 15:00:27', '2026-05-11 15:00:27'),
(215, 4, 215, 86, 15, 267, 'earned', 'Recycled 32g of plastic', '2026-05-11 15:01:09', '2026-05-11 15:01:09'),
(216, 4, 216, 86, 28, 295, 'earned', 'Recycled 40g of aluminum', '2026-05-11 15:01:48', '2026-05-11 15:01:48'),
(217, 4, 217, 86, 28, 323, 'earned', 'Recycled 45g of aluminum', '2026-05-11 15:02:20', '2026-05-11 15:02:20'),
(218, 4, 218, 86, 41, 364, 'earned', 'Recycled 415g of paper', '2026-05-11 15:03:58', '2026-05-11 15:03:58'),
(219, 4, 219, 86, 7, 371, 'earned', 'Recycled 75g of glass', '2026-05-11 15:05:42', '2026-05-11 15:05:42'),
(220, 4, 220, 86, 17, 388, 'earned', 'Recycled 171g of glass', '2026-05-11 15:06:36', '2026-05-11 15:06:36'),
(221, 4, 221, 86, 34, 422, 'earned', 'Recycled 342g of glass', '2026-05-11 15:07:10', '2026-05-11 15:07:10'),
(222, 4, 222, 86, 47, 469, 'earned', 'Recycled 475g of glass', '2026-05-11 15:07:58', '2026-05-11 15:07:58'),
(223, 4, 223, 86, 13, 482, 'earned', 'Recycled 131g of glass', '2026-05-11 15:09:59', '2026-05-11 15:09:59'),
(224, 4, 224, 86, 10, 492, 'earned', 'Recycled 21g of plastic', '2026-05-11 15:10:22', '2026-05-11 15:10:22'),
(225, 4, 225, 86, 47, 539, 'earned', 'Recycled 473g of paper', '2026-05-11 15:10:46', '2026-05-11 15:10:46'),
(226, 4, 226, 86, 14, 553, 'earned', 'Recycled 29g of aluminum', '2026-05-11 15:11:13', '2026-05-11 15:11:13'),
(227, 6, 227, 87, 37, 79, 'earned', 'Recycled 375g of paper', '2026-05-11 15:16:20', '2026-05-11 15:16:20'),
(228, 6, 228, 87, 21, 100, 'earned', 'Recycled 34g of aluminum', '2026-05-11 15:18:42', '2026-05-11 15:18:42'),
(229, 1, 229, 94, 5, 1377, 'earned', 'Recycled 18g of plastic', '2026-05-11 15:58:08', '2026-05-11 15:58:08'),
(230, 6, 230, 93, 7, 107, 'earned', 'Recycled 18g of aluminum', '2026-05-11 16:04:20', '2026-05-11 16:04:20'),
(231, 8, 231, 95, 20, 20, 'earned', 'Recycled 49g of plastic', '2026-05-11 16:13:41', '2026-05-11 16:13:41'),
(232, 8, 232, 97, 10, 30, 'earned', 'Recycled 26g of plastic', '2026-05-11 16:16:33', '2026-05-11 16:16:33'),
(233, 1, 233, 96, 28, 1405, 'earned', 'Recycled 43g of aluminum', '2026-05-11 16:47:18', '2026-05-11 16:47:18'),
(234, 1, 234, 96, 36, 1441, 'earned', 'Recycled 365g of paper', '2026-05-11 16:49:36', '2026-05-11 16:49:36'),
(235, 1, 235, 98, 18, 1459, 'earned', 'Recycled 183g of glass', '2026-05-11 16:53:50', '2026-05-11 16:53:50'),
(236, 1, 236, 99, 28, 1487, 'earned', 'Recycled 49g of aluminum', '2026-05-11 16:55:12', '2026-05-11 16:55:12'),
(237, 1, 237, 99, 26, 1513, 'earned', 'Recycled 261g of glass', '2026-05-11 16:55:43', '2026-05-11 16:55:43'),
(238, 1, 238, 99, 47, 1560, 'earned', 'Recycled 475g of paper', '2026-05-11 16:56:13', '2026-05-11 16:56:13'),
(239, 1, 239, 100, 14, 1574, 'earned', 'Recycled 23g of aluminum', '2026-05-11 16:57:34', '2026-05-11 16:57:34'),
(240, 1, 240, 100, 13, 1587, 'earned', 'Recycled 135g of glass', '2026-05-11 16:58:08', '2026-05-11 16:58:08'),
(241, 1, 241, 100, 29, 1616, 'earned', 'Recycled 296g of glass', '2026-05-11 16:58:45', '2026-05-11 16:58:45'),
(242, 1, 242, 100, 5, 1621, 'earned', 'Recycled 17g of plastic', '2026-05-11 16:59:14', '2026-05-11 16:59:14'),
(243, 1, 243, 101, 21, 1642, 'earned', 'Recycled 38g of aluminum', '2026-05-11 17:27:38', '2026-05-11 17:27:38'),
(244, 1, 244, 101, 15, 1657, 'earned', 'Recycled 37g of plastic', '2026-05-11 17:28:01', '2026-05-11 17:28:01'),
(245, 1, 245, 101, 28, 1685, 'earned', 'Recycled 45g of aluminum', '2026-05-11 17:28:25', '2026-05-11 17:28:25'),
(246, 1, 246, 101, 42, 1727, 'earned', 'Recycled 420g of glass', '2026-05-11 17:28:55', '2026-05-11 17:28:55'),
(247, 1, 247, 101, 41, 1768, 'earned', 'Recycled 418g of paper', '2026-05-11 17:29:27', '2026-05-11 17:29:27'),
(248, 1, 248, 101, 14, 1782, 'earned', 'Recycled 146g of paper', '2026-05-11 17:30:21', '2026-05-11 17:30:21'),
(249, 1, 249, 101, 6, 1788, 'earned', 'Recycled 61g of glass', '2026-05-11 17:31:05', '2026-05-11 17:31:05'),
(250, 1, 250, 101, 14, 1802, 'earned', 'Recycled 26g of aluminum', '2026-05-11 17:32:01', '2026-05-11 17:32:01'),
(251, 1, 251, 101, 38, 1840, 'earned', 'Recycled 381g of glass', '2026-05-11 17:32:59', '2026-05-11 17:32:59'),
(252, 1, 252, 101, 42, 1882, 'earned', 'Recycled 420g of glass', '2026-05-11 17:33:24', '2026-05-11 17:33:24'),
(253, 1, 253, 101, 39, 1921, 'earned', 'Recycled 399g of glass', '2026-05-11 17:33:53', '2026-05-11 17:33:53'),
(254, 1, 254, 102, 11, 1932, 'earned', 'Recycled 113g of glass', '2026-05-11 17:35:29', '2026-05-11 17:35:29'),
(255, 1, 255, 102, 5, 1937, 'earned', 'Recycled 18g of plastic', '2026-05-11 17:36:02', '2026-05-11 17:36:02'),
(256, 1, 256, 102, 10, 1947, 'earned', 'Recycled 20g of plastic', '2026-05-11 17:36:31', '2026-05-11 17:36:31'),
(257, 1, 257, 102, 21, 1968, 'earned', 'Recycled 37g of aluminum', '2026-05-11 17:37:34', '2026-05-11 17:37:34'),
(258, 1, 258, 102, 5, 1973, 'earned', 'Recycled 15g of plastic', '2026-05-11 17:38:02', '2026-05-11 17:38:02'),
(259, 1, 259, 102, 10, 1983, 'earned', 'Recycled 25g of plastic', '2026-05-11 17:38:50', '2026-05-11 17:38:50'),
(260, 1, 260, 102, 28, 2011, 'earned', 'Recycled 45g of aluminum', '2026-05-11 17:39:16', '2026-05-11 17:39:16'),
(261, 1, 261, 102, 15, 2026, 'earned', 'Recycled 34g of plastic', '2026-05-11 17:39:58', '2026-05-11 17:39:58'),
(262, 1, 262, 102, 10, 2036, 'earned', 'Recycled 28g of plastic', '2026-05-11 17:40:38', '2026-05-11 17:40:38'),
(263, 1, 263, 102, 7, 2043, 'earned', 'Recycled 11g of aluminum', '2026-05-11 17:41:09', '2026-05-11 17:41:09'),
(264, 1, 264, 102, 33, 2076, 'earned', 'Recycled 330g of paper', '2026-05-11 17:41:34', '2026-05-11 17:41:34'),
(265, 1, 265, 102, 7, 2083, 'earned', 'Recycled 77g of paper', '2026-05-11 17:42:07', '2026-05-11 17:42:07'),
(266, 1, 266, 102, 5, 2088, 'earned', 'Recycled 57g of paper', '2026-05-11 17:42:33', '2026-05-11 17:42:33'),
(267, 1, 267, 102, 7, 2095, 'earned', 'Recycled 72g of paper', '2026-05-11 17:43:01', '2026-05-11 17:43:01'),
(268, 1, 268, 102, 21, 2116, 'earned', 'Recycled 34g of aluminum', '2026-05-11 17:43:27', '2026-05-11 17:43:27'),
(269, 1, 269, 102, 7, 2123, 'earned', 'Recycled 10g of aluminum', '2026-05-11 17:43:57', '2026-05-11 17:43:57'),
(270, 1, 270, 102, 49, 2172, 'earned', 'Recycled 496g of paper', '2026-05-11 17:44:40', '2026-05-11 17:44:40'),
(271, 1, 271, 102, 28, 2200, 'earned', 'Recycled 45g of aluminum', '2026-05-11 17:45:07', '2026-05-11 17:45:07'),
(272, 1, 272, 103, 48, 2248, 'earned', 'Recycled 488g of paper', '2026-05-11 18:30:20', '2026-05-11 18:30:20'),
(273, 1, 273, 104, 21, 2269, 'earned', 'Recycled 36g of aluminum', '2026-05-11 18:36:05', '2026-05-11 18:36:05'),
(274, 1, 274, 105, 28, 2297, 'earned', 'Recycled 46g of aluminum', '2026-05-11 18:36:59', '2026-05-11 18:36:59'),
(275, 1, 275, 106, 18, 2315, 'earned', 'Recycled 182g of glass', '2026-05-11 18:43:42', '2026-05-11 18:43:42'),
(276, 1, 276, 107, 7, 2322, 'earned', 'Recycled 16g of aluminum', '2026-05-11 23:23:28', '2026-05-11 23:23:28'),
(277, 3, 277, 108, 14, 267, 'earned', 'Recycled 28g of aluminum', '2026-05-11 23:27:37', '2026-05-11 23:27:37'),
(278, 3, 278, 108, 40, 307, 'earned', 'Recycled 406g of paper', '2026-05-11 23:28:17', '2026-05-11 23:28:17'),
(279, 3, 279, 108, 15, 322, 'earned', 'Recycled 35g of plastic', '2026-05-11 23:28:52', '2026-05-11 23:28:52'),
(280, 3, 280, 108, 17, 339, 'earned', 'Recycled 176g of glass', '2026-05-11 23:29:35', '2026-05-11 23:29:35'),
(281, 1, 281, 109, 20, 2342, 'earned', 'Recycled 204g of glass', '2026-05-11 23:32:23', '2026-05-11 23:32:23'),
(282, 1, 282, 110, 20, 2362, 'earned', 'Recycled 48g of plastic', '2026-05-12 02:49:31', '2026-05-12 02:49:31'),
(283, 1, 283, 111, 5, 2367, 'earned', 'Recycled 15g of plastic', '2026-05-12 02:58:30', '2026-05-12 02:58:30'),
(284, 1, 284, 111, 5, 2372, 'earned', 'Recycled 10g of plastic', '2026-05-12 03:00:13', '2026-05-12 03:00:13'),
(285, 1, 285, 111, 10, 2382, 'earned', 'Recycled 27g of plastic', '2026-05-12 03:01:19', '2026-05-12 03:01:19'),
(286, 1, 286, 112, 5, 2387, 'earned', 'Recycled 12g of plastic', '2026-07-29 07:13:20', '2026-07-29 07:13:20'),
(287, 1, 287, 113, 14, 2401, 'earned', 'Recycled 29g of aluminum', '2026-08-13 01:06:24', '2026-08-13 01:06:24'),
(288, 1, 288, 113, 0, 2401, 'earned', 'Recycled 9g of plastic', '2026-08-13 01:06:50', '2026-08-13 01:06:50'),
(289, 1, 289, 113, 7, 2408, 'earned', 'Recycled 10g of aluminum', '2026-08-13 01:07:17', '2026-08-13 01:07:17'),
(290, 1, 290, 113, 25, 2433, 'earned', 'Recycled 251g of paper', '2026-08-13 01:08:24', '2026-08-13 01:08:24'),
(291, 5, 291, 115, 36, 36, 'earned', 'Recycled 368g of paper', '2026-08-13 03:15:28', '2026-08-13 03:15:28'),
(292, 5, 292, 116, 28, 64, 'earned', 'Recycled 44g of aluminum', '2026-08-13 03:28:09', '2026-08-13 03:28:09'),
(293, 5, 293, 117, 15, 79, 'earned', 'Recycled 31g of plastic', '2026-08-13 03:29:29', '2026-08-13 03:29:29'),
(294, 5, 294, 117, 21, 100, 'earned', 'Recycled 36g of aluminum', '2026-08-13 03:29:54', '2026-08-13 03:29:54'),
(295, 5, 295, 118, 7, 107, 'earned', 'Recycled 15g of aluminum', '2026-08-13 03:31:18', '2026-08-13 03:31:18'),
(296, 5, 296, 119, 14, 121, 'earned', 'Recycled 29g of aluminum', '2026-08-13 03:45:59', '2026-08-13 03:45:59'),
(297, 5, 297, 120, 28, 149, 'earned', 'Recycled 43g of aluminum', '2026-08-13 03:48:06', '2026-08-13 03:48:06'),
(298, 9, 298, 121, 28, 28, 'earned', 'Recycled 47g of aluminum', '2026-08-13 03:50:04', '2026-08-13 03:50:04'),
(299, 5, 299, 122, 28, 177, 'earned', 'Recycled 42g of aluminum', '2026-08-13 03:56:13', '2026-08-13 03:56:13'),
(300, 5, 300, 123, 21, 198, 'earned', 'Recycled 37g of aluminum', '2026-08-13 03:57:04', '2026-08-13 03:57:04'),
(301, 5, 301, 124, 14, 212, 'earned', 'Recycled 28g of aluminum', '2026-08-13 03:58:09', '2026-08-13 03:58:09'),
(302, 5, 302, 124, 7, 219, 'earned', 'Recycled 16g of aluminum', '2026-08-13 03:58:38', '2026-08-13 03:58:38'),
(303, 5, 303, 126, 10, 229, 'earned', 'Recycled 20g of plastic', '2026-08-13 09:34:29', '2026-08-13 09:34:29'),
(304, 5, 304, 128, 36, 265, 'earned', 'Recycled 363g of glass', '2026-08-13 09:42:59', '2026-08-13 09:42:59'),
(305, 5, 305, 129, 9, 274, 'earned', 'Recycled 94g of glass', '2026-08-13 09:43:48', '2026-08-13 09:43:48'),
(306, 5, 306, 130, 28, 302, 'earned', 'Recycled 287g of paper', '2026-08-13 09:50:25', '2026-08-13 09:50:25'),
(307, 5, 307, 131, 13, 315, 'earned', 'Recycled 133g of glass', '2026-08-13 09:51:06', '2026-08-13 09:51:06'),
(308, 5, 308, 131, 36, 351, 'earned', 'Recycled 363g of glass', '2026-08-13 09:51:33', '2026-08-13 09:51:33'),
(309, 5, 309, 132, 25, 376, 'earned', 'Recycled 259g of glass', '2026-08-13 09:52:13', '2026-08-13 09:52:13'),
(310, 5, 310, 132, 28, 404, 'earned', 'Recycled 280g of glass', '2026-08-13 09:54:05', '2026-08-13 09:54:05'),
(311, 5, 311, 134, 31, 435, 'earned', 'Recycled 315g of glass', '2026-08-13 09:58:35', '2026-08-13 09:58:35'),
(312, 5, 312, 136, 8, 443, 'earned', 'Recycled 84g of glass', '2026-08-13 10:00:45', '2026-08-13 10:00:45'),
(313, 5, 313, 136, 44, 487, 'earned', 'Recycled 445g of paper', '2026-08-13 10:03:24', '2026-08-13 10:03:24'),
(314, 5, 314, 136, 28, 515, 'earned', 'Recycled 48g of aluminum', '2026-08-13 10:14:14', '2026-08-13 10:14:14'),
(315, 5, 315, 136, 27, 542, 'earned', 'Recycled 271g of paper', '2026-08-13 10:21:15', '2026-08-13 10:21:15'),
(316, 5, 316, 137, 47, 589, 'earned', 'Recycled 477g of paper', '2026-08-13 10:23:39', '2026-08-13 10:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `qr_sessions`
--

CREATE TABLE `qr_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `qr_token` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','scanned','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scanned_by` bigint UNSIGNED DEFAULT NULL,
  `session_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kiosk_token` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_sessions`
--

INSERT INTO `qr_sessions` (`id`, `machine_id`, `qr_token`, `status`, `scanned_by`, `session_code`, `kiosk_token`, `expires_at`, `created_at`, `updated_at`) VALUES
(40, 1, 'Iplr5g8YdRbDcCkqEoPlWSWiplbf9Cleh7eVhQWk1778212209', 'expired', NULL, NULL, NULL, '2026-05-07 19:55:09', '2026-05-07 19:50:09', '2026-05-07 22:27:22'),
(41, 1, 'YC4n1ZwdM3vaU0VDv5GmYtEGQmCp30PXIOkUSsnn1778221644', 'scanned', 3, NULL, NULL, '2026-05-07 22:32:24', '2026-05-07 22:27:24', '2026-05-07 22:27:34'),
(42, 1, 'Q8wXRSeJlt5u6AO4G7TUEm33s4ZRrGAgDGbKAJ1V1778221843', 'scanned', 3, NULL, NULL, '2026-05-07 22:35:43', '2026-05-07 22:30:43', '2026-05-07 22:30:51'),
(43, 1, 'hsNxAQaZxC62Bnqre4fI7fvWFCbICuRI9WekaCMu1778221926', 'scanned', 3, NULL, NULL, '2026-05-07 22:37:06', '2026-05-07 22:32:06', '2026-05-07 22:32:16'),
(44, 1, 'l44ekN8ZNrKV0q7Y1Moq8wN6QTsBZ0sEDP7jDZdx1778221960', 'scanned', 1, NULL, NULL, '2026-05-07 22:37:40', '2026-05-07 22:32:40', '2026-05-07 22:33:43'),
(45, 1, 'uRSuo9OxUghNsGKry1u6TFnNRE01GGrP0bm07fhh1778222333', 'scanned', 1, NULL, NULL, '2026-05-07 22:43:53', '2026-05-07 22:38:53', '2026-05-07 22:39:20'),
(46, 1, 'YGSC1gNXw7mV4OPRrEir2ExgaLHQrqaFk7yeOhDX1778224727', 'scanned', 1, NULL, NULL, '2026-05-07 23:23:47', '2026-05-07 23:18:47', '2026-05-07 23:18:54'),
(47, 1, '9QYUoY7JjN2GLFfOR5rIb7pAs5ebC6I4WbG2ITcD1778225992', 'scanned', 1, NULL, NULL, '2026-05-07 23:44:52', '2026-05-07 23:39:52', '2026-05-07 23:39:59'),
(48, 1, '4B5eVmaHqoiRxsAMqFTWu2bIywKgG4PtVHIiCL4m1778340850', 'scanned', 1, NULL, NULL, '2026-05-09 07:39:10', '2026-05-09 07:34:10', '2026-05-09 07:34:21'),
(49, 1, 'dCX1kQbEoDxiNEusSJqE4T70YNIGq2JhjHL5kfzo1778340873', 'scanned', 1, NULL, NULL, '2026-05-09 07:39:33', '2026-05-09 07:34:33', '2026-05-09 07:34:38'),
(50, 1, 'XANPBtM0eAImaEAWVn2FkTVjehNOWZJDndDrVSEG1778340888', 'scanned', 1, NULL, NULL, '2026-05-09 07:39:48', '2026-05-09 07:34:48', '2026-05-09 07:34:52'),
(51, 1, '9CScJLsN6PKkzMxjlbv2RQA7DebJcUYPb3trDkRH1778341900', 'scanned', 1, NULL, NULL, '2026-05-09 07:56:40', '2026-05-09 07:51:40', '2026-05-09 07:51:44'),
(52, 1, 'a2I8r8RhMUTux82VTTgUh3wYNXsbb1tpBCbYFDbX1778342346', 'scanned', 4, NULL, NULL, '2026-05-09 08:04:06', '2026-05-09 07:59:06', '2026-05-09 07:59:35'),
(53, 1, 'BaLS6xCDd0S5juYfT9rEE7IAhMH6bsqgi2TVolth1778342446', 'scanned', 4, NULL, NULL, '2026-05-09 08:05:46', '2026-05-09 08:00:46', '2026-05-09 08:00:52'),
(54, 1, 'dwnuyMBDH30a6w1bK3La2QNBgk1AvTBxk6XwfkEZ1778343070', 'expired', NULL, NULL, NULL, '2026-05-09 08:16:10', '2026-05-09 08:11:10', '2026-05-09 08:24:59'),
(55, 1, 'hlYgaJQN3WLlTAeUshL207UNderOfXiPmZpKRLq61778343900', 'expired', NULL, NULL, NULL, '2026-05-09 08:30:00', '2026-05-09 08:25:00', '2026-05-09 08:25:00'),
(56, 1, 'k7rte0JzUSJwKkvMVCC6weBBkLADgSiBgCApaLKG1778343900', 'expired', NULL, NULL, NULL, '2026-05-09 08:30:00', '2026-05-09 08:25:00', '2026-05-09 08:25:00'),
(57, 1, '2nM0ZGWg3V6WH7A3vPd3ynCpWXHMG9v1ulXcLmTp1778343900', 'expired', NULL, NULL, NULL, '2026-05-09 08:30:00', '2026-05-09 08:25:00', '2026-05-09 08:25:19'),
(58, 1, 'UrCQ4pysUl8ETmjkTSmftl7iFnCUdOTGGeL38vQq1778343919', 'scanned', 1, NULL, NULL, '2026-05-09 08:30:19', '2026-05-09 08:25:19', '2026-05-09 08:26:02'),
(59, 1, 'zJeYEuFcu20SioZOCSc7xJ4ZiJW7jXFgpHe1LTsq1778343971', 'scanned', 1, NULL, NULL, '2026-05-09 08:31:11', '2026-05-09 08:26:11', '2026-05-09 08:26:15'),
(60, 1, 'jSrsT6Wz8sZjDmFf2eyjM0aEDLJPfMyx0ZKnIwT91778343996', 'scanned', 1, NULL, NULL, '2026-05-09 08:31:36', '2026-05-09 08:26:36', '2026-05-09 08:26:59'),
(61, 1, 'K2MM6I15z0Ajx1VrM5QxdTyMQzquFcqwjZABZCc81778344029', 'scanned', 1, NULL, NULL, '2026-05-09 08:32:09', '2026-05-09 08:27:09', '2026-05-09 08:27:14'),
(62, 1, 'IMmloboL8l1HBYrfJi0CAIFHbxaX0yPJwQ31eQD91778344045', 'scanned', 1, NULL, NULL, '2026-05-09 08:32:25', '2026-05-09 08:27:25', '2026-05-09 08:27:29'),
(63, 1, 'kZgyyBOfiyB5hDeDhUvUdtKA6NLi9UZtUvOBss7m1778344074', 'expired', NULL, NULL, NULL, '2026-05-09 08:32:54', '2026-05-09 08:27:54', '2026-05-09 08:29:56'),
(64, 1, 'lGipEHgEica2ZlpE15l8FXP3tLMzhENKEhRBIQgo1778344196', 'expired', NULL, NULL, NULL, '2026-05-09 08:34:56', '2026-05-09 08:29:56', '2026-05-09 08:30:12'),
(65, 1, 'V8OKOBJFV5zWpL17tUoC2j1cRSMangoG4qE06poI1778344212', 'scanned', 1, NULL, NULL, '2026-05-09 08:35:12', '2026-05-09 08:30:12', '2026-05-09 08:30:25'),
(66, 1, 'YoSHArVDaFuR1AqYDGMwZSP0z8Ki4EBp5Tiq5Ea51778344262', 'scanned', 1, NULL, NULL, '2026-05-09 08:36:02', '2026-05-09 08:31:02', '2026-05-09 08:31:08'),
(67, 1, 'txh2vfy6ImkYgctsrqKU2snJGcz7WBgvUNRoQyf11778344313', 'expired', NULL, NULL, NULL, '2026-05-09 08:36:53', '2026-05-09 08:31:53', '2026-05-09 08:38:49'),
(68, 1, 'tHOgAizaZyO5jPoDZZmDjFFUSolodeowfUzpNqNB1778344729', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:49', '2026-05-09 08:38:49', '2026-05-09 08:38:49'),
(69, 1, 'OaQYjwbP38g4MGfXNOhperIimgLTyBcft9L6EPQS1778344729', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:49', '2026-05-09 08:38:49', '2026-05-09 08:38:51'),
(70, 1, 'oDEN4LMtqVgE5SqjKUuQ6b7LGwEGC4Nji9axvDfE1778344731', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:51', '2026-05-09 08:38:51', '2026-05-09 08:38:53'),
(71, 1, '8moRcxBUw81FxyHgIKDB6IXQJYZnheZIqNdWDmeg1778344733', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:53', '2026-05-09 08:38:53', '2026-05-09 08:38:54'),
(72, 1, 'gxRlmQgk1vnLg2msiWmoYKkt0NWOro6xAaXFtp3K1778344734', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:54', '2026-05-09 08:38:54', '2026-05-09 08:38:54'),
(73, 1, 'DqoWoNpSiyMFBztWtlEsGEkADcyyr9zm315ocHBf1778344734', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:54', '2026-05-09 08:38:54', '2026-05-09 08:38:57'),
(74, 1, 'IaCgeUFqaxoLk3vBcv4ydNUwHo1VivSTJh6HTbMR1778344737', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:57', '2026-05-09 08:38:57', '2026-05-09 08:38:58'),
(75, 1, 'YTI2riVBrva0kLxAi2bnicX6jqFaIDZCk7nWoLER1778344738', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:58', '2026-05-09 08:38:58', '2026-05-09 08:38:59'),
(76, 1, '0xrLcZWE92ecxNZgfVuXPycU8yjVpGohTZB98IOs1778344739', 'expired', NULL, NULL, NULL, '2026-05-09 08:43:59', '2026-05-09 08:38:59', '2026-05-09 08:39:02'),
(77, 1, 'aLDa7HZCoHX3JxLc60dkSuoLElJQu4drD2127rBY1778344742', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:02', '2026-05-09 08:39:02', '2026-05-09 08:39:03'),
(78, 1, '7HhrFnsIG6KsvKteXg3uW0bzj0V6n5V0dAeJ4ZLB1778344743', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:03', '2026-05-09 08:39:03', '2026-05-09 08:39:03'),
(79, 1, 'GxthuSykGHzhjU5206qr2jgffJPUSFJiXLCWKff31778344743', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:03', '2026-05-09 08:39:03', '2026-05-09 08:39:06'),
(80, 1, '9M9X9LFoTiPkUbp5vVEcJEWydQILJBYo856LXPaT1778344746', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:06', '2026-05-09 08:39:06', '2026-05-09 08:39:07'),
(81, 1, 'F7MQrKAopEx6OwyO3rOxB42zmiq1I2Xnh1I4tcN01778344747', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:07', '2026-05-09 08:39:07', '2026-05-09 08:39:07'),
(82, 1, 'S8b9gSOROGQbG5dKUrQzdJHu6UZ1xDdCABRmpYAu1778344747', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:07', '2026-05-09 08:39:07', '2026-05-09 08:39:10'),
(83, 1, 'wSAVzxKoQ6QHawd91XPay88kXhhasRllwNXqk44b1778344750', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:10', '2026-05-09 08:39:10', '2026-05-09 08:39:10'),
(84, 1, 'ePHZeFm4GkLEfkDpmT1bfyol0jihZc2NDkv6zi861778344750', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:10', '2026-05-09 08:39:10', '2026-05-09 08:39:11'),
(85, 1, 'H6J4FWpNVWEnhpc1VBGBJZHEPZA9cMb2x3Q9M9kz1778344751', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:11', '2026-05-09 08:39:11', '2026-05-09 08:39:14'),
(86, 1, 'lp5QRKKkC8KpsNyeiQf55CW2WCUK83ow4N53zuyf1778344754', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:14', '2026-05-09 08:39:14', '2026-05-09 08:39:14'),
(87, 1, 'SmBUQnz9tSXfBIvejaTj8DvAPuvQzz8k6VgNEPS91778344754', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:14', '2026-05-09 08:39:14', '2026-05-09 08:39:15'),
(88, 1, 'qNFcqY0HknzLHNmllicwmiieMr4mc4AyIrxLGIAl1778344755', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:15', '2026-05-09 08:39:15', '2026-05-09 08:39:18'),
(89, 1, 'oLJbP4UTbidvIwWTi9AL111sm4XK25ulwHzSloSB1778344758', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:18', '2026-05-09 08:39:18', '2026-05-09 08:39:18'),
(90, 1, 'cEuyhkDxdDQ4WeuGKgYYQ3zJzE44AIOEBC9gtaxI1778344758', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:18', '2026-05-09 08:39:18', '2026-05-09 08:39:19'),
(91, 1, 'JqLCeeQ6Zob94A7hr3ylLMuO0uhOjQnXQe5O23751778344759', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:19', '2026-05-09 08:39:19', '2026-05-09 08:39:21'),
(92, 1, 'rQnPCsZtnVd8peVNRfKAzkfF2FIdBQSJah6y6U031778344761', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:21', '2026-05-09 08:39:21', '2026-05-09 08:39:22'),
(93, 1, 'ddIkTLn6Hp43wl6QFgQMigsq7mCcs2zrGLQUkgOK1778344762', 'expired', NULL, NULL, NULL, '2026-05-09 08:44:22', '2026-05-09 08:39:22', '2026-05-09 08:41:13'),
(94, 1, 'aGavA2Lp31blH3Vrk8zbuJGLmjSvLmHIO6T4BnJN1778344873', 'expired', NULL, NULL, NULL, '2026-05-09 08:46:13', '2026-05-09 08:41:13', '2026-05-09 08:41:51'),
(95, 1, 'KMjoqiZ5erPhsJ1t0EpFrtpljfhr1owS8MLhFOs51778344911', 'expired', NULL, NULL, NULL, '2026-05-09 08:46:51', '2026-05-09 08:41:51', '2026-05-09 08:45:00'),
(96, 1, 'Z4Zezern7JKvwSM0WTjVftvCDgVIfsRh9vJKKbIZ1778345100', 'scanned', 1, NULL, NULL, '2026-05-09 08:50:00', '2026-05-09 08:45:00', '2026-05-09 08:45:06'),
(97, 1, 'Sm4tqFfffOH6gfEOfIWqu6e4K0xLyxsoLxNa5L4l1778345123', 'scanned', 1, NULL, NULL, '2026-05-09 08:50:23', '2026-05-09 08:45:23', '2026-05-09 08:45:26'),
(98, 1, '7LLCKNG4k5aeE42Fo8B2ut39mikYVg3HBhz87PHo1778345151', 'expired', NULL, NULL, NULL, '2026-05-09 08:50:51', '2026-05-09 08:45:51', '2026-05-09 08:46:50'),
(99, 1, '8po1xDW4KgN3wxQfqnlaspMbrXbiyvgsmiYOEoX81778345210', 'scanned', 1, NULL, NULL, '2026-05-09 08:51:50', '2026-05-09 08:46:50', '2026-05-09 08:47:20'),
(100, 1, '6Jx4X7SiEoBjOOjh33cy0sS5kJx9lKpXB2GFzJyH1778345267', 'scanned', 1, NULL, NULL, '2026-05-09 08:52:47', '2026-05-09 08:47:47', '2026-05-09 08:48:22'),
(101, 1, '7JGnEs6TMhqIfs0TeUx5Wl8HGOuAPxv4sF0Ve9zr1778345321', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:41', '2026-05-09 08:48:41', '2026-05-09 08:48:44'),
(102, 1, 'KrDqIdJcFcc06uQNyaugiFAWI2HgZyhIadSG2S5p1778345324', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:44', '2026-05-09 08:48:44', '2026-05-09 08:48:46'),
(103, 1, 'xMHeAJhfEM2xWo8NHlyVUWRVYtgUu4gLBHcbR8dy1778345326', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:46', '2026-05-09 08:48:46', '2026-05-09 08:48:48'),
(104, 1, 'eLZKCXKaSMyoGKcNVJUDW5LmVDs2DuwijT23OKZn1778345328', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:48', '2026-05-09 08:48:48', '2026-05-09 08:48:51'),
(105, 1, 'OQ0eQrzd4q9GeTaZg0U3tJ6zT9A2iJndefOGRJKS1778345331', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:51', '2026-05-09 08:48:51', '2026-05-09 08:48:55'),
(106, 1, 'y82IGzSaNQY1dMtQXkBVHW4V6f7ZG8EYJUKYMbCG1778345335', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:55', '2026-05-09 08:48:55', '2026-05-09 08:48:58'),
(107, 1, 'T1xkje643AGllaCncKgBqm0mSmZ5Ggarwbf27VEw1778345338', 'expired', NULL, NULL, NULL, '2026-05-09 08:53:58', '2026-05-09 08:48:58', '2026-05-09 08:49:01'),
(108, 1, 'IjZ1znq1YIRogeu8BYHO7hWoQvNCIPMZhdlLQc1r1778345341', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:01', '2026-05-09 08:49:01', '2026-05-09 08:49:05'),
(109, 1, 'a5YLbmjknhkQsGAbgeEfybPxjasHQY42SdWCsGQQ1778345345', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:05', '2026-05-09 08:49:05', '2026-05-09 08:49:08'),
(110, 1, 'zZd5KiJOwx43gjWEl7nPLMRzHCZQPFgLuJJk9ORT1778345348', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:08', '2026-05-09 08:49:08', '2026-05-09 08:49:12'),
(111, 1, 'U4hcXoH0BaJ2BG2GlGAcGmelDSSNVRLMQWPnLJQw1778345352', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:12', '2026-05-09 08:49:12', '2026-05-09 08:49:16'),
(112, 1, 'pRvX129HwMJv4rKXPzQE8Wu83G8PRjFE794IZPEj1778345356', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:16', '2026-05-09 08:49:16', '2026-05-09 08:49:19'),
(113, 1, 'SgMR2WJNPgO5Ncx0jzE6eEDGrTstD54F3dv95ZNE1778345359', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:19', '2026-05-09 08:49:19', '2026-05-09 08:49:22'),
(114, 1, 't5Zcrsc2Zke3Mvqg8nDfEKBhkg2fR1Q2bHeRMnRd1778345362', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:22', '2026-05-09 08:49:22', '2026-05-09 08:49:27'),
(115, 1, '7rQZudKuT6aHwL871itMbo796tWnb1E5wIlz3kqb1778345367', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:27', '2026-05-09 08:49:27', '2026-05-09 08:49:30'),
(116, 1, '1vcPzzHYmnUYHy3KaUHasIdq6Dtqr41ZRjDSQPiO1778345370', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:30', '2026-05-09 08:49:30', '2026-05-09 08:49:34'),
(117, 1, 'YMlTwp84PtKVDVVO918k8UUCjhvi19fJEAj3TuzJ1778345374', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:34', '2026-05-09 08:49:34', '2026-05-09 08:49:37'),
(118, 1, 'lCxHUNFCwN4D5efEPCWgIJ4fxsGImI5KK2AnSbos1778345377', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:37', '2026-05-09 08:49:37', '2026-05-09 08:49:42'),
(119, 1, 'SG01msalF5EsPfJVQzRHCWHgmAozuLkfGxDnd8y31778345382', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:42', '2026-05-09 08:49:42', '2026-05-09 08:49:45'),
(120, 1, 'p5Kf7d07MSAHyN0jInnIlLzLwZd5YfQspngai6851778345385', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:45', '2026-05-09 08:49:45', '2026-05-09 08:49:50'),
(121, 1, 'drvPjcQSqNPlE3Ln7ZzyNnoRmE3rVOz6ryGBTjPR1778345390', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:50', '2026-05-09 08:49:50', '2026-05-09 08:49:53'),
(122, 1, '57NtoeKS55eN7n00ia3onNDDSJ3QwJPhkvz50xyn1778345393', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:53', '2026-05-09 08:49:53', '2026-05-09 08:49:58'),
(123, 1, '02jMTNncdSTIaoJ2sKxoTeJEujPnYJYwrFocYZ181778345398', 'expired', NULL, NULL, NULL, '2026-05-09 08:54:58', '2026-05-09 08:49:58', '2026-05-09 08:50:01'),
(124, 1, 'auHtDC9omn7IDXEZv2Xg3RyPs7kQOSbvoDMXwHTy1778345401', 'expired', NULL, NULL, NULL, '2026-05-09 08:55:01', '2026-05-09 08:50:01', '2026-05-09 08:50:06'),
(125, 1, 'cw8Bd73YjuRJgyun4LtouKCw9MJO8LWSMLX3fHc61778345406', 'expired', NULL, NULL, NULL, '2026-05-09 08:55:06', '2026-05-09 08:50:06', '2026-05-09 08:51:03'),
(126, 1, 'bZzRYkqF4kTcD3DlGhiSwzGnQ7aTN8NR7qRySDPX1778345463', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:03', '2026-05-09 08:51:03', '2026-05-09 08:51:06'),
(127, 1, 'LFuO1KP0ZiyhrOG4p56c12g6jKvsxFZczIFAtVUW1778345466', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:06', '2026-05-09 08:51:06', '2026-05-09 08:51:10'),
(128, 1, 'BDnlHr8jBuqZXHABXl3yEJVnee87HUgopNdOpj7p1778345470', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:10', '2026-05-09 08:51:10', '2026-05-09 08:51:14'),
(129, 1, 'CvGjdCBeeiRLnarYZk4MNtp0jbUW2zOrgVgb6Byt1778345474', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:14', '2026-05-09 08:51:14', '2026-05-09 08:51:18'),
(130, 1, '50tDRsEu1t8hEjYHpOV1Z9lQPCJwrEbgsXYSPmzh1778345478', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:18', '2026-05-09 08:51:18', '2026-05-09 08:51:22'),
(131, 1, 'PB5txRMGmQ59iX9MUEMJqrHmmgr99TSL2SYdAqeP1778345482', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:22', '2026-05-09 08:51:22', '2026-05-09 08:51:26'),
(132, 1, 'uSjYf46DRwt3WcuyjiHoRxRkE6NrrRpA9ok8Rs3X1778345486', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:26', '2026-05-09 08:51:26', '2026-05-09 08:51:30'),
(133, 1, 'Hg1UbR6Fw7keUVwsufBhj8h1Gr3haqkXFuRTeQ2A1778345490', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:30', '2026-05-09 08:51:30', '2026-05-09 08:51:34'),
(134, 1, 'vPAX2F9Orgpp6W1OY5rjG0RlAJO4Szzfhsuio7na1778345494', 'expired', NULL, NULL, NULL, '2026-05-09 08:56:34', '2026-05-09 08:51:34', '2026-05-09 08:52:05'),
(135, 1, 'Ms0l2RStcsBixcRxpvce7hY3zdQcJ8dEaz33s0161778345525', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:05', '2026-05-09 08:52:05', '2026-05-09 08:52:09'),
(136, 1, 'cnOLP103tUDlf4QWQhcuDzgd2qfG6NzSyNOpcUjb1778345529', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:09', '2026-05-09 08:52:09', '2026-05-09 08:52:11'),
(137, 1, 'gHyq8N2ADj7RqpaFSkhaZhIq4cIWKvd4TMzMLBwf1778345531', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:11', '2026-05-09 08:52:11', '2026-05-09 08:52:15'),
(138, 1, 'CAOPJV8iZyMbJOKDPgSYzYTXvdqne1z45F1Dz29B1778345535', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:15', '2026-05-09 08:52:15', '2026-05-09 08:52:18'),
(139, 1, '3i5OmshnuOq9FCdkWsexXbWwWNisjgeTW20cJWOm1778345538', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:18', '2026-05-09 08:52:18', '2026-05-09 08:52:23'),
(140, 1, 'qV5P8yC1PenkvXirtsFhSheeaJEXuSYsnLSi6CES1778345543', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:23', '2026-05-09 08:52:23', '2026-05-09 08:52:26'),
(141, 1, 'qkbzlVG5SzXrDSfEhJss1ZiYxLuvxpx1o8AluU9Z1778345546', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:26', '2026-05-09 08:52:26', '2026-05-09 08:52:31'),
(142, 1, 'kQWNA38Q2JuEYk9qd8iwZ19StdaZD3sM2kPPCoNC1778345551', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:31', '2026-05-09 08:52:31', '2026-05-09 08:52:34'),
(143, 1, '5YzcmLKa5VDbnWaJsotFxlKkHtGr1dPuDUSLVRKo1778345554', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:34', '2026-05-09 08:52:34', '2026-05-09 08:52:39'),
(144, 1, 'ssWSB5JlI7RfoqcycrbRAaUqvlkeiyjYoa7TMmEl1778345559', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:39', '2026-05-09 08:52:39', '2026-05-09 08:52:42'),
(145, 1, 'griYlPmL2kuM6pftZBbapDJEapgBYFXOrqkdmyQ21778345562', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:42', '2026-05-09 08:52:42', '2026-05-09 08:52:58'),
(146, 1, 'M0XCCDIbiCimkT08GV0ajjUcmxjxdH7mESbUOBqr1778345578', 'expired', NULL, NULL, NULL, '2026-05-09 08:57:58', '2026-05-09 08:52:58', '2026-05-09 08:53:29'),
(147, 1, 'aKWhzplhXtvyM1Ur58wX2kCG52T23lVvwBmLdSyE1778345609', 'expired', NULL, NULL, NULL, '2026-05-09 08:58:29', '2026-05-09 08:53:29', '2026-05-09 09:01:49'),
(148, 1, 'fRD1zA0Ed8AVjWHsFNKVcvWplwNpevnjgXy36lRM1778346109', 'expired', NULL, NULL, NULL, '2026-05-09 09:06:49', '2026-05-09 09:01:49', '2026-05-09 09:13:38'),
(149, 1, 'rtV8mCNP4KtBUKEOZh0kFSgzme8HHkLekFkEZxHz1778346110', 'expired', NULL, NULL, NULL, '2026-05-09 09:06:50', '2026-05-09 09:01:50', '2026-05-09 09:13:38'),
(150, 1, 'zy34tcnt0kP1wzLgyZE1X7k5Y3TDOF2Y1AmoKRCy1778346110', 'expired', NULL, NULL, NULL, '2026-05-09 09:06:50', '2026-05-09 09:01:50', '2026-05-09 09:13:38'),
(151, 1, 'nFNIPiUyg4nrbTFggCiL84DMBlxjat2BEBx7jvs41778346110', 'scanned', 1, NULL, NULL, '2026-05-09 09:06:50', '2026-05-09 09:01:50', '2026-05-09 09:02:10'),
(152, 1, 'slg4s9Z4L59aqTfgv8XkN0HN8ZqhyZI6frCRhinh1778346112', 'expired', NULL, NULL, NULL, '2026-05-09 09:06:52', '2026-05-09 09:01:52', '2026-05-09 09:13:38'),
(153, 1, 'jXoUCGOD1ixYrMJD5LaiOWFKIjbzLHHTyYCB7B9l1778346170', 'scanned', 1, NULL, NULL, '2026-05-09 09:07:50', '2026-05-09 09:02:50', '2026-05-09 09:03:41'),
(154, 1, 'sghtShLWPRa4nYrKvkcCZGtcnE9uSK0JPYB0jUMy1778346270', 'scanned', 1, NULL, NULL, '2026-05-09 09:09:30', '2026-05-09 09:04:30', '2026-05-09 09:05:25'),
(155, 1, 'G6G5SE03FtPHBPivJ3ISYvbH0sGoje6i8Q5dU7jg1778346356', 'scanned', 1, NULL, NULL, '2026-05-09 09:10:56', '2026-05-09 09:05:56', '2026-05-09 09:06:02'),
(156, 1, 'slgxrY2325LUIhbad7L6a4eql2R8w2po03MzyLFZ1778346818', 'scanned', 1, NULL, NULL, '2026-05-09 09:18:38', '2026-05-09 09:13:38', '2026-05-09 09:13:45'),
(157, 1, 'cmwxrvkCcDiIDqCv6eVkIKHqucxFnUVRvSHcmT5H1778346846', 'scanned', 1, NULL, NULL, '2026-05-09 09:19:06', '2026-05-09 09:14:06', '2026-05-09 09:14:20'),
(158, 1, 'cXoiVDhPjWODMyE2h9lNG8ZnBsM2eW5xgU5IMfXH1778346884', 'scanned', 1, NULL, NULL, '2026-05-09 09:19:44', '2026-05-09 09:14:44', '2026-05-09 09:15:18'),
(159, 1, 'WxfqDHdg1u9lOBAjwlG77tKO95q5YZWqzQGoTAZX1778347093', 'expired', NULL, NULL, NULL, '2026-05-09 09:23:13', '2026-05-09 09:18:13', '2026-05-09 09:29:51'),
(160, 1, '7uOAQpyKM3wI0QtXxHOhAVEll6xrWWe6hhEKxxSd1778347791', 'expired', NULL, NULL, NULL, '2026-05-09 09:34:51', '2026-05-09 09:29:51', '2026-05-09 09:35:16'),
(161, 1, 'l6pXYWcpTVDg5pYJsD4rFppMBVY5VgaDp1y2kstQ1778347791', 'scanned', 1, NULL, 'gjlr6LNAm77L42O73u3EYigjqItV6Cr8NOkGuVj0kHp5WrLJbBNSKGYaHy8qDnyP', '2026-05-09 09:34:51', '2026-05-09 09:29:51', '2026-05-09 09:30:16'),
(162, 1, 'qZwrXrUpADWh9vSBl85BCW4XTwPRVBFMONNEkt5Q1778347888', 'scanned', 1, NULL, 'XXnRqmDMOP7IN5W1NFuIQBv7NdFj5HOio05gxlNAVwrlYeMMiBK57kP9qH2q4DdO', '2026-05-09 09:36:28', '2026-05-09 09:31:28', '2026-05-09 09:32:14'),
(163, 1, 'YqKXK28OhWiSJvtMGDEjnnumvjFwmsWuv4QcqYUc1778348116', 'scanned', 1, NULL, '9DN4enqfh0P59107drNqfaDG6jd0ka6zy6eQbE5caXAllyG0KoXFYPUKT3YmYLNf', '2026-05-09 09:40:16', '2026-05-09 09:35:16', '2026-05-09 09:35:29'),
(164, 1, '7dR0wOnBFEkXuS9IN22c7HLMI63NRxTqn4eGjam61778348369', 'scanned', 1, NULL, 'RzGM2VToEpu8wtr3IBU99n9gsBXvPvSeKKzkrsDmjhW5TvP7UfBYLmZHcZ15U2FB', '2026-05-09 09:44:29', '2026-05-09 09:39:29', '2026-05-09 09:39:43'),
(165, 1, 'blIsK1OCI2AH65FjQekhuJGEWdZAz0k4uoaUm4Zp1778348436', 'scanned', 1, NULL, 'w1IBk3jx7zwjqtB96HrRrIHDhsOyLEiRaWHCpepFBMLdxXN6Rft8A6rIYcUsqKLi', '2026-05-09 09:45:36', '2026-05-09 09:40:36', '2026-05-09 09:41:00'),
(166, 1, 'GQXfWE9wj3ltj1auZv0KWEo7fHnzNky4JTfQxwR41778348511', 'scanned', 3, NULL, 'OkyEIdBOupnpBcZa8eIFfSXGUuf4YEqVCLdFB45Naon2AYE7U8FBRLmebguw7xb1', '2026-05-09 09:46:51', '2026-05-09 09:41:51', '2026-05-09 09:42:14'),
(167, 1, 'VyqM3TpFYcwSxXEkihErO9Wh2zLXHubNteCtSAPB1778349618', 'scanned', 1, NULL, '4Jj7rA4P87xlHe9H1N756cYUOIo6zqv6iQfO84FdhD9uJPa2ev0jAzSc4noEoIbF', '2026-05-09 10:05:18', '2026-05-09 10:00:18', '2026-05-09 10:00:34'),
(168, 1, '6IGmDTqjFBk4WR78CEIp5tqgzlIlRFK3O0fkzdpA1778349712', 'scanned', 1, NULL, '5VPbEacqH6IcAG0RQBQai2YzU0XRelQKRtusfISwM3tidbqEeBeC6VxVyeug2jJA', '2026-05-09 10:06:52', '2026-05-09 10:01:52', '2026-05-09 10:02:08'),
(169, 1, 'w9eCcZ72W8q6RoMMe25kKHe3hfjfFvS9sas5pjdE1778350604', 'expired', NULL, NULL, NULL, '2026-05-09 10:21:44', '2026-05-09 10:16:44', '2026-05-09 10:37:03'),
(170, 1, 'x9oHLaIl0khLXzq70jZA81Iso0rdxNqDByqVBRhB1778350621', 'scanned', 1, NULL, '9CbZhNJ1Eb55Ku8TUw0nJy8wKabEPxWGDlE6uFETaCxPQmHfJJFX88zqKSupDgg4', '2026-05-09 10:22:01', '2026-05-09 10:17:01', '2026-05-09 10:17:27'),
(171, 1, '28HnJYAEo4IfHMMla9k32ClUlKUe8F2rPY4O0W3h1778351823', 'scanned', 1, NULL, 'ctuez14Kdo9yuhp3fvUmc7yBRpwZsUKQXKRoWpOT3dYnDuqqFXMSq7onBL9M7eto', '2026-05-09 10:42:03', '2026-05-09 10:37:03', '2026-05-09 10:37:15'),
(172, 1, 'pHKeruzhJDuarMwVWtOY0gIYgbytdCFwN9oRUiSm1778352088', 'scanned', 3, NULL, 'aerViLrMYEG3UHhcHwWvbw4LQMbpRuY2iORz67Z0oI0KNGWrs28JdVrl4bMEyJiN', '2026-05-09 10:46:28', '2026-05-09 10:41:28', '2026-05-09 10:41:49'),
(173, 1, 'uSQdb0YNNBV7qise1vt8C2JlzCm6GAmBgBF8LZms1778353023', 'scanned', 3, NULL, 'Vv5uCIa82xJdvZztfeaSpbeOFuXmwHTEPqXBkUzmyXILyf4RLnPbpaai0aap3Q0B', '2026-05-09 11:02:03', '2026-05-09 10:57:03', '2026-05-09 10:57:37'),
(174, 1, 'gm6uV8rzkBlSCyZsmK7VRzzQNEgXMjpqQqR9w7uT1778353331', 'scanned', 1, NULL, 'mfigUcV3bttFY9xm8J3vYTalwIKrXOPFhtitBfWVF6MFpvj4ZaeL4RScOBEcdOgM', '2026-05-09 11:07:11', '2026-05-09 11:02:11', '2026-05-09 11:02:27'),
(175, 1, 'VYwu3YrWRy9jpzXQ9VlvMWwdIvgkgC4GGZGgjJVY1778353492', 'scanned', 1, NULL, 'eFpkC5Q4ghjpPTmYuIH4x3MqlLbBr2vxjd49c6yNu12o97TZOvD7r6zFc5Bm37YK', '2026-05-09 11:09:52', '2026-05-09 11:04:52', '2026-05-09 11:04:59'),
(176, 1, '0Jud3XqCM9t71LymATFV2tfMcayGW5h3g07HT8EU1778381158', 'expired', NULL, NULL, NULL, '2026-05-09 18:50:58', '2026-05-09 18:45:58', '2026-05-09 18:51:33'),
(177, 1, 'onBZJK9uqlLSFuLMZzEp7T97WGeDxmFqWAo0ErdU1778381205', 'expired', NULL, NULL, NULL, '2026-05-09 18:51:45', '2026-05-09 18:46:45', '2026-05-09 18:52:45'),
(178, 1, 'xgkiP9dsxXMRdebxOYrRD1eSa3OkonqklqbxUFmb1778381254', 'scanned', 1, NULL, 'p1ZPV6GVlkyFBknykuyCu8laQTS91jMhmpFrXgtMr3YNwbjY8KUKzKt6cg8PpS0H', '2026-05-09 18:52:34', '2026-05-09 18:47:34', '2026-05-09 18:48:12'),
(179, 1, 'G0SJvTtmBYh33HljLuZ38mvdx99Wx60FuWCcuHcR1778381442', 'scanned', 1, NULL, 'yi1bGsAFKpfeEzRwuyAKgOwlRi4vtZ7eIkhw6sZYR7gbZ6ndlYlkWwbGPbZVJeoR', '2026-05-09 18:55:42', '2026-05-09 18:50:42', '2026-05-09 18:50:47'),
(180, 1, '2VP6NSbrgPUrOZB3QWOTTHBDRC56OjR7R47Y11S71778381493', 'expired', NULL, NULL, NULL, '2026-05-09 18:56:33', '2026-05-09 18:51:33', '2026-05-09 18:57:29'),
(181, 1, 'uJbZ2UhVk7hEEkc7530L7BBxBHsfEWG6MfL0LOo21778381565', 'scanned', 4, NULL, '7jgzMUMhuxQ8V5HWZBS9bEUpgGzZsQdYsbWUVeIxHo9D0EQd48yp8bYqISf0y2kV', '2026-05-09 18:57:45', '2026-05-09 18:52:45', '2026-05-09 18:52:54'),
(182, 1, '4FiIlE9i7BMXzeEfGemd5oWCVlEU8Inw5a44ZU781778381849', 'scanned', 4, NULL, 'KR1r8UdCusFXDkR6ftNXOMr2NtUTMxawRKBWF1S1Om9W53Tg4TmfpywCfgLYfejZ', '2026-05-09 19:02:29', '2026-05-09 18:57:29', '2026-05-09 18:57:34'),
(183, 1, 'TTlLTaSUfxfwPrciLqA1b37NN6OCykcBDRKmutkF1778381897', 'scanned', 4, NULL, '6UMXXFy7P48kd1QqA2tzBTWRzVorGQzgIahcRNRPJelAkGoWKwAEVpFeBH6FFKxD', '2026-05-09 19:03:17', '2026-05-09 18:58:17', '2026-05-09 18:58:25'),
(184, 1, 'WtgxFaggagFCdJNcxYvcVQuuNNq9q7DOgl259cti1778382011', 'scanned', 1, NULL, 'jJNtKicNgwA1RJTcEiqcVf4uYIOivviAAp8kvosGki8EnXJOTaKTMwPD5u6QZIq4', '2026-05-09 19:05:11', '2026-05-09 19:00:11', '2026-05-09 19:00:37'),
(185, 1, 'qT2Ek96I3cJcP4ZxwPoCX8cP1tOtBapQAthrFqri1778382246', 'scanned', 1, NULL, 'xefzFRcCcrd2AKomWY1059hWRLDH4MKYkl36pnuhUgyL0ksx2fD6mPZNgFQDMLEE', '2026-05-09 19:09:06', '2026-05-09 19:04:06', '2026-05-09 19:04:12'),
(186, 1, '07pcPFVdBytjUo1e3ApHO9XYSJa0nEu3HEgs6BLQ1778382474', 'scanned', 4, NULL, 'zFczCzIQIPzK0yB8aqTSbROALhRz4Rsm2SeAtA9k6lKhK8kXurn7iFgIug31BHrA', '2026-05-09 19:12:54', '2026-05-09 19:07:54', '2026-05-09 19:08:22'),
(187, 1, 'o7VV4n8sUrajdQLrQhV2GYAXyk1PAcshqGJHpfTE1778383539', 'scanned', 3, NULL, 'HQ6KnzMf7qx87y9uXl0ypYYg1GiNBBHylRo0nDVZ6BHibeiygajotce9MCdZLSd7', '2026-05-09 19:30:39', '2026-05-09 19:25:39', '2026-05-09 19:27:24'),
(188, 1, 'REi7mszG7PUBVemMAlNF9Vo2L8hSm9FeMRuxiZZh1778384374', 'scanned', 1, NULL, 'OAF3ekCsCuAbK2bZHhcOIdAUvSYWjESffrX4IMrR2lqBpFgpoPri4nn29RHI1stb', '2026-05-09 19:44:34', '2026-05-09 19:39:34', '2026-05-09 19:39:46'),
(189, 1, 'JsEXu8fFQ0dLgW4BKioWmxui3iyMfbd1mHuPNgAv1778384456', 'expired', NULL, NULL, NULL, '2026-05-09 19:45:56', '2026-05-09 19:40:56', '2026-05-09 19:46:38'),
(190, 1, 'SeSlxkC9ojfIOcWdZPWlwxobsScHaWcnH4e4QP1I1778384473', 'expired', NULL, NULL, NULL, '2026-05-09 19:46:13', '2026-05-09 19:41:13', '2026-05-09 19:46:38'),
(191, 1, 'i21CSkjykbWlCQ2KHteq95WBWwr1xJiCykx80ht21778384477', 'scanned', 4, NULL, '89xO9gKS0nQsj8hIMxd3HLH0L0ZG1eV0rZxpAgUhwo34XzOQLcYhKrUU8QN7ktX2', '2026-05-09 19:46:17', '2026-05-09 19:41:17', '2026-05-09 19:41:40'),
(192, 1, 'Z0hb6KXK4AxWJXfteFBUy9TG4Hr8G05dwOuaK5211778384574', 'scanned', 3, NULL, 'M2A3cAA2a1k1CgsvKI23vaRgCNBY76hTUzKkj1Lm2GXnMzQSYzzAR5FzlYh7fOAz', '2026-05-09 19:47:54', '2026-05-09 19:42:54', '2026-05-09 19:43:00'),
(193, 1, 'SGioikRE5XLkSuhsLQhHqcFYDHIpvVXYsEvfF3Db1778384606', 'scanned', 3, NULL, 'U8wlsafRuWXLtmC8jleXy20wwQaAqdzJrZwwkmtTwzoIQTwAvaeEJ42RDkhFyFu1', '2026-05-09 19:48:26', '2026-05-09 19:43:26', '2026-05-09 19:43:42'),
(194, 1, 'fAMiONJG0g0iSx2oLkn0rkZCp2AIyLKSZU8RKKHk1778384670', 'scanned', 3, NULL, 'YGu1gcfWb1CJBpfskxDqRCwfWyqouV6E1MbYpj1bAssN2KixuGI5DmM8EhgvQbY5', '2026-05-09 19:49:30', '2026-05-09 19:44:30', '2026-05-09 19:44:36'),
(195, 1, 'E43BUqiBf5dHWWxNOs1sOgjNfYDfGtX6q6KTiJYD1778384798', 'scanned', 3, NULL, 'nfaqQHW6jLpKp0sqvIBoWD5WjNiUjMxBwelJKbXHbXcr806TI1Ylv6XNpgD7U2L3', '2026-05-09 19:51:38', '2026-05-09 19:46:38', '2026-05-09 19:46:50'),
(196, 1, 'Lv44mJ6WpGFUMnoyo6f27RT6hlD0vbwAI35pqIm81778384908', 'scanned', 3, NULL, 'qS8TyMo5Csww8XU8KSbvXFJbgVsGchrS3QjUz3yb0PEsmdjceLSKiNzi1NTr45Ax', '2026-05-09 19:53:28', '2026-05-09 19:48:28', '2026-05-09 19:48:52'),
(197, 1, 'wWakmanASDgXRmCMP8rWGyrF23oGwRmawIldx84V1778384986', 'scanned', 3, NULL, 'g6kiiF4Lt9J51WY2nsI77uvpcj72evQEd4EHtuwxMhJAvmeVjuweED86zJRZmnQf', '2026-05-09 19:54:46', '2026-05-09 19:49:46', '2026-05-09 19:50:01'),
(198, 1, 'bJXuqoQr0JyweME8b2wLgSVZO948tXz9a3trhIs71778385965', 'scanned', 3, NULL, 'MccqILiL5O7KNnGEOxLTyz6tA4IVU5vyeaUx0u01moZvKwOOfgjvASCm04IT4q2u', '2026-05-09 20:11:05', '2026-05-09 20:06:05', '2026-05-09 20:06:19'),
(199, 1, 'BmZzeDJwgQynUkswMXwFYspdTSpWnYD0kcz57H0X1778386014', 'expired', NULL, NULL, NULL, '2026-05-09 20:11:54', '2026-05-09 20:06:54', '2026-05-09 20:18:37'),
(200, 1, 'T4nHcDJIAt1g5OQ9MVvSreChp3kGfLeD539ZFeEW1778386020', 'expired', NULL, NULL, NULL, '2026-05-09 20:12:00', '2026-05-09 20:07:00', '2026-05-09 20:18:37'),
(201, 1, 'zV7LX8PNZwKTOMloO1hhJKa5yZ5cYCppkG7089Iv1778386027', 'scanned', 3, NULL, 'TX241nuZFVZ1OALiwpqXS1lQPjUe0umemXjbRbo83EJfb0p0uukwRMIp3VDNvrVX', '2026-05-09 20:12:07', '2026-05-09 20:07:07', '2026-05-09 20:07:48'),
(202, 1, 'xTD8p17h77tqkRizlWQmtdb8Pr9UwR5hmg3YMfY61778386717', 'scanned', 3, NULL, 'yjP4FWpNe74SFqq4xfacMR4hsOxZDP75lrfhssI4zcFrldEBRfYKRXiOPrbUjewc', '2026-05-09 20:23:37', '2026-05-09 20:18:37', '2026-05-09 20:18:54'),
(203, 1, '00hgtpNmGuXkXn08Rl78VgUY3qC1B9TxOpgZHdli1778386787', 'scanned', 3, NULL, '4ZfZJ7Qst2x2A81QtdpVYyuXPpnNmh0yZ7fl0TfIQczp6eKsojLZ2KKOVOn1wsyv', '2026-05-09 20:24:47', '2026-05-09 20:19:47', '2026-05-09 20:20:51'),
(204, 1, 'cpZm4KK26xl20O7WG3FEXJB6PSs62VeimTcSFqvu1778386955', 'expired', NULL, NULL, NULL, '2026-05-09 20:27:35', '2026-05-09 20:22:35', '2026-05-09 20:37:51'),
(205, 1, 'rh5RGSPFBETorNrbiCJIZHnsQNrofunZMRAbnPoe1778386969', 'scanned', 3, NULL, 'OQLE2JCTI8gpSaCUv5Ii6EA8to4dP3ktW7e8K3lo8pJSATXNqg6RyESCWUEi0dYW', '2026-05-09 20:27:49', '2026-05-09 20:22:49', '2026-05-09 20:23:11'),
(206, 1, 'eZpGicLcFxzlqbZXpXGAVTcnPPJUk9y6au8HwVKd1778387029', 'scanned', 3, NULL, 'GQAKrCEskjf6jnSfJ2QL4iuAkiNnPKBpdWiZ2mzyDijXG0U252c6drfVYeEphpFR', '2026-05-09 20:28:49', '2026-05-09 20:23:49', '2026-05-09 20:24:56'),
(207, 1, 'ZRivciSSXI6P9Nv0pJo0QsLZcnqkeyWytChm2ghw1778387871', 'scanned', 3, NULL, 'NrQzXH6Z043z4cZih9vWFC1MfS9CwvOuEz1IN9bFPeGLOYzCAhqpCP0No635Qhsu', '2026-05-09 20:42:51', '2026-05-09 20:37:51', '2026-05-09 20:38:21'),
(208, 1, 'pLKBPwF0AHVVmuyITErNZDTvnMDKMgHfoSxdn8Ho1778387998', 'scanned', 3, NULL, 'rcn2gkAUB7wRw7cnekJqUY9nHn8EMHFtdzmj6M43AJRrb6p2J4P3PSP4ZUAjMyin', '2026-05-09 20:44:58', '2026-05-09 20:39:58', '2026-05-09 20:40:04'),
(209, 1, 'BVmGNlLpUVbdBpRgijoYKQk5aBakN9Pd5X2Zk6fn1778388047', 'expired', NULL, NULL, NULL, '2026-05-09 20:45:47', '2026-05-09 20:40:47', '2026-05-09 20:51:04'),
(210, 1, 'dN49el3T95SxvalIeM1mTVNBrvIAdcug955r0Jgr1778388066', 'scanned', 3, NULL, '2LpcWiIV7Mp2a7F3ycF8p5lx4Eyzx2E46CuUhVq91juB4lodulcjIxq7bmBZEU9l', '2026-05-09 20:46:06', '2026-05-09 20:41:06', '2026-05-09 20:41:29'),
(211, 1, 'S8EYaep6gIac4kWSAlTGsakvdsrguA9qLUGjqxeM1778388128', 'scanned', 3, NULL, 'ELA2K5N5duZKwx2hTHs4xP1VOYI6H2yhYYembxEEbb0x19yd8hjkkNDY3ZiJx7Ih', '2026-05-09 20:47:08', '2026-05-09 20:42:08', '2026-05-09 20:42:22'),
(212, 1, 'pvKXugGNCqgarR4tLTT2jUz1qwOrn7usZq9CQDJt1778388664', 'scanned', 3, NULL, 'aU8d08ZlbfST42E9S9ViJ49uCgyGmpO7ahfOLx917czbkiPnFCAyU1zoMRWtmaAa', '2026-05-09 20:56:04', '2026-05-09 20:51:04', '2026-05-09 20:51:29'),
(213, 1, 'vd83yEbqDdEfP6g1FVmMIdjbpG6M8CJMoVmgsIij1778388749', 'expired', NULL, NULL, NULL, '2026-05-09 20:57:29', '2026-05-09 20:52:29', '2026-05-09 21:12:07'),
(214, 1, 'GmxgVtf6iwSeYJCw0h2EoaGxigV5MZp1AZY8zaQW1778389030', 'scanned', 1, NULL, 'TbbWiDwnvQvtLg5UShaJsYMMFkg5QaqukpThmOU3lZ5xRn67haJNOWIGrD4l6EvA', '2026-05-09 21:02:10', '2026-05-09 20:57:10', '2026-05-09 20:57:24'),
(215, 1, 'J4ogOoCsaXbUO75FxCHCmsx4TWPGtEwGlEGoAqwW1778389927', 'scanned', 3, NULL, 'TDsANjKGEZNr7tLZiSBsuetKCjanTYYqByhQaHlFeTiMFdOFOpmG5HQdgTtI2ABY', '2026-05-09 21:17:07', '2026-05-09 21:12:07', '2026-05-09 21:12:24'),
(216, 1, 'G6iCc4C9hetcpmpLhk93AEdEyyVHFHzGqE27omlO1778390817', 'scanned', 3, NULL, '3XKnXF38lT630x2NK82GWigdSnwLVtwy6Kp3iLd5grniAWL1pKTn4slKPgtX2Wcn', '2026-05-09 21:31:57', '2026-05-09 21:26:57', '2026-05-09 21:27:11'),
(217, 1, 'TD3hEFJ0gsBXCf24NvQ2GlAskVCGwlqtw5JNSrNm1778392092', 'expired', NULL, NULL, NULL, '2026-05-09 21:53:12', '2026-05-09 21:48:12', '2026-05-09 21:56:44'),
(218, 1, 'RxySUsg6aQStcd3lWWIkLRaaGSFeAgs5FCbMR7tr1778392103', 'scanned', 3, NULL, 'mbP3OGMGRuXIW7bOYyZsqabxYzR25KilPppHKdnDG7kFhjGodlM7esrM2NTYprpG', '2026-05-09 21:53:23', '2026-05-09 21:48:23', '2026-05-09 21:48:39'),
(219, 1, 'ajUUUceEUkuRYnZVY8gARoj6hFmPCjjx9I4EONcp1778392220', 'scanned', 3, NULL, 'BpDIeqfgj0HXfP9Lz9YiKsQy6YYhjRl1daKguqgtNSQW1xhX5HhiBmHXX567lbUT', '2026-05-09 21:55:20', '2026-05-09 21:50:20', '2026-05-09 21:50:51'),
(220, 1, 'Pr5a1VayKaRGL5EQv7Hjch3Db5W9uipjFZ8FmHWR1778392300', 'scanned', 3, NULL, '2dsVjlDi2KKqJAfi4YUhxNIlfFG9b0X4Fsoh1gYOAxERRTLaO3ucBmkdEfjbCUEk', '2026-05-09 21:56:40', '2026-05-09 21:51:40', '2026-05-09 21:52:05'),
(221, 1, 'i11pfTqXbt9ktG6r7Xj76b1EWNG0fa8kNtKEr4Zc1778392604', 'expired', NULL, NULL, NULL, '2026-05-09 22:01:44', '2026-05-09 21:56:44', '2026-05-10 07:40:32'),
(222, 1, 'ydqwRpf13eIQG7iUWAVt97IEWwRlsDokgPsrf4uv1778392611', 'expired', NULL, NULL, NULL, '2026-05-09 22:01:51', '2026-05-09 21:56:51', '2026-05-10 07:40:32'),
(223, 1, '6EwUT79lirchTPSN8wvx6DAprpJhyUSm9HMhP0HR1778392612', 'expired', NULL, NULL, NULL, '2026-05-09 22:01:52', '2026-05-09 21:56:52', '2026-05-10 07:40:32'),
(224, 1, '10GgqGcLGFgEoLB8gfv6U1fxXzzmisOpBsD44BU21778392635', 'expired', NULL, NULL, NULL, '2026-05-09 22:02:15', '2026-05-09 21:57:15', '2026-05-10 07:40:32'),
(225, 1, 'GvpMlxFetN2D0gizOhA3MUjeBdIiiusyyPfZyTIt1778392739', 'expired', NULL, NULL, NULL, '2026-05-09 22:03:59', '2026-05-09 21:58:59', '2026-05-10 07:40:32'),
(226, 1, 'dnBiVJvD9pun88SKouwHvBFzePyDMoQ6lL85S9eq1778392846', 'scanned', 1, NULL, 'l9Z4iBVO3Kz61SYz0rsKyTHxRACFOje3W9AFbb3oEJxOoxjilJn99FFyPfa56TrI', '2026-05-09 22:05:46', '2026-05-09 22:00:46', '2026-05-09 22:01:07'),
(227, 1, 's4bXhYTnWPjNyJl83Qp4Bjh0CPMFko6xklrKcgjD1778398832', 'scanned', 1, NULL, 's98ZrfbD3E1pqfJ71GwmUrlMLcnWn1Vt2UUgX7rV3MZkIHNkze7RJQoH8mjQkKF8', '2026-05-10 07:45:32', '2026-05-10 07:40:32', '2026-05-10 07:40:45'),
(228, 1, 'ju3jp9ZDqtIpYIwbU3f3W0RCxdhLzuIgIq8s1do71778399004', 'scanned', 1, NULL, 'uFmMom4E0yHPJz1FqRQzKikw4P5oh6MvRG8FT1JEyQInQqXmCwVW9S6xFEiyKFYv', '2026-05-10 07:48:24', '2026-05-10 07:43:24', '2026-05-10 07:43:53'),
(229, 1, 'qxRpL6sj4tvn7STpfIGghZrbqTGFQAYXC9ONUAC41778399211', 'expired', NULL, NULL, NULL, '2026-05-10 07:51:51', '2026-05-10 07:46:51', '2026-05-10 09:02:36'),
(230, 1, 'BTHb0gBrQHhRx4cM1ReKnoDbvk15FJU99sxh3YdX1778399218', 'expired', NULL, NULL, NULL, '2026-05-10 07:51:58', '2026-05-10 07:46:58', '2026-05-10 09:02:36'),
(231, 1, 'N0VygKtzDkKQtZo5Hro1bHfQF31e8RHKN2KUiizW1778399263', 'scanned', 1, NULL, '9fpSqThvIkVFdW6tjJ1HHe7aiGixR688Wb3s5gPcnF9cUWmuEoq3a2dtweCn82eW', '2026-05-10 07:52:43', '2026-05-10 07:47:43', '2026-05-10 07:47:59'),
(232, 1, 'WrXJAqAooPySlZhsU6iIpYTjTsl8Vo1BjEn69iFK1778403756', 'scanned', 1, NULL, '6t8bIoi51vcLOXFgIjGCLYKhIwre63BRXkeiGEDEl0ranGcBBmVxhWlsXnznCr0j', '2026-05-10 09:07:36', '2026-05-10 09:02:36', '2026-05-10 09:03:05'),
(233, 1, 'po2VF8AhKwzPxkCgZwRWwf94Okf2CmGvYYePdLSW1778422014', 'scanned', 1, NULL, 'Yl2JlqH6pq6qzJabST8lRAMhEuv6du8ERuYy9Q1EtzXyT0TgUEQbuLvUWzLhRatY', '2026-05-10 14:11:54', '2026-05-10 14:06:54', '2026-05-10 14:08:09'),
(234, 1, 'j9E04RX8yCDyb31VcSTg4caILaIoDtAStqm4LuS81778430012', 'scanned', 4, NULL, 'KShTKE5Hipy3TeNFmm05Ws15HykXv2GXDWDFRZawUoRmmrNnZBl1rAvFhQuUP77i', '2026-05-10 16:25:12', '2026-05-10 16:20:12', '2026-05-10 16:20:41'),
(235, 1, 'Rzx9a2c6qbgwswk5AvbyIr2DrWWyyHzlZxkWogdj1778430266', 'scanned', 4, NULL, 'Z2sotb04rPbAwoiRrISa0Vmo79K8nI4JtjxNDT1kHBE6xDVmJ8ZUjHunXmCoeTgj', '2026-05-10 16:29:26', '2026-05-10 16:24:26', '2026-05-10 16:24:35'),
(236, 1, 'EdQaI8G9F2XP8XptkmMo9XZJrfwcZ8xCRsa6ZtzZ1778430333', 'scanned', 4, NULL, '7SRjd3w875hjjgtp8ZrNmpQax16dqAIWiHeBolGbTJyhanS7mF7gFX6ixK2bMyjt', '2026-05-10 16:30:33', '2026-05-10 16:25:33', '2026-05-10 16:25:41'),
(237, 1, 'lovFWPUBdt8nbh0umtqxNdSAKB3ZwTPGXsk57txL1778430433', 'scanned', 4, NULL, 'opcL7XToDILLgt7pGcM8K0nqUAYTQuwMey2OIvPGDypXMMUGoSpQM2zYCW1eT9Cv', '2026-05-10 16:32:13', '2026-05-10 16:27:13', '2026-05-10 16:27:21'),
(238, 1, 'u4hdFjw4D6DogEDa6WqsLzALBOD21qrFoPoGEU0Q1778430626', 'scanned', 4, NULL, 'T7lid1BLu9XqPY8bi3rTGpSdr8oQ4cwMWeMP5UUTT8cDNhCY5CZGeXaiaasLQpGa', '2026-05-10 16:35:26', '2026-05-10 16:30:26', '2026-05-10 16:30:33'),
(239, 1, 'N84PJjMFIttgvq8w1iY5gVscunpfPkFfCqJ1tB1x1778430683', 'expired', NULL, NULL, NULL, '2026-05-10 16:36:23', '2026-05-10 16:31:23', '2026-05-10 16:37:14'),
(240, 1, 'Z3XcVF9F9lfgGqUIOKvpwaSDTsNJIn3mLE45bkgL1778430721', 'scanned', 4, NULL, 'HQG7p1JxDWSUD0ImX5jojmyrPl5LE2FylHgM3QE6SUT27ZbyPId1EVUgfPhwWwp7', '2026-05-10 16:37:01', '2026-05-10 16:32:01', '2026-05-10 16:36:39'),
(241, 1, 'p9PbroiZhR6dQ80b1ensKrPYXyttfYxDKb6Zx8EO1778431034', 'scanned', 4, NULL, 'nwUnlQavSzZaYFQfbicW4R49SGPsV1WfgDTie0FgHBKhL4bJfwZ55x5ZhU4WQ8bI', '2026-05-10 16:42:14', '2026-05-10 16:37:14', '2026-05-10 16:37:28'),
(242, 1, 'hL91Tn8o5HHEawiaLkgHwsbHvhZMk9WiIa7nzrNL1778492078', 'scanned', 1, NULL, 'ETMeQlwVI1yMcyKQNbzf2UA3rb2mgv0GOfRkbzKBIMleAlQnmdFTKoUhYOtyVjCK', '2026-05-11 09:39:38', '2026-05-11 09:34:38', '2026-05-11 09:35:33'),
(243, 1, 'xwZ56iMkOAmNvQi3SEUlssq4Ve43AB1CE08cEoKa1778492767', 'scanned', 6, NULL, 'ct9x4n79Md2v4ST1R5JBYX5RNq3CscJ8wv7ky6wRDqz9FDFZ5TtgNZPBZOkRXLjH', '2026-05-11 09:51:07', '2026-05-11 09:46:07', '2026-05-11 09:49:51'),
(244, 1, 'oLSMo6xBQyMvV08tqBXDSIaGqdazXlgTpLYvbXvR1778493428', 'expired', NULL, NULL, NULL, '2026-05-11 10:02:08', '2026-05-11 09:57:08', '2026-05-11 10:08:49'),
(245, 1, '1UuZAniY2NZg6B3HiLKKlUKqeQaS6mabOjRvPm3y1778493705', 'expired', NULL, NULL, NULL, '2026-05-11 10:06:45', '2026-05-11 10:01:45', '2026-05-11 10:08:49'),
(246, 1, 'EcxFUTljXI3dVq7RteNKmjWPoQJPAyY2zPh4xVlY1778494129', 'scanned', 3, NULL, 'ElqyTZ6Bdl94hZylWdscORwemFeuKYFAPcnM73sSZD85s5odH3V4yqJjeYUW8Ddu', '2026-05-11 10:13:49', '2026-05-11 10:08:49', '2026-05-11 10:10:30'),
(247, 1, 'KghhOtGUjDP6C2C4EFDoItbrfWUewteQwZ5nN9c91778512288', 'scanned', 6, NULL, 'twgari7LdLh1iIMMwHqOzWb0YDgptFtHlwgdY2XUkNxVojXFyaBgzirsPQiCO3lv', '2026-05-11 15:16:28', '2026-05-11 15:11:28', '2026-05-11 15:15:00'),
(248, 1, 'SjiiXkIiTR86NOABytS5bz3DiMJxLj0tOjHB6jmf1778512543', 'expired', NULL, NULL, NULL, '2026-05-11 15:20:43', '2026-05-11 15:15:43', '2026-05-11 15:20:43'),
(249, 1, 'OUMReCXiLMFF3jtF72BTlZMycXQDjI5vs8BbkPmV1778512846', 'expired', NULL, NULL, NULL, '2026-05-11 15:25:46', '2026-05-11 15:20:46', '2026-05-11 15:32:14'),
(250, 1, 'LdfGxTdw5cWHUumMUuM2G4hCnB6vDhsIx913JkON1778513534', 'scanned', 6, NULL, 'xbwAYmA1ZaERaFUPkNKk27NQph44DSxeA6k2xD477kEYHk6ClmgQIFO5YfxGIgey', '2026-05-11 15:37:14', '2026-05-11 15:32:14', '2026-05-11 15:33:04'),
(251, 1, 'SOtPVRp9FiQGrpK4MEIRSYRCTcwhm1vpBR2mLd4C1778513692', 'scanned', 1, NULL, 'B7j5hWAluspM0J31BwDME1ust2U92Oju5rmuk4OHrtJYHaMh0IAIeWvvQsjlzIvX', '2026-05-11 15:39:52', '2026-05-11 15:34:52', '2026-05-11 15:35:17'),
(252, 3, '4GRx90EMFxgyq1GViS2xWt23Sd6bmg0pySRscbJz1778514027', 'expired', NULL, NULL, NULL, '2026-05-11 15:45:27', '2026-05-11 15:40:27', '2026-05-11 17:15:14'),
(253, 3, 'T4mcA7bKxIJjskGHI2JV59G1tUlmMiki4oiwOLQB1778514037', 'scanned', 6, NULL, 'jjM8vHl5Jkd4iSkZRrokbxNYQi7F2NJQJOXqt9D5SQsUJUosx0cwztiS1jKi8lgY', '2026-05-11 15:45:37', '2026-05-11 15:40:37', '2026-05-11 15:41:03'),
(254, 1, 'K3WIBW3G', 'scanned', 6, NULL, 'Wnfo53M2ojr9D0cE9gJHerYBfqfEqFAb5msJa72bnKdmM1rBr3YAhkUKeCvzwp4E', '2026-05-11 15:49:06', '2026-05-11 15:44:06', '2026-05-11 15:45:12'),
(255, 1, 'ORKRVCCB', 'expired', NULL, NULL, NULL, '2026-05-11 15:49:46', '2026-05-11 15:44:46', '2026-05-11 15:55:12'),
(256, 1, 'KECEWXBF', 'expired', NULL, NULL, NULL, '2026-05-11 15:51:03', '2026-05-11 15:46:03', '2026-05-11 15:55:12'),
(257, 1, 'NCM2GKO2', 'scanned', 6, NULL, 'Cw34rOCutDqBeEPSiuPDE06FCPzXb8V2xXSW7tZj0f3qUTduooVsiG6MAMBu8LJR', '2026-05-11 15:52:39', '2026-05-11 15:47:39', '2026-05-11 15:48:18'),
(258, 1, 'KGWG6FDD', 'scanned', 6, NULL, 'OWK9ugtbmgznKTSGAvqznLStRzRo8H3Rn4ZPDhlO7kwMOraHbByFkNESxCTGRwaQ', '2026-05-11 15:54:05', '2026-05-11 15:49:05', '2026-05-11 15:49:33'),
(259, 1, '9VOYU9YG', 'scanned', 1, NULL, 'pHKGTrJASbXa463QVo7v6lFaJzbg8yYIvHVrpo98jszxKVgzKdaDgL7kW4wMPreJ', '2026-05-11 16:00:12', '2026-05-11 15:55:12', '2026-05-11 15:57:35'),
(260, 1, '53JVJI52', 'expired', NULL, NULL, NULL, '2026-05-11 16:04:16', '2026-05-11 15:59:16', '2026-05-11 16:04:25'),
(261, 1, 'QZ1MIX7D', 'scanned', 6, NULL, 'Hvc8lTA0gm5BqwtSyEjagzDBGLpgZHeNVEAuAa4iYi76yIYTKbWTjLF4gGdUWjRj', '2026-05-11 16:08:07', '2026-05-11 16:03:07', '2026-05-11 16:03:18'),
(262, 1, 'L0YMLJ25', 'scanned', 6, NULL, '3Mr1YliAWqCqdjzVgEPhiZFKLqxbyQM6S2LZHslSXtUGIgRKMCRfFbMDOaJcgG6n', '2026-05-11 16:08:40', '2026-05-11 16:03:40', '2026-05-11 16:03:58'),
(263, 1, 'WOPNQPJO', 'scanned', 8, NULL, 'CHCOMYGKHQF1u7CMrlxg7EbWK9sTwW0OtI3eACcGLd0yEZPDxDjijGcoygjI38AN', '2026-05-11 16:09:25', '2026-05-11 16:04:25', '2026-05-11 16:05:52'),
(264, 1, 'SNGIWD6M', 'scanned', 1, NULL, '7JfUV6DrNVNHVVKznhpNjJp3vq3y6f4zNOTTb1cOhHaTkarXjqmg8jMw4r8MSHp7', '2026-05-11 16:12:54', '2026-05-11 16:07:54', '2026-05-11 16:08:07'),
(265, 1, 'WMYCFQII', 'expired', NULL, NULL, NULL, '2026-05-11 16:13:25', '2026-05-11 16:08:25', '2026-05-11 16:14:06'),
(266, 1, 'ORAGN', 'expired', NULL, NULL, NULL, '2026-05-11 16:16:06', '2026-05-11 16:11:06', '2026-05-11 16:24:41'),
(267, 1, 'HBWV5', 'expired', NULL, NULL, NULL, '2026-05-11 16:16:17', '2026-05-11 16:11:17', '2026-05-11 16:24:41'),
(268, 1, 'BHMDO', 'expired', NULL, NULL, NULL, '2026-05-11 16:16:22', '2026-05-11 16:11:22', '2026-05-11 16:24:41'),
(269, 1, 'XDUY', 'scanned', 8, NULL, '0IFjb30db0r5RHllXrmcLDhDfYSvPiKoL4jsFqvg0YhyYQghNzdtsReTXxqDRXWG', '2026-05-11 16:17:23', '2026-05-11 16:12:23', '2026-05-11 16:13:02'),
(270, 1, 'EN83', 'scanned', 8, NULL, 'kiCTBaqOeYP0Tc7qprk2BMOCqggAV3LkOpzzmTkgVvUYJGqKImqOxCdVLzJ453wY', '2026-05-11 16:19:06', '2026-05-11 16:14:06', '2026-05-11 16:14:18'),
(271, 1, 'XN05', 'scanned', 8, NULL, '1ATySYygVzjv2b8FocKYdIxuX0yiLrcmXrbPrfGmMD9hTcqmTV5AXz0EtIb8Rq6V', '2026-05-11 16:19:36', '2026-05-11 16:14:36', '2026-05-11 16:14:44'),
(272, 1, 'M17J', 'expired', NULL, NULL, NULL, '2026-05-11 16:29:41', '2026-05-11 16:24:41', '2026-05-11 16:31:17'),
(273, 1, '9PW7', 'expired', NULL, NULL, NULL, '2026-05-11 16:36:17', '2026-05-11 16:31:17', '2026-05-11 16:43:22'),
(274, 1, 'YOUV', 'expired', NULL, NULL, NULL, '2026-05-11 16:37:59', '2026-05-11 16:32:59', '2026-05-11 16:43:20'),
(275, 1, 'IEIR', 'scanned', 1, NULL, 'ifjQ0L7EHpKXts1KWNrr5Gd9vvX5jzTlNFv9lfepc1BWL5CNgtj1ebnz4J16wwPm', '2026-05-11 16:48:22', '2026-05-11 16:43:22', '2026-05-11 16:43:36'),
(276, 1, 'BGBZ', 'scanned', 1, NULL, '2XcBYQlNlJmytDhy6WvvlmOIRpIhb7TW0de4hpTjXf0rx9ylKYBIotvVgBpEn1AN', '2026-05-11 16:54:48', '2026-05-11 16:49:48', '2026-05-11 16:50:09'),
(277, 1, 'NNR1', 'scanned', 1, NULL, '9dFssYaCFtHoTBdeKaHb5DnSWySykYspMINqJh9jOZAjEiTy0FwbfLvXUbIPrR8T', '2026-05-11 16:57:34', '2026-05-11 16:52:34', '2026-05-11 16:53:15'),
(278, 1, 'FEVT', 'scanned', 1, NULL, 'pOT4Y65rIrjxRInRaU2423rRfj5MebhOMG5OYpvjw7nNV0ROVD69XHS8RLgFg0cj', '2026-05-11 16:59:29', '2026-05-11 16:54:29', '2026-05-11 16:54:47'),
(279, 1, 'IK1C', 'scanned', 1, NULL, 'SmdlkNJtfJIP9sCP4UA0WYVvRkCz9NR19ifcAomDKZSzs7OxRayfQbQ0fSAnwy6N', '2026-05-11 17:01:53', '2026-05-11 16:56:53', '2026-05-11 16:57:04'),
(280, 1, '9BWW', 'expired', NULL, NULL, NULL, '2026-05-11 17:05:22', '2026-05-11 17:00:22', '2026-05-11 17:08:04'),
(281, 1, 'TYYO', 'expired', NULL, NULL, NULL, '2026-05-11 17:13:04', '2026-05-11 17:08:04', '2026-05-11 17:15:27'),
(282, 3, '6DE0', 'expired', NULL, NULL, NULL, '2026-05-11 17:20:14', '2026-05-11 17:15:14', '2026-05-11 17:20:18'),
(283, 1, 'KWA7', 'expired', NULL, NULL, NULL, '2026-05-11 17:20:27', '2026-05-11 17:15:27', '2026-05-11 17:23:45'),
(284, 1, 'SA8R', 'expired', NULL, NULL, NULL, '2026-05-11 17:22:47', '2026-05-11 17:17:47', '2026-05-11 17:23:45'),
(285, 3, 'WDGO', 'expired', NULL, NULL, NULL, '2026-05-11 17:25:18', '2026-05-11 17:20:18', '2026-05-12 03:15:41'),
(286, 1, 'OSK1', 'scanned', 1, NULL, 'g90cxklVmFsIsGavy0Ot2ENS6D5GYmYvgsIVBk3xxCfTjDgc3jucTmXmIUpRAvp5', '2026-05-11 17:28:45', '2026-05-11 17:23:45', '2026-05-11 17:27:15'),
(287, 1, 'HP27', 'scanned', 1, NULL, '2VFbniFPTsQ7Dip1UKIf1GK0Vt9JQnVmGFHbK9xPar6aNiqkbbPxByblYPeA9X9N', '2026-05-11 17:39:38', '2026-05-11 17:34:38', '2026-05-11 17:34:53'),
(288, 1, 'KCBC', 'scanned', 1, NULL, 'VLViztF5AYbbTJznco6OvJsBCxdY9GXixjvZyaqzUmIolDZViLIsXXyg8sbqgXpc', '2026-05-11 18:23:18', '2026-05-11 18:18:18', '2026-05-11 18:18:34'),
(289, 1, 'LTXJEBME', 'expired', NULL, NULL, NULL, '2026-05-11 18:25:33', '2026-05-11 18:20:33', '2026-05-11 18:29:13'),
(290, 1, 'OXJL', 'expired', NULL, NULL, NULL, '2026-05-11 18:26:13', '2026-05-11 18:21:13', '2026-05-11 18:29:13'),
(291, 1, 'XI3R', 'scanned', 1, NULL, 'CpBzsipeSBSynyhaZtbXQlT89vq5Sgw8bf35ocZCUwMSQoxXk7GnCakSOA5XBJrE', '2026-05-11 18:26:56', '2026-05-11 18:21:56', '2026-05-11 18:22:16'),
(292, 1, 'RILN', 'scanned', 1, NULL, 'SVeA2QwcOxTJ50jEJDStyP2PH7hnDH9JysNnhQWt9Kt5YjTG9XVKVkb3gghTp0vo', '2026-05-11 18:28:02', '2026-05-11 18:23:02', '2026-05-11 18:23:19'),
(293, 1, 'FWYS', 'scanned', 1, NULL, 'wKiZ6NSIGR2IES0pveSrjAQUKXLRkqYcDEiKYy5yG94g8vuG89Acd4GKp21RgMod', '2026-05-11 18:29:25', '2026-05-11 18:24:25', '2026-05-11 18:24:40'),
(294, 1, 'KKPA', 'expired', NULL, NULL, NULL, '2026-05-11 18:30:18', '2026-05-11 18:25:18', '2026-05-11 18:30:34'),
(295, 1, 'N2TF', 'expired', NULL, NULL, NULL, '2026-05-11 18:30:27', '2026-05-11 18:25:27', '2026-05-11 18:30:34'),
(296, 1, 'TDM3', 'expired', NULL, NULL, NULL, '2026-05-11 18:34:13', '2026-05-11 18:29:13', '2026-05-11 18:34:22'),
(297, 1, 'JASD', 'scanned', 1, NULL, 'Uez82v2QeuIGPyAFL9bNGnW9H1rJ2ZZjbeaAuyQi1Rrf6rDsz6KX4JZmwChlRgp3', '2026-05-11 18:34:34', '2026-05-11 18:29:34', '2026-05-11 18:29:55'),
(298, 1, 'MW6T', 'scanned', 1, NULL, 'ArohsyU7FBdAOY4z25DiVZZt1yAambT4NozmbyRRB4ycQlWALyXNB3fnbq1oeBRW', '2026-05-11 18:35:34', '2026-05-11 18:30:34', '2026-05-11 18:33:48'),
(299, 1, 'OWVZ', 'scanned', 1, NULL, 'lXKxzvPUQFBneX3cZX8hOKgHUT9C9TDDEIvK8uAx6BW03gTAeikpPgWhrWyTs5Zh', '2026-05-11 18:39:22', '2026-05-11 18:34:22', '2026-05-11 18:34:40'),
(300, 1, 'OSRT', 'scanned', 1, NULL, 'jDGEUsZIjdWnx6WChYUx9cLoAcnrxHtKeNItaTNQEVigwurPgAgbmu2YyHZT77L7', '2026-05-11 18:39:53', '2026-05-11 18:34:53', '2026-05-11 18:35:00'),
(301, 1, 'XDCQ', 'scanned', 1, NULL, 'L1XHi6kaGJzJfcMLlKlWQHCgg9hUVR3wEly06N4vmRy68djehSHVrv7Fy2bvHlWg', '2026-05-11 18:40:11', '2026-05-11 18:35:11', '2026-05-11 18:35:27'),
(302, 1, 'RGNG', 'scanned', 1, NULL, 'vxMQrDxjYI6PsA8EHnQB04s7SkaFePcMUBEXtbo2FZ5pxSTpIS3I0kMK1Vggvt7E', '2026-05-11 18:41:31', '2026-05-11 18:36:31', '2026-05-11 18:36:36'),
(303, 1, '9N7C', 'expired', NULL, NULL, NULL, '2026-05-11 18:42:08', '2026-05-11 18:37:08', '2026-05-11 18:42:08'),
(304, 1, 'R0YR', 'scanned', 1, NULL, 'S9ayyP9IgC42EOg1JPqq3zXODJ8voSnwYk5DuelsTbjf8fRypRJEHMOoiSL99mby', '2026-05-11 18:47:10', '2026-05-11 18:42:10', '2026-05-11 18:42:27'),
(305, 1, 'JDFR', 'expired', NULL, NULL, NULL, '2026-05-11 18:48:53', '2026-05-11 18:43:53', '2026-05-11 23:23:36'),
(306, 1, 'DJUF', 'scanned', 3, NULL, 'ch7nSHp7MDwommfEC5j5GbZiWIcGJ7cSC4gZviSsLHOC9pGmG0h5xLtbNScxs2H8', '2026-05-11 23:28:36', '2026-05-11 23:23:36', '2026-05-11 23:27:08'),
(307, 1, 'TAZO', 'scanned', 1, NULL, 'XWbcyFvReEfVVlrO2qHTJl1sdmc9sCaKmUJKZRzgRALwBK9ozigAQbe00e7XrCIa', '2026-05-11 23:36:10', '2026-05-11 23:31:10', '2026-05-11 23:31:34'),
(308, 1, 'LPCP', 'scanned', 1, NULL, 'w90vD5wzFTGCCHr2RUvDFXWAlIkDSXV2rx0xgEnhCT3wI4VXTd7WPEOQvanZ2LAL', '2026-05-12 02:22:46', '2026-05-12 02:17:46', '2026-05-12 02:18:12'),
(309, 1, 'TZZM', 'scanned', 1, NULL, 'bgHJ6aDrNjYNS457bklP4gQBKCmAeC1G1LKeoiMjlINqNIdq4sP9G8S6sux0tS6Z', '2026-05-12 02:23:26', '2026-05-12 02:18:26', '2026-05-12 02:18:30'),
(310, 1, 'IDFB', 'scanned', 1, NULL, 'nodDI0y6HPlN5scafcOp27v05j5nNZRcAiD4oFh7fqMO2igfbBXvKG4s1lxl6N9m', '2026-05-12 02:51:53', '2026-05-12 02:46:53', '2026-05-12 02:47:58'),
(311, 1, 'ZA0Y', 'scanned', 1, NULL, 'gxxNj3fxwuzGzxoM36n0A5adKbRjtNUA9BqSpHcavYXYe9RU8i2WhnIEMLe22BQz', '2026-05-12 03:00:30', '2026-05-12 02:55:30', '2026-05-12 02:56:27'),
(312, 3, 'JGT4', 'scanned', 1, NULL, 'nkTKC6JZK31PRGSweDUYnoWvTtnX98RfjdiNl7YlMb1QfzkXq1XkPcE1lGaYRMw8', '2026-05-12 03:20:41', '2026-05-12 03:15:41', '2026-05-12 03:16:19'),
(313, 1, 'LGGO', 'expired', NULL, NULL, NULL, '2026-07-29 07:05:31', '2026-07-29 07:00:31', '2026-07-29 07:09:40'),
(314, 1, 'SKIX', 'expired', NULL, NULL, NULL, '2026-07-29 07:14:40', '2026-07-29 07:09:40', '2026-07-29 07:17:25'),
(315, 1, 'MW2C', 'expired', NULL, NULL, NULL, '2026-07-29 07:17:21', '2026-07-29 07:12:21', '2026-07-29 07:17:25'),
(316, 1, 'LYQC', 'expired', NULL, NULL, NULL, '2026-07-29 07:17:24', '2026-07-29 07:12:24', '2026-07-29 07:17:25'),
(317, 1, 'WMRP', 'expired', NULL, NULL, NULL, '2026-07-29 07:17:46', '2026-07-29 07:12:46', '2026-07-29 07:43:48'),
(318, 1, 'FT08', 'expired', NULL, NULL, NULL, '2026-07-29 07:22:25', '2026-07-29 07:17:25', '2026-07-29 07:43:48'),
(319, 1, 'ZPZR', 'expired', NULL, NULL, NULL, '2026-07-29 07:48:48', '2026-07-29 07:43:48', '2026-07-29 07:52:24'),
(320, 1, '9TFT', 'expired', NULL, NULL, NULL, '2026-07-29 07:57:24', '2026-07-29 07:52:24', '2026-08-13 00:23:36'),
(321, 1, 'YNWB', 'expired', NULL, NULL, NULL, '2026-07-29 07:57:37', '2026-07-29 07:52:37', '2026-08-13 00:23:36'),
(322, 1, 'CFR6', 'expired', NULL, NULL, NULL, '2026-08-13 00:28:36', '2026-08-13 00:23:36', '2026-08-13 00:29:52'),
(323, 1, '6UZS', 'expired', NULL, NULL, NULL, '2026-08-13 00:32:02', '2026-08-13 00:27:02', '2026-08-13 00:32:57'),
(324, 1, 'X6TP', 'expired', NULL, NULL, NULL, '2026-08-13 00:34:52', '2026-08-13 00:29:52', '2026-08-13 00:37:06'),
(325, 1, 'YRLA', 'expired', NULL, NULL, NULL, '2026-08-13 00:37:57', '2026-08-13 00:32:57', '2026-08-13 00:41:26'),
(326, 1, 'FYOJ', 'expired', NULL, NULL, NULL, '2026-08-13 00:38:00', '2026-08-13 00:33:00', '2026-08-13 00:41:26'),
(327, 1, 'VITA', 'expired', NULL, NULL, NULL, '2026-08-13 00:38:05', '2026-08-13 00:33:05', '2026-08-13 00:41:26'),
(328, 1, 'SK3J', 'expired', NULL, NULL, NULL, '2026-08-13 00:38:06', '2026-08-13 00:33:06', '2026-08-13 00:41:26'),
(329, 1, 'K33D', 'expired', NULL, NULL, NULL, '2026-08-13 00:38:43', '2026-08-13 00:33:43', '2026-08-13 00:41:26'),
(330, 1, 'BTWR', 'expired', NULL, NULL, NULL, '2026-08-13 00:42:06', '2026-08-13 00:37:06', '2026-08-13 00:46:30'),
(331, 1, 'UGAV', 'expired', NULL, NULL, NULL, '2026-08-13 00:46:26', '2026-08-13 00:41:26', '2026-08-13 00:46:30'),
(332, 1, 'UF4F', 'expired', NULL, NULL, NULL, '2026-08-13 00:46:28', '2026-08-13 00:41:28', '2026-08-13 00:46:28'),
(333, 1, '7HAV', 'expired', NULL, NULL, NULL, '2026-08-13 00:51:30', '2026-08-13 00:46:30', '2026-08-13 00:55:18'),
(334, 1, 'EM23', 'expired', NULL, NULL, NULL, '2026-08-13 00:51:31', '2026-08-13 00:46:31', '2026-08-13 00:55:18'),
(335, 1, '5XM1', 'expired', NULL, NULL, NULL, '2026-08-13 01:00:18', '2026-08-13 00:55:18', '2026-08-13 01:01:30'),
(336, 1, 'N9TX', 'expired', NULL, NULL, NULL, '2026-08-13 01:00:50', '2026-08-13 00:55:50', '2026-08-13 01:01:30');
INSERT INTO `qr_sessions` (`id`, `machine_id`, `qr_token`, `status`, `scanned_by`, `session_code`, `kiosk_token`, `expires_at`, `created_at`, `updated_at`) VALUES
(337, 1, 'BWKR', 'expired', NULL, NULL, NULL, '2026-08-13 01:01:07', '2026-08-13 00:56:07', '2026-08-13 01:01:30'),
(338, 1, 'OL6A', 'scanned', 1, NULL, 'JfvEsTwkrIpQdfXx74lXg68rI7oscW05tTYJVvNDwNIOkjE7wPwCJCZjECGORFCz', '2026-08-13 01:06:30', '2026-08-13 01:01:30', '2026-08-13 01:02:23'),
(339, 1, 'WSCC', 'expired', NULL, NULL, NULL, '2026-08-13 01:13:49', '2026-08-13 01:08:49', '2026-08-13 01:16:38'),
(340, 1, 'JLW1', 'expired', NULL, NULL, NULL, '2026-08-13 01:16:01', '2026-08-13 01:11:01', '2026-08-13 01:16:38'),
(341, 1, 'H2XZ', 'expired', NULL, NULL, NULL, '2026-08-13 01:18:07', '2026-08-13 01:13:07', '2026-08-13 01:18:30'),
(342, 1, 'FCG0', 'expired', NULL, NULL, NULL, '2026-08-13 01:21:38', '2026-08-13 01:16:38', '2026-08-13 01:23:59'),
(343, 1, 'LU17', 'expired', NULL, NULL, NULL, '2026-08-13 01:23:30', '2026-08-13 01:18:30', '2026-08-13 01:23:59'),
(344, 1, '2IIC', 'expired', NULL, NULL, NULL, '2026-08-13 01:24:24', '2026-08-13 01:19:24', '2026-08-13 01:24:25'),
(345, 1, 'RLYP', 'expired', NULL, NULL, NULL, '2026-08-13 01:28:59', '2026-08-13 01:23:59', '2026-08-13 01:30:11'),
(346, 1, 'LXAH', 'expired', NULL, NULL, NULL, '2026-08-13 01:29:25', '2026-08-13 01:24:25', '2026-08-13 01:30:11'),
(347, 1, 'YA5U', 'expired', NULL, NULL, NULL, '2026-08-13 01:30:08', '2026-08-13 01:25:08', '2026-08-13 01:30:09'),
(348, 1, 'WNYT', 'expired', NULL, NULL, NULL, '2026-08-13 01:35:11', '2026-08-13 01:30:11', '2026-08-13 02:01:04'),
(349, 1, 'XUZJ', 'expired', NULL, NULL, NULL, '2026-08-13 01:35:11', '2026-08-13 01:30:11', '2026-08-13 02:01:04'),
(350, 1, 'WB2V', 'expired', NULL, NULL, NULL, '2026-08-13 01:38:54', '2026-08-13 01:33:54', '2026-08-13 02:01:04'),
(351, 1, '6F5J', 'expired', NULL, NULL, NULL, '2026-08-13 01:38:57', '2026-08-13 01:33:57', '2026-08-13 02:01:04'),
(352, 1, 'TDH3', 'expired', NULL, NULL, NULL, '2026-08-13 01:40:01', '2026-08-13 01:35:01', '2026-08-13 02:01:04'),
(353, 1, 'EEUU', 'expired', NULL, NULL, NULL, '2026-08-13 02:06:04', '2026-08-13 02:01:04', '2026-08-13 02:06:04'),
(354, 1, 'TAFN', 'expired', NULL, NULL, NULL, '2026-08-13 02:11:06', '2026-08-13 02:06:06', '2026-08-13 02:14:00'),
(355, 1, 'J2XM', 'expired', NULL, NULL, NULL, '2026-08-13 02:11:07', '2026-08-13 02:06:07', '2026-08-13 02:14:00'),
(356, 1, 'DERP', 'expired', NULL, NULL, NULL, '2026-08-13 02:11:41', '2026-08-13 02:06:41', '2026-08-13 02:14:00'),
(357, 1, 'Q6RE', 'expired', NULL, NULL, NULL, '2026-08-13 02:19:00', '2026-08-13 02:14:00', '2026-08-13 03:04:28'),
(358, 1, 'IFBI', 'scanned', 10, NULL, 'R5i1jn9RyjfprFafCMLZGNaazuvHROzf09v56OP94pEKeUM1C0OVMJdxGEcRh3Y0', '2026-08-13 02:21:46', '2026-08-13 02:16:46', '2026-08-13 02:17:14'),
(359, 1, 'KOJA', 'expired', NULL, NULL, NULL, '2026-08-13 03:09:28', '2026-08-13 03:04:28', '2026-08-13 03:14:05'),
(360, 1, 'I5CB', 'expired', NULL, NULL, NULL, '2026-08-13 03:10:02', '2026-08-13 03:05:02', '2026-08-13 03:14:05'),
(361, 1, 'QBUU', 'expired', NULL, NULL, NULL, '2026-08-13 03:10:10', '2026-08-13 03:05:10', '2026-08-13 03:14:05'),
(362, 1, 'TTXF', 'expired', NULL, NULL, NULL, '2026-08-13 03:11:30', '2026-08-13 03:06:30', '2026-08-13 03:14:05'),
(363, 1, '3DNO', 'scanned', 5, NULL, 'hkI1Fn05TAK6YUMU0bEOPs7Y9Efhp3yy7I97VZpLIu4zN5T0adFGBFopeSqIrYY6', '2026-08-13 03:13:11', '2026-08-13 03:08:11', '2026-08-13 03:08:27'),
(364, 1, 'W4ZB', 'scanned', 5, NULL, 'dixwvSgz3ELw6wktvWlTWyciosSrIrNn3U8aj3M1LN56BIx9uzesPt1z64HFmpfW', '2026-08-13 03:19:05', '2026-08-13 03:14:05', '2026-08-13 03:14:43'),
(365, 1, 'GMWP', 'scanned', 5, NULL, 'oUqNvrTv81uqCgmF5BLCoq9mZOMfVLCfxQSafApSy1wGwtUy7MPQCppTJOJUH2N3', '2026-08-13 03:32:07', '2026-08-13 03:27:07', '2026-08-13 03:27:37'),
(366, 1, '89BZ', 'scanned', 5, NULL, 'vuZYR4DvXpSiwsJIrJfjdkBZJ3Y6dkxxN1WqpFEt6xGyWEyhm2auQr47HfpBojht', '2026-08-13 03:33:58', '2026-08-13 03:28:58', '2026-08-13 03:29:05'),
(367, 1, 'JOLF', 'scanned', 5, NULL, 'QOveqwI3pVDK9XBXypaLv0d734JX7T2JCxrM4DekLFiXe0pbhKLU7tnlR4nOsgmJ', '2026-08-13 03:35:46', '2026-08-13 03:30:46', '2026-08-13 03:30:55'),
(368, 1, 'DCMM', 'scanned', 5, NULL, 'Mtqj5jBgT2kfFIyhGZcaQoHyyxgpJm3YscDah5r62g0MAV7nvAIVBdGr2ZVwPakB', '2026-08-13 03:50:23', '2026-08-13 03:45:23', '2026-08-13 03:45:33'),
(369, 1, 'I9BY', 'scanned', 5, NULL, 'qvfbxQN6abDfry6SguEtRrQKNZkn6QuSsEO4WLZe1DNfCSoljmZ5O91JDm9t0HDc', '2026-08-13 03:51:36', '2026-08-13 03:46:36', '2026-08-13 03:47:31'),
(370, 1, 'MDJS', 'expired', NULL, NULL, NULL, '2026-08-13 03:53:27', '2026-08-13 03:48:27', '2026-08-13 03:55:23'),
(371, 1, 'XA2K', 'scanned', 9, NULL, 'ob9rUW0lrF17G35xP66MOwTsWz5yEqAxOtRa1cGQskPK6gx3fWD8SAEKDpkliUgc', '2026-08-13 03:53:33', '2026-08-13 03:48:33', '2026-08-13 03:49:34'),
(372, 1, 'BUCF', 'expired', NULL, NULL, NULL, '2026-08-13 03:56:45', '2026-08-13 03:51:45', '2026-08-13 09:12:08'),
(373, 1, 'GECD', 'scanned', 5, NULL, 'LVAGFYzyaBQ85zAWZu9IPdSUe10nj3jqLff1PZSCdbsF8EakGmqWb8qIc5XUmjkl', '2026-08-13 04:00:23', '2026-08-13 03:55:23', '2026-08-13 03:55:48'),
(374, 3, 'PERS', 'scanned', 5, NULL, 'LWzFmDfgGmktm3JYT5z7U9n0RMSwFMDZssdd5dlgE45nEukceaFPa2ca1hgp4syj', '2026-08-13 04:01:34', '2026-08-13 03:56:34', '2026-08-13 03:56:39'),
(375, 3, 'FQIY', 'scanned', 5, NULL, 'VjEObh2yGRP7TCsrtmB8xH6yf13RWltdPFTkMj8Rsd8z1kGHHjHFG8QBIZ3SzEjy', '2026-08-13 04:02:20', '2026-08-13 03:57:20', '2026-08-13 03:57:23'),
(376, 3, '8N2Y', 'expired', NULL, NULL, NULL, '2026-08-13 08:45:24', '2026-08-13 08:40:24', '2026-08-13 08:47:04'),
(377, 3, 'JRTL', 'expired', NULL, NULL, NULL, '2026-08-13 08:45:25', '2026-08-13 08:40:25', '2026-08-13 08:47:04'),
(378, 3, 'JMAJ', 'expired', NULL, NULL, NULL, '2026-08-13 08:45:28', '2026-08-13 08:40:28', '2026-08-13 08:47:04'),
(379, 3, 'CM9X', 'pending', NULL, NULL, NULL, '2026-08-13 08:52:04', '2026-08-13 08:47:04', '2026-08-13 08:47:04'),
(380, 1, 'CJWK', 'expired', NULL, NULL, NULL, '2026-08-13 09:17:08', '2026-08-13 09:12:08', '2026-08-13 09:33:50'),
(381, 1, 'BWPJ', 'expired', NULL, NULL, NULL, '2026-08-13 09:18:11', '2026-08-13 09:13:11', '2026-08-13 09:33:50'),
(382, 1, 'P10X', 'expired', NULL, NULL, NULL, '2026-08-13 09:19:05', '2026-08-13 09:14:05', '2026-08-13 09:33:50'),
(383, 1, 'DGQN', 'expired', NULL, NULL, NULL, '2026-08-13 09:19:44', '2026-08-13 09:14:44', '2026-08-13 09:33:50'),
(384, 1, 'B4H3', 'scanned', 5, NULL, 'R3bUw3iMiBwhhOndqGl0Vo1srOnHv1COV9wL4ortXIYM1BNChnfTjQgPJNa85dIJ', '2026-08-13 09:38:50', '2026-08-13 09:33:50', '2026-08-13 09:33:58'),
(385, 1, 'XHQS', 'scanned', 5, NULL, 'dSsfdzrh76GgMNn9aRH3kx2holFAkcJqpiNdc09Mm7kgbJQ7KRgI96zIOFcuozXI', '2026-08-13 09:46:00', '2026-08-13 09:41:00', '2026-08-13 09:41:28'),
(386, 1, 'WZN4', 'scanned', 5, NULL, 'lhXtzzJcdmN5avDGWNk2M5Nv9Uh8d8Qh8uMdfFGmc2hGv0B9PZtpUPSRhWVw0tK2', '2026-08-13 09:47:32', '2026-08-13 09:42:32', '2026-08-13 09:42:33'),
(387, 1, 'VDWG', 'scanned', 5, NULL, '9BpNTg8LW3J5I3FCBK4blUwJT6Qt8pEu9KRvNWpSdwx38NUFPt3W6AHaw64Irtkl', '2026-08-13 09:48:03', '2026-08-13 09:43:03', '2026-08-13 09:43:20'),
(388, 1, 'JVHT', 'scanned', 5, NULL, 'ciYiQBDyQjxSJPtVHarbKv8ZMDN5Gn9YSYsHU2U8CNB6g2IWtwtzdFXEept6mJH4', '2026-08-13 09:48:56', '2026-08-13 09:43:56', '2026-08-13 09:44:03'),
(389, 1, 'US4N', 'scanned', 5, NULL, '5fnk0bUZnA1BIA6i2ZeYKzlKbwTOTLIH5Q5QLhA9Uo8QfFMqUq7wH1azRWysrSSA', '2026-08-13 09:54:51', '2026-08-13 09:49:51', '2026-08-13 09:49:59'),
(390, 1, 'VSIP', 'scanned', 5, NULL, 'w3SyQnc42ZQyHAk1do90t4aTLpCNK5ZmZDIvsblw32hC7o0pBRZvS1UYG2NiCFJB', '2026-08-13 09:55:35', '2026-08-13 09:50:35', '2026-08-13 09:50:36'),
(391, 1, '6TNC', 'scanned', 5, NULL, 'lKIpmbPBFL4hYi4STozxiQdQk4xV3c6HD1g879NKNVzaVv0C6iuws8Yi8u01N4ZJ', '2026-08-13 09:56:45', '2026-08-13 09:51:45', '2026-08-13 09:51:49'),
(392, 1, 'RCRP', 'scanned', 5, NULL, '9mkRefC8zUpHyp2Dvhvi6Ubg73AIhu2EbO4IyixAdrTWhzJNErn01GB5cxLvRuol', '2026-08-13 09:58:30', '2026-08-13 09:53:30', '2026-08-13 09:53:35'),
(393, 1, 'BGPX', 'scanned', 5, NULL, 'jD4sDaygiRPSb6Aclkjei7Vw2oOj0Nix2nl8GOsM5WVS8AyPN8KGvTqqtx2sVVdG', '2026-08-13 09:59:10', '2026-08-13 09:54:10', '2026-08-13 09:54:21'),
(394, 1, '3GBS', 'scanned', 5, NULL, 'LKAtKYBUWUU7SrsoLWIXwLbCjgnqBKypZb7JP5lnCWsPXDCXElBqdk9wJdWeJNwV', '2026-08-13 10:02:15', '2026-08-13 09:57:15', '2026-08-13 09:57:27'),
(395, 1, 'YYYF', 'scanned', 5, NULL, 'srCr8JPEAqZgOF0EmhBnxd1rpKh7mPgCNuyOrUmcVWhprZ6vn6N5rlvyxu78yZqZ', '2026-08-13 10:02:57', '2026-08-13 09:57:57', '2026-08-13 09:58:10'),
(396, 1, 'DRLW', 'scanned', 5, NULL, 'nrdOrbJTUMXON8FNPR0XA0g8ciVZLOlOoa4QiMkGnMt0LArtqTMi5H9aiQpF62kG', '2026-08-13 10:03:41', '2026-08-13 09:58:41', '2026-08-13 09:58:50'),
(397, 1, 'T2LN', 'scanned', 5, NULL, 'xD7LkylgJj9t020nylKxXZPYHXmB3pr2yZsulX7JqYCZfwD0TmMK1Vqf0BMkgiOG', '2026-08-13 10:04:27', '2026-08-13 09:59:27', '2026-08-13 10:00:18'),
(398, 1, 'PZ5K', 'scanned', 5, NULL, 'pjVrxd20nokIZMvh79JBV4wkOAbLXC6EwSZyyuhtKvraRVcGOdpIKeAUUIr6iVzZ', '2026-08-13 10:07:47', '2026-08-13 10:02:47', '2026-08-13 10:02:59'),
(399, 1, 'TVZS', 'scanned', 5, NULL, 'EgWtJQB69s7kMYngH87wdgogjtb1YVtmlsApG474p38GsFQkuQfslLeDjZ8bR31u', '2026-08-13 10:27:31', '2026-08-13 10:22:31', '2026-08-13 10:23:06'),
(400, 1, 'YGUZ', 'pending', NULL, NULL, NULL, '2026-08-13 10:29:02', '2026-08-13 10:24:02', '2026-08-13 10:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `recycling_sessions`
--

CREATE TABLE `recycling_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `session_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `status` enum('active','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `start_points` int NOT NULL DEFAULT '0',
  `end_points` int NOT NULL DEFAULT '0',
  `points_earned` int NOT NULL DEFAULT '0',
  `total_items` int NOT NULL DEFAULT '0',
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycling_sessions`
--

INSERT INTO `recycling_sessions` (`id`, `session_code`, `user_id`, `machine_id`, `status`, `start_points`, `end_points`, `points_earned`, `total_items`, `started_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(18, 'RVM-27RQ0MJEI3BH', 3, 1, 'completed', 9, 35, 26, 1, '2026-05-07 22:27:34', '2026-05-07 22:30:35', '2026-05-07 22:27:34', '2026-05-09 23:25:26'),
(19, 'RVM-5QOOKFCISM77', 3, 1, 'completed', 35, 35, 0, 0, '2026-05-07 22:30:51', '2026-05-07 22:31:19', '2026-05-07 22:30:51', '2026-05-09 23:25:26'),
(20, 'RVM-GE70CE8HO4LF', 3, 1, 'completed', 35, 43, 8, 1, '2026-05-07 22:32:17', '2026-05-09 19:29:42', '2026-05-07 22:32:17', '2026-05-09 23:25:26'),
(21, 'RVM-NLINYIYQQCUW', 1, 1, 'completed', 0, 20, 20, 1, '2026-05-07 22:33:43', '2026-05-07 22:38:43', '2026-05-07 22:33:43', '2026-05-09 23:25:26'),
(22, 'RVM-PCNTNV5ANZYZ', 1, 1, 'completed', 20, 191, 171, 11, '2026-05-07 22:39:21', '2026-05-07 23:18:36', '2026-05-07 22:39:21', '2026-05-09 23:25:26'),
(23, 'RVM-K2IV7WHODGJY', 1, 1, 'completed', 191, 196, 5, 1, '2026-05-07 23:18:54', '2026-05-07 23:35:18', '2026-05-07 23:18:54', '2026-05-09 23:25:26'),
(24, 'RVM-FYVZ8TSFQB4J', 1, 1, 'completed', 196, 196, 0, 0, '2026-05-07 23:39:59', '2026-05-09 09:13:54', '2026-05-07 23:39:59', '2026-05-09 23:25:26'),
(25, 'RVM-QQZTCYMKJCIA', 4, 1, 'completed', 0, 0, -20, 0, '2026-05-09 07:59:35', '2026-05-09 08:00:37', '2026-05-09 07:59:35', '2026-05-09 23:25:26'),
(26, 'RVM-INAWV2KBRF8A', 4, 1, 'completed', 0, 0, 0, 0, '2026-05-09 08:00:52', '2026-05-09 08:01:07', '2026-05-09 08:00:52', '2026-05-09 23:25:26'),
(27, 'RVM-JE0HGTJJP62K', 4, 1, 'completed', 0, 0, 0, 0, '2026-05-09 08:01:30', '2026-05-09 08:01:31', '2026-05-09 08:01:30', '2026-05-09 23:25:26'),
(28, 'RVM-YWYD7JFTBEXB', 4, 1, 'completed', 0, 0, 0, 0, '2026-05-09 08:05:46', '2026-05-09 08:05:47', '2026-05-09 08:05:46', '2026-05-09 23:25:26'),
(29, 'RVM-7WFBEEWVIZZU', 1, 1, 'completed', 196, 196, 0, 0, '2026-05-09 09:14:20', '2026-05-09 09:14:34', '2026-05-09 09:14:20', '2026-05-09 23:25:26'),
(30, 'RVM-HS6GN0L51SIG', 1, 1, 'completed', 196, 186, -10, 0, '2026-05-09 09:15:19', '2026-05-09 09:16:25', '2026-05-09 09:15:19', '2026-05-09 23:25:26'),
(31, 'RVM-EROUE9KFKDHA', 1, 1, 'completed', 186, 166, -20, 0, '2026-05-09 09:30:16', '2026-05-09 10:18:28', '2026-05-09 09:30:16', '2026-05-09 23:25:26'),
(32, 'RVM-OZ8TUQYPEW0P', 1, 1, 'completed', 166, 174, 8, 1, '2026-05-09 10:18:43', '2026-05-09 10:19:46', '2026-05-09 10:18:43', '2026-05-09 23:25:26'),
(33, 'RVM-NGIQLFRV0YST', 1, 1, 'completed', 174, 184, 10, 1, '2026-05-09 10:20:03', '2026-05-09 10:20:36', '2026-05-09 10:20:03', '2026-05-09 23:25:26'),
(34, 'RVM-AWRJAF45KR9E', 1, 1, 'completed', 184, 184, 0, 0, '2026-05-09 10:21:04', '2026-05-09 10:25:53', '2026-05-09 10:21:04', '2026-05-09 23:25:26'),
(35, 'RVM-ZW6JHE85UDBJ', 1, 1, 'completed', 184, 164, -20, 1, '2026-05-09 10:26:15', '2026-05-09 10:30:29', '2026-05-09 10:26:15', '2026-05-09 23:25:26'),
(36, 'RVM-RCZGWGWR7ZFU', 1, 1, 'completed', 164, 164, 0, 0, '2026-05-09 10:36:56', '2026-05-09 10:36:58', '2026-05-09 10:36:56', '2026-05-09 23:25:26'),
(37, 'RVM-FVW0MOZL2SRO', 1, 1, 'completed', 164, 162, -2, 1, '2026-05-09 10:37:15', '2026-05-09 11:06:14', '2026-05-09 10:37:15', '2026-05-09 23:25:26'),
(38, 'RVM-WJBQX1TKASGD', 1, 1, 'completed', 162, 102, -60, 0, '2026-05-09 11:06:23', '2026-05-09 21:55:45', '2026-05-09 11:06:23', '2026-05-09 23:25:26'),
(39, 'RVM-GFL1MKELUWVG', 4, 1, 'completed', 0, 0, -30, 0, '2026-05-09 18:52:10', '2026-05-09 18:55:13', '2026-05-09 18:52:10', '2026-05-09 23:25:26'),
(40, 'RVM-KLJBUVA4VF5D', 4, 1, 'completed', 0, 0, -13, 1, '2026-05-09 18:56:44', '2026-05-10 16:21:42', '2026-05-09 18:56:44', '2026-05-10 16:21:42'),
(41, 'RVM-Y92QKSFJMOPU', 3, 1, 'completed', 43, 75, 32, 1, '2026-05-09 19:30:45', '2026-05-09 19:31:07', '2026-05-09 19:30:45', '2026-05-09 23:25:26'),
(42, 'RVM-5GS6FN397PEC', 3, 1, 'completed', 75, 125, 50, 2, '2026-05-09 19:43:00', '2026-05-09 19:46:18', '2026-05-09 19:43:00', '2026-05-09 23:25:26'),
(43, 'RVM-1VYSX4BMFBC4', 3, 1, 'completed', 125, 133, 8, 1, '2026-05-09 19:46:50', '2026-05-09 20:05:55', '2026-05-09 19:46:50', '2026-05-09 23:25:26'),
(44, 'RVM-MZ32MLQWA5HQ', 3, 1, 'completed', 133, 159, 26, 2, '2026-05-09 20:06:20', '2026-05-09 20:22:25', '2026-05-09 20:06:20', '2026-05-09 23:25:26'),
(45, 'RVM-TEE4XSQMDEQ0', 3, 1, 'completed', 159, 196, 37, 1, '2026-05-09 20:23:11', '2026-05-09 20:39:16', '2026-05-09 20:23:11', '2026-05-09 23:25:26'),
(46, 'RVM-AFBVLYEUPDNJ', 3, 1, 'completed', 196, 196, 0, 0, '2026-05-09 20:39:34', '2026-05-09 20:39:39', '2026-05-09 20:39:34', '2026-05-09 23:25:26'),
(47, 'RVM-F2HDDLXIOARO', 3, 1, 'completed', 196, 196, 0, 0, '2026-05-09 20:40:04', '2026-05-09 20:52:37', '2026-05-09 20:40:04', '2026-05-09 23:25:26'),
(48, 'RVM-S7UQZNCUUEON', 3, 1, 'completed', 196, 201, 5, 1, '2026-05-09 20:52:49', '2026-05-09 20:53:11', '2026-05-09 20:52:49', '2026-05-09 23:25:26'),
(49, 'RVM-GXXJNEFEVLYG', 3, 1, 'completed', 201, 201, 0, 0, '2026-05-09 20:53:19', '2026-05-09 20:53:29', '2026-05-09 20:53:19', '2026-05-09 23:25:26'),
(50, 'RVM-O072CMVPMFEZ', 3, 1, 'completed', 201, 201, 0, 0, '2026-05-09 21:12:25', '2026-05-09 21:48:01', '2026-05-09 21:12:25', '2026-05-09 23:25:26'),
(51, 'RVM-YLLQXJEKFJUV', 3, 1, 'completed', 201, 212, 11, 1, '2026-05-09 21:48:39', '2026-05-09 21:49:43', '2026-05-09 21:48:39', '2026-05-09 23:25:26'),
(52, 'RVM-GSGV0ANELDBB', 3, 1, 'completed', 212, 212, 0, 0, '2026-05-09 21:50:00', '2026-05-09 21:50:02', '2026-05-09 21:50:00', '2026-05-09 23:25:26'),
(53, 'RVM-6OKEQZCRQQEC', 3, 1, 'completed', 212, 235, 23, 1, '2026-05-09 21:50:51', '2026-05-09 21:51:35', '2026-05-09 21:50:51', '2026-05-09 23:25:26'),
(54, 'RVM-LFVHWQS0KVNO', 3, 1, 'completed', 235, 225, -10, 0, '2026-05-09 21:52:06', '2026-05-09 21:53:54', '2026-05-09 21:52:06', '2026-05-09 23:25:26'),
(55, 'RVM-LCRU3MQNTQ3M', 1, 1, 'completed', 102, 82, -20, 0, '2026-05-09 22:01:07', '2026-05-09 22:02:09', '2026-05-09 22:01:07', '2026-05-09 23:25:26'),
(56, 'RVM-GLGOQADPVPCV', 1, 1, 'completed', 82, 72, -10, 0, '2026-05-09 22:02:21', '2026-05-09 22:02:45', '2026-05-09 22:02:21', '2026-05-09 23:25:26'),
(57, 'RVM-FK1ADHLYDBUG', 1, 1, 'completed', 72, 62, -10, 0, '2026-05-09 22:03:20', '2026-05-09 22:03:49', '2026-05-09 22:03:20', '2026-05-09 23:25:26'),
(58, 'RVM-TP4C4M9ZQFZS', 1, 1, 'completed', 62, 227, 165, 5, '2026-05-09 22:09:03', '2026-05-09 22:24:49', '2026-05-09 22:09:03', '2026-05-09 23:25:26'),
(59, 'RVM-MEOCOSG4BEYS', 1, 3, 'completed', 227, 227, 0, 0, '2026-05-09 23:17:45', '2026-05-09 23:17:52', '2026-05-09 23:17:45', '2026-05-09 23:25:26'),
(60, 'RVM-CRGKRSEK7HUP', 1, 3, 'completed', 227, 227, 0, 1, '2026-05-10 07:31:11', '2026-05-10 07:40:06', '2026-05-10 07:31:11', '2026-05-10 07:40:06'),
(61, 'RVM-DGVR6OTFNO6E', 1, 1, 'completed', 227, 217, -10, 0, '2026-05-10 07:40:45', '2026-05-10 07:43:01', '2026-05-10 07:40:45', '2026-05-10 07:43:01'),
(62, 'RVM-NWKQ6WDDXXTL', 1, 1, 'completed', 217, 207, -10, 0, '2026-05-10 07:43:05', '2026-05-10 07:44:51', '2026-05-10 07:43:05', '2026-05-10 07:44:51'),
(63, 'RVM-RNAWE7IMBWGP', 1, 1, 'completed', 207, 207, 0, 0, '2026-05-10 07:45:26', '2026-05-10 07:45:51', '2026-05-10 07:45:26', '2026-05-10 07:45:51'),
(64, 'RVM-FAZIYN4XKKZG', 1, 1, 'completed', 207, 217, 10, 1, '2026-05-10 07:45:57', '2026-05-10 07:46:26', '2026-05-10 07:45:57', '2026-05-10 07:46:26'),
(65, 'RVM-9IADCLEMLYYX', 1, 1, 'completed', 217, 217, 0, 0, '2026-05-10 07:48:00', '2026-05-10 07:52:43', '2026-05-10 07:48:00', '2026-05-10 07:52:43'),
(66, 'RVM-NX9QXMMR3NUP', 1, 1, 'completed', 217, 217, 0, 0, '2026-05-10 09:02:25', '2026-05-10 09:02:26', '2026-05-10 09:02:25', '2026-05-10 09:02:26'),
(67, 'RVM-C2MJKMDT7LAA', 1, 1, 'completed', 217, 217, 0, 0, '2026-05-10 09:03:05', '2026-05-10 09:03:48', '2026-05-10 09:03:05', '2026-05-10 09:03:48'),
(68, 'RVM-FXHZ8VTFFYZY', 1, 1, 'completed', 217, 207, -10, 0, '2026-05-10 10:56:19', '2026-05-10 10:56:56', '2026-05-10 10:56:19', '2026-05-10 10:56:56'),
(69, 'RVM-PIAJVCUEVIDC', 1, 3, 'completed', 207, 92, -115, 7, '2026-05-10 12:57:49', '2026-05-10 13:28:02', '2026-05-10 12:57:49', '2026-05-10 13:28:02'),
(70, 'RVM-0WNYUPUEO43N', 1, 1, 'completed', 92, 262, 170, 19, '2026-05-10 13:29:11', '2026-05-10 14:06:38', '2026-05-10 13:29:11', '2026-05-10 14:06:38'),
(71, 'RVM-NN1IYG0OSRL2', 1, 1, 'completed', 262, 1145, 883, 30, '2026-05-10 14:08:09', '2026-05-10 14:54:17', '2026-05-10 14:08:09', '2026-05-10 14:54:17'),
(72, 'RVM-CIVKAQHRG5EO', 1, 1, 'completed', 1145, 1152, 7, 1, '2026-05-10 16:19:39', '2026-05-10 16:20:03', '2026-05-10 16:19:39', '2026-05-10 16:20:03'),
(73, 'RVM-QFCVUVVPZKW9', 4, 1, 'completed', 0, 33, 33, 1, '2026-05-10 16:22:07', '2026-05-10 16:23:50', '2026-05-10 16:22:07', '2026-05-10 16:23:50'),
(74, 'RVM-EWZBQIXP82VA', 4, 1, 'completed', 33, 58, 25, 1, '2026-05-10 16:24:35', '2026-05-10 16:25:05', '2026-05-10 16:24:35', '2026-05-10 16:25:05'),
(75, 'RVM-RZWP0NPWTJFN', 4, 1, 'completed', 58, 72, 14, 1, '2026-05-10 16:25:42', '2026-05-10 16:26:41', '2026-05-10 16:25:42', '2026-05-10 16:26:41'),
(76, 'RVM-MT1PXTI99ZOL', 4, 1, 'completed', 72, 86, 14, 1, '2026-05-10 16:27:22', '2026-05-10 16:28:03', '2026-05-10 16:27:22', '2026-05-10 16:28:03'),
(77, 'RVM-RXWZIRL2DHUN', 4, 1, 'completed', 86, 100, 14, 1, '2026-05-10 16:30:34', '2026-05-10 16:30:59', '2026-05-10 16:30:34', '2026-05-10 16:30:59'),
(78, 'RVM-Z4TBYBZ92OVI', 4, 1, 'completed', 100, 128, 28, 1, '2026-05-10 16:36:40', '2026-05-10 16:37:03', '2026-05-10 16:36:40', '2026-05-10 16:37:03'),
(79, 'RVM-TTEATXORNCKK', 4, 1, 'completed', 128, 165, 37, 1, '2026-05-10 16:37:28', '2026-05-10 16:38:05', '2026-05-10 16:37:28', '2026-05-10 16:38:05'),
(80, 'RVM-C0CG2CR0R2ST', 1, 1, 'completed', 1152, 1237, 85, 4, '2026-05-11 09:35:33', '2026-05-11 09:38:46', '2026-05-11 09:35:33', '2026-05-11 09:38:46'),
(81, 'RVM-X583PBOFNLAX', 6, 1, 'completed', 0, 42, 42, 2, '2026-05-11 09:49:52', '2026-05-11 09:55:40', '2026-05-11 09:49:52', '2026-05-11 09:55:40'),
(82, 'RVM-R9E1KFKKWGSC', 6, 1, 'completed', 42, 42, 0, 0, '2026-05-11 09:58:00', '2026-05-11 09:59:09', '2026-05-11 09:58:00', '2026-05-11 09:59:09'),
(83, 'RVM-X7MAW7W0LBBX', 1, 1, 'completed', 1237, 1270, 33, 1, '2026-05-11 09:59:48', '2026-05-11 10:01:36', '2026-05-11 09:59:48', '2026-05-11 10:01:36'),
(84, 'RVM-K9PYKQ6MLEAJ', 3, 1, 'completed', 225, 253, 28, 1, '2026-05-11 10:10:30', '2026-05-11 10:11:22', '2026-05-11 10:10:30', '2026-05-11 10:11:22'),
(85, 'RVM-JMTO3TJXPOBV', 1, 1, 'completed', 1270, 1372, 102, 6, '2026-05-11 10:15:36', '2026-05-11 10:19:09', '2026-05-11 10:15:36', '2026-05-11 10:19:09'),
(86, 'RVM-DCVT3DRNQLIK', 4, 1, 'completed', 165, 553, 388, 17, '2026-05-11 14:56:24', '2026-05-11 15:11:15', '2026-05-11 14:56:24', '2026-05-11 15:11:15'),
(87, 'RVM-PRDWOSGL431G', 6, 1, 'completed', 42, 100, 58, 2, '2026-05-11 15:15:00', '2026-05-11 15:20:24', '2026-05-11 15:15:00', '2026-05-11 15:20:24'),
(88, 'RVM-TJGJXFDCPQJB', 6, 1, 'completed', 100, 100, 0, 0, '2026-05-11 15:33:04', '2026-05-11 15:34:46', '2026-05-11 15:33:04', '2026-05-11 15:34:46'),
(89, 'RVM-BH8OXEOJWFUF', 1, 1, 'completed', 1372, 1372, 0, 0, '2026-05-11 15:35:17', '2026-05-11 15:36:28', '2026-05-11 15:35:17', '2026-05-11 15:36:28'),
(90, 'RVM-1C16TAHANMNO', 6, 3, 'completed', 100, 100, 0, 0, '2026-05-11 15:38:27', '2026-05-11 15:39:13', '2026-05-11 15:38:27', '2026-05-11 15:39:13'),
(91, 'RVM-QKFVFMZWAO8G', 6, 3, 'completed', 100, 100, 0, 0, '2026-05-11 15:41:04', '2026-05-11 15:41:51', '2026-05-11 15:41:04', '2026-05-11 15:41:51'),
(92, 'RVM-5ULGNDHNBV2F', 6, 1, 'completed', 100, 100, 0, 0, '2026-05-11 15:45:13', '2026-05-11 15:45:57', '2026-05-11 15:45:13', '2026-05-11 15:45:57'),
(93, 'RVM-07EPAWXLTQAD', 6, 1, 'completed', 100, 107, 7, 1, '2026-05-11 15:48:18', '2026-05-11 16:04:22', '2026-05-11 15:48:18', '2026-05-11 16:04:22'),
(94, 'RVM-RVIMEBIEZ01Y', 1, 1, 'completed', 1372, 1377, 5, 1, '2026-05-11 15:57:35', '2026-05-11 15:58:10', '2026-05-11 15:57:35', '2026-05-11 15:58:10'),
(95, 'RVM-MS7ENPJSXS3N', 8, 1, 'completed', 0, 20, 20, 1, '2026-05-11 16:05:53', '2026-05-11 16:13:45', '2026-05-11 16:05:53', '2026-05-11 16:13:45'),
(96, 'RVM-QVFVHQPPGOUP', 1, 1, 'completed', 1377, 1441, 64, 2, '2026-05-11 16:08:07', '2026-05-11 16:49:37', '2026-05-11 16:08:07', '2026-05-11 16:49:37'),
(97, 'RVM-JI0VUAP1JEGM', 8, 1, 'completed', 20, 30, 10, 1, '2026-05-11 16:14:18', '2026-05-11 16:16:41', '2026-05-11 16:14:18', '2026-05-11 16:16:41'),
(98, 'RVM-NRMPIICDEHSM', 1, 1, 'completed', 1441, 1459, 18, 1, '2026-05-11 16:50:09', '2026-05-11 16:53:55', '2026-05-11 16:50:09', '2026-05-11 16:53:55'),
(99, 'RVM-622E0L0HVGTP', 1, 1, 'completed', 1459, 1560, 101, 3, '2026-05-11 16:54:47', '2026-05-11 16:56:16', '2026-05-11 16:54:47', '2026-05-11 16:56:16'),
(100, 'RVM-CNTVPT9WHW9R', 1, 1, 'completed', 1560, 1621, 61, 4, '2026-05-11 16:57:04', '2026-05-11 16:59:16', '2026-05-11 16:57:04', '2026-05-11 16:59:16'),
(101, 'RVM-AHNGVEBOH4UQ', 1, 3, 'completed', 1621, 1921, 300, 11, '2026-05-11 17:14:59', '2026-05-11 17:33:57', '2026-05-11 17:14:59', '2026-05-11 17:33:57'),
(102, 'RVM-XGD0F7V1JPTY', 1, 1, 'completed', 1921, 2200, 279, 18, '2026-05-11 17:34:53', '2026-05-11 17:45:20', '2026-05-11 17:34:53', '2026-05-11 17:45:20'),
(103, 'RVM-5BJHZYYHF2FW', 1, 1, 'completed', 2200, 2248, 48, 1, '2026-05-11 18:18:34', '2026-05-11 18:30:24', '2026-05-11 18:18:34', '2026-05-11 18:30:24'),
(104, 'RVM-IBM8C2ZJ9CJL', 1, 1, 'completed', 2248, 2269, 21, 1, '2026-05-11 18:33:49', '2026-05-11 18:36:23', '2026-05-11 18:33:49', '2026-05-11 18:36:23'),
(105, 'RVM-JD52NJ8Q6OPM', 1, 1, 'completed', 2269, 2297, 28, 1, '2026-05-11 18:36:36', '2026-05-11 18:37:04', '2026-05-11 18:36:36', '2026-05-11 18:37:04'),
(106, 'RVM-20UH2ZFMO4KJ', 1, 1, 'completed', 2297, 2315, 18, 1, '2026-05-11 18:42:27', '2026-05-11 18:43:44', '2026-05-11 18:42:27', '2026-05-11 18:43:44'),
(107, 'RVM-MVWPG46VOGT2', 1, 1, 'completed', 2315, 2322, 7, 1, '2026-05-11 23:22:59', '2026-05-11 23:23:31', '2026-05-11 23:22:59', '2026-05-11 23:23:31'),
(108, 'RVM-L1VDKCLTTDPN', 3, 1, 'completed', 253, 339, 86, 4, '2026-05-11 23:27:09', '2026-05-11 23:31:05', '2026-05-11 23:27:09', '2026-05-11 23:31:05'),
(109, 'RVM-NC1IFJ2IWBV4', 1, 1, 'completed', 2322, 2342, 20, 1, '2026-05-11 23:31:35', '2026-05-11 23:32:26', '2026-05-11 23:31:35', '2026-05-11 23:32:26'),
(110, 'RVM-5XFYGQIOBH1P', 1, 1, 'completed', 2342, 2362, 20, 1, '2026-05-12 02:18:13', '2026-05-12 02:49:34', '2026-05-12 02:18:13', '2026-05-12 02:49:34'),
(111, 'RVM-CNHJWJNNXARA', 1, 1, 'completed', 2362, 2382, 20, 3, '2026-05-12 02:56:28', '2026-05-12 03:01:52', '2026-05-12 02:56:28', '2026-05-12 03:01:52'),
(112, 'RVM-HYM0HRL0RESP', 1, 3, 'completed', 2382, 2387, 5, 1, '2026-05-12 03:16:19', '2026-07-29 07:13:21', '2026-05-12 03:16:19', '2026-07-29 07:13:21'),
(113, 'RVM-YQXBSNFT3IJF', 1, 1, 'completed', 2387, 2433, 46, 4, '2026-08-13 01:02:23', '2026-08-13 01:08:45', '2026-08-13 01:02:23', '2026-08-13 01:08:45'),
(114, 'RVM-YI6ZTHMXRQB2', 10, 1, 'active', 0, 0, 0, 0, '2026-08-13 02:17:14', NULL, '2026-08-13 02:17:14', '2026-08-13 02:17:14'),
(115, 'RVM-TYNI0SP8LW9K', 5, 1, 'completed', 0, 36, 36, 1, '2026-08-13 03:08:28', '2026-08-13 03:15:29', '2026-08-13 03:08:28', '2026-08-13 03:15:29'),
(116, 'RVM-TVXWPNICVAHE', 5, 1, 'completed', 36, 64, 28, 1, '2026-08-13 03:27:37', '2026-08-13 03:28:25', '2026-08-13 03:27:37', '2026-08-13 03:28:25'),
(117, 'RVM-9YJFXWIFF9EE', 5, 1, 'completed', 64, 100, 36, 2, '2026-08-13 03:29:05', '2026-08-13 03:30:09', '2026-08-13 03:29:05', '2026-08-13 03:30:09'),
(118, 'RVM-THOBRBGYMAR4', 5, 1, 'completed', 100, 107, 7, 1, '2026-08-13 03:30:55', '2026-08-13 03:31:20', '2026-08-13 03:30:55', '2026-08-13 03:31:20'),
(119, 'RVM-7OWTNUSK1AXT', 5, 1, 'completed', 107, 121, 14, 1, '2026-08-13 03:45:33', '2026-08-13 03:46:01', '2026-08-13 03:45:33', '2026-08-13 03:46:01'),
(120, 'RVM-NTAEFLTZ207K', 5, 1, 'completed', 121, 149, 28, 1, '2026-08-13 03:47:31', '2026-08-13 03:48:11', '2026-08-13 03:47:31', '2026-08-13 03:48:11'),
(121, 'RVM-HFAQUAPYSEKY', 9, 1, 'completed', 0, 28, 28, 1, '2026-08-13 03:49:34', '2026-08-13 03:50:22', '2026-08-13 03:49:34', '2026-08-13 03:50:22'),
(122, 'RVM-GHJCNWLKZKWY', 5, 1, 'completed', 149, 177, 28, 1, '2026-08-13 03:55:49', '2026-08-13 03:56:20', '2026-08-13 03:55:49', '2026-08-13 03:56:20'),
(123, 'RVM-N8ZTNDZPOFBA', 5, 3, 'completed', 177, 198, 21, 1, '2026-08-13 03:56:40', '2026-08-13 03:57:10', '2026-08-13 03:56:40', '2026-08-13 03:57:10'),
(124, 'RVM-RNEL83INO789', 5, 3, 'completed', 198, 219, 21, 2, '2026-08-13 03:57:23', '2026-08-13 03:59:26', '2026-08-13 03:57:23', '2026-08-13 03:59:26'),
(125, 'RVM-IRVKRDIULLWS', 13, 3, 'active', 0, 0, 0, 0, '2026-08-13 08:50:07', NULL, '2026-08-13 08:50:07', '2026-08-13 08:50:07'),
(126, 'RVM-90HIRBWVHH3Z', 5, 1, 'completed', 219, 229, 10, 1, '2026-08-13 09:33:59', '2026-08-13 09:34:34', '2026-08-13 09:33:59', '2026-08-13 09:34:34'),
(127, 'RVM-UE0I1PAYQXSE', 5, 1, 'completed', 229, 229, 0, 0, '2026-08-13 09:41:28', '2026-08-13 09:42:14', '2026-08-13 09:41:28', '2026-08-13 09:42:14'),
(128, 'RVM-R8HYUPMWIFFX', 5, 1, 'completed', 229, 265, 36, 1, '2026-08-13 09:42:34', '2026-08-13 09:43:01', '2026-08-13 09:42:34', '2026-08-13 09:43:01'),
(129, 'RVM-EUQ4XZXORGZM', 5, 1, 'completed', 265, 274, 9, 1, '2026-08-13 09:43:21', '2026-08-13 09:43:54', '2026-08-13 09:43:21', '2026-08-13 09:43:54'),
(130, 'RVM-BGKZKSU8JQER', 5, 1, 'completed', 274, 302, 28, 1, '2026-08-13 09:44:03', '2026-08-13 09:50:27', '2026-08-13 09:44:03', '2026-08-13 09:50:27'),
(131, 'RVM-SZM6K8TIQEDN', 5, 1, 'completed', 302, 351, 49, 2, '2026-08-13 09:50:37', '2026-08-13 09:51:35', '2026-08-13 09:50:37', '2026-08-13 09:51:35'),
(132, 'RVM-JPYATGJMCRX8', 5, 1, 'completed', 351, 404, 53, 2, '2026-08-13 09:51:49', '2026-08-13 09:54:07', '2026-08-13 09:51:49', '2026-08-13 09:54:07'),
(133, 'RVM-PDL4WRB5WXGZ', 5, 1, 'completed', 404, 404, 0, 0, '2026-08-13 09:54:21', '2026-08-13 09:57:54', '2026-08-13 09:54:21', '2026-08-13 09:57:54'),
(134, 'RVM-SLNUKOBG6LHJ', 5, 1, 'completed', 404, 435, 31, 1, '2026-08-13 09:58:10', '2026-08-13 09:58:38', '2026-08-13 09:58:10', '2026-08-13 09:58:38'),
(135, 'RVM-ZDLVICXBSIU4', 5, 1, 'completed', 435, 435, 0, 0, '2026-08-13 09:58:50', '2026-08-13 09:59:24', '2026-08-13 09:58:50', '2026-08-13 09:59:24'),
(136, 'RVM-ZG3MPNAQUN9F', 5, 1, 'completed', 435, 542, 107, 4, '2026-08-13 10:00:19', '2026-08-13 10:21:22', '2026-08-13 10:00:19', '2026-08-13 10:21:22'),
(137, 'RVM-M0O87KOFRGXB', 5, 1, 'completed', 542, 589, 47, 1, '2026-08-13 10:23:07', '2026-08-13 10:23:40', '2026-08-13 10:23:07', '2026-08-13 10:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `rvm_machines`
--

CREATE TABLE `rvm_machines` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `aluminum_level` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `plastic_level` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `glass_level` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `paper_level` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rvm_machines`
--

INSERT INTO `rvm_machines` (`id`, `machine_code`, `name`, `location_name`, `latitude`, `longitude`, `status`, `aluminum_level`, `plastic_level`, `glass_level`, `paper_level`, `created_at`, `updated_at`) VALUES
(1, 'RVM-001', 'RVM UMPSA Gambang', 'Main Lobby', '3.7225406', '103.1206398', 'active', 0, 0, 61, 90, '2026-05-06 20:42:50', '2026-08-13 10:23:39'),
(3, 'RVM-002', 'RVM UMPSA Pekan', '2nd Floor', '3.5434789', '103.4287399', 'active', 2, 2, 32, 16, '2026-05-09 23:14:30', '2026-05-11 17:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `material_selected` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ai_detected_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ai_confidence` decimal(5,4) DEFAULT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '0',
  `weight_grams` int NOT NULL DEFAULT '0',
  `points_earned` int NOT NULL DEFAULT '0',
  `points_deducted` int NOT NULL DEFAULT '0',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `session_id`, `user_id`, `machine_id`, `material_selected`, `ai_detected_type`, `ai_confidence`, `is_valid`, `weight_grams`, `points_earned`, `points_deducted`, `image_path`, `created_at`, `updated_at`) VALUES
(15, 18, 3, 1, 'glass', 'glass', '0.9800', 1, 440, 26, 0, NULL, '2026-05-07 22:29:56', '2026-05-07 22:29:56'),
(16, 21, 1, 1, 'aluminum', 'aluminum', '0.7500', 1, 301, 30, 0, NULL, '2026-05-07 22:34:16', '2026-05-07 22:34:16'),
(17, 21, 1, 1, 'plastic', 'glass', '0.7600', 0, 0, 0, 10, NULL, '2026-05-07 22:34:41', '2026-05-07 22:34:41'),
(18, 22, 1, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-07 23:05:19', '2026-05-07 23:05:19'),
(19, 22, 1, 1, 'plastic', 'plastic', '0.4121', 1, 291, 23, 0, NULL, '2026-05-07 23:05:44', '2026-05-07 23:05:44'),
(20, 22, 1, 1, 'aluminum', 'aluminum', '0.5052', 1, 462, 46, 0, NULL, '2026-05-07 23:06:10', '2026-05-07 23:06:10'),
(21, 22, 1, 1, 'glass', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-07 23:06:42', '2026-05-07 23:06:42'),
(22, 22, 1, 1, 'glass', 'glass', '0.9223', 1, 423, 25, 0, NULL, '2026-05-07 23:07:34', '2026-05-07 23:07:34'),
(23, 22, 1, 1, 'aluminum', 'aluminum', '0.9352', 1, 496, 49, 0, NULL, '2026-05-07 23:09:36', '2026-05-07 23:09:36'),
(24, 22, 1, 1, 'plastic', 'plastic', '0.9145', 1, 249, 19, 0, NULL, '2026-05-07 23:11:04', '2026-05-07 23:11:04'),
(25, 22, 1, 1, 'plastic', 'plastic', '0.9144', 1, 54, 4, 0, NULL, '2026-05-07 23:11:25', '2026-05-07 23:11:25'),
(26, 22, 1, 1, 'glass', 'glass', '0.7439', 1, 60, 3, 0, NULL, '2026-05-07 23:11:55', '2026-05-07 23:11:55'),
(27, 22, 1, 1, 'paper', 'paper', '0.8485', 1, 437, 21, 0, NULL, '2026-05-07 23:12:21', '2026-05-07 23:12:21'),
(28, 22, 1, 1, 'paper', 'paper', '0.8823', 1, 165, 8, 0, NULL, '2026-05-07 23:12:43', '2026-05-07 23:12:43'),
(29, 22, 1, 1, 'paper', 'paper', '0.8809', 1, 400, 20, 0, NULL, '2026-05-07 23:13:03', '2026-05-07 23:13:03'),
(30, 22, 1, 1, 'paper', 'paper', '0.8605', 1, 64, 3, 0, NULL, '2026-05-07 23:13:25', '2026-05-07 23:13:25'),
(31, 22, 1, 1, 'aluminum', 'plastic', '0.5349', 0, 0, 0, 10, NULL, '2026-05-07 23:16:47', '2026-05-07 23:16:47'),
(32, 22, 1, 1, 'plastic', 'paper', '0.9073', 0, 0, 0, 10, NULL, '2026-05-07 23:17:09', '2026-05-07 23:17:09'),
(33, 22, 1, 1, 'glass', 'paper', '0.9190', 0, 0, 0, 10, NULL, '2026-05-07 23:18:27', '2026-05-07 23:18:27'),
(34, 23, 1, 1, 'aluminum', 'paper', '0.9157', 0, 0, 0, 10, NULL, '2026-05-07 23:33:53', '2026-05-07 23:33:53'),
(35, 23, 1, 1, 'paper', 'paper', '0.8980', 1, 304, 15, 0, NULL, '2026-05-07 23:35:15', '2026-05-07 23:35:15'),
(36, 25, 4, 1, 'aluminum', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 08:00:07', '2026-05-09 08:00:07'),
(37, 25, 4, 1, 'aluminum', 'plastic', '0.6819', 0, 0, 0, 10, NULL, '2026-05-09 08:00:32', '2026-05-09 08:00:32'),
(38, 30, 1, 1, 'glass', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 09:16:21', '2026-05-09 09:16:21'),
(39, 31, 1, 1, 'aluminum', 'plastic', '0.4605', 0, 0, 0, 10, NULL, '2026-05-09 10:17:52', '2026-05-09 10:17:52'),
(40, 31, 1, 1, 'aluminum', 'glass', '0.3195', 0, 0, 0, 10, NULL, '2026-05-09 10:18:21', '2026-05-09 10:18:21'),
(41, 32, 1, 1, 'aluminum', 'plastic', '0.4344', 0, 0, 0, 10, NULL, '2026-05-09 10:19:02', '2026-05-09 10:19:02'),
(42, 32, 1, 1, 'aluminum', 'plastic', '0.7072', 0, 0, 0, 10, NULL, '2026-05-09 10:19:22', '2026-05-09 10:19:22'),
(43, 32, 1, 1, 'plastic', 'plastic', '0.9204', 1, 356, 28, 0, NULL, '2026-05-09 10:19:44', '2026-05-09 10:19:44'),
(44, 33, 1, 1, 'plastic', 'plastic', '0.4754', 1, 128, 10, 0, NULL, '2026-05-09 10:20:33', '2026-05-09 10:20:33'),
(45, 35, 1, 1, 'aluminum', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 10:27:00', '2026-05-09 10:27:00'),
(46, 35, 1, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 10:27:20', '2026-05-09 10:27:20'),
(47, 35, 1, 1, 'plastic', 'plastic', '0.3451', 1, 258, 20, 0, NULL, '2026-05-09 10:27:40', '2026-05-09 10:27:40'),
(48, 35, 1, 1, 'plastic', 'paper', '0.7177', 0, 0, 0, 10, NULL, '2026-05-09 10:29:47', '2026-05-09 10:29:47'),
(49, 35, 1, 1, 'paper', 'plastic', '0.7985', 0, 0, 0, 10, NULL, '2026-05-09 10:30:25', '2026-05-09 10:30:25'),
(50, 37, 1, 1, 'glass', 'aluminum', '0.7245', 0, 0, 0, 10, NULL, '2026-05-09 11:05:26', '2026-05-09 11:05:26'),
(51, 37, 1, 1, 'plastic', 'aluminum', '0.8145', 0, 0, 0, 10, NULL, '2026-05-09 11:05:49', '2026-05-09 11:05:49'),
(52, 37, 1, 1, 'aluminum', 'aluminum', '0.5335', 1, 188, 18, 0, NULL, '2026-05-09 11:06:12', '2026-05-09 11:06:12'),
(53, 38, 1, 1, 'paper', 'plastic', '0.5380', 0, 0, 0, 10, NULL, '2026-05-09 11:06:41', '2026-05-09 11:06:41'),
(54, 38, 1, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:49:13', '2026-05-09 18:49:13'),
(55, 38, 1, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:50:29', '2026-05-09 18:50:29'),
(56, 38, 1, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:51:08', '2026-05-09 18:51:08'),
(57, 39, 4, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:52:28', '2026-05-09 18:52:28'),
(58, 39, 4, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:53:15', '2026-05-09 18:53:15'),
(59, 39, 4, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:53:34', '2026-05-09 18:53:34'),
(60, 40, 4, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:57:04', '2026-05-09 18:57:04'),
(61, 40, 4, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 18:57:23', '2026-05-09 18:57:23'),
(62, 20, 3, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 19:27:50', '2026-05-09 19:27:50'),
(63, 20, 3, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 19:28:37', '2026-05-09 19:28:37'),
(64, 20, 3, 1, 'plastic', 'aluminum', '0.5115', 0, 0, 0, 10, NULL, '2026-05-09 19:28:56', '2026-05-09 19:28:56'),
(65, 20, 3, 1, 'plastic', 'plastic', '0.7577', 1, 483, 38, 0, NULL, '2026-05-09 19:29:22', '2026-05-09 19:29:22'),
(66, 41, 3, 1, 'plastic', 'plastic', '0.7494', 1, 408, 32, 0, NULL, '2026-05-09 19:31:06', '2026-05-09 19:31:06'),
(67, 42, 3, 1, 'plastic', 'plastic', '0.6003', 1, 355, 28, 0, NULL, '2026-05-09 19:44:59', '2026-05-09 19:44:59'),
(68, 42, 3, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 19:45:29', '2026-05-09 19:45:29'),
(69, 42, 3, 1, 'plastic', 'plastic', '0.6357', 1, 401, 32, 0, NULL, '2026-05-09 19:46:13', '2026-05-09 19:46:13'),
(70, 43, 3, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 20:05:31', '2026-05-09 20:05:31'),
(71, 43, 3, 1, 'plastic', 'plastic', '0.6123', 1, 230, 18, 0, NULL, '2026-05-09 20:05:53', '2026-05-09 20:05:53'),
(72, 44, 3, 1, 'plastic', 'plastic', '0.7468', 1, 239, 19, 0, NULL, '2026-05-09 20:21:32', '2026-05-09 20:21:32'),
(73, 44, 3, 1, 'plastic', 'plastic', '0.7722', 1, 98, 7, 0, NULL, '2026-05-09 20:22:04', '2026-05-09 20:22:04'),
(74, 45, 3, 1, 'plastic', 'plastic', '0.5659', 1, 463, 37, 0, NULL, '2026-05-09 20:39:15', '2026-05-09 20:39:15'),
(75, 48, 3, 1, 'plastic', 'plastic', '0.5269', 1, 64, 5, 0, NULL, '2026-05-09 20:53:09', '2026-05-09 20:53:09'),
(76, 51, 3, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 21:49:12', '2026-05-09 21:49:12'),
(77, 51, 3, 1, 'plastic', 'plastic', '0.8756', 1, 269, 21, 0, NULL, '2026-05-09 21:49:33', '2026-05-09 21:49:33'),
(78, 53, 3, 1, 'plastic', 'plastic', '0.8774', 1, 294, 23, 0, NULL, '2026-05-09 21:51:12', '2026-05-09 21:51:12'),
(79, 54, 3, 1, 'glass', 'plastic', '0.8675', 0, 0, 0, 10, NULL, '2026-05-09 21:52:32', '2026-05-09 21:52:32'),
(80, 38, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 21:55:13', '2026-05-09 21:55:13'),
(81, 38, 1, 1, 'paper', 'plastic', '0.8618', 0, 0, 0, 10, NULL, '2026-05-09 21:55:33', '2026-05-09 21:55:33'),
(82, 55, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:01:44', '2026-05-09 22:01:44'),
(83, 55, 1, 1, 'paper', 'aluminum', '0.5024', 0, 0, 0, 10, NULL, '2026-05-09 22:02:02', '2026-05-09 22:02:02'),
(84, 56, 1, 1, 'paper', 'plastic', '0.5057', 0, 0, 0, 10, NULL, '2026-05-09 22:02:39', '2026-05-09 22:02:39'),
(85, 57, 1, 1, 'paper', 'plastic', '0.8470', 0, 0, 0, 10, NULL, '2026-05-09 22:03:43', '2026-05-09 22:03:43'),
(86, 58, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:09:30', '2026-05-09 22:09:30'),
(87, 58, 1, 1, 'paper', 'plastic', '0.8612', 0, 0, 0, 10, NULL, '2026-05-09 22:09:52', '2026-05-09 22:09:52'),
(88, 58, 1, 1, 'paper', 'paper', '0.3293', 1, 420, 105, 0, NULL, '2026-05-09 22:10:17', '2026-05-09 22:10:17'),
(89, 58, 1, 1, 'paper', 'plastic', '0.8995', 0, 0, 0, 10, NULL, '2026-05-09 22:11:25', '2026-05-09 22:11:25'),
(90, 58, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:11:55', '2026-05-09 22:11:55'),
(91, 58, 1, 1, 'paper', 'aluminum', '0.8952', 0, 0, 0, 10, NULL, '2026-05-09 22:12:15', '2026-05-09 22:12:15'),
(92, 58, 1, 1, 'paper', 'aluminum', '0.5094', 0, 0, 0, 10, NULL, '2026-05-09 22:12:37', '2026-05-09 22:12:37'),
(93, 58, 1, 1, 'aluminum', 'aluminum', '0.8760', 1, 151, 83, 0, NULL, '2026-05-09 22:13:02', '2026-05-09 22:13:02'),
(94, 58, 1, 1, 'glass', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:15:44', '2026-05-09 22:15:44'),
(95, 58, 1, 1, 'aluminum', 'aluminum', '0.7568', 1, 100, 55, 0, NULL, '2026-05-09 22:16:06', '2026-05-09 22:16:06'),
(96, 58, 1, 1, 'aluminum', 'aluminum', '0.8165', 1, 100, 55, 0, NULL, '2026-05-09 22:16:34', '2026-05-09 22:16:34'),
(97, 58, 1, 1, 'paper', 'plastic', '0.5509', 0, 0, 0, 10, NULL, '2026-05-09 22:18:35', '2026-05-09 22:18:35'),
(98, 58, 1, 1, 'aluminum', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:19:04', '2026-05-09 22:19:04'),
(99, 58, 1, 1, 'aluminum', 'plastic', '0.9048', 0, 0, 0, 10, NULL, '2026-05-09 22:19:26', '2026-05-09 22:19:26'),
(100, 58, 1, 1, 'aluminum', 'paper', '0.8523', 0, 0, 0, 10, NULL, '2026-05-09 22:19:49', '2026-05-09 22:19:49'),
(101, 58, 1, 1, 'paper', 'paper', '0.8792', 1, 249, 37, 0, NULL, '2026-05-09 22:20:11', '2026-05-09 22:20:11'),
(102, 58, 1, 1, 'glass', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:20:48', '2026-05-09 22:20:48'),
(103, 58, 1, 1, 'glass', 'plastic', '0.2709', 0, 0, 0, 10, NULL, '2026-05-09 22:21:08', '2026-05-09 22:21:08'),
(104, 58, 1, 1, 'aluminum', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:21:28', '2026-05-09 22:21:28'),
(105, 58, 1, 1, 'aluminum', 'plastic', '0.4205', 0, 0, 0, 10, NULL, '2026-05-09 22:21:49', '2026-05-09 22:21:49'),
(106, 58, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:22:18', '2026-05-09 22:22:18'),
(107, 58, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-09 22:22:38', '2026-05-09 22:22:38'),
(108, 60, 1, 3, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 07:32:42', '2026-05-10 07:32:42'),
(109, 60, 1, 3, 'plastic', 'plastic', '0.8611', 1, 98, 10, 0, NULL, '2026-05-10 07:33:04', '2026-05-10 07:33:04'),
(110, 61, 1, 1, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 07:41:40', '2026-05-10 07:41:40'),
(111, 62, 1, 1, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 07:44:30', '2026-05-10 07:44:30'),
(112, 64, 1, 1, 'plastic', 'plastic', '0.8135', 1, 77, 10, 0, NULL, '2026-05-10 07:46:17', '2026-05-10 07:46:17'),
(113, 68, 1, 1, 'paper', 'plastic', '0.7736', 0, 0, 0, 10, NULL, '2026-05-10 10:56:54', '2026-05-10 10:56:54'),
(114, 69, 1, 3, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 12:58:32', '2026-05-10 12:58:32'),
(115, 69, 1, 3, 'plastic', 'glass', '0.3662', 0, 0, 0, 10, NULL, '2026-05-10 12:58:52', '2026-05-10 12:58:52'),
(116, 69, 1, 3, 'plastic', 'aluminum', '0.4471', 0, 0, 0, 10, NULL, '2026-05-10 12:59:13', '2026-05-10 12:59:13'),
(117, 69, 1, 3, 'paper', 'aluminum', '0.7428', 0, 0, 0, 10, NULL, '2026-05-10 13:00:40', '2026-05-10 13:00:40'),
(118, 69, 1, 3, 'aluminum', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:01:12', '2026-05-10 13:01:12'),
(119, 69, 1, 3, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:01:34', '2026-05-10 13:01:34'),
(120, 69, 1, 3, 'plastic', 'aluminum', '0.7759', 0, 0, 0, 10, NULL, '2026-05-10 13:01:55', '2026-05-10 13:01:55'),
(121, 69, 1, 3, 'aluminum', 'aluminum', '0.3669', 1, 33, 10, 0, NULL, '2026-05-10 13:02:20', '2026-05-10 13:02:20'),
(122, 69, 1, 3, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:03:55', '2026-05-10 13:03:55'),
(123, 69, 1, 3, 'paper', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:04:15', '2026-05-10 13:04:15'),
(124, 69, 1, 3, 'aluminum', 'aluminum', '0.8620', 1, 79, 10, 0, NULL, '2026-05-10 13:12:04', '2026-05-10 13:12:04'),
(125, 69, 1, 3, 'paper', 'paper', '0.6932', 1, 163, 5, 0, NULL, '2026-05-10 13:12:29', '2026-05-10 13:12:29'),
(126, 69, 1, 3, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:13:02', '2026-05-10 13:13:02'),
(127, 69, 1, 3, 'glass', 'plastic', '0.7710', 0, 0, 0, 10, NULL, '2026-05-10 13:13:22', '2026-05-10 13:13:22'),
(128, 69, 1, 3, 'glass', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:13:50', '2026-05-10 13:13:50'),
(129, 69, 1, 3, 'plastic', 'glass', '0.8697', 0, 0, 0, 10, NULL, '2026-05-10 13:14:34', '2026-05-10 13:14:34'),
(130, 69, 1, 3, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:20:45', '2026-05-10 13:20:45'),
(131, 69, 1, 3, 'plastic', 'aluminum', '0.4302', 0, 0, 0, 10, NULL, '2026-05-10 13:21:07', '2026-05-10 13:21:07'),
(132, 69, 1, 3, 'plastic', 'plastic', '0.9403', 1, 96, 10, 0, NULL, '2026-05-10 13:22:18', '2026-05-10 13:22:18'),
(133, 69, 1, 3, 'paper', 'paper', '0.9586', 1, 164, 5, 0, NULL, '2026-05-10 13:22:56', '2026-05-10 13:22:56'),
(134, 69, 1, 3, 'aluminum', 'aluminum', '0.9462', 1, 60, 10, 0, NULL, '2026-05-10 13:23:18', '2026-05-10 13:23:18'),
(135, 69, 1, 3, 'glass', 'glass', '0.9539', 1, 56, 5, 0, NULL, '2026-05-10 13:23:48', '2026-05-10 13:23:48'),
(136, 69, 1, 3, 'plastic', 'unknown', '0.0000', 0, 0, 0, 10, NULL, '2026-05-10 13:24:19', '2026-05-10 13:24:19'),
(137, 69, 1, 3, 'paper', 'plastic', '0.9320', 0, 0, 0, 10, NULL, '2026-05-10 13:24:42', '2026-05-10 13:24:42'),
(138, 70, 1, 1, 'plastic', 'plastic', '0.9440', 1, 78, 10, 0, NULL, '2026-05-10 13:30:13', '2026-05-10 13:30:13'),
(139, 70, 1, 1, 'plastic', 'plastic', '0.9514', 1, 30, 10, 0, NULL, '2026-05-10 13:30:39', '2026-05-10 13:30:39'),
(140, 70, 1, 1, 'paper', 'paper', '0.9157', 1, 195, 5, 0, NULL, '2026-05-10 13:31:03', '2026-05-10 13:31:03'),
(141, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 42, 10, 0, NULL, '2026-05-10 13:46:03', '2026-05-10 13:46:03'),
(142, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 74, 10, 0, NULL, '2026-05-10 13:46:24', '2026-05-10 13:46:24'),
(143, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 57, 10, 0, NULL, '2026-05-10 13:47:33', '2026-05-10 13:47:33'),
(144, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 66, 10, 0, NULL, '2026-05-10 13:49:06', '2026-05-10 13:49:06'),
(145, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 30, 10, 0, NULL, '2026-05-10 13:49:48', '2026-05-10 13:49:48'),
(146, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 35, 10, 0, NULL, '2026-05-10 13:50:28', '2026-05-10 13:50:28'),
(147, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 47, 10, 0, NULL, '2026-05-10 13:50:56', '2026-05-10 13:50:56'),
(148, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 83, 10, 0, NULL, '2026-05-10 13:55:38', '2026-05-10 13:55:38'),
(149, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 29, 10, 0, NULL, '2026-05-10 13:56:01', '2026-05-10 13:56:01'),
(150, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 62, 10, 0, NULL, '2026-05-10 13:56:21', '2026-05-10 13:56:21'),
(151, 70, 1, 1, 'plastic', 'plastic', '0.0000', 1, 40, 10, 0, NULL, '2026-05-10 13:56:40', '2026-05-10 13:56:40'),
(152, 70, 1, 1, 'paper', 'paper', '0.9157', 1, 428, 5, 0, NULL, '2026-05-10 14:04:43', '2026-05-10 14:04:43'),
(153, 70, 1, 1, 'glass', 'glass', '0.9335', 1, 83, 5, 0, NULL, '2026-05-10 14:05:21', '2026-05-10 14:05:21'),
(154, 70, 1, 1, 'plastic', 'plastic', '0.9453', 1, 31, 10, 0, NULL, '2026-05-10 14:05:44', '2026-05-10 14:05:44'),
(155, 70, 1, 1, 'glass', 'glass', '0.9632', 1, 17, 5, 0, NULL, '2026-05-10 14:06:06', '2026-05-10 14:06:06'),
(156, 70, 1, 1, 'plastic', 'plastic', '0.8458', 1, 22, 10, 0, NULL, '2026-05-10 14:06:33', '2026-05-10 14:06:33'),
(157, 71, 1, 1, 'plastic', 'plastic', '0.5183', 1, 94, 10, 0, NULL, '2026-05-10 14:08:40', '2026-05-10 14:08:40'),
(158, 71, 1, 1, 'aluminum', 'aluminum', '0.9462', 1, 47, 10, 0, NULL, '2026-05-10 14:09:13', '2026-05-10 14:09:13'),
(159, 71, 1, 1, 'aluminum', 'aluminum', '0.9444', 1, 18, 10, 0, NULL, '2026-05-10 14:11:29', '2026-05-10 14:11:29'),
(160, 71, 1, 1, 'aluminum', 'aluminum', '0.9462', 1, 27, 10, 0, NULL, '2026-05-10 14:15:46', '2026-05-10 14:15:46'),
(161, 71, 1, 1, 'paper', 'paper', '0.9157', 1, 396, 39, 0, NULL, '2026-05-10 14:16:22', '2026-05-10 14:16:22'),
(162, 71, 1, 1, 'paper', 'paper', '0.9157', 1, 140, 14, 0, NULL, '2026-05-10 14:16:57', '2026-05-10 14:16:57'),
(163, 71, 1, 1, 'glass', 'glass', '0.9632', 1, 475, 235, 0, NULL, '2026-05-10 14:17:21', '2026-05-10 14:17:21'),
(164, 71, 1, 1, 'glass', 'glass', '0.9648', 1, 450, 90, 0, NULL, '2026-05-10 14:18:13', '2026-05-10 14:18:13'),
(165, 71, 1, 1, 'glass', 'glass', '0.9531', 1, 277, 27, 0, NULL, '2026-05-10 14:18:36', '2026-05-10 14:18:36'),
(166, 71, 1, 1, 'plastic', 'plastic', '0.9384', 1, 36, 15, 0, NULL, '2026-05-10 14:19:05', '2026-05-10 14:19:05'),
(167, 71, 1, 1, 'glass', 'glass', '0.9228', 1, 83, 8, 0, NULL, '2026-05-10 14:21:39', '2026-05-10 14:21:39'),
(168, 71, 1, 1, 'glass', 'glass', '0.9335', 1, 94, 9, 0, NULL, '2026-05-10 14:29:44', '2026-05-10 14:29:44'),
(169, 71, 1, 1, 'aluminum', 'aluminum', '0.9286', 1, 43, 28, 0, NULL, '2026-05-10 14:30:07', '2026-05-10 14:30:07'),
(170, 71, 1, 1, 'plastic', 'plastic', '0.8143', 1, 48, 20, 0, NULL, '2026-05-10 14:30:31', '2026-05-10 14:30:31'),
(171, 71, 1, 1, 'paper', 'paper', '0.8621', 1, 224, 22, 0, NULL, '2026-05-10 14:31:57', '2026-05-10 14:31:57'),
(172, 71, 1, 1, 'aluminum', 'aluminum', '0.3791', 1, 20, 14, 0, NULL, '2026-05-10 14:32:34', '2026-05-10 14:32:34'),
(173, 71, 1, 1, 'plastic', 'plastic', '0.9384', 1, 9, 0, 0, NULL, '2026-05-10 14:33:12', '2026-05-10 14:33:12'),
(174, 71, 1, 1, 'glass', 'glass', '0.9335', 1, 176, 17, 0, NULL, '2026-05-10 14:33:35', '2026-05-10 14:33:35'),
(175, 71, 1, 1, 'paper', 'paper', '0.9114', 1, 65, 6, 0, NULL, '2026-05-10 14:34:09', '2026-05-10 14:34:09'),
(176, 71, 1, 1, 'paper', 'paper', '0.9287', 1, 305, 30, 0, NULL, '2026-05-10 14:34:41', '2026-05-10 14:34:41'),
(177, 71, 1, 1, 'glass', 'glass', '0.7963', 1, 475, 47, 0, NULL, '2026-05-10 14:35:03', '2026-05-10 14:35:03'),
(178, 71, 1, 1, 'glass', 'glass', '0.9244', 1, 447, 44, 0, NULL, '2026-05-10 14:35:27', '2026-05-10 14:35:27'),
(179, 71, 1, 1, 'aluminum', 'aluminum', '0.9247', 1, 41, 28, 0, NULL, '2026-05-10 14:35:55', '2026-05-10 14:35:55'),
(180, 71, 1, 1, 'paper', 'paper', '0.9835', 1, 297, 29, 0, NULL, '2026-05-10 14:36:24', '2026-05-10 14:36:24'),
(181, 71, 1, 1, 'aluminum', 'aluminum', '0.9539', 1, 15, 7, 0, NULL, '2026-05-10 14:39:26', '2026-05-10 14:39:26'),
(182, 71, 1, 1, 'paper', 'paper', '0.9157', 1, 168, 16, 0, NULL, '2026-05-10 14:43:59', '2026-05-10 14:43:59'),
(183, 71, 1, 1, 'glass', 'glass', '0.9335', 1, 382, 38, 0, NULL, '2026-05-10 14:47:22', '2026-05-10 14:47:22'),
(184, 71, 1, 1, 'paper', 'paper', '0.9361', 1, 356, 35, 0, NULL, '2026-05-10 14:51:41', '2026-05-10 14:51:41'),
(185, 71, 1, 1, 'paper', 'paper', '0.8621', 1, 117, 11, 0, NULL, '2026-05-10 14:52:42', '2026-05-10 14:52:42'),
(186, 71, 1, 1, 'aluminum', 'aluminum', '0.9462', 1, 26, 14, 0, NULL, '2026-05-10 14:53:12', '2026-05-10 14:53:12'),
(187, 72, 1, 1, 'glass', 'glass', '0.9335', 1, 79, 7, 0, NULL, '2026-05-10 16:20:00', '2026-05-10 16:20:00'),
(188, 40, 4, 1, 'paper', 'paper', '0.9157', 1, 75, 7, 0, NULL, '2026-05-10 16:21:38', '2026-05-10 16:21:38'),
(189, 73, 4, 1, 'paper', 'paper', '0.9114', 1, 336, 33, 0, NULL, '2026-05-10 16:23:48', '2026-05-10 16:23:48'),
(190, 74, 4, 1, 'paper', 'paper', '0.9157', 1, 252, 25, 0, NULL, '2026-05-10 16:24:57', '2026-05-10 16:24:57'),
(191, 75, 4, 1, 'aluminum', 'aluminum', '0.9325', 1, 20, 14, 0, NULL, '2026-05-10 16:26:39', '2026-05-10 16:26:39'),
(192, 76, 4, 1, 'aluminum', 'aluminum', '0.9477', 1, 28, 14, 0, NULL, '2026-05-10 16:27:45', '2026-05-10 16:27:45'),
(193, 77, 4, 1, 'aluminum', 'aluminum', '0.9539', 1, 20, 14, 0, NULL, '2026-05-10 16:30:56', '2026-05-10 16:30:56'),
(194, 78, 4, 1, 'aluminum', 'aluminum', '0.9477', 1, 44, 28, 0, NULL, '2026-05-10 16:37:01', '2026-05-10 16:37:01'),
(195, 79, 4, 1, 'paper', 'paper', '0.8131', 1, 377, 37, 0, NULL, '2026-05-10 16:38:03', '2026-05-10 16:38:03'),
(196, 80, 1, 1, 'plastic', 'plastic', '0.6332', 1, 45, 20, 0, NULL, '2026-05-11 09:36:30', '2026-05-11 09:36:30'),
(197, 80, 1, 1, 'glass', 'glass', '0.8152', 1, 294, 29, 0, NULL, '2026-05-11 09:36:56', '2026-05-11 09:36:56'),
(198, 80, 1, 1, 'aluminum', 'aluminum', '0.9462', 1, 33, 21, 0, NULL, '2026-05-11 09:37:19', '2026-05-11 09:37:19'),
(199, 80, 1, 1, 'glass', 'glass', '0.8449', 1, 157, 15, 0, NULL, '2026-05-11 09:38:44', '2026-05-11 09:38:44'),
(200, 81, 6, 1, 'aluminum', 'aluminum', '0.6278', 1, 43, 28, 0, NULL, '2026-05-11 09:51:06', '2026-05-11 09:51:06'),
(201, 81, 6, 1, 'aluminum', 'aluminum', '0.9519', 1, 26, 14, 0, NULL, '2026-05-11 09:55:26', '2026-05-11 09:55:26'),
(202, 83, 1, 1, 'glass', 'glass', '0.9455', 1, 334, 33, 0, NULL, '2026-05-11 10:01:34', '2026-05-11 10:01:34'),
(203, 84, 3, 1, 'aluminum', 'aluminum', '0.9149', 1, 42, 28, 0, NULL, '2026-05-11 10:11:18', '2026-05-11 10:11:18'),
(204, 85, 1, 1, 'paper', 'paper', '0.9157', 1, 78, 7, 0, NULL, '2026-05-11 10:15:59', '2026-05-11 10:15:59'),
(205, 85, 1, 1, 'aluminum', 'aluminum', '0.9462', 1, 12, 7, 0, NULL, '2026-05-11 10:16:23', '2026-05-11 10:16:23'),
(206, 85, 1, 1, 'glass', 'glass', '0.9228', 1, 416, 41, 0, NULL, '2026-05-11 10:17:20', '2026-05-11 10:17:20'),
(207, 85, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 24, 14, 0, NULL, '2026-05-11 10:17:50', '2026-05-11 10:17:50'),
(208, 85, 1, 1, 'plastic', 'plastic', '0.9399', 1, 12, 5, 0, NULL, '2026-05-11 10:18:40', '2026-05-11 10:18:40'),
(209, 85, 1, 1, 'aluminum', 'aluminum', '0.9462', 1, 49, 28, 0, NULL, '2026-05-11 10:19:06', '2026-05-11 10:19:06'),
(210, 86, 4, 1, 'paper', 'paper', '0.9104', 1, 453, 45, 0, NULL, '2026-05-11 14:56:57', '2026-05-11 14:56:57'),
(211, 86, 4, 1, 'plastic', 'plastic', '0.9222', 1, 21, 10, 0, NULL, '2026-05-11 14:58:07', '2026-05-11 14:58:07'),
(212, 86, 4, 1, 'plastic', 'plastic', '0.9222', 1, 47, 20, 0, NULL, '2026-05-11 14:58:33', '2026-05-11 14:58:33'),
(213, 86, 4, 1, 'aluminum', 'aluminum', '0.9291', 1, 19, 7, 0, NULL, '2026-05-11 14:59:37', '2026-05-11 14:59:37'),
(214, 86, 4, 1, 'plastic', 'plastic', '0.9306', 1, 15, 5, 0, NULL, '2026-05-11 15:00:27', '2026-05-11 15:00:27'),
(215, 86, 4, 1, 'plastic', 'plastic', '0.9384', 1, 32, 15, 0, NULL, '2026-05-11 15:01:09', '2026-05-11 15:01:09'),
(216, 86, 4, 1, 'aluminum', 'aluminum', '0.9209', 1, 40, 28, 0, NULL, '2026-05-11 15:01:48', '2026-05-11 15:01:48'),
(217, 86, 4, 1, 'aluminum', 'aluminum', '0.9209', 1, 45, 28, 0, NULL, '2026-05-11 15:02:20', '2026-05-11 15:02:20'),
(218, 86, 4, 1, 'paper', 'paper', '0.9287', 1, 415, 41, 0, NULL, '2026-05-11 15:03:58', '2026-05-11 15:03:58'),
(219, 86, 4, 1, 'glass', 'glass', '0.6073', 1, 75, 7, 0, NULL, '2026-05-11 15:05:42', '2026-05-11 15:05:42'),
(220, 86, 4, 1, 'glass', 'glass', '0.7996', 1, 171, 17, 0, NULL, '2026-05-11 15:06:36', '2026-05-11 15:06:36'),
(221, 86, 4, 1, 'glass', 'glass', '0.9531', 1, 342, 34, 0, NULL, '2026-05-11 15:07:10', '2026-05-11 15:07:10'),
(222, 86, 4, 1, 'glass', 'glass', '0.9588', 1, 475, 47, 0, NULL, '2026-05-11 15:07:58', '2026-05-11 15:07:58'),
(223, 86, 4, 1, 'glass', 'glass', '0.9057', 1, 131, 13, 0, NULL, '2026-05-11 15:09:59', '2026-05-11 15:09:59'),
(224, 86, 4, 1, 'plastic', 'plastic', '0.9145', 1, 21, 10, 0, NULL, '2026-05-11 15:10:22', '2026-05-11 15:10:22'),
(225, 86, 4, 1, 'paper', 'paper', '0.9719', 1, 473, 47, 0, NULL, '2026-05-11 15:10:46', '2026-05-11 15:10:46'),
(226, 86, 4, 1, 'aluminum', 'aluminum', '0.9577', 1, 29, 14, 0, NULL, '2026-05-11 15:11:13', '2026-05-11 15:11:13'),
(227, 87, 6, 1, 'paper', 'paper', '0.9104', 1, 375, 37, 0, NULL, '2026-05-11 15:16:20', '2026-05-11 15:16:20'),
(228, 87, 6, 1, 'aluminum', 'aluminum', '0.9291', 1, 34, 21, 0, NULL, '2026-05-11 15:18:42', '2026-05-11 15:18:42'),
(229, 94, 1, 1, 'plastic', 'plastic', '0.9306', 1, 18, 5, 0, NULL, '2026-05-11 15:58:08', '2026-05-11 15:58:08'),
(230, 93, 6, 1, 'aluminum', 'aluminum', '0.9291', 1, 18, 7, 0, NULL, '2026-05-11 16:04:20', '2026-05-11 16:04:20'),
(231, 95, 8, 1, 'plastic', 'plastic', '0.9222', 1, 49, 20, 0, NULL, '2026-05-11 16:13:41', '2026-05-11 16:13:41'),
(232, 97, 8, 1, 'plastic', 'plastic', '0.9306', 1, 26, 10, 0, NULL, '2026-05-11 16:16:33', '2026-05-11 16:16:33'),
(233, 96, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 43, 28, 0, NULL, '2026-05-11 16:47:18', '2026-05-11 16:47:18'),
(234, 96, 1, 1, 'paper', 'paper', '0.9287', 1, 365, 36, 0, NULL, '2026-05-11 16:49:36', '2026-05-11 16:49:36'),
(235, 98, 1, 1, 'glass', 'glass', '0.9588', 1, 183, 18, 0, NULL, '2026-05-11 16:53:50', '2026-05-11 16:53:50'),
(236, 99, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 49, 28, 0, NULL, '2026-05-11 16:55:12', '2026-05-11 16:55:12'),
(237, 99, 1, 1, 'glass', 'glass', '0.9057', 1, 261, 26, 0, NULL, '2026-05-11 16:55:43', '2026-05-11 16:55:43'),
(238, 99, 1, 1, 'paper', 'paper', '0.9104', 1, 475, 47, 0, NULL, '2026-05-11 16:56:13', '2026-05-11 16:56:13'),
(239, 100, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 23, 14, 0, NULL, '2026-05-11 16:57:34', '2026-05-11 16:57:34'),
(240, 100, 1, 1, 'glass', 'glass', '0.9531', 1, 135, 13, 0, NULL, '2026-05-11 16:58:08', '2026-05-11 16:58:08'),
(241, 100, 1, 1, 'glass', 'glass', '0.9531', 1, 296, 29, 0, NULL, '2026-05-11 16:58:45', '2026-05-11 16:58:45'),
(242, 100, 1, 1, 'plastic', 'plastic', '0.9306', 1, 17, 5, 0, NULL, '2026-05-11 16:59:14', '2026-05-11 16:59:14'),
(243, 101, 1, 3, 'aluminum', 'aluminum', '0.9291', 1, 38, 21, 0, NULL, '2026-05-11 17:27:38', '2026-05-11 17:27:38'),
(244, 101, 1, 3, 'plastic', 'plastic', '0.9306', 1, 37, 15, 0, NULL, '2026-05-11 17:28:01', '2026-05-11 17:28:01'),
(245, 101, 1, 3, 'aluminum', 'aluminum', '0.9577', 1, 45, 28, 0, NULL, '2026-05-11 17:28:25', '2026-05-11 17:28:25'),
(246, 101, 1, 3, 'glass', 'glass', '0.9531', 1, 420, 42, 0, NULL, '2026-05-11 17:28:55', '2026-05-11 17:28:55'),
(247, 101, 1, 3, 'paper', 'paper', '0.9104', 1, 418, 41, 0, NULL, '2026-05-11 17:29:27', '2026-05-11 17:29:27'),
(248, 101, 1, 3, 'paper', 'paper', '0.9287', 1, 146, 14, 0, NULL, '2026-05-11 17:30:21', '2026-05-11 17:30:21'),
(249, 101, 1, 3, 'glass', 'glass', '0.9531', 1, 61, 6, 0, NULL, '2026-05-11 17:31:05', '2026-05-11 17:31:05'),
(250, 101, 1, 3, 'aluminum', 'aluminum', '0.9291', 1, 26, 14, 0, NULL, '2026-05-11 17:32:01', '2026-05-11 17:32:01'),
(251, 101, 1, 3, 'glass', 'glass', '0.9057', 1, 381, 38, 0, NULL, '2026-05-11 17:32:59', '2026-05-11 17:32:59'),
(252, 101, 1, 3, 'glass', 'glass', '0.9057', 1, 420, 42, 0, NULL, '2026-05-11 17:33:24', '2026-05-11 17:33:24'),
(253, 101, 1, 3, 'glass', 'glass', '0.9057', 1, 399, 39, 0, NULL, '2026-05-11 17:33:53', '2026-05-11 17:33:53'),
(254, 102, 1, 1, 'glass', 'glass', '0.9057', 1, 113, 11, 0, NULL, '2026-05-11 17:35:29', '2026-05-11 17:35:29'),
(255, 102, 1, 1, 'plastic', 'plastic', '0.9306', 1, 18, 5, 0, NULL, '2026-05-11 17:36:02', '2026-05-11 17:36:02'),
(256, 102, 1, 1, 'plastic', 'plastic', '0.9222', 1, 20, 10, 0, NULL, '2026-05-11 17:36:31', '2026-05-11 17:36:31'),
(257, 102, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 37, 21, 0, NULL, '2026-05-11 17:37:34', '2026-05-11 17:37:34'),
(258, 102, 1, 1, 'plastic', 'plastic', '0.9222', 1, 15, 5, 0, NULL, '2026-05-11 17:38:02', '2026-05-11 17:38:02'),
(259, 102, 1, 1, 'plastic', 'plastic', '0.9384', 1, 25, 10, 0, NULL, '2026-05-11 17:38:50', '2026-05-11 17:38:50'),
(260, 102, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 45, 28, 0, NULL, '2026-05-11 17:39:16', '2026-05-11 17:39:16'),
(261, 102, 1, 1, 'plastic', 'plastic', '0.9306', 1, 34, 15, 0, NULL, '2026-05-11 17:39:58', '2026-05-11 17:39:58'),
(262, 102, 1, 1, 'plastic', 'plastic', '0.9222', 1, 28, 10, 0, NULL, '2026-05-11 17:40:38', '2026-05-11 17:40:38'),
(263, 102, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 11, 7, 0, NULL, '2026-05-11 17:41:09', '2026-05-11 17:41:09'),
(264, 102, 1, 1, 'paper', 'paper', '0.9287', 1, 330, 33, 0, NULL, '2026-05-11 17:41:34', '2026-05-11 17:41:34'),
(265, 102, 1, 1, 'paper', 'paper', '0.9287', 1, 77, 7, 0, NULL, '2026-05-11 17:42:07', '2026-05-11 17:42:07'),
(266, 102, 1, 1, 'paper', 'paper', '0.9104', 1, 57, 5, 0, NULL, '2026-05-11 17:42:33', '2026-05-11 17:42:33'),
(267, 102, 1, 1, 'paper', 'paper', '0.9104', 1, 72, 7, 0, NULL, '2026-05-11 17:43:00', '2026-05-11 17:43:00'),
(268, 102, 1, 1, 'aluminum', 'aluminum', '0.9209', 1, 34, 21, 0, NULL, '2026-05-11 17:43:27', '2026-05-11 17:43:27'),
(269, 102, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 10, 7, 0, NULL, '2026-05-11 17:43:57', '2026-05-11 17:43:57'),
(270, 102, 1, 1, 'paper', 'paper', '0.9287', 1, 496, 49, 0, NULL, '2026-05-11 17:44:40', '2026-05-11 17:44:40'),
(271, 102, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 45, 28, 0, NULL, '2026-05-11 17:45:07', '2026-05-11 17:45:07'),
(272, 103, 1, 1, 'paper', 'paper', '0.9287', 1, 488, 48, 0, NULL, '2026-05-11 18:30:20', '2026-05-11 18:30:20'),
(273, 104, 1, 1, 'aluminum', 'aluminum', '0.9209', 1, 36, 21, 0, NULL, '2026-05-11 18:36:05', '2026-05-11 18:36:05'),
(274, 105, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 46, 28, 0, NULL, '2026-05-11 18:36:59', '2026-05-11 18:36:59'),
(275, 106, 1, 1, 'glass', 'glass', '0.9588', 1, 182, 18, 0, NULL, '2026-05-11 18:43:42', '2026-05-11 18:43:42'),
(276, 107, 1, 1, 'aluminum', 'aluminum', '0.9291', 1, 16, 7, 0, NULL, '2026-05-11 23:23:28', '2026-05-11 23:23:28'),
(277, 108, 3, 1, 'aluminum', 'aluminum', '0.9291', 1, 28, 14, 0, NULL, '2026-05-11 23:27:37', '2026-05-11 23:27:37'),
(278, 108, 3, 1, 'paper', 'paper', '0.9104', 1, 406, 40, 0, NULL, '2026-05-11 23:28:17', '2026-05-11 23:28:17'),
(279, 108, 3, 1, 'plastic', 'plastic', '0.9306', 1, 35, 15, 0, NULL, '2026-05-11 23:28:52', '2026-05-11 23:28:52'),
(280, 108, 3, 1, 'glass', 'glass', '0.9588', 1, 176, 17, 0, NULL, '2026-05-11 23:29:35', '2026-05-11 23:29:35'),
(281, 109, 1, 1, 'glass', 'glass', '0.9588', 1, 204, 20, 0, NULL, '2026-05-11 23:32:23', '2026-05-11 23:32:23'),
(282, 110, 1, 1, 'plastic', 'plastic', '0.9306', 1, 48, 20, 0, NULL, '2026-05-12 02:49:31', '2026-05-12 02:49:31'),
(283, 111, 1, 1, 'plastic', 'plastic', '0.9306', 1, 15, 5, 0, NULL, '2026-05-12 02:58:30', '2026-05-12 02:58:30'),
(284, 111, 1, 1, 'plastic', 'plastic', '0.9410', 1, 10, 5, 0, NULL, '2026-05-12 03:00:13', '2026-05-12 03:00:13'),
(285, 111, 1, 1, 'plastic', 'plastic', '0.9290', 1, 27, 10, 0, NULL, '2026-05-12 03:01:19', '2026-05-12 03:01:19'),
(286, 112, 1, 3, 'plastic', 'plastic', '0.7331', 1, 12, 5, 0, NULL, '2026-07-29 07:13:20', '2026-07-29 07:13:20'),
(287, 113, 1, 1, 'aluminum', 'aluminum', '0.9324', 1, 29, 14, 0, NULL, '2026-08-13 01:06:24', '2026-08-13 01:06:24'),
(288, 113, 1, 1, 'plastic', 'plastic', '0.9079', 1, 9, 0, 0, NULL, '2026-08-13 01:06:50', '2026-08-13 01:06:50'),
(289, 113, 1, 1, 'aluminum', 'aluminum', '0.8058', 1, 10, 7, 0, NULL, '2026-08-13 01:07:17', '2026-08-13 01:07:17'),
(290, 113, 1, 1, 'paper', 'paper', '0.9228', 1, 251, 25, 0, NULL, '2026-08-13 01:08:24', '2026-08-13 01:08:24'),
(291, 115, 5, 1, 'paper', 'paper', '0.9281', 1, 368, 36, 0, NULL, '2026-08-13 03:15:28', '2026-08-13 03:15:28'),
(292, 116, 5, 1, 'aluminum', 'aluminum', '0.6024', 1, 44, 28, 0, NULL, '2026-08-13 03:28:09', '2026-08-13 03:28:09'),
(293, 117, 5, 1, 'plastic', 'plastic', '0.6420', 1, 31, 15, 0, NULL, '2026-08-13 03:29:29', '2026-08-13 03:29:29'),
(294, 117, 5, 1, 'aluminum', 'aluminum', '0.9270', 1, 36, 21, 0, NULL, '2026-08-13 03:29:54', '2026-08-13 03:29:54'),
(295, 118, 5, 1, 'aluminum', 'aluminum', '0.9255', 1, 15, 7, 0, NULL, '2026-08-13 03:31:18', '2026-08-13 03:31:18'),
(296, 119, 5, 1, 'aluminum', 'aluminum', '0.9246', 1, 29, 14, 0, NULL, '2026-08-13 03:45:59', '2026-08-13 03:45:59'),
(297, 120, 5, 1, 'aluminum', 'aluminum', '0.9260', 1, 43, 28, 0, NULL, '2026-08-13 03:48:06', '2026-08-13 03:48:06'),
(298, 121, 9, 1, 'aluminum', 'aluminum', '0.9373', 1, 47, 28, 0, NULL, '2026-08-13 03:50:04', '2026-08-13 03:50:04'),
(299, 122, 5, 1, 'aluminum', 'aluminum', '0.9243', 1, 42, 28, 0, NULL, '2026-08-13 03:56:13', '2026-08-13 03:56:13'),
(300, 123, 5, 3, 'aluminum', 'aluminum', '0.9309', 1, 37, 21, 0, NULL, '2026-08-13 03:57:04', '2026-08-13 03:57:04'),
(301, 124, 5, 3, 'aluminum', 'aluminum', '0.9317', 1, 28, 14, 0, NULL, '2026-08-13 03:58:09', '2026-08-13 03:58:09'),
(302, 124, 5, 3, 'aluminum', 'aluminum', '0.9365', 1, 16, 7, 0, NULL, '2026-08-13 03:58:38', '2026-08-13 03:58:38'),
(303, 126, 5, 1, 'plastic', 'plastic', '0.8310', 1, 20, 10, 0, NULL, '2026-08-13 09:34:29', '2026-08-13 09:34:29'),
(304, 128, 5, 1, 'glass', 'glass', '0.8911', 1, 363, 36, 0, NULL, '2026-08-13 09:42:59', '2026-08-13 09:42:59'),
(305, 129, 5, 1, 'glass', 'glass', '0.8873', 1, 94, 9, 0, NULL, '2026-08-13 09:43:48', '2026-08-13 09:43:48'),
(306, 130, 5, 1, 'paper', 'paper', '0.8623', 1, 287, 28, 0, NULL, '2026-08-13 09:50:25', '2026-08-13 09:50:25'),
(307, 131, 5, 1, 'glass', 'glass', '0.6574', 1, 133, 13, 0, NULL, '2026-08-13 09:51:06', '2026-08-13 09:51:06'),
(308, 131, 5, 1, 'glass', 'glass', '0.8568', 1, 363, 36, 0, NULL, '2026-08-13 09:51:33', '2026-08-13 09:51:33'),
(309, 132, 5, 1, 'glass', 'glass', '0.8591', 1, 259, 25, 0, NULL, '2026-08-13 09:52:13', '2026-08-13 09:52:13'),
(310, 132, 5, 1, 'glass', 'glass', '0.8274', 1, 280, 28, 0, NULL, '2026-08-13 09:54:05', '2026-08-13 09:54:05'),
(311, 134, 5, 1, 'glass', 'glass', '0.8945', 1, 315, 31, 0, NULL, '2026-08-13 09:58:35', '2026-08-13 09:58:35'),
(312, 136, 5, 1, 'glass', 'glass', '0.8273', 1, 84, 8, 0, NULL, '2026-08-13 10:00:45', '2026-08-13 10:00:45'),
(313, 136, 5, 1, 'paper', 'paper', '0.9311', 1, 445, 44, 0, NULL, '2026-08-13 10:03:24', '2026-08-13 10:03:24'),
(314, 136, 5, 1, 'aluminum', 'aluminum', '0.9379', 1, 48, 28, 0, NULL, '2026-08-13 10:14:14', '2026-08-13 10:14:14'),
(315, 136, 5, 1, 'paper', 'paper', '0.8899', 1, 271, 27, 0, NULL, '2026-08-13 10:21:15', '2026-08-13 10:21:15'),
(316, 137, 5, 1, 'paper', 'paper', '0.7720', 1, 477, 47, 0, NULL, '2026-08-13 10:23:39', '2026-08-13 10:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_points` int UNSIGNED NOT NULL DEFAULT '0',
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_verified` tinyint NOT NULL DEFAULT '0',
  `otp_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `preferred_language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_preference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `google_id`, `avatar_url`, `total_points`, `role`, `is_verified`, `otp_code`, `otp_expires_at`, `preferred_language`, `theme_preference`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin Demo', 'admin@rvm.com', '0800000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 2433, 'admin', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-24 01:26:07', '2026-08-13 01:08:24'),
(2, 'Demo User 2', 'user2@example.com', '0800000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-06 16:56:14', '2026-05-09 23:21:07'),
(3, 'Demo User 3', 'user3@example.com', '0800000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 339, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-06 17:25:59', '2026-05-11 23:29:35'),
(4, 'Demo User 4', 'user4@example.com', '0800000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 553, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-09 07:58:29', '2026-05-11 15:11:13'),
(5, 'Demo User 5', 'user5@example.com', '0800000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 589, 'user', 1, NULL, NULL, NULL, 'light', NULL, '2026-05-09 08:06:35', '2026-08-13 10:23:39'),
(6, 'Demo User 6', 'user6@example.com', '0800000006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 107, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-11 09:47:48', '2026-05-11 16:04:20'),
(7, 'Demo User 7', 'user7@example.com', '0800000007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-11 15:25:20', '2026-05-11 15:26:20'),
(8, 'Demo User 8', 'user8@example.com', '0800000008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 30, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-11 16:05:16', '2026-05-11 16:16:33'),
(9, 'Demo User 9', 'user9@example.com', '0800000009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 28, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-08-13 01:12:39', '2026-08-13 03:50:04'),
(10, 'Demo User 10', 'user10@example.com', '0800000010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-08-13 02:17:03', '2026-08-13 02:17:03'),
(11, 'Demo User 11', 'user11@example.com', '0800000011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-08-13 08:42:20', '2026-08-13 09:01:26'),
(12, 'Demo User 12', 'user12@example.com', '0800000012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-13 08:49:05', '2026-08-13 08:49:05'),
(13, 'Demo User 13', 'user13@example.com', '0800000013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-13 08:49:59', '2026-08-13 08:49:59'),
(14, 'Demo User 14', 'user14@example.com', '0800000014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, 'user', 1, NULL, NULL, NULL, NULL, NULL, '2026-08-13 08:56:24', '2026-08-13 09:00:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_logs_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `points_history_user_id_foreign` (`user_id`);

--
-- Indexes for table `qr_sessions`
--
ALTER TABLE `qr_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_sessions_qr_token_unique` (`qr_token`),
  ADD KEY `qr_sessions_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `recycling_sessions`
--
ALTER TABLE `recycling_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recycling_sessions_session_code_unique` (`session_code`),
  ADD KEY `recycling_sessions_user_id_foreign` (`user_id`),
  ADD KEY `recycling_sessions_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `rvm_machines`
--
ALTER TABLE `rvm_machines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rvm_machines_machine_code_unique` (`machine_code`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_session_id_foreign` (`session_id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`),
  ADD KEY `transactions_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;

--
-- AUTO_INCREMENT for table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=317;

--
-- AUTO_INCREMENT for table `qr_sessions`
--
ALTER TABLE `qr_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=401;

--
-- AUTO_INCREMENT for table `recycling_sessions`
--
ALTER TABLE `recycling_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `rvm_machines`
--
ALTER TABLE `rvm_machines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=317;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `points_history`
--
ALTER TABLE `points_history`
  ADD CONSTRAINT `points_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_sessions`
--
ALTER TABLE `qr_sessions`
  ADD CONSTRAINT `qr_sessions_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `rvm_machines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recycling_sessions`
--
ALTER TABLE `recycling_sessions`
  ADD CONSTRAINT `recycling_sessions_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `rvm_machines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recycling_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `rvm_machines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `recycling_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
