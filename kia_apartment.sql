-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Jul 2025 pada 19.15
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Struktur dari tabel `bookings`
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
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `status` enum('booked','checkin','checkout','cancelled','no_show','completed') DEFAULT 'booked',
  `checkin_time` datetime DEFAULT NULL,
  `checkout_time` datetime DEFAULT NULL,
  `extra_time_hours` int(11) DEFAULT 0,
  `extra_time_amount` decimal(10,2) DEFAULT 0.00,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_method` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `guest_name`, `arrival_time`, `phone_number`, `duration_type`, `duration_hours`, `price_amount`, `payment_method`, `deposit_type`, `deposit_amount`, `notes`, `status`, `checkin_time`, `checkout_time`, `extra_time_hours`, `extra_time_amount`, `refund_amount`, `refund_method`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'riru', '2025-07-02 22:43:00', '1239999', 'transit', 3, '100000.00', 'cash', 'cash', '10000.00', 'pinjam kompor', 'checkout', '2025-07-02 20:39:10', '2025-07-02 20:39:44', 0, '0.00', '100000.00', 'cash', 1, '2025-07-02 13:38:59', '2025-07-05 06:36:24'),
(2, 1, 'prof', '2025-07-02 23:40:00', '12345657890', 'transit', 3, '150000.00', 'cash', 'cash', '100000.00', 'pinjam kompor', 'checkout', '2025-07-02 21:34:57', '2025-07-02 21:35:48', 2, '50000.00', '0.00', NULL, 2, '2025-07-02 14:34:44', '2025-07-05 06:36:24'),
(3, 1, 'riru', '2025-07-03 09:18:00', '1239999', 'transit', 4, '200000.00', 'transfer', 'id_card', '10000.00', 'KTP ditahan', 'checkout', '2025-07-03 09:18:09', '2025-07-03 09:34:11', 0, '0.00', '0.00', NULL, 2, '2025-07-03 02:17:38', '2025-07-05 06:36:24'),
(4, 2, 'goku', '2025-07-03 13:12:00', '12221113', 'transit', 2, '150000.00', 'cash', 'no_deposit', '0.00', 'adassss', 'checkout', '2025-07-03 13:10:45', '2025-07-03 17:37:55', 0, '0.00', '0.00', NULL, 2, '2025-07-03 06:10:38', '2025-07-05 06:36:24'),
(5, 2, 'tayo', '2025-07-03 19:00:00', '545678654', 'transit', 1, '120000.00', 'card', 'no_deposit', '100000.00', 'pinjam dildo', 'checkout', '2025-07-03 18:00:24', '2025-07-03 18:00:48', 0, '0.00', '0.00', NULL, 2, '2025-07-03 10:59:43', '2025-07-05 06:36:24'),
(6, 3, 'rirukkkk', '2025-07-03 20:46:00', '12345657890', 'transit', 2, '100000.00', 'cash', 'cash', '10000.00', 'decefcef', 'checkout', '2025-07-03 18:45:48', '2025-07-03 18:48:05', 2, '100000.00', '0.00', NULL, 2, '2025-07-03 11:45:41', '2025-07-05 06:36:24'),
(7, 3, 'riru', '2025-07-05 18:48:00', '12345657890', 'transit', 1, '-0.02', 'cash', 'cash', '988899.00', '', 'checkout', '2025-07-03 18:48:48', '2025-07-03 19:17:30', 0, '0.00', '0.00', NULL, 2, '2025-07-03 11:48:39', '2025-07-05 06:36:24'),
(8, 3, 'tayo', '2025-07-04 11:08:00', '0871827883', 'transit', 1, '100000.00', 'cash', 'cash', '100000.00', 'tdjghdfh', 'checkout', '2025-07-04 11:07:20', '2025-07-04 11:11:11', 0, '0.00', '0.00', NULL, 1, '2025-07-04 04:07:14', '2025-07-05 06:36:24'),
(9, 3, 'prof', '2025-07-04 11:56:00', '32t223t', 'transit', 1, '120000.00', 'cash', 'cash', '10000.00', 'fdhbdfh', 'checkout', '2025-07-04 11:53:02', '2025-07-04 13:16:12', 0, '0.00', '0.00', NULL, 1, '2025-07-04 04:52:56', '2025-07-05 06:36:24'),
(10, 6, 'feknvk.efnv', '2025-07-04 13:18:00', '2078340', 'transit', 1, '70000.00', 'cash', 'cash', '10000.00', 'feihvelfjvlehjv', 'checkout', '2025-07-04 13:16:44', '2025-07-04 14:24:41', 0, '0.00', '0.00', NULL, 1, '2025-07-04 06:16:39', '2025-07-05 06:36:24'),
(11, 5, 'jak', '2025-07-04 13:18:00', '1084308', 'transit', 3, '180000.00', 'cash', 'cash', '100000.00', 'lknvfevev', 'checkout', '2025-07-04 13:17:24', '2025-07-04 14:24:38', 0, '0.00', '0.00', NULL, 1, '2025-07-04 06:17:20', '2025-07-05 06:36:24'),
(12, 1, 'speed', '2025-07-04 13:21:00', '0899927287', 'transit', 1, '888000.00', 'cash', 'id_card', '10000.00', 'viofhhfv', 'checkout', '2025-07-04 13:18:22', '2025-07-04 14:24:31', 0, '0.00', '0.00', NULL, 1, '2025-07-04 06:18:17', '2025-07-05 06:36:24'),
(13, 9, 'akuma', '2025-07-04 17:56:00', '1616161', 'transit', 12, '1500000.00', 'cash', 'cash', '190000.00', 'asannadvdv', 'checkout', '2025-07-04 17:54:06', '2025-07-05 00:27:44', 0, '0.00', '0.00', NULL, 1, '2025-07-04 10:53:58', '2025-07-05 06:36:24'),
(14, 1, 'ilham', '2025-07-05 02:30:00', '12345657890', 'transit', 3, '150000.00', 'cash', 'cash', '50000.00', '', 'checkout', '2025-07-05 00:29:22', '2025-07-05 00:29:37', 0, '0.00', '0.00', NULL, 1, '2025-07-04 17:29:07', '2025-07-05 06:36:24'),
(15, 4, 'waraas', '2025-07-05 03:58:00', '3263262362', 'fullday', 33, '15000.00', 'cash', 'id_card', '0.00', 'fhfdhfdsh', 'checkout', '2025-07-05 01:57:50', '2025-07-05 01:57:55', 0, '0.00', '0.00', NULL, 1, '2025-07-04 18:57:46', '2025-07-05 06:36:24'),
(16, 2, 'damar', '2025-07-05 12:39:00', '0896666322', 'fullday', 24, '300000.00', 'cash', 'id_card', '0.00', 'agadgdgadg', 'checkout', '2025-07-05 10:37:35', '2025-07-05 10:38:25', 0, '0.00', '0.00', NULL, 1, '2025-07-05 03:37:25', '2025-07-05 06:36:24'),
(17, 2, 'qwsacasc', '2025-07-05 11:57:00', '1311232121', 'transit', 2, '250000.00', 'cash', 'no_deposit', '0.00', 'sfbdfbsdzb', 'checkout', '2025-07-05 11:58:33', '2025-07-05 11:58:45', 0, '0.00', '0.00', NULL, 1, '2025-07-05 04:58:22', '2025-07-05 06:36:24'),
(18, 8, 'klblkhlkh', '2025-07-06 00:00:00', '8328199', 'transit', 2, '120000.00', 'transfer', 'id_card', '0.00', 'cfwuegfwec', 'checkout', '2025-07-06 00:01:49', '2025-07-06 00:02:11', 0, '0.00', '0.00', NULL, 1, '2025-07-05 17:01:18', '2025-07-05 17:02:11'),
(19, 7, 'effejj', '2025-07-06 00:10:00', '332323', 'transit', 11, '150000.00', 'cash', 'no_deposit', '0.00', 'zxcascasc', 'checkout', '2025-07-06 00:10:58', '2025-07-06 00:11:26', 0, '0.00', '0.00', NULL, 1, '2025-07-05 17:10:49', '2025-07-05 17:11:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rooms`
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `rooms`
--

INSERT INTO `rooms` (`id`, `location`, `floor_number`, `room_number`, `room_type`, `wifi_name`, `wifi_password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Building A', 2, '205', 'Deluxe', 'Test-Wifi', 'wifiku123', 'ready', '2025-07-02 13:34:57', '2025-07-03 10:39:29'),
(2, 'Building A', 1, '102', 'Standard', 'KIA_WiFi_102', 'kia123102', 'ready', '2025-07-02 13:34:57', '2025-07-02 13:34:57'),
(3, 'Building A', 2, '201', 'Deluxe', 'KIA_WiFi_201', 'kia123201', 'ready', '2025-07-02 13:34:57', '2025-07-02 13:34:57'),
(4, 'Building A', 2, '202', 'Deluxe', 'KIA_WiFi_202', 'kia123202', 'ready', '2025-07-02 13:34:57', '2025-07-02 13:34:57'),
(5, 'Building B', 1, '103', 'Standard', 'KIA_WiFi_103', 'kia123103', 'ready', '2025-07-02 13:34:57', '2025-07-02 13:34:57'),
(6, 'Building B', 2, '203', 'Suite', 'KIA_WiFi_203', 'kia123203', 'ready', '2025-07-02 13:34:57', '2025-07-02 13:34:57'),
(7, 'Gedung B', 8, '19', 'VIP', 'aparttest', 'test123', 'ready', '2025-07-02 14:37:35', '2025-07-05 17:11:32'),
(8, 'Gedung Pink ', 12, '1205', 'Standard', 'fujisan', 'uji12000', 'ready', '2025-07-04 06:21:06', '2025-07-04 06:21:06'),
(9, 'Gedung Pink ', 18, '1805', 'Standard', 'dawg', 'wifikasasa', 'ready', '2025-07-04 10:52:49', '2025-07-04 10:52:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shift_reports`
--

CREATE TABLE `shift_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `total_transactions` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `shift_reports`
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
(19, 1, '2025-07-05', 4, '715000.00', 'xz xz zx', '2025-07-05 05:46:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `transaction_type` enum('booking','extra_time','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `transactions`
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
(22, 19, 'booking', '150000.00', 'cash', '2025-07-06 00:10:49', 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('superuser','admin','cashier') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'superuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super User', 'superuser', '2025-07-02 13:34:56', '2025-07-02 13:34:56'),
(2, 'admin', '$2y$10$C1pnZTcjw8RLW8OSIHhmWOgD/G5KmyB/X9ZBmhis1GV.CeZDgCfCe', 'kia', 'admin', '2025-07-02 13:45:05', '2025-07-02 13:45:05'),
(3, 'kasir', '$2y$10$VAig3xjG3f7fEUfKdMoXNOS9VJiQuZs5OPIHnN9SAAau2DK6gWwn2', 'kasirtest', 'cashier', '2025-07-02 15:10:31', '2025-07-02 15:10:31');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `shift_reports`
--
ALTER TABLE `shift_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `shift_reports`
--
ALTER TABLE `shift_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `shift_reports`
--
ALTER TABLE `shift_reports`
  ADD CONSTRAINT `shift_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
