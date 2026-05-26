-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2026 at 02:08 PM
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
-- Table structure for table `antrean_harian`
--

CREATE TABLE `antrean_harian` (
  `id` int(11) NOT NULL,
  `tanggal_servis` date NOT NULL,
  `nomor_terakhir` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `antrean_harian`
--

INSERT INTO `antrean_harian` (`id`, `tanggal_servis`, `nomor_terakhir`, `created_at`, `updated_at`) VALUES
(1, '2026-05-22', 2, '2026-05-26 07:55:25', '2026-05-26 07:55:25'),
(5, '2026-05-26', 3, '2026-05-26 08:33:45', '2026-05-26 09:19:24');

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
  `status` enum('tersedia','sibuk','libur') NOT NULL DEFAULT 'tersedia',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mekanik`
--

INSERT INTO `mekanik` (`id`, `nama`, `no_hp`, `status`, `foto`, `created_at`) VALUES
(1, 'Ahmad Wijaya', '082111112222', 'tersedia', NULL, '2026-05-16 18:04:59'),
(2, 'Risky Pratama', '0822-2222-3333', 'tersedia', NULL, '2026-05-16 18:04:59'),
(3, 'Doni Saputra', '081433334444', 'sibuk', NULL, '2026-05-16 18:04:59');

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
(30, 19, '0895397129107', 'Reservasi online Anda berhasil dibuat dengan nomor antrean A001. Terima kasih telah memilih Akasia Motor.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-21 19:12:37'),
(31, 19, '0895397129107', 'Nomor antrean A001 sedang dipanggil.', 'Antrian Dipanggil', 'Terkirim', '2026-05-21 19:13:11'),
(32, 19, '0895397129107', 'Reservasi A001 telah diterima oleh Akasia Motor.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-21 19:13:27'),
(33, 19, '0895397129107', 'Status reservasi A001 diperbarui menjadi Selesai.', 'Perubahan Status', 'Terkirim', '2026-05-21 19:14:55'),
(34, 20, '0895397129107', 'Reservasi online Anda berhasil dibuat dengan nomor antrean A002. Terima kasih telah memilih Akasia Motor.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-21 19:18:20'),
(35, 20, '0895397129107', 'Status reservasi A002 diperbarui menjadi Diproses.', 'Perubahan Status', 'Gagal', '2026-05-21 19:19:38'),
(36, 20, '0895397129107', 'Reservasi A002 telah diterima oleh Akasia Motor.', 'Konfirmasi Reservasi', 'Terkirim', '2026-05-21 19:20:30'),
(37, 20, '0895397129107', 'Status reservasi A002 diperbarui menjadi Dipanggil.', 'Perubahan Status', 'Terkirim', '2026-05-21 19:20:45'),
(38, 24, '62895397129107', 'Halo Bapak/Ibu test,\n\nReservasi servis kendaraan Anda dengan nomor antrean A001\nuntuk tanggal 26 Mei 2026\ntelah berhasil dikonfirmasi oleh Bengkel Akasia Motor.\n\nSilakan datang ke bengkel sesuai tanggal reservasi dan lakukan konfirmasi kehadiran kepada admin bengkel.\n\nTerima kasih telah menggunakan layanan Bengkel Akasia Motor.', 'Konfirmasi Reservasi', 'Gagal', '2026-05-26 08:34:05'),
(39, 24, '62895397129107', 'Halo Bapak/Ibu test,\n\nReservasi servis kendaraan Anda dengan nomor antrean A001\nuntuk tanggal 26 Mei 2026\ntelah berhasil dikonfirmasi oleh Bengkel Akasia Motor.\n\nSilakan datang ke bengkel sesuai tanggal reservasi dan lakukan konfirmasi kehadiran kepada admin bengkel.\n\nTerima kasih telah menggunakan layanan Bengkel Akasia Motor.', 'Konfirmasi Reservasi', 'Gagal', '2026-05-26 08:34:05'),
(40, 25, '62895397129107', 'Halo Bapak/Ibu test,\n\nReservasi servis kendaraan Anda dengan nomor antrean A002\nuntuk tanggal 26 Mei 2026\ntelah berhasil dikonfirmasi oleh Bengkel Akasia Motor.\n\nSilakan datang ke bengkel sesuai tanggal reservasi dan lakukan konfirmasi kehadiran kepada admin bengkel.\n\nTerima kasih telah menggunakan layanan Bengkel Akasia Motor.', 'Konfirmasi Reservasi', 'Gagal', '2026-05-26 08:35:41'),
(41, 25, '62895397129107', 'Halo Bapak/Ibu test,\n\nKehadiran Anda di Bengkel Akasia Motor telah berhasil dikonfirmasi.\nSaat ini kendaraan Anda sedang masuk dalam daftar antrean servis.\n\nSilakan menunggu hingga proses servis dimulai.\n\nTerima kasih.', 'Perubahan Status', 'Gagal', '2026-05-26 08:35:54'),
(42, 24, '62895397129107', 'Halo Bapak/Ibu test,\n\nReservasi servis kendaraan dengan nomor antrean A001\nsementara dipindahkan ke status pending karena pelanggan belum hadir di bengkel.\n\nSilakan datang ke Bengkel Akasia Motor untuk melanjutkan antrean servis.\n\nTerima kasih.', 'Callback Pending', 'Gagal', '2026-05-26 08:35:57'),
(43, 25, '62895397129107', 'Halo Bapak/Ibu test,\n\nKendaraan Anda dengan nomor antrean A002\nsaat ini sedang dalam proses servis di Bengkel Akasia Motor.\n\nMekanik yang menangani:\nDoni Saputra\n\nTerima kasih.', 'Perubahan Status', 'Gagal', '2026-05-26 08:36:11'),
(44, 26, '62895397129107', 'Halo Bapak/Ibu test,\n\nReservasi servis kendaraan Anda dengan nomor antrean A003\nuntuk tanggal 26 Mei 2026\ntelah berhasil dikonfirmasi oleh Bengkel Akasia Motor.\n\nSilakan datang ke bengkel sesuai tanggal reservasi dan lakukan konfirmasi kehadiran kepada admin bengkel.\n\nTerima kasih telah menggunakan layanan Bengkel Akasia Motor.', 'Konfirmasi Reservasi', 'Gagal', '2026-05-26 09:19:34');

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
(1, 'Akasia Motor', 'Jl. Merdeka No. 123, Semarang', '081200000000', '08:00:00', '17:00:00', 'Senin - Sabtu', '2026-05-19 18:38:52');

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
  `status` enum('menunggu_konfirmasi','dikonfirmasi','menunggu_antrean','diproses','pending','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_konfirmasi',
  `catatan_mekanik` text DEFAULT NULL,
  `hasil_servis` text DEFAULT NULL,
  `biaya_jasa` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_biaya` decimal(12,2) NOT NULL DEFAULT 0.00,
  `catatan_tambahan` text DEFAULT NULL,
  `tanggal_servis` date DEFAULT NULL,
  `waktu_konfirmasi` datetime DEFAULT NULL,
  `waktu_hadir` datetime DEFAULT NULL,
  `waktu_pending` datetime DEFAULT NULL,
  `waktu_mulai_servis` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservasi`
--

INSERT INTO `reservasi` (`id`, `no_antrian`, `user_id`, `mekanik_id`, `jenis_layanan_id`, `jenis_reservasi`, `no_plat`, `jenis_kendaraan`, `tipe_model`, `tahun`, `warna`, `keluhan`, `kehadiran`, `status`, `catatan_mekanik`, `hasil_servis`, `biaya_jasa`, `total_biaya`, `catatan_tambahan`, `tanggal_servis`, `waktu_konfirmasi`, `waktu_hadir`, `waktu_pending`, `waktu_mulai_servis`, `waktu_selesai`, `created_at`, `updated_at`) VALUES
(19, 'A001', 13, NULL, 2, 'Online', '43', 'test', 'test', '2023', 'test', 'test', 'Hadir', 'selesai', 'test', NULL, 0.00, 0.00, NULL, '2026-05-22', '2026-05-22 02:12:35', '2026-05-26 14:54:58', NULL, '2026-05-26 14:54:58', '2026-05-26 14:54:58', '2026-05-21 19:12:35', '2026-05-26 07:54:59'),
(20, 'A002', 13, 1, 1, 'Online', '43', 'test', 'test', '2003', 'test', 'test', 'Belum Hadir', 'menunggu_konfirmasi', 'testtest', NULL, 0.00, 0.00, NULL, '2026-05-22', NULL, NULL, NULL, NULL, NULL, '2026-05-21 19:18:12', '2026-05-26 07:54:58'),
(24, 'A001', 10, NULL, 1, 'Walk-in', '43', 'test', 'test', '2023', 'tet', 'tst', 'Belum Hadir', 'pending', NULL, NULL, 0.00, 0.00, NULL, '2026-05-26', '2026-05-26 15:34:05', NULL, '2026-05-26 15:35:56', NULL, NULL, '2026-05-26 08:33:45', '2026-05-26 08:35:56'),
(25, 'A002', 10, 3, 1, 'Walk-in', '4343', 'test', 'test', '2023', 'tst', 'tset', 'Hadir', 'diproses', NULL, NULL, 0.00, 0.00, NULL, '2026-05-26', '2026-05-26 15:35:39', '2026-05-26 15:35:53', NULL, '2026-05-26 15:36:10', NULL, '2026-05-26 08:35:36', '2026-05-26 08:36:10'),
(26, 'A003', 10, NULL, 1, 'Walk-in', '3232', 'tet', 'test', '2023', 'tes', 'test', 'Belum Hadir', 'dikonfirmasi', NULL, NULL, 0.00, 0.00, NULL, '2026-05-26', '2026-05-26 16:19:33', NULL, NULL, NULL, NULL, '2026-05-26 09:19:24', '2026-05-26 09:19:33');

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
(47, 24, 4),
(48, 24, 2),
(49, 25, 4),
(50, 25, 1),
(51, 26, 4),
(52, 26, 2);

-- --------------------------------------------------------

--
-- Table structure for table `reservasi_sparepart`
--

CREATE TABLE `reservasi_sparepart` (
  `id` int(11) NOT NULL,
  `reservasi_id` int(11) NOT NULL,
  `nama_item` varchar(150) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `template_whatsapp`
--

CREATE TABLE `template_whatsapp` (
  `id` int(11) NOT NULL,
  `kode_template` varchar(50) NOT NULL,
  `nama_template` varchar(100) NOT NULL,
  `isi_pesan` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `template_whatsapp`
--

INSERT INTO `template_whatsapp` (`id`, `kode_template`, `nama_template`, `isi_pesan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'reservasi_dikonfirmasi', 'Reservasi Dikonfirmasi', 'Halo Bapak/Ibu {nama},\n\nReservasi servis kendaraan Anda dengan nomor antrean {antrean}\nuntuk tanggal {tanggal}\ntelah berhasil dikonfirmasi oleh Bengkel Akasia Motor.\n\nSilakan datang ke bengkel sesuai tanggal reservasi dan lakukan konfirmasi kehadiran kepada admin bengkel.\n\nTerima kasih telah menggunakan layanan Bengkel Akasia Motor.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59'),
(2, 'reservasi_dibatalkan', 'Reservasi Dibatalkan', 'Halo Bapak/Ibu {nama},\n\nMohon maaf, reservasi servis kendaraan dengan nomor antrean {antrean}\ntidak dapat diproses dan telah dibatalkan.\n\nSilakan melakukan reservasi ulang atau menghubungi pihak bengkel untuk informasi lebih lanjut.\n\nTerima kasih.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59'),
(3, 'kehadiran_dikonfirmasi', 'Kehadiran Dikonfirmasi', 'Halo Bapak/Ibu {nama},\n\nKehadiran Anda di Bengkel Akasia Motor telah berhasil dikonfirmasi.\nSaat ini kendaraan Anda sedang masuk dalam daftar antrean servis.\n\nSilakan menunggu hingga proses servis dimulai.\n\nTerima kasih.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59'),
(4, 'pending_kehadiran', 'Pending Kehadiran', 'Halo Bapak/Ibu {nama},\n\nReservasi servis kendaraan dengan nomor antrean {antrean}\nsementara dipindahkan ke status pending karena pelanggan belum hadir di bengkel.\n\nSilakan datang ke Bengkel Akasia Motor untuk melanjutkan antrean servis.\n\nTerima kasih.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59'),
(5, 'proses_servis_dimulai', 'Proses Servis Dimulai', 'Halo Bapak/Ibu {nama},\n\nKendaraan Anda dengan nomor antrean {antrean}\nsaat ini sedang dalam proses servis di Bengkel Akasia Motor.\n\nMekanik yang menangani:\n{mekanik}\n\nTerima kasih.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59'),
(6, 'callback_antrean', 'Callback Antrean', 'Halo Bapak/Ibu {nama},\n\nAntrean servis kendaraan Anda telah dipanggil kembali oleh Bengkel Akasia Motor.\n\nSilakan menuju area pelayanan servis untuk melanjutkan proses servis kendaraan Anda.\n\nTerima kasih.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59'),
(7, 'servis_selesai', 'Servis Selesai', 'Halo Bapak/Ibu {nama},\n\nServis kendaraan Anda di Bengkel Akasia Motor telah selesai dikerjakan.\n\nNomor antrean:\n{antrean}\nTotal biaya servis:\nRp {total}\n\nSilakan datang ke bengkel untuk pengambilan kendaraan.\n\nTerima kasih telah menggunakan layanan Bengkel Akasia Motor.', 1, '2026-05-26 07:54:59', '2026-05-26 07:54:59');

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
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812-3456-7890', 'Jl. Merdeka No. 123, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(3, 'Andi Wijaya', 'andi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0821-1111-2222', 'Jl. Sudirman No. 45, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(4, 'Siti Aminah', 'siti@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0813-4444-5555', 'Jl. Pahlawan No. 12, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(5, 'Dewi Lestari', 'dewi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0813-2222-3333', 'Jl. Diponegoro No. 78, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(6, 'Rudi Hartono', 'rudi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0821-1111-1111', 'Jl. Ahmad Yani No. 5, Semarang', 'pelanggan', '2026-05-16 18:04:59'),
(7, 'testtest', 'test.0895397129107@walkin.akasia.local', '$2y$10$bN6yEExU/ox84ieBXO4ZVufogcZXo9NMQuON/zyYPW6jscSERmsYC', '0895397129107', 'test', 'pelanggan', '2026-05-17 00:02:03'),
(8, 'testtest', 'test.08953971291072@walkin.akasia.local', '$2y$10$TmqxaDKtCsMZTLPhaa6om.T8CDJHGu64d6GFD7fRP./rI7ISw.vMS', '0895397129107', 'test', 'pelanggan', '2026-05-18 05:56:56'),
(9, 'teste', 'teste.0895397129107@walkin.akasia.local', '$2y$10$49B8jXv1NcZ4rylPKun/ruynxCv6Y..nwHNqpt9LaDr/2cU00LJqq', '0895397129107', 'tet', 'pelanggan', '2026-05-19 18:51:21'),
(10, 'test', 'test.08953971291073@walkin.akasia.local', '$2y$10$/IjbiFmfipY/Cs1bzzm9Y.ZiKA/nLLInZuUChj1jouozt1FB97s6G', '0895397129107', 'test', 'pelanggan', '2026-05-19 19:59:18'),
(11, 'test', 'test@gmail.com', '$2y$10$QLwbvmNVs7n8aSnlf7q8VOT.Se2OJhjxqb4LLiMN8aTvJy69AIAne', '089512121212', 'JL Pelabuhan Ketapang', 'pelanggan', '2026-05-20 15:31:18'),
(12, 'testtesttest', 'testtest@gmail.com', '$2y$10$3D3XQdRk3JxQTWDXLfdVQOjaHQU0fhwfk2zCZRH3p5A59b2E3vXAW', '089323232332', 'test', 'pelanggan', '2026-05-20 15:32:02'),
(13, 'andriano', 'andrianow817@gmail.com', '$2y$10$Ea9lFlQ04N1uYevHVVn3eeBwLwTuGd5mYeh/UMQkGoTtm7SAgOtDu', '0895397129107', 'JL pelabuhan', 'pelanggan', '2026-05-21 19:12:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antrean_harian`
--
ALTER TABLE `antrean_harian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_antrean_harian_tanggal` (`tanggal_servis`);

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
  ADD UNIQUE KEY `uk_reservasi_tanggal_no_antrian` (`tanggal_servis`,`no_antrian`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mekanik_id` (`mekanik_id`),
  ADD KEY `jenis_layanan_id` (`jenis_layanan_id`),
  ADD KEY `idx_reservasi_tanggal_servis_no_antrian` (`tanggal_servis`,`no_antrian`);

--
-- Indexes for table `reservasi_kegiatan`
--
ALTER TABLE `reservasi_kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservasi_id` (`reservasi_id`),
  ADD KEY `kegiatan_servis_id` (`kegiatan_servis_id`);

--
-- Indexes for table `reservasi_sparepart`
--
ALTER TABLE `reservasi_sparepart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservasi_sparepart_reservasi_id` (`reservasi_id`);

--
-- Indexes for table `template_whatsapp`
--
ALTER TABLE `template_whatsapp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_template_whatsapp_kode` (`kode_template`);

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
-- AUTO_INCREMENT for table `antrean_harian`
--
ALTER TABLE `antrean_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jenis_layanan`
--
ALTER TABLE `jenis_layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kegiatan_servis`
--
ALTER TABLE `kegiatan_servis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mekanik`
--
ALTER TABLE `mekanik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifikasi_wa`
--
ALTER TABLE `notifikasi_wa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `reservasi_kegiatan`
--
ALTER TABLE `reservasi_kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `reservasi_sparepart`
--
ALTER TABLE `reservasi_sparepart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `template_whatsapp`
--
ALTER TABLE `template_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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

--
-- Constraints for table `reservasi_sparepart`
--
ALTER TABLE `reservasi_sparepart`
  ADD CONSTRAINT `fk_reservasi_sparepart_reservasi` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
