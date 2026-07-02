-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 10:27 AM
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
-- Database: `web_pengaduan`
--

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggapan` text DEFAULT NULL,
  `status` enum('belum ditanggapi','sudah ditanggapi') DEFAULT 'belum ditanggapi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `user_id`, `nama`, `email`, `fakultas`, `kategori`, `deskripsi`, `foto`, `created_at`, `tanggapan`, `status`) VALUES
(57, 21, 'fikar', 'kiboy@gmail.com', 'Fakultas Pertanian', 'Pelecehan', 'asd fasdf as dfasdf', '', '2025-05-14 20:50:24', NULL, 'belum ditanggapi'),
(60, 24, 'Coklat', '', 'Fakultas Pertanian', 'Pembulian', 'aku di buliii', '', '2025-06-04 08:16:35', 'okee maasuh', 'sudah ditanggapi');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(9, 'chloe', '2310631170159@student.unsika.ac.id', '$2y$10$4.ILD938ZHjHyLi3E.c6F.8zN.SdKoQTHI4127A9HjlTxfkzODl2S', 'admin'),
(13, 'rolis', 'rolisliu0@gmail.com', '$2y$10$u7JeK/cr.slXMTi8Fmh7EexDWPwSokdQzbwxxLSG2KMmz44.XztnO', 'admin'),
(21, 'ronal', 'kiboy@gmail.com', '$2y$10$tT7ruF4fIlvtPIW/hsXd0e53GIRZLdmLkJPTrDXWuseN7RMdow/eu', 'user'),
(23, 'apipah', 'apipah1@gmail.com', '$2y$10$WZ6pGegfzUE3UpLMEjVP9ud2U8yTdm9BAazuagpZM2DnqZajHYz5S', 'admin'),
(24, 'padilah', 'padilah1@gmail.com', '$2y$10$QDjC3wwC0Sfde0Z504r2rucbtPSdfc8EJWjfGSzJL03h4cE0PngdG', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
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
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
