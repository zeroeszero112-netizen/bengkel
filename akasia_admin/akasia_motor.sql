-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 10:31 PM
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
-- Database: `akasia_motor`
--

-- --------------------------------------------------------

--
-- Table structure for table `jenis_layanan`
--

CREATE TABLE `jenis_layanan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_layanan`
--

INSERT INTO `jenis_layanan` (`id`, `nama`, `deskripsi`, `harga`) VALUES
(1, 'Servis Ringan', 'Perawatan rutin untuk menjaga performa kendaraan.', 75000),
(2, 'Servis Sedang', 'Perawatan lanjutan untuk meningkatkan performa mesin yang mulai menurun.', 150000),
(3, 'Servis Berat', 'Perbaikan menyeluruh untuk kendaraan dengan kondisi kerusakan atau penurunan performa signifikan.', 300000);

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan_servis`
--

CREATE TABLE `kegiatan_servis` (
  `id` int(11) NOT NULL,
  `jenis_layanan_id` int(11) NOT NULL,
  `nama_kegiatan` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan_servis`
--

INSERT INTO `kegiatan_servis` (`id`, `jenis_layanan_id`, `nama_kegiatan`) VALUES
(1, 1, 'Ganti oli mesin'),
(2, 1, 'Ganti busi'),
(3, 1, 'Ganti rem'),
(4, 1, 'Bersihkan filter udara'),
(5, 2, 'Semua servis ringan'),
(6, 2, 'Pembersihan injektor/karburator'),
(7, 2, 'Servis CVT Matic'),
(8, 2, 'Setel klep'),
(9, 2, 'Cek radiator'),
(10, 2, 'Cek sistem bahan bakar'),
(11, 3, 'Semua servis sebelumnya'),
(12, 3, 'Bongkar mesin'),
(13, 3, 'Cek piston & ring piston'),
(14, 3, 'Cek transmisi'),
(15, 3, 'Cek komponen aus'),
(16, 3, 'Pembersihan ruang bakar');

-- --------------------------------------------------------

--
-- Table structure for table `mekanik`
--

CREATE TABLE `mekanik` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `status` enum('Bekerja','Bertugas','Libur') DEFAULT 'Bekerja',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mekanik`
--

INSERT INTO `mekanik` (`id`, `nama`, `no_hp`, `status`, `foto`, `created_at`) VALUES
(1, 'Ahmad Wijaya', '6282111112222', 'Bertugas', NULL, '2026-05-16 18:04:59'),
(2, 'Risky Pratama', '6282222223333', 'Bekerja', NULL, '2026-05-16 18:04:59'),
(3, 'Doni Saputra', '6281433334444', 'Bekerja', NULL, '2026-05-16 18:04:59');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi_wa`
--

CREATE TABLE `notifikasi_wa` (
  `id` int(11) NOT NULL,
  `reservasi_id` int(11) NOT NULL,
  `no_tujuan` varchar(20) NOT NULL,
  `pesan` text NOT NULL,
  `jenis` enum('Konfirmasi Reservasi','Pengingat Jadwal','Antrian Dipanggil','Callback Pending','Perubahan Status','Servis Selesai') NOT NULL,
  `status` enum('Terkirim','Gagal') DEFAULT 'Terkirim',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifikasi_wa`
--

INSERT INTO `notifikasi_wa` (`id`, `reservasi_id`, `no_tujuan`, `pesan`, `jenis`, `status`, `sent_at`) VALUES
(5, 9, '62895397129107', 'Reservasi walk-in berhasil dibuat dengan nomor antrean A001.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-19 18:50:14'),
(6, 10, '62895397129107', 'Reservasi walk-in berhasil dibuat dengan nomor antrean A002.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-19 18:51:21'),
(7, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Dipanggil.', 'Perubahan Status', 'Terkirim', '2026-05-19 18:54:40'),
(8, 9, '62895397129107', 'Status reservasi A001 diperbarui menjadi Pending.', 'Perubahan Status', 'Terkirim', '2026-05-19 18:54:43'),
(9, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Menunggu.', 'Perubahan Status', 'Terkirim', '2026-05-19 18:55:21'),
(10, 9, '62895397129107', 'Status reservasi A001 diperbarui menjadi Menunggu.', 'Perubahan Status', 'Terkirim', '2026-05-19 18:55:24'),
(11, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Diproses.', 'Perubahan Status', 'Terkirim', '2026-05-19 18:56:15'),
(12, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Dipanggil.', 'Perubahan Status', 'Terkirim', '2026-05-19 19:16:40'),
(13, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Diproses.', 'Perubahan Status', 'Terkirim', '2026-05-19 19:20:21'),
(14, 9, '62895397129107', 'Status reservasi A001 diperbarui menjadi Diproses.', 'Perubahan Status', 'Terkirim', '2026-05-19 19:38:15'),
(15, 11, '62895397129107', 'Reservasi walk-in berhasil dibuat dengan nomor antrean A003.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-19 19:59:18'),
(17, 11, '62895397129107', 'Status reservasi A003 diperbarui menjadi Selesai.', 'Perubahan Status', 'Terkirim', '2026-05-19 20:12:08'),
(18, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Dipanggil.', 'Perubahan Status', 'Terkirim', '2026-05-19 20:12:16'),
(19, 9, '62895397129107', 'Status reservasi A001 diperbarui menjadi Selesai.', 'Perubahan Status', 'Terkirim', '2026-05-19 20:12:24'),
(20, 10, '62895397129107', 'Status reservasi A002 diperbarui menjadi Selesai.', 'Perubahan Status', 'Terkirim', '2026-05-19 20:12:28'),
(22, 11, '62895397129107', 'Status reservasi A003 diperbarui menjadi Diproses.', 'Perubahan Status', 'Terkirim', '2026-05-19 20:29:18');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_bengkel` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `jam_buka` time DEFAULT NULL,
  `jam_tutup` time DEFAULT NULL,
  `hari_operasional` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_bengkel`, `alamat`, `no_whatsapp`, `jam_buka`, `jam_tutup`, `hari_operasional`, `updated_at`) VALUES
(1, 'Akasia Motor', 'Jl. Merdeka No. 123, Semarang', '628120000000', '08:00:00', '17:00:00', 'Senin - Sabtu', '2026-05-19 18:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `reservasi`
--

CREATE TABLE `reservasi` (
  `id` int(11) NOT NULL,
  `no_antrian` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mekanik_id` int(11) DEFAULT NULL,
  `jenis_layanan_id` int(11) NOT NULL,
  `jenis_reservasi` enum('Online','Walk-in') DEFAULT 'Online',
  `no_plat` varchar(20) NOT NULL,
  `jenis_kendaraan` varchar(50) DEFAULT NULL,
  `tipe_model` varchar(50) DEFAULT NULL,
  `tahun` year(4) DEFAULT NULL,
  `warna` varchar(30) DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `kehadiran` enum('Belum Hadir','Hadir') DEFAULT 'Belum Hadir',
  `status` enum('Menunggu','Dipanggil','Diproses','Selesai','Pending','Batal') DEFAULT 'Menunggu',
  `catatan_mekanik` text DEFAULT NULL,
  `tanggal_servis` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservasi`
--

INSERT INTO `reservasi` (`id`, `no_antrian`, `user_id`, `mekanik_id`, `jenis_layanan_id`, `jenis_reservasi`, `no_plat`, `jenis_kendaraan`, `tipe_model`, `tahun`, `warna`, `keluhan`, `kehadiran`, `status`, `catatan_mekanik`, `tanggal_servis`, `created_at`, `updated_at`) VALUES
(9, 'A001', 8, 1, 1, 'Walk-in', '43', 'test', 'test', '2000', 'test', 'test', 'Hadir', 'Selesai', NULL, '2026-05-19', '2026-05-19 18:50:14', '2026-05-19 20:12:24'),
(10, 'A002', 9, 1, 1, 'Walk-in', '4343', 'test', 'test', '2003', 'test', 'test', 'Hadir', 'Selesai', 'test', '2026-05-19', '2026-05-19 18:51:21', '2026-05-19 20:12:28'),
(11, 'A003', 10, 3, 3, 'Walk-in', '4343', 'test', 'test', '2003', 'test', 'test', 'Hadir', 'Diproses', NULL, '2026-05-02', '2026-05-19 19:59:18', '2026-05-19 20:29:40');

-- --------------------------------------------------------

--
-- Table structure for table `reservasi_kegiatan`
--

CREATE TABLE `reservasi_kegiatan` (
  `id` int(11) NOT NULL,
  `reservasi_id` int(11) NOT NULL,
  `kegiatan_servis_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservasi_kegiatan`
--

INSERT INTO `reservasi_kegiatan` (`id`, `reservasi_id`, `kegiatan_servis_id`) VALUES
(24, 11, 16);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','pelanggan') DEFAULT 'pelanggan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_whatsapp`, `alamat`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@akasiamotor.com', '$2y$10$Vf0MOID8XqtaDo0B6jWzBOIXNjnNrfa9y2tJ6YK9B6jzv/jqFESAO', NULL, NULL, 'admin', '2026-05-16 18:04:59'),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '6281234567890', 'Jl. Merdeka No. 123, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(3, 'Andi Wijaya', 'andi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '6282111112222', 'Jl. Sudirman No. 45, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(4, 'Siti Aminah', 'siti@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '6281344445555', 'Jl. Pahlawan No. 12, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(5, 'Dewi Lestari', 'dewi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '6281322223333', 'Jl. Diponegoro No. 78, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(6, 'Rudi Hartono', 'rudi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '6282111111111', 'Jl. Ahmad Yani No. 5, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(7, 'testtest', 'test.0895397129107@walkin.akasia.local', '$2y$10$bN6yEExU/ox84ieBXO4ZVufogcZXo9NMQuON/zyYPW6jscSERmsYC', '62895397129107', 'test', 'pelanggan', '2026-05-17 00:02:03'),
(8, 'testtest', 'test.08953971291072@walkin.akasia.local', '$2y$10$TmqxaDKtCsMZTLPhaa6om.T8CDJHGu64d6GFD7fRP./rI7ISw.vMS', '62895397129107', 'test', 'pelanggan', '2026-05-18 05:56:56'),
(9, 'teste', 'teste.0895397129107@walkin.akasia.local', '$2y$10$49B8jXv1NcZ4rylPKun/ruynxCv6Y..nwHNqpt9LaDr/2cU00LJqq', '62895397129107', 'tet', 'pelanggan', '2026-05-19 18:51:21'),
(10, 'test', 'test.08953971291073@walkin.akasia.local', '$2y$10$/IjbiFmfipY/Cs1bzzm9Y.ZiKA/nLLInZuUChj1jouozt1FB97s6G', '62895397129107', 'test', 'pelanggan', '2026-05-19 19:59:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jenis_layanan`
--
ALTER TABLE `jenis_layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kegiatan_servis`
--
ALTER TABLE `kegiatan_servis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jenis_layanan_id` (`jenis_layanan_id`);

--
-- Indexes for table `mekanik`
--
ALTER TABLE `mekanik`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi_wa`
--
ALTER TABLE `notifikasi_wa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservasi_id` (`reservasi_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_antrian` (`no_antrian`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mekanik_id` (`mekanik_id`),
  ADD KEY `jenis_layanan_id` (`jenis_layanan_id`);

--
-- Indexes for table `reservasi_kegiatan`
--
ALTER TABLE `reservasi_kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservasi_id` (`reservasi_id`),
  ADD KEY `kegiatan_servis_id` (`kegiatan_servis_id`);

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
-- AUTO_INCREMENT for table `jenis_layanan`
--
ALTER TABLE `jenis_layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `kegiatan_servis`
--
ALTER TABLE `kegiatan_servis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `mekanik`
--
ALTER TABLE `mekanik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `notifikasi_wa`
--
ALTER TABLE `notifikasi_wa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `reservasi_kegiatan`
--
ALTER TABLE `reservasi_kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kegiatan_servis`
--
ALTER TABLE `kegiatan_servis`
  ADD CONSTRAINT `kegiatan_servis_ibfk_1` FOREIGN KEY (`jenis_layanan_id`) REFERENCES `jenis_layanan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi_wa`
--
ALTER TABLE `notifikasi_wa`
  ADD CONSTRAINT `notifikasi_wa_ibfk_1` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservasi_ibfk_2` FOREIGN KEY (`mekanik_id`) REFERENCES `mekanik` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservasi_ibfk_3` FOREIGN KEY (`jenis_layanan_id`) REFERENCES `jenis_layanan` (`id`);

--
-- Constraints for table `reservasi_kegiatan`
--
ALTER TABLE `reservasi_kegiatan`
  ADD CONSTRAINT `reservasi_kegiatan_ibfk_1` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservasi_kegiatan_ibfk_2` FOREIGN KEY (`kegiatan_servis_id`) REFERENCES `kegiatan_servis` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
