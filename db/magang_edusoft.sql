-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 14, 2025 at 03:01 AM
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
-- Database: `magang_edusoft`
--

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas`
--

CREATE TABLE `aktivitas` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `deskripsi` text,
  `status_validasi` enum('pending','disetujui') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `aktivitas`
--

INSERT INTO `aktivitas` (`id`, `user_id`, `tanggal`, `deskripsi`, `status_validasi`) VALUES
(1, 1, '2025-07-15', 'Mengerjakan Aplikasi presensi dan aktifitas harian', 'disetujui'),
(2, 5, '2025-07-15', 'Membuat aplikasi menggunakan php', 'disetujui'),
(3, 6, '2025-07-15', 'mengerjakan Aplikasi absensi dan aktivitas magang ', 'disetujui'),
(4, 7, '2025-07-15', 'mengerjakan Tugas mengerjakan website', 'disetujui'),
(5, 8, '2025-07-16', 'Melanjutkan pengerjaan project website', 'disetujui'),
(6, 7, '2025-07-17', 'membuat web absen', 'disetujui'),
(7, 6, '2025-07-17', 'Mengerjakan pengerjaan website absensi magang 2025\r\n', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `presensi`
--

CREATE TABLE `presensi` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `presensi`
--

INSERT INTO `presensi` (`id`, `user_id`, `tanggal`, `jam_masuk`, `jam_keluar`) VALUES
(1, 1, '2025-07-15', '08:48:00', '15:10:00'),
(2, 5, '2025-07-15', '08:50:00', '15:15:00'),
(3, 6, '2025-07-15', '10:41:00', '10:41:00'),
(4, 6, '2025-07-15', '08:49:00', NULL),
(5, 7, '2025-07-15', '08:55:00', '13:54:00'),
(6, 8, '2025-07-16', '08:51:00', '15:10:00'),
(7, 7, '2025-07-17', '10:00:00', '12:00:00'),
(8, 6, '2025-07-17', '08:30:00', '20:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('siswa','pembimbing') NOT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `kelas`, `jurusan`, `nama`) VALUES
(1, 'siswa1', '$2y$10$2Qa8FjsPOE5yG85J..wg.OvtQdOokHvGAC0xWIiwZUQyunS70b4fC', 'siswa', NULL, NULL, 'Siswa Contoh'),
(2, 'pembimbing1', '$2y$10$HfCuqb4zU8YQHe1soBhw6u9Fq3fwZphNlQ6BWrMjGzXmkzLFM3T3e', 'pembimbing', NULL, NULL, 'Pembimbing Contoh'),
(3, 'Pembimbing2', '$2y$10$kP/TnUw5KToBVYXEF3NpPeUa3TH/ovbDQuyJqb5zdr3UoUG3hdwBy', 'pembimbing', NULL, NULL, ''),
(4, 'Pembimbing3', '$2y$10$t2mKh/T8M1BY0XQW48e0j.gOSztXWPxrHo26VJJ6wJSU4oMds6lFO', 'pembimbing', NULL, NULL, 'Adam'),
(5, 'Dito123', '$2y$10$K.9s4JOT/TzcbZGr7mrf4.jonLpMeI9KxOKYUehN7Slmrve2.3TF2', 'siswa', NULL, NULL, 'Dito'),
(6, 'Andini123', '$2y$10$0LkokRKriBw66b1TxE9QVOTw1sRP7K5rmtTsbDVbvYyWs7mGmf3zW', 'siswa', 'XII', 'Rekayasa Perangkat Lunak', 'Andini'),
(7, 'aya123', '$2y$10$LWyCcVwrcx.23SlrZa57POqANEN4WkYsWqcuh32DTtEQ5zVyjjcd.', 'siswa', 'XII', 'Rekayasa Perangkat Lunak', 'aya'),
(8, 'Marlin2345', '$2y$10$W4kjlX.xL/H7/itrpNwKh.hbJRW2ItxUIzoxqBGw4Hvy6Ty1a0Hn.', 'siswa', 'XII', 'Rekayasa Perangkat Lunak', 'Marlin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `aktivitas`
--
ALTER TABLE `aktivitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD CONSTRAINT `aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `presensi`
--
ALTER TABLE `presensi`
  ADD CONSTRAINT `presensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
