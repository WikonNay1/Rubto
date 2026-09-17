-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2026 at 07:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rubto_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `table_number` varchar(50) NOT NULL,
  `booking_date` date DEFAULT curdate(),
  `booking_time` datetime NOT NULL,
  `expire_time` datetime NOT NULL,
  `people` int(11) DEFAULT 1,
  `status` enum('WAITING','FINDING_RECEIVER','RECEIVER_ACCEPTED','ON_THE_WAY','ARRIVED','CONFIRMED','EXPIRED','CANCELLED') DEFAULT 'FINDING_RECEIVER',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `guests` int(11) DEFAULT 1,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `restaurant_id`, `table_id`, `table_number`, `booking_date`, `booking_time`, `expire_time`, `people`, `status`, `created_at`, `guests`, `note`) VALUES
(11, NULL, 4, NULL, 'EX5', '2026-09-16', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-16 16:06:44', 2, 'Walk-in: Nay'),
(12, NULL, 4, NULL, 'EX6', '2026-09-16', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-16 16:15:06', 2, 'Walk-in: BEST'),
(13, NULL, 4, NULL, 'EX2', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 14:33:56', 3, 'Walk-in: tomo'),
(14, 8, 5, NULL, 'VIP1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 14:47:03', 2, ''),
(15, 8, 5, NULL, 'R2', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 14:47:05', 2, ''),
(16, 8, 4, NULL, 'EX2', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:00:11', 2, ''),
(17, 8, 4, NULL, 'EX3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:06:46', 2, ''),
(18, 8, 4, NULL, 'EX3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:09:20', 2, ''),
(19, 8, 4, NULL, 'EX5', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:12:04', 2, ''),
(20, 8, 4, NULL, 'EX4', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:12:26', 2, ''),
(21, 8, 4, NULL, 'EX1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:12:56', 2, ''),
(22, 8, 4, NULL, 'EX1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:30:45', 2, ''),
(23, 8, 4, NULL, 'EX2', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:35:07', 2, ''),
(24, 8, 4, NULL, 'EX3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:40:09', 2, ''),
(25, 8, 4, NULL, 'EX3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:41:27', 2, ''),
(26, 8, 4, NULL, 'EX5', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:41:40', 2, ''),
(27, 8, 4, NULL, 'EX3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:43:53', 2, ''),
(28, 8, 4, NULL, 'EX3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 15:46:38', 2, ''),
(29, 8, 4, NULL, 'A1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:09:30', 4, ''),
(30, 8, 4, NULL, 'A3', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:24:53', 2, ''),
(31, 8, 4, NULL, 'A1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:36:04', 4, ''),
(32, 8, 4, NULL, 'A1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:44:45', 2, ''),
(33, 8, 4, NULL, 'EX2', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:48:28', 2, ''),
(34, 8, 4, NULL, 'A1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:53:41', 2, ''),
(35, 8, 4, NULL, 'A1', '2026-09-17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 16:59:26', 2, ''),
(36, 8, 4, NULL, 'EX2', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:04:00', 2, ''),
(37, 8, 4, NULL, 'EX1', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:10:29', 2, ''),
(38, 8, 5, NULL, 'VIP1', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:17:04', 2, ''),
(39, 8, 4, NULL, 'EX2', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:20:07', 2, ''),
(40, 8, 5, NULL, 'VIP1', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:22:49', 2, ''),
(41, 8, 4, NULL, 'EX1', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:22:59', 2, ''),
(42, 8, 4, NULL, 'EX5', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:24:12', 2, ''),
(43, 8, 4, NULL, 'EX2', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CANCELLED', '2026-09-17 17:35:36', 2, ''),
(44, 8, 4, NULL, 'A1', '2026-09-18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'CONFIRMED', '2026-09-17 17:49:10', 7, '');

-- --------------------------------------------------------

--
-- Table structure for table `job_messages`
--

CREATE TABLE `job_messages` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_messages`
--

INSERT INTO `job_messages` (`id`, `job_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 20, 5, 'ถึงแล้วคับ', '2026-09-17 23:27:17'),
(2, 20, 8, 'ค่ะ กลป', '2026-09-17 23:29:29'),
(3, 20, 8, 'จีบได้ไหม', '2026-09-17 23:32:03'),
(4, 20, 5, 'โสดคับ', '2026-09-17 23:32:20'),
(5, 22, 5, 'ชื่อไรคับ', '2026-09-17 23:46:25'),
(6, 23, 5, '📍 [อัปเดตสถานะ] ผู้รับโต๊ะเดินทางถึงร้าน ร้าน dreadlocks.barr แล้ว (ทำการอัปโหลดรูปภาพหลักฐานโต๊ะเรียบร้อยแล้ว)', '2026-09-17 23:52:22'),
(7, 23, 5, '🎉 [อัปเดตสถานะ] ส่งมอบโต๊ะเรียบร้อย เสร็จสิ้นภารกิจ!', '2026-09-17 23:53:25'),
(8, 24, 5, '🚶‍♂️ [อัปเดตสถานะ] ผู้รับโต๊ะกำลังออกเดินทางไปยังร้าน ร้าน dreadlocks.barr (โต๊ะ A1)', '2026-09-17 23:54:22'),
(9, 24, 5, '📍 [อัปเดตสถานะ] ผู้รับโต๊ะเดินทางถึงร้าน ร้าน dreadlocks.barr เรียบร้อยแล้ว', '2026-09-17 23:54:47'),
(10, 24, 5, '📷 [รูปถ่ายหลักฐาน]\nuploads/1789664087_6aac1b5741fac.png', '2026-09-17 23:54:47'),
(11, 24, 5, '🎉 [อัปเดตสถานะ] ส่งมอบโต๊ะเรียบร้อย เสร็จสิ้นภารกิจ!', '2026-09-17 23:55:58'),
(12, 27, 5, 'ดีคับ', '2026-09-18 00:12:39'),
(13, 27, 5, '🚶‍♂️ [อัปเดตสถานะ] ผู้รับโต๊ะกำลังออกเดินทางไปยังร้าน ร้าน dreadlocks.barr (โต๊ะ EX1)', '2026-09-18 00:12:45'),
(14, 27, 5, '📍 [อัปเดตสถานะ] ผู้รับโต๊ะเดินทางถึงร้าน ร้าน dreadlocks.barr เรียบร้อยแล้ว', '2026-09-18 00:14:36'),
(15, 27, 5, '📷 [รูปถ่ายหลักฐาน]\nuploads/1789665276_6aac1ffc2f75c.heic', '2026-09-18 00:14:36'),
(16, 27, 5, '🎉 [อัปเดตสถานะ] ส่งมอบโต๊ะเรียบร้อย เสร็จสิ้นภารกิจ!', '2026-09-18 00:15:37'),
(17, 28, 5, '🚶‍♂️ [อัปเดตสถานะ] ผู้รับโต๊ะกำลังออกเดินทางไปยังร้าน ร้าน Ratch Hour (โต๊ะ VIP1)', '2026-09-18 00:17:58'),
(18, 28, 5, 'Fpjq', '2026-09-18 00:18:02'),
(19, 28, 5, 'wasup', '2026-09-18 00:18:10'),
(20, 28, 5, '📍 [อัปเดตสถานะ] ผู้รับโต๊ะเดินทางถึงร้าน ร้าน Ratch Hour เรียบร้อยแล้ว', '2026-09-18 00:18:22'),
(21, 28, 5, '📷 [รูปถ่ายหลักฐาน]\nuploads/1789665502_6aac20de02440.png', '2026-09-18 00:18:22'),
(22, 28, 5, '🎉 [อัปเดตสถานะ] ส่งมอบโต๊ะเรียบร้อย เสร็จสิ้นภารกิจ!', '2026-09-18 00:19:00'),
(23, 33, 8, 'ดีค่ะ', '2026-09-18 00:47:21'),
(24, 33, 5, 'ดีคับ', '2026-09-18 00:47:48'),
(25, 33, 5, '🚶‍♂️ [อัปเดตสถานะ] ผู้รับโต๊ะกำลังออกเดินทางไปยังร้าน ร้าน dreadlocks.barr (โต๊ะ EX2)', '2026-09-18 00:48:27'),
(26, 33, 5, '📍 [อัปเดตสถานะ] ผู้รับโต๊ะเดินทางถึงร้าน ร้าน dreadlocks.barr เรียบร้อยแล้ว', '2026-09-18 00:48:35'),
(27, 33, 5, '📷 [รูปถ่ายหลักฐาน]\nuploads/1789667315_6aac27f304fdc.heic', '2026-09-18 00:48:35'),
(28, 33, 5, '🎉 [อัปเดตสถานะ] ส่งมอบโต๊ะเรียบร้อย เสร็จสิ้นภารกิจ!', '2026-09-18 00:48:49'),
(29, 34, 5, '🚶‍♂️ [อัปเดตสถานะ] ผู้รับโต๊ะกำลังออกเดินทางไปยังร้าน ร้าน dreadlocks.barr (โต๊ะ A1)', '2026-09-18 00:50:18'),
(30, 34, 5, '📍 [อัปเดตสถานะ] ผู้รับโต๊ะเดินทางถึงร้าน ร้าน dreadlocks.barr เรียบร้อยแล้ว', '2026-09-18 00:50:23'),
(31, 34, 5, '📷 [รูปถ่ายหลักฐาน]\nuploads/1789667423_6aac285f35979.png', '2026-09-18 00:50:23'),
(32, 34, 5, '🎉 [อัปเดตสถานะ] ส่งมอบโต๊ะเรียบร้อย เสร็จสิ้นภารกิจ!', '2026-09-18 00:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pickup_job_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pickup_jobs`
--

CREATE TABLE `pickup_jobs` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `reward` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `arrived_at` datetime DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `status` enum('OPEN','ACCEPTED','ON_THE_WAY','ARRIVED','COMPLETED','EXPIRED','CANCELLED') DEFAULT 'OPEN',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pickup_jobs`
--

INSERT INTO `pickup_jobs` (`id`, `booking_id`, `customer_id`, `receiver_id`, `reward`, `notes`, `proof_image`, `accepted_at`, `arrived_at`, `confirmed_at`, `status`, `created_at`) VALUES
(6, 11, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-16 16:06:44'),
(7, 12, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-16 16:15:06'),
(8, 13, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 14:33:56'),
(9, 14, 8, 5, 0.00, NULL, '1789661607_6aac11a7b4a45.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 14:47:03'),
(10, 15, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 14:47:05'),
(11, 16, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:00:11'),
(12, 18, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:09:20'),
(13, 19, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:12:04'),
(14, 20, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:12:26'),
(15, 21, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:12:56'),
(16, 22, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:30:45'),
(17, 23, 8, 5, 0.00, NULL, '1789661271_6aac1057b9171.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:35:07'),
(18, 28, 8, 5, 0.00, NULL, '1789661076_6aac0f943de16.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 15:46:38'),
(19, 29, 8, 5, 0.00, NULL, '1789661598_6aac119eb7533.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:09:30'),
(20, 30, 8, 5, 100.00, NULL, '1789662751_6aac161fbe56f.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:25:21'),
(21, 31, 8, 5, 500.00, NULL, '1789663309_6aac184d01988.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:41:29'),
(22, 32, 8, 5, 500.00, NULL, '1789663610_6aac197ac544a.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:45:39'),
(23, 33, 8, 5, 500.00, NULL, '1789663942_6aac1ac6cc18d.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:48:52'),
(24, 34, 8, 5, 1000.00, NULL, '1789664087_6aac1b5741fac.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:54:09'),
(25, 35, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 16:59:26'),
(26, 36, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:04:00'),
(27, 37, 8, 5, 430.00, NULL, '1789665276_6aac1ffc2f75c.heic', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:12:06'),
(28, 38, 8, 5, 500.00, NULL, '1789665502_6aac20de02440.png', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:17:40'),
(29, 39, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:20:07'),
(30, 40, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:22:49'),
(31, 41, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:22:59'),
(32, 42, 8, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:24:12'),
(33, 43, 8, 5, 300.00, NULL, '1789667315_6aac27f304fdc.heic', NULL, NULL, NULL, 'CANCELLED', '2026-09-17 17:45:48'),
(34, 44, 8, 5, 500.00, NULL, '1789667423_6aac285f35979.png', NULL, NULL, NULL, 'COMPLETED', '2026-09-17 17:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT 13.75633100,
  `longitude` decimal(11,8) DEFAULT 100.50184400,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `floor_plan_image` varchar(255) DEFAULT 'default_plan.jpg',
  `layout_type` varchar(50) DEFAULT 'dreadlocks',
  `status` varchar(20) DEFAULT 'approved',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `user_id`, `name`, `address`, `latitude`, `longitude`, `created_at`, `floor_plan_image`, `layout_type`, `status`, `description`) VALUES
(4, 6, 'ร้าน dreadlocks.barr', 'ระบุที่อยู่ร้านค้า', 13.75633100, 100.50184400, '2026-09-16 15:31:49', 'default_plan.jpg', 'dreadlocks', 'approved', NULL),
(5, 7, 'ร้าน Ratch Hour', 'ระบุที่อยู่ร้านค้า', 13.75633100, 100.50184400, '2026-09-16 15:33:49', 'default_plan.jpg', 'ratch_hour', 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `pickup_job_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `table_number` varchar(50) NOT NULL,
  `capacity` int(11) DEFAULT 2,
  `status` enum('AVAILABLE','RESERVED','OCCUPIED') DEFAULT 'AVAILABLE',
  `pos_x` int(11) DEFAULT 50,
  `pos_y` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','receiver','restaurant','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `role`, `created_at`) VALUES
(5, 'tomo', '0834424532', 'tomo@gmail.com', '$2y$10$ORCWuWWSXhniiclq3KYCE.8VDfcTQ1YFJKbNCtVGNdtMUAkj6auRS', 'receiver', '2026-09-16 15:23:35'),
(6, 'dreadlocks.barr', '0938876543', 'dreadlocks.barr@gmail.com', '$2y$10$M.iaFfinyGxqJS3TMI.Z7ehGxqwDydmLvehNqPGnmNzi5VLMfMR3a', 'restaurant', '2026-09-16 15:23:46'),
(7, 'Ratch Hour', '0897723123', 'RH@gmail.com', '$2y$10$s3fJ8E4KqDUhhdQAJ1Py4e2xwkG9TKqQw7PsSUfQtfjE1E4dE3rMG', 'restaurant', '2026-09-16 15:33:49'),
(8, 'แจ็กกี้', '0640071425', 'customer@gmail.com', '$2y$10$WaC1lg7POGDS9BQ7pMPigeCnwGUa0auBu.VyuxNxDap23rhZGxrYG', 'customer', '2026-09-17 14:46:48'),
(9, 'ADMINRUBTO', '0123456789', 'admin@rubto.com', '$2y$10$0uqOfZOZ9S3d7MrCifXDRuEVG7uXROsFra64yRupT8pbBZB/DPeba', 'admin', '2026-09-17 17:32:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `job_messages`
--
ALTER TABLE `job_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pickup_job_id` (`pickup_job_id`);

--
-- Indexes for table `pickup_jobs`
--
ALTER TABLE `pickup_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pickup_job_id` (`pickup_job_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `job_messages`
--
ALTER TABLE `job_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pickup_jobs`
--
ALTER TABLE `pickup_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_messages`
--
ALTER TABLE `job_messages`
  ADD CONSTRAINT `job_messages_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `pickup_jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`pickup_job_id`) REFERENCES `pickup_jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pickup_jobs`
--
ALTER TABLE `pickup_jobs`
  ADD CONSTRAINT `pickup_jobs_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pickup_jobs_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pickup_jobs_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`pickup_job_id`) REFERENCES `pickup_jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tables`
--
ALTER TABLE `tables`
  ADD CONSTRAINT `tables_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
