-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 02, 2025 at 12:34 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_dor`
--

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
(1, '2025_09_24_125738_create_users_table', 1),
(2, '2025_09_24_125821_create_products_table', 1),
(3, '2025_09_24_125828_create_stocks_table', 1),
(4, '2025_09_24_125835_create_transactions_table', 1),
(5, '2025_09_24_125841_create_purchase_orders_table', 1),
(6, '2025_09_24_125848_create_sales_orders_table', 1),
(8, '2025_09_24_170556_create_sessions_table', 2),
(9, '2025_09_25_032509_create_personal_access_tokens_table', 3),
(10, '2025_09_25_084732_add_status_to_users_table', 4),
(11, '2025_09_25_120000_update_products_table_add_inventory_fields', 5),
(12, '2025_09_25_130000_add_missing_fields_to_transactions_table', 6),
(13, '2025_09_25_151200_add_reason_to_transactions_table', 7),
(14, '2025_09_25_151300_add_notes_to_transactions_table', 8),
(15, '2025_09_26_061631_create_settings_table', 9),
(16, '2025_09_26_000001_add_status_and_last_login_to_users_table', 10),
(17, '2025_09_29_115433_modify_products_table_primary_key', 11),
(18, '2025_09_29_122036_modify_users_table_for_string_id', 12),
(19, '2025_09_29_122704_remove_id_string_and_modify_id_on_users_table', 13),
(20, '2025_09_29_123348_drop_id_string_column_from_users', 14),
(21, '2025_09_29_124517_fix_personal_access_tokens_for_string_ids', 15),
(22, '2025_09_30_104845_create_suppliers_table', 16),
(23, '2025_10_01_175443_add_po_number_to_purchase_orders', 17),
(24, '2025_10_01_175658_add_index_to_po_number', 18),
(25, '2025_10_01_180018_add_supplier_name_to_purchase_orders', 19);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
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

INSERT INTO `personal_access_tokens` (`id`, `tokenable_id`, `tokenable_type`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, '4', 'App\\Models\\User', 'authToken', 'c6c74f35fc54490736bae3c024fb65dd7577ce8085ded121d5a0b08c727747f7', '[\"*\"]', NULL, NULL, '2025-09-24 23:31:36', '2025-09-24 23:31:36'),
(2, '5', 'App\\Models\\User', 'authToken', 'b1dc512cd8190aeb98c6ed30e84155d337822fd383e45eb9713c4ff8cde67041', '[\"*\"]', NULL, NULL, '2025-09-24 23:32:15', '2025-09-24 23:32:15'),
(3, '7', 'App\\Models\\User', 'authToken', '8b647744fb5b66cd644f31ef77643a295600a863d8be6e43d170c8e14b412b69', '[\"*\"]', NULL, NULL, '2025-09-24 23:32:30', '2025-09-24 23:32:30'),
(4, '8', 'App\\Models\\User', 'authToken', '0d6617e839805c09bed11215c905788173682dd0a7a56cde81a666c07ac647d4', '[\"*\"]', NULL, NULL, '2025-09-24 23:37:32', '2025-09-24 23:37:32'),
(5, '9', 'App\\Models\\User', 'authToken', 'b5d475aab7c779f61bc27faab6ab7f3c84eceb4a92ef690e01070f44947cfcd0', '[\"*\"]', NULL, NULL, '2025-09-24 23:38:41', '2025-09-24 23:38:41'),
(6, '8', 'App\\Models\\User', 'authToken', '637154a72825d833a8087e7890f3876920cc918826384b44fbb68b388f371f47', '[\"*\"]', NULL, NULL, '2025-09-24 23:39:07', '2025-09-24 23:39:07'),
(7, '10', 'App\\Models\\User', 'authToken', 'c4ecf8f13dc76cb9443b6503f09c8d887336a59869afb322a8725c0349e4fab0', '[\"*\"]', NULL, NULL, '2025-09-24 23:42:47', '2025-09-24 23:42:47'),
(10, '6', 'App\\Models\\User', 'authToken', 'fbc5dce0340367a6df2bae9d2170fc90930f11e8153c4eae5ac74ee7375fbeb5', '[\"*\"]', NULL, NULL, '2025-09-25 00:16:46', '2025-09-25 00:16:46'),
(11, '5', 'App\\Models\\User', 'authToken', 'b5d6e46eb556d00c4a2c9196f4d3de10b6ed349bd3829fbef4aa9c9401cd081a', '[\"*\"]', NULL, NULL, '2025-09-25 00:17:03', '2025-09-25 00:17:03'),
(12, '5', 'App\\Models\\User', 'authToken', 'f384067ebb8fc79a09a983c4d75706912b333ebdcfe28c8de539e1694700f869', '[\"*\"]', NULL, NULL, '2025-09-25 00:23:27', '2025-09-25 00:23:27'),
(13, '5', 'App\\Models\\User', 'authToken', 'fd9bca989c2debc8088aa6f6fa90bcf921b03ee5b50371501311cb19752eb214', '[\"*\"]', NULL, NULL, '2025-09-25 00:28:32', '2025-09-25 00:28:32'),
(19, '6', 'App\\Models\\User', 'authToken', '010f31d1e29a82491dc81e85c36da30b476b14ddae09a15e4854e15a3e13e210', '[\"*\"]', NULL, NULL, '2025-09-25 01:31:35', '2025-09-25 01:31:35'),
(20, '11', 'App\\Models\\User', 'authToken', 'b4e4b8415da1bb46a40ed1e53949887abea6589e5d5ae2494b871456f7754c5e', '[\"*\"]', NULL, NULL, '2025-09-25 01:32:02', '2025-09-25 01:32:02'),
(21, '11', 'App\\Models\\User', 'authToken', '9782e33c8d226ca7aee756226961547be80f758f2ff01dac010db34e9b146611', '[\"*\"]', NULL, NULL, '2025-09-25 01:32:11', '2025-09-25 01:32:11'),
(22, '5', 'App\\Models\\User', 'authToken', '9927039c7abc9181696f791f1833f59e56f1afcd59ca88d681085c6b9aedf3c3', '[\"*\"]', NULL, NULL, '2025-09-25 01:42:52', '2025-09-25 01:42:52'),
(23, '5', 'App\\Models\\User', 'authToken', '5af2908c62279e9a4d092d749599fd29760b702fc38957c0f767a87edb149e74', '[\"*\"]', '2025-09-25 04:12:03', NULL, '2025-09-25 01:55:46', '2025-09-25 04:12:03'),
(24, '5', 'App\\Models\\User', 'test', '0ab2546be5d0447b073a604fdd5545ca83a76f5d02307db476270228bbcfe094', '[\"*\"]', NULL, NULL, '2025-09-25 02:00:33', '2025-09-25 02:00:33'),
(25, '5', 'App\\Models\\User', 'authToken', 'ca3f4533a762454089a526cc2ee8fbbca8b6e80636df047ea2ce53bcaa48292f', '[\"*\"]', '2025-09-25 05:20:52', NULL, '2025-09-25 05:20:32', '2025-09-25 05:20:52'),
(27, '5', 'App\\Models\\User', 'authToken', '6c1c85a1059ef06f7149af4e29c399b39330eebc844fb6d0a1cf0a8b3466f84a', '[\"*\"]', '2025-09-25 06:17:16', NULL, '2025-09-25 05:21:34', '2025-09-25 06:17:16'),
(34, '5', 'App\\Models\\User', 'authToken', 'ca13bb8536d60657c3d9e5020d8a8cf56a6b638be087fa249e78f70b6b25bae0', '[\"*\"]', '2025-09-25 09:18:56', NULL, '2025-09-25 09:14:50', '2025-09-25 09:18:56'),
(36, '6', 'App\\Models\\User', 'authToken', '0325a6ed3bba3df26743174e67083f5a49f92b1c0ff37c2939280fcee1e06fd3', '[\"*\"]', '2025-09-25 22:39:09', NULL, '2025-09-25 22:35:23', '2025-09-25 22:39:09'),
(43, '5', 'App\\Models\\User', 'authToken', '6088c95b7b3761f50b9887282e2e3ed473cf958a32cd3225a3cfe18b14b25422', '[\"*\"]', '2025-09-26 04:38:52', NULL, '2025-09-26 02:54:01', '2025-09-26 04:38:52'),
(46, '5', 'App\\Models\\User', 'authToken', '15bd7560c5bc355fb7892561e42b845bd6bb44b3737605b6ef17d52797c5658e', '[\"*\"]', '2025-09-26 05:33:49', NULL, '2025-09-26 05:30:50', '2025-09-26 05:33:49'),
(47, '5', 'App\\Models\\User', 'authToken', 'e1ed01a8c4d7579b03050d06df4445b4c7f3fdb2bf714c1bd88013a23c069f45', '[\"*\"]', '2025-09-26 05:37:21', NULL, '2025-09-26 05:34:58', '2025-09-26 05:37:21'),
(49, '5', 'App\\Models\\User', 'authToken', '2f7456182e3fb020f30b5dd8677b5b0d8755ecb6d3625c024fe6d0081a9e460f', '[\"*\"]', '2025-09-26 06:26:30', NULL, '2025-09-26 06:26:29', '2025-09-26 06:26:30'),
(50, '5', 'App\\Models\\User', 'authToken', 'feca130cdbeffc9509bb9cdc07f41a1003b1cbffde1143513879c680d3586d72', '[\"*\"]', NULL, NULL, '2025-09-26 06:46:11', '2025-09-26 06:46:11'),
(51, '5', 'App\\Models\\User', 'authToken', 'fb1970da2f7e7f4ab6c22b62cffba90d6edd2fa30924e117dca9aecd8e5ec933', '[\"*\"]', '2025-09-28 22:02:07', NULL, '2025-09-28 21:51:38', '2025-09-28 22:02:07'),
(53, '6', 'App\\Models\\User', 'authToken', '5a2c769eb66ef57a2e137fc1e9be3974d670342c61691426e512f4380aaf39da', '[\"*\"]', '2025-09-28 22:06:45', NULL, '2025-09-28 22:06:34', '2025-09-28 22:06:45'),
(59, '5', 'App\\Models\\User', 'authToken', 'd0fb17a9d73673fd6b49ef3692339b80eb7967fdd524b0f1510b88739d8c72cb', '[\"*\"]', '2025-09-29 00:53:07', NULL, '2025-09-29 00:02:43', '2025-09-29 00:53:07'),
(60, '5', 'App\\Models\\User', 'authToken', '2f5e05da346824c56e8b37b09b43c01f75bc04bb32e3838f4c126396f72db35a', '[\"*\"]', '2025-09-29 01:31:52', NULL, '2025-09-29 00:53:13', '2025-09-29 01:31:52'),
(61, '5', 'App\\Models\\User', 'authToken', 'e0b68ae916963284a408ece11df5bd762d2ff3bc0cd14df837c3e2a88ace93dc', '[\"*\"]', '2025-09-29 01:33:12', NULL, '2025-09-29 01:29:36', '2025-09-29 01:33:12'),
(62, '6', 'App\\Models\\User', 'authToken', '5a335db95de6f98c11823aba8c7f74d627bab88ae1b0c6984210c485d24b4b45', '[\"*\"]', '2025-09-29 01:33:29', NULL, '2025-09-29 01:33:26', '2025-09-29 01:33:29'),
(63, '5', 'App\\Models\\User', 'authToken', '9117275a0d61c470c9994c41c5be2f2620f7f76810cf6910b8758654fd52d21e', '[\"*\"]', '2025-09-29 02:05:00', NULL, '2025-09-29 01:34:31', '2025-09-29 02:05:00'),
(64, '5', 'App\\Models\\User', 'authToken', '86108451b0f42920e4069e352ba302654ad24a4fd767af79928bc8641f7f1805', '[\"*\"]', '2025-09-29 03:11:55', NULL, '2025-09-29 02:05:15', '2025-09-29 03:11:55'),
(66, '5', 'App\\Models\\User', 'authToken', 'edd9c0d4ab42429c0b3da6b6fe5e550d19a2d26f081939c4cf045b6180f554ef', '[\"*\"]', '2025-09-29 03:15:48', NULL, '2025-09-29 03:13:14', '2025-09-29 03:15:48'),
(67, '6', 'App\\Models\\User', 'authToken', 'aca627454aacf31ac7351ea1f4f000c9922e73975e182984501bcbdabb33d6b9', '[\"*\"]', '2025-09-29 03:16:02', NULL, '2025-09-29 03:16:00', '2025-09-29 03:16:02'),
(68, '5', 'App\\Models\\User', 'authToken', '2a7272f7d0c30a831abb31453472291cb6065429f1fd65edfd286e5d3013dfb6', '[\"*\"]', '2025-09-29 04:31:39', NULL, '2025-09-29 03:16:58', '2025-09-29 04:31:39'),
(69, '5', 'App\\Models\\User', 'authToken', '64920b4b6d3f094ace103c27ec9f90b33213557f68162df8b198b0a6919ae535', '[\"*\"]', '2025-09-29 04:32:16', NULL, '2025-09-29 04:32:06', '2025-09-29 04:32:16'),
(70, '6', 'App\\Models\\User', 'authToken', '175225127d50dfc8d05f95f385f245614f9b6d6beb20112265b71844829dfad8', '[\"*\"]', '2025-09-29 04:32:46', NULL, '2025-09-29 04:32:23', '2025-09-29 04:32:46'),
(71, '5', 'App\\Models\\User', 'authToken', 'a99bfb23640eb2236f727c833ccc62fa592fda2106c5cc42856b3bc9b6545a8a', '[\"*\"]', '2025-09-29 05:29:39', NULL, '2025-09-29 04:32:51', '2025-09-29 05:29:39'),
(72, '0', 'App\\Models\\User', 'authToken', '18d2ecd43fca0a7c1c18491d92bc47714fb714fc2daee34a47e8f6dcef072748', '[\"*\"]', '2025-09-29 05:41:40', NULL, '2025-09-29 05:30:40', '2025-09-29 05:41:40'),
(73, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '5a6233118c5f1872922d3cfd1092866853ea35a1a3bf1dd3d900ec862b27d147', '[\"*\"]', '2025-09-29 06:20:03', NULL, '2025-09-29 06:19:59', '2025-09-29 06:20:03'),
(74, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '0021c23a686994ed1cfb2870314cb76e938179b35ceb6792ed08a3f18861cdc1', '[\"*\"]', '2025-09-29 06:21:01', NULL, '2025-09-29 06:20:13', '2025-09-29 06:21:01'),
(75, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'e390c30300047a967e800ff85e7126f7636fd24e0ebe0005babc9e744c47bce9', '[\"*\"]', '2025-09-29 06:40:51', NULL, '2025-09-29 06:21:49', '2025-09-29 06:40:51'),
(76, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '7517a42a4c08215df2b2eb1bb0d0451ecc912f43ef38e906f16023ac80917736', '[\"*\"]', '2025-09-29 06:41:55', NULL, '2025-09-29 06:40:59', '2025-09-29 06:41:55'),
(77, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'f8ad1db05e16efb5eed4968ee4a9414ece4e36ac80641125bf138206236f7287', '[\"*\"]', '2025-09-29 06:45:32', NULL, '2025-09-29 06:44:57', '2025-09-29 06:45:32'),
(78, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '867e2ca3774569cf4fe8161f624b3976c119b27b0d78c4537bec02bae62fd499', '[\"*\"]', '2025-09-29 07:00:27', NULL, '2025-09-29 06:45:46', '2025-09-29 07:00:27'),
(79, 'USR-148539-2517', 'App\\Models\\User', 'authToken', 'd3a490b34c538251547042eb67fc8335e8dec821b82446e54409b59ce56f28e9', '[\"*\"]', '2025-09-29 07:47:59', NULL, '2025-09-29 07:00:38', '2025-09-29 07:47:59'),
(80, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '189c7de18cadd56256f3d7b3ed178455d8d7a1ab1371c0bf4b14e07473734c2a', '[\"*\"]', '2025-09-29 07:48:38', NULL, '2025-09-29 07:48:25', '2025-09-29 07:48:38'),
(81, 'USR-148539-2517', 'App\\Models\\User', 'authToken', 'e4ca0fc16f8e7181f0b9ffb5c1c677c32e4e9968fe5440cf3bea635d1ff290f0', '[\"*\"]', '2025-09-29 07:51:28', NULL, '2025-09-29 07:48:54', '2025-09-29 07:51:28'),
(82, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '8e0e0323178d144b6c6568153d9dffd16b0e930c9af123e209ae7aa72e5914c4', '[\"*\"]', '2025-09-30 03:33:42', NULL, '2025-09-30 03:25:03', '2025-09-30 03:33:42'),
(83, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '89d486935bba15f881532a123f3a856b45630feba7d8aec425fa84878e919941', '[\"*\"]', '2025-09-30 03:36:25', NULL, '2025-09-30 03:34:39', '2025-09-30 03:36:25'),
(84, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '85849793a102c76c899064656c2f21f4fae8824c27ebcab7b2388cc36244d855', '[\"*\"]', '2025-09-30 04:03:44', NULL, '2025-09-30 03:36:38', '2025-09-30 04:03:44'),
(85, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'dfc963f0e7ea5c1087a85dbac44e0763b9aca550d80916105702785ea4efdc40', '[\"*\"]', '2025-09-30 04:04:51', NULL, '2025-09-30 04:04:07', '2025-09-30 04:04:51'),
(86, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '293074dadd25d704bf50f13e2cb835091444386f044ff1e26c12b353743de4fb', '[\"*\"]', '2025-09-30 04:12:23', NULL, '2025-09-30 04:05:46', '2025-09-30 04:12:23'),
(87, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '68bdb8703007dadd5f0a7f25e249dc61c303b6dac12be535ad7669cc085abc1d', '[\"*\"]', '2025-09-30 04:14:01', NULL, '2025-09-30 04:12:42', '2025-09-30 04:14:01'),
(88, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'f273b565ec5a10532546683141ab6afafb42ddfc66c48c5b1173b3e19053c437', '[\"*\"]', '2025-09-30 04:22:00', NULL, '2025-09-30 04:14:30', '2025-09-30 04:22:00'),
(89, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '59f5522f689f334468a9521bb5af10f78dc705d542138d6b521dcc804e1c5ea8', '[\"*\"]', '2025-09-30 04:22:34', NULL, '2025-09-30 04:22:26', '2025-09-30 04:22:34'),
(90, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '92c2b0e47e57bcf18d0ef89cad26d6a2e50fc601bb72aa783da1ae0346c3a64b', '[\"*\"]', '2025-09-30 04:22:58', NULL, '2025-09-30 04:22:46', '2025-09-30 04:22:58'),
(91, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '091efb533ee7f7bb2ec5303f4047e1cf548d8d5efa0c87a7d0413edd89c6df3f', '[\"*\"]', '2025-09-30 04:32:21', NULL, '2025-09-30 04:26:09', '2025-09-30 04:32:21'),
(92, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'bff1a94e1591f2e84c71e840dc58968d367afea0346161bb2ebf0cee044eac3d', '[\"*\"]', '2025-09-30 04:36:04', NULL, '2025-09-30 04:32:38', '2025-09-30 04:36:04'),
(93, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '63ce4f8e7a07523d33c7fee01a9b620e39917295b629dddc95a084d0d2a2bf85', '[\"*\"]', '2025-09-30 04:58:42', NULL, '2025-09-30 04:42:36', '2025-09-30 04:58:42'),
(94, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '7bdb4e8ec9d5450bf7939bda1d6395b935fa3e6861ea6c558154235547e14f35', '[\"*\"]', '2025-09-30 05:10:42', NULL, '2025-09-30 05:02:51', '2025-09-30 05:10:42'),
(95, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '3bce83892b50d2680b251a4b5dad7f83ed6d463e71ef64f0896603b53d793bdf', '[\"*\"]', '2025-09-30 05:11:12', NULL, '2025-09-30 05:11:07', '2025-09-30 05:11:12'),
(96, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '307a86e17111bc72b83f2092b79a441f436a5348e4315f7197bbce3dda4ae4d3', '[\"*\"]', '2025-09-30 05:41:08', NULL, '2025-09-30 05:15:19', '2025-09-30 05:41:08'),
(97, 'USR-148539-2517', 'App\\Models\\User', 'authToken', 'f11fe0b2e776461f8bcfb40e375c04360af976d909f82fb2b7b428d7bf991723', '[\"*\"]', '2025-09-30 06:23:34', NULL, '2025-09-30 05:41:13', '2025-09-30 06:23:34'),
(98, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'd48f95c58134514033870f2160818f2f807b9c6bfd47b991c4522932b092c56a', '[\"*\"]', '2025-09-30 06:38:59', NULL, '2025-09-30 06:26:19', '2025-09-30 06:38:59'),
(99, 'USR-148539-2517', 'App\\Models\\User', 'authToken', '46b3d60bf93737c1b2b94fa2b4cd616bff5eaf8389ad49262a91c3e7867e0db4', '[\"*\"]', '2025-09-30 06:39:53', NULL, '2025-09-30 06:39:25', '2025-09-30 06:39:53'),
(100, 'USR-148539-4386', 'App\\Models\\User', 'authToken', '4d4a215e912afee02f8b2f8236de1ba8c8f0a5a07bc8bbf6e128921ef1e9057f', '[\"*\"]', '2025-10-01 10:37:04', NULL, '2025-10-01 10:36:51', '2025-10-01 10:37:04'),
(101, 'USR-148539-2517', 'App\\Models\\User', 'authToken', 'ccbbc73f552303034551c1d0efd47f9d425e741ce3bb21ed8c19ed6f3aa48610', '[\"*\"]', '2025-10-01 11:08:48', NULL, '2025-10-01 10:37:52', '2025-10-01 11:08:48'),
(102, 'USR-148539-4386', 'App\\Models\\User', 'authToken', 'f5eb86ed2d1ab1b6b7ed9e80fa88fbe77ea51bf9d44322c21f1b7379b0d1b653', '[\"*\"]', '2025-10-02 04:20:15', NULL, '2025-10-02 04:11:30', '2025-10-02 04:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Lainnya',
  `category_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `purchase_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `min_stock` int NOT NULL DEFAULT '10',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `sku`, `category`, `category_id`, `purchase_price`, `selling_price`, `stock`, `min_stock`, `status`, `user_id`, `created_at`, `updated_at`) VALUES
('PRD-147371-1057', 'Laptop Thinkpad T460', NULL, 'ELELAP250929233', 'Elektronik', 0, 1200000.00, 2500000.00, 15, 10, 'active', 5, '2025-09-29 05:02:51', '2025-09-29 05:06:08'),
('PRD-147462-5726', 'Laptop Thinkpad L420', NULL, 'ELELAP250929638', 'Elektronik', 0, 900000.00, 2000000.00, 11, 10, 'active', 5, '2025-09-29 05:04:22', '2025-09-29 05:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `po_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `supplier_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0WCIG5DAV4PNPrIJOeImoEWAFDGIkLmP50CyQq4q', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVXZ0NUYyd3VEMVBMWWllZFN4Y3lDR21xMnpETXBhWVQ3Q28xak94NSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758783072),
('5bHVw5vd9WhhTXcNbYYLjAQIw12LcP4vAMFBLmYh', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiU2k5QlN3VzVzSlNCN2F0QnUxRFRUWHo4WmdSUWdNQXRmMDRibjNRRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758782984),
('EtC9ICrMQqTThHCWcNz1i61dgCKbctRCWQK29lZl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTHB2dElXTDl4OHJjTzkxRjNFeElvT0huYTc5elhabndNa09ZOFl0eSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758783016),
('IcveFMKikiVc90VJSeEKNLbSZ2d9OXV3Mh8MB78f', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiS1FDVEx0aFNzVUQxQXdhY3FvR052YmlvVmo0d1VYMGtIWXdMOFhEYyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758782941),
('JerFCTy1QZf7Eq2exArpsjrvbrQ4jt392c3rU64u', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQmI2NmJpSTBKMkppd09uR3JZbGkyMDhIdHQ2WkRwM29FNUZFaE14SSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758782979),
('pI2vvexHU3zzkHmau7ilizZ99342WVeHfxgUkbjT', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMm1JMVhGNDBsQXFiT1NaazhQd2hUTlZCWFBUMk9wejhmUWRTZEtuciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758783149),
('RbZxel1P4AiOpY54D435CLmjaxi0piuBhjPUA4Z8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiYVpiRXdYSjVURnhFb095Zk1PdEhpUHdWY2hWOW9LRWZXdVRPMUc4aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758783028),
('zyZiiMudth6lp8UoBkTppAiUZ7OzpBC47vAaxPKM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiU2d0T1NUVDN2ckh3Q1VlcG9kQ2cxb3loamdoTDJ3RGFYR1VUTkc3SCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758782522);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `settings_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `settings_data`, `created_at`, `updated_at`) VALUES
(1, '{\"backup\": {\"frequency\": \"weekly\", \"autoBackup\": true, \"retainDays\": 30}, \"general\": {\"currency\": \"IDR\", \"timezone\": \"Asia/Jakarta\", \"dateFormat\": \"DD/MM/YYYY\", \"companyName\": \"Inventaris App\", \"companyEmail\": \"admin@inventarisapp.com\", \"companyPhone\": \"+62 812 3456 7890\", \"companyAddress\": \"Jl. Contoh No. 123, Jakarta, Indonesia\"}, \"security\": {\"sessionTimeout\": 60, \"maxLoginAttempts\": 5, \"requireTwoFactor\": false, \"passwordMinLength\": 8}, \"inventory\": {\"skuPrefix\": \"PRD\", \"stockAlerts\": true, \"autoGenerateSku\": true, \"defaultMinStock\": 10, \"lowStockThreshold\": 5}, \"notifications\": {\"types\": {\"reports\": true, \"lowStock\": true, \"newOrders\": true, \"systemUpdates\": true}, \"smsNotifications\": false, \"pushNotifications\": true, \"emailNotifications\": true}}', NULL, '2025-09-29 05:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `minimum_stock` int DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `address`, `contact_person`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(2, 'PT.DUTA OTO RAYA', 'ptdutaotoraya@gmail.com', '12312313', 'Jl.Raya H.Baping', 'DICKY', NULL, 'active', '2025-09-30 04:14:00', '2025-09-30 04:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `temp_users`
--

CREATE TABLE `temp_users` (
  `id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `old_stock` int DEFAULT NULL,
  `new_stock` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `last_login`, `remember_token`, `created_at`, `updated_at`) VALUES
('USR-148539-2517', 'Staff User', 'staff@inventaris.com', NULL, '$2y$12$rrgM9UZ0bNk6u0MjufYrSO1neZo2Q9IItZbC0iHWPoONaLdkyQPmC', 'staff', 'active', '2025-10-01 10:37:52', NULL, '2025-09-24 23:31:59', '2025-10-01 10:37:52'),
('USR-148539-4386', 'Administrator', 'admin@inventaris.com', NULL, '$2y$12$gIECT3MA6AjfAz1v.NIgte5Kr59ZvDkBqCSLNqGodn7EZsgO/qb5y', 'admin', 'active', '2025-10-02 04:11:30', NULL, '2025-09-24 23:31:58', '2025-10-02 04:11:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_new_token_unique` (`token`),
  ADD KEY `personal_access_tokens_new_tokenable_id_tokenable_type_index` (`tokenable_id`,`tokenable_type`),
  ADD KEY `personal_access_tokens_new_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `temp_products_sku_unique` (`sku`),
  ADD KEY `products_user_id_foreign` (`user_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_users`
--
ALTER TABLE `temp_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `temp_users_email_unique` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `temp_users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
