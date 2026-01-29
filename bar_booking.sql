-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2026 at 02:30 PM
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
-- Database: `bar_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `table_number` varchar(10) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_slip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `table_number`, `quantity`, `status`, `booking_date`, `customer_name`, `customer_phone`, `customer_email`, `total_price`, `payment_slip`) VALUES
(1, 1, 3, NULL, 1, 'confirmed', '2026-01-24 07:43:45', 'Komon Thungmanee', '0923959606', NULL, 5.00, 'uploads/slip_69747831a1777.jpg'),
(2, 1, 3, NULL, 1, 'confirmed', '2026-01-25 12:28:01', 'Komon Thungmanee', '0923959606', NULL, 5.00, 'uploads/slip_69760c5174cea.jpg'),
(3, 1, 3, NULL, 1, 'cancelled', '2026-01-25 12:28:20', 'Komon Thungmanee', '0923959606', NULL, 5.00, 'uploads/slip_69760c64c7601.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `prefix` varchar(10) DEFAULT 'TKT',
  `start_num` int(11) DEFAULT 1,
  `max_tickets` int(11) DEFAULT 100,
  `current_sold` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `ticket_price`, `image_url`, `prefix`, `start_num`, `max_tickets`, `current_sold`) VALUES
(2, 'Concert Metalica', '1 ใบ 4 ท่าน ', '2026-01-24 15:00:00', 3500.00, 'uploads/evt_697475fae8a99.jpg', 'MTC', 1, 350, 0),
(3, 'TAF Concert', '1 ท่านต่อใบ', '2026-02-07 18:00:00', 5.00, 'uploads/evt_69747756d77d3.gif', 'TAF', 1, 100, 3);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `details`, `image_url`, `created_at`) VALUES
(2, 'Promotion 1 free 1 ', 'แค่คุณสั่ง Nirvana 1 กรม แภม อีก กรม ในราคา 500 บาท', 'uploads/pro_69748d2f40abe.jpg', '2026-01-24 09:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `table_number` varchar(50) NOT NULL,
  `guest_count` int(11) DEFAULT 1,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `zone` varchar(50) NOT NULL,
  `status` enum('available','maintenance') DEFAULT 'available',
  `row_idx` int(11) DEFAULT 1,
  `col_idx` int(11) DEFAULT 1,
  `price_modifier` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `table_name`, `zone`, `status`, `row_idx`, `col_idx`, `price_modifier`) VALUES
(1, 'A1', 'Standard', 'available', 1, 1, 0.00),
(2, 'A2', 'Standard', 'available', 1, 2, 0.00),
(3, 'A3', 'Standard', 'available', 1, 3, 0.00),
(4, 'A4', 'Standard', 'available', 1, 4, 0.00),
(5, 'A5', 'Standard', 'available', 1, 5, 0.00),
(6, 'A6', 'Standard', 'available', 1, 6, 0.00),
(7, 'A7', 'Standard', 'available', 1, 7, 0.00),
(8, 'A8', 'Standard', 'available', 1, 8, 0.00),
(9, 'A9', 'Standard', 'available', 1, 9, 0.00),
(10, 'A10', 'Standard', 'available', 1, 10, 0.00),
(11, 'B1', 'Standard', 'available', 2, 1, 0.00),
(12, 'B2', 'Standard', 'available', 2, 2, 0.00),
(13, 'B3', 'Standard', 'available', 2, 3, 0.00),
(14, 'B4', 'Standard', 'available', 2, 4, 0.00),
(15, 'B5', 'Standard', 'available', 2, 5, 0.00),
(16, 'B6', 'Standard', 'available', 2, 6, 0.00),
(17, 'B7', 'Standard', 'available', 2, 7, 0.00),
(18, 'B8', 'Standard', 'available', 2, 8, 0.00),
(19, 'B9', 'Standard', 'available', 2, 9, 0.00),
(20, 'B10', 'Standard', 'available', 2, 10, 0.00),
(21, 'C1', 'Standard', 'available', 3, 1, 0.00),
(22, 'C2', 'Standard', 'available', 3, 2, 0.00),
(23, 'C3', 'Standard', 'available', 3, 3, 0.00),
(24, 'C4', 'Standard', 'available', 3, 4, 0.00),
(25, 'C5', 'Standard', 'available', 3, 5, 0.00),
(26, 'C6', 'Standard', 'available', 3, 6, 0.00),
(27, 'C7', 'Standard', 'available', 3, 7, 0.00),
(28, 'C8', 'Standard', 'available', 3, 8, 0.00),
(29, 'C9', 'Standard', 'available', 3, 9, 0.00),
(30, 'C10', 'Standard', 'available', 3, 10, 0.00),
(31, 'D1', 'Standard', 'available', 4, 1, 0.00),
(32, 'D2', 'Standard', 'available', 4, 2, 0.00),
(33, 'D3', 'Standard', 'available', 4, 3, 0.00),
(34, 'D4', 'Standard', 'available', 4, 4, 0.00),
(35, 'D5', 'Standard', 'available', 4, 5, 0.00),
(36, 'D6', 'Standard', 'available', 4, 6, 0.00),
(37, 'D7', 'Standard', 'available', 4, 7, 0.00),
(38, 'D8', 'Standard', 'available', 4, 8, 0.00),
(39, 'D9', 'Standard', 'available', 4, 9, 0.00),
(40, 'D10', 'Standard', 'available', 4, 10, 0.00),
(41, 'E1', 'Standard', 'available', 5, 1, 0.00),
(42, 'E2', 'Standard', 'available', 5, 2, 0.00),
(43, 'E3', 'Standard', 'available', 5, 3, 0.00),
(44, 'E4', 'Standard', 'available', 5, 4, 0.00),
(45, 'E5', 'Standard', 'available', 5, 5, 0.00),
(46, 'E6', 'Standard', 'available', 5, 6, 0.00),
(47, 'E7', 'Standard', 'available', 5, 7, 0.00),
(48, 'E8', 'Standard', 'available', 5, 8, 0.00),
(49, 'E9', 'Standard', 'available', 5, 9, 0.00),
(50, 'E10', 'Standard', 'available', 5, 10, 0.00),
(51, 'F1', 'Standard', 'available', 6, 1, 0.00),
(52, 'F2', 'Standard', 'available', 6, 2, 0.00),
(53, 'F3', 'Standard', 'available', 6, 3, 0.00),
(54, 'F4', 'Standard', 'available', 6, 4, 0.00),
(55, 'F5', 'Standard', 'available', 6, 5, 0.00),
(56, 'F6', 'Standard', 'available', 6, 6, 0.00),
(57, 'F7', 'Standard', 'available', 6, 7, 0.00),
(58, 'F8', 'Standard', 'available', 6, 8, 0.00),
(59, 'F9', 'Standard', 'available', 6, 9, 0.00),
(60, 'F10', 'Standard', 'available', 6, 10, 0.00),
(61, 'G1', 'Standard', 'available', 7, 1, 0.00),
(62, 'G2', 'Standard', 'available', 7, 2, 0.00),
(63, 'G3', 'Standard', 'available', 7, 3, 0.00),
(64, 'G4', 'Standard', 'available', 7, 4, 0.00),
(65, 'G5', 'Standard', 'available', 7, 5, 0.00),
(66, 'G6', 'Standard', 'available', 7, 6, 0.00),
(67, 'G7', 'Standard', 'available', 7, 7, 0.00),
(68, 'G8', 'Standard', 'available', 7, 8, 0.00),
(69, 'G9', 'Standard', 'available', 7, 9, 0.00),
(70, 'G10', 'Standard', 'available', 7, 10, 0.00),
(71, 'H1', 'Standard', 'available', 8, 1, 0.00),
(72, 'H2', 'Standard', 'available', 8, 2, 0.00),
(73, 'H3', 'Standard', 'available', 8, 3, 0.00),
(74, 'H4', 'Standard', 'available', 8, 4, 0.00),
(75, 'H5', 'Standard', 'available', 8, 5, 0.00),
(76, 'H6', 'Standard', 'available', 8, 6, 0.00),
(77, 'H7', 'Standard', 'available', 8, 7, 0.00),
(78, 'H8', 'Standard', 'available', 8, 8, 0.00),
(79, 'H9', 'Standard', 'available', 8, 9, 0.00),
(80, 'H10', 'Standard', 'available', 8, 10, 0.00),
(81, 'I1', 'Standard', 'available', 9, 1, 0.00),
(82, 'I2', 'Standard', 'available', 9, 2, 0.00),
(83, 'I3', 'Standard', 'available', 9, 3, 0.00),
(84, 'I4', 'Standard', 'available', 9, 4, 0.00),
(85, 'I5', 'Standard', 'available', 9, 5, 0.00),
(86, 'I6', 'Standard', 'available', 9, 6, 0.00),
(87, 'I7', 'Standard', 'available', 9, 7, 0.00),
(88, 'I8', 'Standard', 'available', 9, 8, 0.00),
(89, 'I9', 'Standard', 'available', 9, 9, 0.00),
(90, 'I10', 'Standard', 'available', 9, 10, 0.00),
(91, 'J1', 'Standard', 'available', 10, 1, 0.00),
(92, 'J2', 'Standard', 'available', 10, 2, 0.00),
(93, 'J3', 'Standard', 'available', 10, 3, 0.00),
(94, 'J4', 'Standard', 'available', 10, 4, 0.00),
(95, 'J5', 'Standard', 'available', 10, 5, 0.00),
(96, 'J6', 'Standard', 'available', 10, 6, 0.00),
(97, 'J7', 'Standard', 'available', 10, 7, 0.00),
(98, 'J8', 'Standard', 'available', 10, 8, 0.00),
(99, 'J9', 'Standard', 'available', 10, 9, 0.00),
(100, 'J10', 'Standard', 'available', 10, 10, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_items`
--

CREATE TABLE `ticket_items` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `ticket_code` varchar(50) NOT NULL,
  `is_used` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_items`
--

INSERT INTO `ticket_items` (`id`, `booking_id`, `ticket_code`, `is_used`) VALUES
(1, 1, 'TAF 001', 0),
(2, 2, 'TAF 002', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$/gIeBA.eJ5jf7GYUrN8rIOt.kQFZDDajloZTsdyJpvXmwuK0gc6bW', 'admin', '2026-01-06 13:59:55'),
(2, 'user', '$2y$10$YUy6ndxar/SiqLvMcFAX6ep.uug8I8z96cvlwv8DlmIgqd1VX4o9e', 'user', '2026-01-06 14:33:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_items`
--
ALTER TABLE `ticket_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `ticket_items`
--
ALTER TABLE `ticket_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `ticket_items`
--
ALTER TABLE `ticket_items`
  ADD CONSTRAINT `ticket_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
