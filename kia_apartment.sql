-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2025 at 07:39 AM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kia_apartment`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `arrival_time` datetime NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `duration_type` enum('transit','fullday') NOT NULL,
  `duration_hours` int(11) NOT NULL,
  `price_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `deposit_type` enum('cash','id_card','no_deposit') NOT NULL,
  `deposit_amount` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `status` enum('booked','checkin','checkout','cancelled','no_show','completed') DEFAULT 'booked',
  `checkin_time` datetime DEFAULT NULL,
  `checkout_time` datetime DEFAULT NULL,
  `extra_time_hours` int(11) DEFAULT '0',
  `extra_time_amount` decimal(10,2) DEFAULT '0.00',
  `refund_amount` decimal(10,2) DEFAULT '0.00',
  `refund_method` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `checkout_datetime` datetime DEFAULT NULL,
  `planned_checkout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `guest_name`, `arrival_time`, `phone_number`, `duration_type`, `duration_hours`, `price_amount`, `payment_method`, `deposit_type`, `deposit_amount`, `notes`, `status`, `checkin_time`, `checkout_time`, `extra_time_hours`, `extra_time_amount`, `refund_amount`, `refund_method`, `created_by`, `created_at`, `updated_at`, `checkout_datetime`, `planned_checkout_time`) VALUES
(1, 2, 'riru', '2025-07-02 22:43:00', '1239999', 'transit', 3, '100000.00', 'cash', 'cash', '10000.00', 'pinjam kompor', 'completed', '2025-07-02 20:39:10', '2025-07-02 20:39:44', 0, '0.00', '100000.00', 'cash', 1, '2025-07-02 13:38:59', '2025-07-07 03:59:28', NULL, NULL),
(2, 1, 'prof', '2025-07-02 23:40:00', '12345657890', 'transit', 3, '150000.00', 'cash', 'cash', '100000.00', 'pinjam kompor', 'completed', '2025-07-02 21:34:57', '2025-07-02 21:35:48', 2, '50000.00', '0.00', NULL, 2, '2025-07-02 14:34:44', '2025-07-18 02:35:42', NULL, NULL),
(3, 1, 'riru', '2025-07-03 09:18:00', '1239999', 'transit', 4, '200000.00', 'transfer', 'id_card', '10000.00', 'KTP ditahan', 'completed', '2025-07-03 09:18:09', '2025-07-03 09:34:11', 0, '0.00', '0.00', NULL, 2, '2025-07-03 02:17:38', '2025-07-18 02:35:42', NULL, NULL),
(4, 2, 'goku', '2025-07-03 13:12:00', '12221113', 'transit', 2, '150000.00', 'cash', 'no_deposit', '0.00', 'adassss', 'completed', '2025-07-03 13:10:45', '2025-07-03 17:37:55', 0, '0.00', '0.00', NULL, 2, '2025-07-03 06:10:38', '2025-07-07 03:59:28', NULL, NULL),
(5, 2, 'tayo', '2025-07-03 19:00:00', '545678654', 'transit', 1, '120000.00', 'card', 'no_deposit', '100000.00', 'pinjam dildo', 'completed', '2025-07-03 18:00:24', '2025-07-03 18:00:48', 0, '0.00', '0.00', NULL, 2, '2025-07-03 10:59:43', '2025-07-07 03:59:28', NULL, NULL),
(6, 3, 'rirukkkk', '2025-07-03 20:46:00', '12345657890', 'transit', 2, '100000.00', 'cash', 'cash', '10000.00', 'decefcef', 'completed', '2025-07-03 18:45:48', '2025-07-03 18:48:05', 2, '100000.00', '0.00', NULL, 2, '2025-07-03 11:45:41', '2025-07-07 06:56:37', NULL, NULL),
(7, 3, 'riru', '2025-07-05 18:48:00', '12345657890', 'transit', 1, '-0.02', 'cash', 'cash', '988899.00', '', 'completed', '2025-07-03 18:48:48', '2025-07-03 19:17:30', 0, '0.00', '0.00', NULL, 2, '2025-07-03 11:48:39', '2025-07-07 06:56:37', NULL, NULL),
(8, 3, 'tayo', '2025-07-04 11:08:00', '0871827883', 'transit', 1, '100000.00', 'cash', 'cash', '100000.00', 'tdjghdfh', 'completed', '2025-07-04 11:07:20', '2025-07-04 11:11:11', 0, '0.00', '0.00', NULL, 1, '2025-07-04 04:07:14', '2025-07-07 06:56:37', NULL, NULL),
(9, 3, 'prof', '2025-07-04 11:56:00', '32t223t', 'transit', 1, '120000.00', 'cash', 'cash', '10000.00', 'fdhbdfh', 'completed', '2025-07-04 11:53:02', '2025-07-04 13:16:12', 0, '0.00', '0.00', NULL, 1, '2025-07-04 04:52:56', '2025-07-07 06:56:37', NULL, NULL),
(10, 6, 'feknvk.efnv', '2025-07-04 13:18:00', '2078340', 'transit', 1, '70000.00', 'cash', 'cash', '10000.00', 'feihvelfjvlehjv', 'completed', '2025-07-04 13:16:44', '2025-07-04 14:24:41', 0, '0.00', '0.00', NULL, 1, '2025-07-04 06:16:39', '2025-07-18 02:35:36', NULL, NULL),
(11, 5, 'jak', '2025-07-04 13:18:00', '1084308', 'transit', 3, '180000.00', 'cash', 'cash', '100000.00', 'lknvfevev', 'completed', '2025-07-04 13:17:24', '2025-07-04 14:24:38', 0, '0.00', '0.00', NULL, 1, '2025-07-04 06:17:20', '2025-07-12 04:34:51', NULL, NULL),
(12, 1, 'speed', '2025-07-04 13:21:00', '0899927287', 'transit', 1, '888000.00', 'cash', 'id_card', '10000.00', 'viofhhfv', 'completed', '2025-07-04 13:18:22', '2025-07-04 14:24:31', 0, '0.00', '0.00', NULL, 1, '2025-07-04 06:18:17', '2025-07-18 02:35:42', NULL, NULL),
(13, 9, 'akuma', '2025-07-04 17:56:00', '1616161', 'transit', 12, '1500000.00', 'cash', 'cash', '190000.00', 'asannadvdv', 'completed', '2025-07-04 17:54:06', '2025-07-05 00:27:44', 0, '0.00', '0.00', NULL, 1, '2025-07-04 10:53:58', '2025-07-18 02:35:46', NULL, NULL),
(14, 1, 'ilham', '2025-07-05 02:30:00', '12345657890', 'transit', 3, '150000.00', 'cash', 'cash', '50000.00', '', 'completed', '2025-07-05 00:29:22', '2025-07-05 00:29:37', 0, '0.00', '0.00', NULL, 1, '2025-07-04 17:29:07', '2025-07-18 02:35:42', NULL, NULL),
(15, 4, 'waraas', '2025-07-05 03:58:00', '3263262362', 'fullday', 33, '15000.00', 'cash', 'id_card', '0.00', 'fhfdhfdsh', 'completed', '2025-07-05 01:57:50', '2025-07-05 01:57:55', 0, '0.00', '0.00', NULL, 1, '2025-07-04 18:57:46', '2025-07-13 04:18:33', NULL, NULL),
(16, 2, 'damar', '2025-07-05 12:39:00', '0896666322', 'fullday', 24, '300000.00', 'cash', 'id_card', '0.00', 'agadgdgadg', 'completed', '2025-07-05 10:37:35', '2025-07-05 10:38:25', 0, '0.00', '0.00', NULL, 1, '2025-07-05 03:37:25', '2025-07-07 03:59:28', NULL, NULL),
(17, 2, 'qwsacasc', '2025-07-05 11:57:00', '1311232121', 'transit', 2, '250000.00', 'cash', 'no_deposit', '0.00', 'sfbdfbsdzb', 'completed', '2025-07-05 11:58:33', '2025-07-05 11:58:45', 0, '0.00', '0.00', NULL, 1, '2025-07-05 04:58:22', '2025-07-07 03:59:28', NULL, NULL),
(18, 8, 'klblkhlkh', '2025-07-06 00:00:00', '8328199', 'transit', 2, '120000.00', 'transfer', 'id_card', '0.00', 'cfwuegfwec', 'checkout', '2025-07-06 00:01:49', '2025-07-06 00:02:11', 0, '0.00', '0.00', NULL, 1, '2025-07-05 17:01:18', '2025-07-05 17:02:11', NULL, NULL),
(19, 7, 'effejj', '2025-07-06 00:10:00', '332323', 'transit', 11, '150000.00', 'cash', 'no_deposit', '0.00', 'zxcascasc', 'checkout', '2025-07-06 00:10:58', '2025-07-06 00:11:26', 0, '0.00', '0.00', NULL, 1, '2025-07-05 17:10:49', '2025-07-05 17:11:26', NULL, NULL),
(20, 2, 'dama', '2025-07-07 11:02:00', '2125413651', 'transit', 1, '100000.00', 'cash', 'no_deposit', '0.00', 'sqadcqsawdqw', 'completed', '2025-07-07 10:59:03', '2025-07-07 10:59:23', 0, '0.00', '0.00', NULL, 1, '2025-07-07 03:58:57', '2025-07-07 03:59:28', NULL, NULL),
(21, 3, 'albi', '2025-07-07 13:59:00', '1256671', 'fullday', 23, '300000.00', 'qris', 'no_deposit', '0.00', '', 'completed', '2025-07-07 13:56:02', '2025-07-07 13:56:34', 0, '0.00', '0.00', NULL, 1, '2025-07-07 06:55:57', '2025-07-07 06:56:37', NULL, NULL),
(22, 10, 'nnnn', '2025-07-07 14:00:00', '784544', 'transit', 1, '50000.00', 'cash', 'cash', '10000.00', 'cwcvaqcvawqs', 'completed', '2025-07-07 13:59:21', '2025-07-07 13:59:26', 0, '0.00', '0.00', NULL, 1, '2025-07-07 06:59:16', '2025-07-07 06:59:29', NULL, NULL),
(23, 2, 'nami', '2025-07-07 15:45:00', '12434123', 'transit', 1, '100000.00', 'transfer', 'no_deposit', '0.00', '12e1dsd', 'completed', '2025-07-07 15:41:22', '2025-07-07 15:41:32', 0, '0.00', '0.00', NULL, 3, '2025-07-07 08:41:17', '2025-07-07 08:41:35', NULL, NULL),
(24, 3, 'jojo', '2025-07-07 22:44:00', '0856439137974', 'transit', 1, '150000.00', 'qris', 'no_deposit', '0.00', 'test', 'completed', '2025-07-07 21:44:38', '2025-07-07 21:44:46', 0, '0.00', '0.00', NULL, 1, '2025-07-07 14:44:32', '2025-07-07 14:44:50', NULL, NULL),
(25, 2, 'bash', '2025-07-08 16:09:00', '12332623562', 'transit', 1, '125000.00', 'cash', 'id_card', '0.00', 'hgfdsghdssdghd', 'completed', '2025-07-08 16:03:06', '2025-07-08 16:03:18', 0, '0.00', '0.00', NULL, 1, '2025-07-08 09:02:58', '2025-07-08 09:03:21', NULL, NULL),
(26, 2, 'jona', '2025-07-08 16:24:00', '325632652', 'fullday', 20, '400000.00', 'cash', 'id_card', '0.00', 'dfvdswfwf', 'completed', '2025-07-08 16:22:39', '2025-07-08 16:22:57', 0, '0.00', '0.00', NULL, 1, '2025-07-08 09:22:35', '2025-07-08 09:23:05', NULL, NULL),
(27, 2, 'asfsfafsa', '2025-07-08 17:30:00', '123414321', 'transit', 1, '100000.00', 'cash', 'id_card', '0.00', 'efwef1er1', 'completed', '2025-07-08 17:29:21', '2025-07-08 21:04:14', 0, '0.00', '0.00', NULL, 3, '2025-07-08 10:29:16', '2025-07-08 14:04:17', NULL, NULL),
(28, 2, 'jo', '2025-07-08 22:00:00', '0856625575', 'transit', 3, '100000.00', 'cash', 'no_deposit', '0.00', 'bebas\n', 'completed', '2025-07-08 21:07:09', '2025-07-08 21:07:50', 0, '0.00', '0.00', NULL, 3, '2025-07-08 14:06:15', '2025-07-08 14:08:44', NULL, NULL),
(29, 2, 'karim', '2025-07-08 23:25:00', '235523532', 'transit', 3, '150000.00', 'cash', 'no_deposit', '0.00', '', 'completed', '2025-07-08 23:22:45', '2025-07-08 23:23:05', 0, '0.00', '0.00', NULL, 3, '2025-07-08 16:21:53', '2025-07-08 16:26:15', NULL, NULL),
(30, 3, 'babi', '2025-07-08 23:25:00', '362464', 'transit', 1, '100000.00', 'transfer', 'no_deposit', '0.00', '', 'no_show', NULL, NULL, 0, '0.00', '0.00', NULL, 3, '2025-07-08 16:23:56', '2025-07-08 16:25:44', NULL, NULL),
(31, 5, 'maya', '2025-07-08 23:26:00', '315433512', 'transit', 1, '100000.00', 'qris', 'cash', '10000.00', 'rehfh', 'completed', '2025-07-08 23:27:35', '2025-07-12 11:34:48', 0, '0.00', '0.00', NULL, 3, '2025-07-08 16:27:31', '2025-07-12 04:34:51', NULL, NULL),
(32, 3, 'raya', '2025-07-08 23:28:00', '315631263', 'fullday', 13, '120000.00', 'cash', 'id_card', '0.00', 'svasdv', 'completed', '2025-07-08 23:29:19', '2025-07-08 23:29:26', 0, '0.00', '0.00', NULL, 3, '2025-07-08 16:29:14', '2025-07-09 02:29:18', NULL, NULL),
(33, 5, 'mayaee', '2025-07-12 11:37:00', '123213', 'transit', 1, '1231.00', 'cash', 'id_card', '0.00', '', 'completed', '2025-07-12 11:35:34', '2025-07-12 14:03:23', 0, '0.00', '0.00', NULL, 2, '2025-07-12 04:35:28', '2025-07-12 07:03:26', NULL, NULL),
(34, 9, 'zenmio', '2025-07-12 14:07:00', '12462623', 'transit', 1, '123.00', 'cash', 'id_card', '0.00', '12e12e', 'completed', '2025-07-12 14:07:50', '2025-07-14 16:51:47', 0, '0.00', '0.00', NULL, 2, '2025-07-12 07:04:26', '2025-07-18 02:35:46', NULL, NULL),
(35, 2, 'asdfasd', '2025-07-12 18:10:00', '123412', 'fullday', 18, '123423.00', 'cash', 'no_deposit', '0.00', '', 'completed', '2025-07-12 18:10:25', '2025-07-13 09:53:27', 0, '0.00', '0.00', NULL, 1, '2025-07-12 11:10:21', '2025-07-13 02:53:51', NULL, NULL),
(36, 3, 'jkkkk', '2025-07-12 19:35:00', '47656', 'fullday', 17, '5679.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-12 19:32:43', '2025-07-13 09:53:45', 0, '0.00', '0.00', NULL, 1, '2025-07-12 12:32:39', '2025-07-13 02:53:48', NULL, NULL),
(37, 4, 'niku', '2025-07-13 09:56:00', '123', 'transit', 1, '12331.00', 'cash', 'no_deposit', '0.00', '', 'completed', '2025-07-13 09:54:20', '2025-07-13 11:18:29', 0, '0.00', '0.00', NULL, 2, '2025-07-13 02:54:15', '2025-07-13 04:18:33', NULL, NULL),
(38, 4, 'mina', '2025-07-13 11:21:00', '4567', 'transit', 1, '1234.00', 'cash', 'id_card', '0.00', 'hihghb', 'completed', '2025-07-13 11:19:23', '2025-07-14 14:22:32', 0, '0.00', '0.00', NULL, 2, '2025-07-13 04:19:03', '2025-07-14 07:37:42', NULL, NULL),
(39, 2, 'fas', '2025-07-14 14:39:00', '123123', 'transit', 1, '123.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-14 14:38:59', '2025-07-14 16:51:51', 0, '0.00', '0.00', NULL, 1, '2025-07-14 07:38:04', '2025-07-15 06:23:11', NULL, NULL),
(40, 3, 'sadfdf', '2025-07-14 14:43:00', '1231', 'fullday', 22, '1233.00', 'qris', 'id_card', '0.00', '', 'completed', '2025-07-14 14:38:56', '2025-07-15 13:23:07', 0, '0.00', '0.00', NULL, 1, '2025-07-14 07:38:22', '2025-07-15 10:24:55', NULL, NULL),
(41, 4, 'asdfqwedqwd', '2025-07-14 14:44:00', '1233313', 'transit', 3, '12132312.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-14 14:40:17', '2025-07-14 17:40:19', 0, '0.00', '0.00', NULL, 1, '2025-07-14 07:38:47', '2025-07-18 03:52:27', NULL, NULL),
(42, 6, 'efwwef', '2025-07-14 14:45:00', '123321', 'transit', 1, '12312.00', 'transfer', 'cash', '121211.00', '', 'completed', '2025-07-14 14:39:49', '2025-07-14 14:40:21', 0, '0.00', '0.00', NULL, 1, '2025-07-14 07:39:38', '2025-07-18 02:35:36', NULL, NULL),
(43, 5, 'qwreqw', '2025-07-14 14:45:00', '13333', 'transit', 12, '123131.00', 'qris', 'no_deposit', '0.00', '12e12', 'completed', '2025-07-14 16:20:47', '2025-07-15 13:23:07', 0, '0.00', '0.00', NULL, 1, '2025-07-14 07:40:11', '2025-07-18 02:35:39', NULL, NULL),
(44, 2, 'mansnn', '2025-07-15 17:24:00', '123123', 'transit', 1, '1233.00', 'transfer', 'no_deposit', '0.00', '', 'completed', '2025-07-15 17:24:59', '2025-07-15 18:25:20', 0, '0.00', '0.00', NULL, 2, '2025-07-15 10:19:03', '2025-07-16 06:54:34', NULL, NULL),
(45, 3, 'ajax', '2025-07-15 17:30:00', '124321', 'transit', 2, '124312.00', 'transfer', 'no_deposit', '0.00', '', 'completed', '2025-07-15 17:25:26', '2025-07-15 19:26:20', 0, '0.00', '0.00', NULL, 2, '2025-07-15 10:25:20', '2025-07-18 02:19:43', NULL, NULL),
(46, 1, 'Default Guest', '2025-07-16 13:29:00', '6666666', 'transit', 1, '12133.00', 'cash', 'id_card', '0.00', '', 'completed', '2025-07-16 13:29:38', '2025-07-16 15:24:08', 0, '0.00', '0.00', NULL, 2, '2025-07-16 06:29:28', '2025-07-18 02:35:42', NULL, NULL),
(47, 2, 'Default Guest', '2025-07-18 08:30:00', '6666666', 'transit', 1, '12222.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-18 08:30:26', '2025-07-18 09:33:21', 0, '0.00', '0.00', NULL, 2, '2025-07-18 01:30:20', '2025-07-18 02:33:25', NULL, NULL),
(48, 3, 'Default Guest', '2025-07-18 09:24:00', '6666666', 'fullday', 27, '123556.00', 'qris', 'no_deposit', '0.00', '', 'completed', '2025-07-18 09:20:12', '2025-07-18 09:20:33', 0, '0.00', '0.00', NULL, 2, '2025-07-18 02:19:59', '2025-07-18 02:20:39', NULL, NULL),
(49, 3, 'Default Guest', '2025-07-18 09:25:00', '6666666', 'fullday', 27, '423.00', 'qris', 'no_deposit', '0.00', 'asdfsaqwd', 'completed', '2025-07-18 09:20:58', '2025-07-18 09:55:45', 0, '0.00', '0.00', NULL, 2, '2025-07-18 02:20:53', '2025-07-18 02:55:48', NULL, NULL),
(50, 2, 'Default Guest', '2025-07-18 08:35:00', '6666666', 'transit', 1, '123.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-18 08:37:48', '2025-07-18 09:37:49', 0, '0.00', '0.00', NULL, 2, '2025-07-18 01:37:43', '2025-07-18 03:55:32', NULL, NULL),
(51, 3, 'Default Guest', '2025-07-18 09:57:00', '6666666', 'fullday', 27, '12334.00', 'qris', 'id_card', '0.00', '', 'completed', '2025-07-18 09:56:07', '2025-07-18 11:25:29', 0, '0.00', '0.00', NULL, 2, '2025-07-18 02:56:03', '2025-07-18 04:25:35', NULL, NULL),
(52, 4, 'Default Guest', '2025-07-18 10:54:00', '6666666', 'fullday', 26, '5321.00', 'cash', 'no_deposit', '0.00', '', 'completed', '2025-07-18 10:52:45', '2025-07-18 11:25:31', 0, '0.00', '0.00', NULL, 2, '2025-07-18 03:52:41', '2025-07-18 04:25:38', NULL, NULL),
(53, 2, 'Default Guest', '2025-07-18 10:55:00', '6666666', 'fullday', 26, '12.00', 'transfer', 'no_deposit', '0.00', '', 'completed', '2025-07-18 10:55:46', '2025-07-18 11:25:42', 0, '0.00', '0.00', NULL, 2, '2025-07-18 03:55:42', '2025-07-18 04:25:51', NULL, NULL),
(54, 1, 'Default Guest', '2025-07-18 10:56:00', '6666666', 'fullday', 26, '12.00', 'transfer', 'no_deposit', '0.00', '', 'completed', '2025-07-18 10:57:01', '2025-07-18 11:25:46', 0, '0.00', '0.00', NULL, 2, '2025-07-18 03:56:57', '2025-07-18 04:25:54', NULL, NULL),
(55, 5, 'Default Guest', '2025-07-18 11:21:00', '6666666', 'fullday', 25, '444.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-18 11:17:19', '2025-07-18 11:20:10', 0, '0.00', '0.00', NULL, 2, '2025-07-18 04:17:15', '2025-07-18 04:25:57', NULL, NULL),
(56, 6, 'Default Guest', '2025-07-18 11:19:00', '6666666', 'fullday', 25, '33.00', 'cash', 'id_card', '0.00', '', 'completed', '2025-07-18 11:19:54', '2025-07-18 11:20:07', 0, '0.00', '0.00', NULL, 2, '2025-07-18 04:19:43', '2025-07-18 04:25:59', NULL, NULL),
(57, 2, 'Default Guest', '2025-07-18 11:26:00', '6666666', 'fullday', 25, '12312.00', 'transfer', 'id_card', '0.00', 'sf', 'completed', '2025-07-18 11:26:14', '2025-07-18 11:33:26', 0, '0.00', '0.00', NULL, 2, '2025-07-18 04:26:10', '2025-07-18 04:33:39', NULL, '2025-07-19 12:00:00'),
(58, 3, 'Default Guest', '2025-07-18 11:33:00', '6666666', 'fullday', 25, '1232.00', 'transfer', 'id_card', '0.00', '', 'completed', '2025-07-18 11:33:54', '2025-07-18 11:33:55', 0, '0.00', '0.00', NULL, 2, '2025-07-18 04:33:50', '2025-07-18 05:01:46', NULL, '2025-07-19 12:00:00'),
(59, 2, 'Default Guest', '2025-07-18 11:35:00', '6666666', 'fullday', 25, '123.00', 'transfer', 'id_card', '0.00', '', 'checkout', '2025-07-18 11:35:37', '2025-07-18 11:35:38', 0, '0.00', '0.00', NULL, 2, '2025-07-18 04:35:33', '2025-07-18 04:35:38', NULL, '2025-07-19 12:00:00'),
(60, 4, 'Default Guest', '2025-07-18 11:37:00', '6666666', 'fullday', 25, '2121.00', 'cash', 'id_card', '0.00', '123ew12', 'completed', '2025-07-18 11:38:05', '2025-07-18 12:12:26', 0, '0.00', '678.00', 'cash', 2, '2025-07-18 04:38:01', '2025-07-18 05:12:29', NULL, '2025-07-19 12:00:00'),
(61, 3, 'Default Guest', '2025-07-18 12:06:00', '6666666', 'transit', 1, '7887.00', 'transfer', 'id_card', '0.00', '', 'checkin', '2025-07-18 12:02:08', NULL, 0, '0.00', '0.00', NULL, 2, '2025-07-18 05:02:05', '2025-07-18 05:02:08', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `wifi_name` varchar(100) DEFAULT NULL,
  `wifi_password` varchar(100) DEFAULT NULL,
  `status` enum('ready','booked','checkin','checkout') DEFAULT 'ready',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `location`, `floor_number`, `room_number`, `room_type`, `wifi_name`, `wifi_password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Building A', 2, '205', 'Deluxe', 'Test-Wifi', 'wifiku123', 'ready', '2025-07-02 13:34:57', '2025-07-18 04:25:54'),
(2, 'Building A', 1, '102', 'Standard', 'KIA_WiFi_102', 'kia123102', 'checkout', '2025-07-02 13:34:57', '2025-07-18 04:35:38'),
(3, 'Building A', 2, '201', 'Deluxe', 'KIA_WiFi_201', 'kia123201', 'checkin', '2025-07-02 13:34:57', '2025-07-18 05:02:08'),
(4, 'Building A', 2, '202', 'Deluxe', 'KIA_WiFi_202', 'kia123202', 'ready', '2025-07-02 13:34:57', '2025-07-18 05:12:29'),
(5, 'Building B', 1, '103', 'Standard', 'KIA_WiFi_103', 'kia123103', 'ready', '2025-07-02 13:34:57', '2025-07-18 04:25:57'),
(6, 'Building B', 2, '203', 'Suite', 'KIA_WiFi_203', 'kia123203', 'ready', '2025-07-02 13:34:57', '2025-07-18 04:25:59'),
(7, 'Gedung B', 8, '19', 'VIP', 'aparttest', 'test123', 'ready', '2025-07-02 14:37:35', '2025-07-05 17:11:32'),
(8, 'Gedung Pink ', 12, '1205', 'Standard', 'fujisan', 'uji12000', 'ready', '2025-07-04 06:21:06', '2025-07-04 06:21:06'),
(9, 'Gedung Pink ', 18, '1805', 'Standard', 'dawg', 'wifikasasa', 'ready', '2025-07-04 10:52:49', '2025-07-18 02:35:46'),
(10, 'Columbus', 1, '222', 'Standard', 'vantek', 'gina', 'ready', '2025-07-07 06:58:22', '2025-07-07 06:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `shift_reports`
--

CREATE TABLE `shift_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `total_transactions` int(11) DEFAULT '0',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `shift_reports`
--

INSERT INTO `shift_reports` (`id`, `user_id`, `shift_date`, `total_transactions`, `total_amount`, `notes`, `created_at`) VALUES
(1, 2, '2025-07-03', 2, '350000.00', '', '2025-07-03 10:43:19'),
(2, 3, '2025-07-03', 0, NULL, '', '2025-07-03 11:59:05'),
(3, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:38:01'),
(4, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:38:32'),
(5, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:39:02'),
(6, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:39:32'),
(7, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:40:03'),
(8, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:40:34'),
(9, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:41:05'),
(10, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:41:36'),
(11, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:42:07'),
(12, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:42:38'),
(13, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:43:09'),
(14, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:43:40'),
(15, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:44:11'),
(16, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:44:42'),
(17, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:45:13'),
(18, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:45:44'),
(19, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:46:15'),
(20, 1, '2025-07-07', 3, '450000.00', 'test', '2025-07-07 07:04:21'),
(21, 1, '2025-07-07', 3, '450000.00', 'test', '2025-07-07 07:04:51'),
(22, 3, '2025-07-08', 6, '670000.00', 'ktp abi', '2025-07-08 16:32:57');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `transaction_type` enum('booking','extra_time','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) NOT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `booking_id`, `transaction_type`, `amount`, `payment_method`, `transaction_date`, `created_by`, `notes`) VALUES
(1, 1, 'booking', '100000.00', 'cash', '2025-07-02 20:38:59', 1, NULL),
(2, 1, 'refund', '-100000.00', 'cash', '2025-07-02 20:39:36', 1, NULL),
(3, 2, 'booking', '150000.00', 'cash', '2025-07-02 21:34:44', 2, NULL),
(4, 2, 'extra_time', '50000.00', 'cash', '2025-07-02 21:35:28', 2, NULL),
(5, 3, 'booking', '200000.00', 'transfer', '2025-07-03 09:17:38', 2, NULL),
(6, 4, 'booking', '150000.00', 'cash', '2025-07-03 13:10:38', 2, NULL),
(7, 5, 'booking', '120000.00', 'card', '2025-07-03 17:59:43', 2, NULL),
(8, 6, 'booking', '100000.00', 'cash', '2025-07-03 18:45:41', 2, NULL),
(9, 6, 'extra_time', '100000.00', 'cash', '2025-07-03 18:46:08', 2, NULL),
(10, 7, 'booking', '-0.02', 'cash', '2025-07-03 18:48:39', 2, NULL),
(11, 8, 'booking', '100000.00', 'cash', '2025-07-04 11:07:14', 1, NULL),
(12, 9, 'booking', '120000.00', 'cash', '2025-07-04 11:52:56', 1, NULL),
(13, 10, 'booking', '70000.00', 'cash', '2025-07-04 13:16:39', 1, NULL),
(14, 11, 'booking', '180000.00', 'cash', '2025-07-04 13:17:21', 1, NULL),
(15, 12, 'booking', '888000.00', 'cash', '2025-07-04 13:18:17', 1, NULL),
(16, 13, 'booking', '1500000.00', 'cash', '2025-07-04 17:53:58', 1, NULL),
(17, 14, 'booking', '150000.00', 'cash', '2025-07-05 00:29:07', 1, NULL),
(18, 15, 'booking', '15000.00', 'cash', '2025-07-05 01:57:46', 1, NULL),
(19, 16, 'booking', '300000.00', 'cash', '2025-07-05 10:37:25', 1, NULL),
(20, 17, 'booking', '250000.00', 'cash', '2025-07-05 11:58:23', 1, NULL),
(21, 18, 'booking', '120000.00', 'transfer', '2025-07-06 00:01:18', 1, NULL),
(22, 19, 'booking', '150000.00', 'cash', '2025-07-06 00:10:49', 1, NULL),
(23, 20, 'booking', '100000.00', 'cash', '2025-07-07 10:58:57', 1, NULL),
(24, 21, 'booking', '300000.00', 'qris', '2025-07-07 13:55:57', 1, NULL),
(25, 22, 'booking', '50000.00', 'cash', '2025-07-07 13:59:16', 1, NULL),
(26, 23, 'booking', '100000.00', 'transfer', '2025-07-07 15:41:17', 3, NULL),
(27, 24, 'booking', '150000.00', 'qris', '2025-07-07 21:44:32', 1, NULL),
(28, 25, 'booking', '125000.00', 'cash', '2025-07-08 16:02:58', 1, NULL),
(29, 26, 'booking', '400000.00', 'cash', '2025-07-08 16:22:35', 1, NULL),
(30, 27, 'booking', '100000.00', 'cash', '2025-07-08 17:29:16', 3, NULL),
(31, 28, 'booking', '100000.00', 'cash', '2025-07-08 21:06:15', 3, NULL),
(32, 29, 'booking', '150000.00', 'cash', '2025-07-08 23:21:53', 3, NULL),
(33, 30, 'booking', '100000.00', 'transfer', '2025-07-08 23:23:56', 3, NULL),
(34, 31, 'booking', '100000.00', 'qris', '2025-07-08 23:27:31', 3, NULL),
(35, 32, 'booking', '120000.00', 'cash', '2025-07-08 23:29:14', 3, NULL),
(36, 33, 'booking', '1231.00', 'cash', '2025-07-12 11:35:28', 2, NULL),
(37, 34, 'booking', '123.00', 'cash', '2025-07-12 14:04:26', 2, NULL),
(38, 35, 'booking', '123423.00', 'cash', '2025-07-12 18:10:21', 1, NULL),
(39, 36, 'booking', '5679.00', 'transfer', '2025-07-12 19:32:39', 1, NULL),
(40, 37, 'booking', '12331.00', 'cash', '2025-07-13 09:54:15', 2, NULL),
(41, 38, 'booking', '1234.00', 'cash', '2025-07-13 11:19:03', 2, NULL),
(42, 39, 'booking', '123.00', 'transfer', '2025-07-14 14:38:04', 1, NULL),
(43, 40, 'booking', '1233.00', 'qris', '2025-07-14 14:38:22', 1, NULL),
(44, 41, 'booking', '12132312.00', 'transfer', '2025-07-14 14:38:47', 1, NULL),
(45, 42, 'booking', '12312.00', 'transfer', '2025-07-14 14:39:38', 1, NULL),
(46, 43, 'booking', '123131.00', 'qris', '2025-07-14 14:40:11', 1, NULL),
(47, 44, 'booking', '1233.00', 'transfer', '2025-07-15 17:19:03', 2, NULL),
(48, 45, 'booking', '124312.00', 'transfer', '2025-07-15 17:25:20', 2, NULL),
(49, 46, 'booking', '12133.00', 'cash', '2025-07-16 13:29:28', 2, NULL),
(50, 47, 'booking', '12222.00', 'transfer', '2025-07-18 09:19:20', 2, NULL),
(51, 48, 'booking', '123556.00', 'qris', '2025-07-18 09:19:59', 2, NULL),
(52, 49, 'booking', '423.00', 'qris', '2025-07-18 09:20:53', 2, NULL),
(53, 50, 'booking', '123.00', 'transfer', '2025-07-18 09:33:43', 2, NULL),
(54, 51, 'booking', '12334.00', 'qris', '2025-07-18 09:56:03', 2, NULL),
(55, 52, 'booking', '5321.00', 'cash', '2025-07-18 10:52:41', 2, NULL),
(56, 53, 'booking', '12.00', 'transfer', '2025-07-18 10:55:42', 2, NULL),
(57, 54, 'booking', '12.00', 'transfer', '2025-07-18 10:56:57', 2, NULL),
(58, 55, 'booking', '444.00', 'transfer', '2025-07-18 11:17:15', 2, NULL),
(59, 56, 'booking', '33.00', 'cash', '2025-07-18 11:19:43', 2, NULL),
(60, 57, 'booking', '12312.00', 'transfer', '2025-07-18 11:26:10', 2, NULL),
(61, 58, 'booking', '1232.00', 'transfer', '2025-07-18 11:33:50', 2, NULL),
(62, 59, 'booking', '123.00', 'transfer', '2025-07-18 11:35:33', 2, NULL),
(63, 60, 'booking', '2121.00', 'cash', '2025-07-18 11:38:01', 2, NULL),
(64, 61, 'booking', '7887.00', 'transfer', '2025-07-18 12:02:05', 2, NULL),
(65, 60, 'refund', '-678.00', 'cash', '2025-07-18 12:12:21', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('superuser','admin','cashier') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'superuser', '$2y$10$J7gL8Uoq0wcSXNAJG19NLO0Ft86S95GnSpd91NXy1eM6Ap7zAhDL6', 'Super User', 'superuser', '2025-07-02 13:34:56', '2025-07-07 09:00:28'),
(2, 'admin', '$2y$10$C1pnZTcjw8RLW8OSIHhmWOgD/G5KmyB/X9ZBmhis1GV.CeZDgCfCe', 'kia', 'admin', '2025-07-02 13:45:05', '2025-07-02 13:45:05'),
(3, 'jotaro', '$2y$10$W9hrM3.ixHuLIgrosKYC0.cFCZG/1BjbTJGfgbnaIpQG6WqNUfbZi', 'kasirtest', 'cashier', '2025-07-02 15:10:31', '2025-07-07 08:39:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shift_reports`
--
ALTER TABLE `shift_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `created_by` (`created_by`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `shift_reports`
--
ALTER TABLE `shift_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `shift_reports`
--
ALTER TABLE `shift_reports`
  ADD CONSTRAINT `shift_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
