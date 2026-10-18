-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 18, 2026 at 08:16 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kalilaya`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_setoran`
--

CREATE TABLE `detail_setoran` (
  `id` int NOT NULL,
  `setoran_id` int NOT NULL,
  `jenis_id` int NOT NULL,
  `berat_kg` decimal(10,2) NOT NULL DEFAULT '0.00',
  `harga_per_kg` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_setoran`
--

INSERT INTO `detail_setoran` (`id`, `setoran_id`, `jenis_id`, `berat_kg`, `harga_per_kg`, `subtotal`) VALUES
(1, 1, 1, 3.50, 4000.00, 14000.00),
(2, 1, 2, 5.00, 2500.00, 12500.00),
(3, 2, 3, 1.20, 12000.00, 14400.00),
(4, 2, 5, 4.00, 2000.00, 8000.00),
(5, 3, 1, 6.00, 4000.00, 24000.00),
(6, 3, 4, 3.00, 1500.00, 4500.00),
(7, 4, 7, 10.00, 5000.00, 50000.00),
(8, 5, 2, 20.00, 2500.00, 50000.00),
(9, 6, 3, 2.00, 12000.00, 24000.00),
(10, 6, 6, 0.50, 65000.00, 32500.00),
(11, 7, 1, 8.00, 4000.00, 32000.00),
(12, 8, 4, 10.00, 1500.00, 15000.00),
(13, 8, 8, 4.00, 3000.00, 12000.00),
(14, 9, 1, 5.00, 4000.00, 20000.00),
(15, 9, 2, 10.00, 2500.00, 25000.00),
(16, 10, 1, 10.00, 4000.00, 40000.00);

-- --------------------------------------------------------

--
-- Table structure for table `edukasi`
--

CREATE TABLE `edukasi` (
  `id` int NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `edukasi`
--

INSERT INTO `edukasi` (`id`, `judul`, `isi`, `gambar`, `tanggal`) VALUES
(1, 'Cara Memilah Sampah Rumah Tangga', 'Pilah sampah menjadi tiga kelompok utama: organik (sisa makanan, daun), anorganik yang bisa didaur ulang (plastik, kertas, kaleng, kaca), dan residu (sampah yang tidak dapat didaur ulang). Pisahkan wadah plastik agar memudahkan penimbangan di bank sampah.', NULL, '2026-09-01'),
(2, 'Mengubah Sampah Plastik Menjadi Bernilai', 'Botol plastik PET memiliki nilai jual tinggi. Bilas botol hingga bersih, lepaskan tutup dan label, lalu pipihkan agar hemat tempat. Semakin bersih dan kering, semakin tinggi harga yang diterima di bank sampah Kali Laya Berseri.', NULL, '2026-09-15'),
(3, 'Manfaat Bank Sampah bagi Lingkungan', 'Bank sampah mengurangi volume sampah ke TPA, menghemat sumber daya, dan menambah penghasilan warga. Setiap kilogram sampah yang Anda setorkan membantu menjaga kebersihan sungai Kali Laya dan lingkungan sekitar.', NULL, '2026-10-01');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_sampah`
--

CREATE TABLE `jenis_sampah` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_per_kg` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stok_kg` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_sampah`
--

INSERT INTO `jenis_sampah` (`id`, `nama`, `kategori`, `harga_per_kg`, `stok_kg`, `created_at`) VALUES
(1, 'Botol Plastik PET', 'Plastik', 4000.00, 135.00, '2026-10-16 08:44:46'),
(2, 'Kardus', 'Kertas', 2500.00, 310.00, '2026-10-16 08:44:46'),
(3, 'Kaleng Aluminium', 'Logam', 12000.00, 45.00, '2026-10-16 08:44:46'),
(4, 'Botol Kaca', 'Kaca', 1500.00, 80.00, '2026-10-16 08:44:46'),
(5, 'Kertas HVS', 'Kertas', 2000.00, 150.00, '2026-10-16 08:44:46'),
(6, 'Tembaga', 'Logam', 65000.00, 8.00, '2026-10-16 08:44:46'),
(7, 'Besi Tua', 'Logam', 5000.00, 60.00, '2026-10-16 08:44:46'),
(8, 'Ember Plastik', 'Plastik', 3000.00, 40.00, '2026-10-16 08:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `pencairan`
--

CREATE TABLE `pencairan` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nominal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `status` enum('menunggu','disetujui','ditolak','selesai') NOT NULL DEFAULT 'menunggu',
  `disetujui_oleh` int DEFAULT NULL,
  `catatan_ketua` text,
  `diproses_oleh` int DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pencairan`
--

INSERT INTO `pencairan` (`id`, `user_id`, `nominal`, `status`, `disetujui_oleh`, `catatan_ketua`, `diproses_oleh`, `tanggal`, `tanggal_disetujui`, `tanggal_selesai`) VALUES
(1, 3, 30000.00, 'selesai', 2, 'Disetujui.', 1, '2026-10-08 09:00:00', '2026-10-08 09:30:00', '2026-10-08 10:00:00'),
(2, 3, 20000.00, 'disetujui', 2, '', NULL, '2026-10-14 08:00:00', '2026-10-16 16:03:16', NULL),
(3, 4, 40000.00, 'menunggu', NULL, NULL, NULL, '2026-10-13 11:00:00', NULL, NULL),
(4, 5, 50000.00, 'disetujui', 2, 'Disetujui, silakan diproses kasir.', NULL, '2026-10-12 10:00:00', '2026-10-12 12:00:00', NULL),
(5, 6, 10000.00, 'menunggu', NULL, NULL, NULL, '2026-10-15 09:30:00', NULL, NULL),
(6, 6, 5000.00, 'ditolak', 2, 'Pengajuan ganda, mohon tunggu proses sebelumnya selesai.', NULL, '2026-09-30 10:00:00', '2026-09-30 11:00:00', NULL),
(7, 3, 30000.00, 'selesai', 2, '', 1, '2026-10-16 16:04:18', '2026-10-16 16:04:50', '2026-10-16 16:05:25'),
(8, 7, 30000.00, 'selesai', 2, '', 1, '2026-10-16 16:46:32', '2026-10-16 16:46:44', '2026-10-16 16:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan_offtaker`
--

CREATE TABLE `penjualan_offtaker` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `nama_offtaker` varchar(150) NOT NULL,
  `jenis_id` int NOT NULL,
  `berat_kg` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `petugas_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjualan_offtaker`
--

INSERT INTO `penjualan_offtaker` (`id`, `tanggal`, `nama_offtaker`, `jenis_id`, `berat_kg`, `harga`, `total`, `petugas_id`) VALUES
(1, '2026-10-01', 'PT Daur Ulang Nusantara', 1, 100.00, 4500.00, 450000.00, 1),
(2, '2026-10-06', 'UD Kertas Jaya', 2, 250.00, 2800.00, 700000.00, 1),
(3, '2026-10-12', 'CV Logam Mulia', 3, 40.00, 13500.00, 540000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_jemput`
--

CREATE TABLE `permintaan_jemput` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `alamat` text NOT NULL,
  `jadwal` datetime NOT NULL,
  `estimasi` varchar(255) DEFAULT NULL,
  `status` enum('Diminta','Dijadwalkan','Selesai','Dibatalkan') NOT NULL DEFAULT 'Diminta',
  `catatan` text,
  `petugas_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permintaan_jemput`
--

INSERT INTO `permintaan_jemput` (`id`, `user_id`, `alamat`, `jadwal`, `estimasi`, `status`, `catatan`, `petugas_id`, `created_at`) VALUES
(1, 3, 'Jl. Kali Laya No. 12, RT 01/RW 02', '2026-10-17 09:00:00', 'Plastik, Kardus (?? 10 kg)', 'Selesai', 'Mohon dijemput pagi.', 1, '2026-10-16 08:44:46'),
(2, 5, 'Jl. Kenanga No. 8, RT 04/RW 01', '2026-10-18 10:00:00', 'Kaleng, Kaca (?? 8 kg)', 'Dijadwalkan', 'Ada banyak kaca.', 1, '2026-10-16 08:44:46'),
(3, 4, 'Jl. Melati No. 5, RT 03/RW 02', '2026-10-10 08:00:00', 'Besi tua (?? 15 kg)', 'Selesai', 'Sudah dijemput.', 1, '2026-10-16 08:44:46'),
(4, 3, 'Jl. Kali Laya No. 12, RT 01/RW 02', '2026-10-16 16:30:00', 'Plastik 5kg', 'Diminta', '', NULL, '2026-10-16 09:30:35'),
(5, 7, 'Jl. Kali Laya No. 12, RT 01/RW 02', '2026-10-16 16:45:00', 'Plastik 10kg', 'Diminta', '', NULL, '2026-10-16 09:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `saldo_mutasi`
--

CREATE TABLE `saldo_mutasi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tipe` enum('kredit','debit') NOT NULL,
  `nominal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `saldo_akhir` decimal(14,2) NOT NULL DEFAULT '0.00',
  `ref_type` varchar(20) DEFAULT NULL,
  `ref_id` int DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saldo_mutasi`
--

INSERT INTO `saldo_mutasi` (`id`, `user_id`, `tipe`, `nominal`, `saldo_akhir`, `ref_type`, `ref_id`, `keterangan`, `tanggal`) VALUES
(1, 3, 'kredit', 26500.00, 26500.00, 'setoran', 1, 'Setoran sampah', '2026-09-05 10:15:00'),
(2, 3, 'kredit', 22400.00, 48900.00, 'setoran', 2, 'Setoran sampah', '2026-09-18 14:30:00'),
(3, 3, 'kredit', 28500.00, 77400.00, 'setoran', 3, 'Setoran sampah', '2026-10-02 09:00:00'),
(4, 3, 'debit', 30000.00, 47400.00, 'pencairan', 1, 'Pencairan saldo', '2026-10-08 10:00:00'),
(5, 4, 'kredit', 50000.00, 50000.00, 'setoran', 4, 'Setoran sampah', '2026-09-10 11:00:00'),
(6, 4, 'kredit', 50000.00, 100000.00, 'setoran', 5, 'Setoran sampah', '2026-09-28 15:45:00'),
(7, 5, 'kredit', 56500.00, 56500.00, 'setoran', 6, 'Setoran sampah', '2026-09-15 08:30:00'),
(8, 5, 'kredit', 32000.00, 88500.00, 'setoran', 7, 'Setoran sampah', '2026-10-05 13:00:00'),
(9, 6, 'kredit', 27000.00, 27000.00, 'setoran', 8, 'Setoran sampah', '2026-09-20 16:20:00'),
(10, 3, 'kredit', 45000.00, 92400.00, 'setoran', 9, 'Setoran sampah', '2026-10-16 16:02:06'),
(11, 3, 'debit', 30000.00, 62400.00, 'pencairan', 7, 'Pencairan saldo', '2026-10-16 16:05:25'),
(12, 7, 'kredit', 40000.00, 40000.00, 'setoran', 10, 'Setoran sampah', '2026-10-16 16:45:58'),
(13, 7, 'debit', 30000.00, 10000.00, 'pencairan', 8, 'Pencairan saldo', '2026-10-16 16:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `setoran`
--

CREATE TABLE `setoran` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tanggal` datetime NOT NULL,
  `total_nilai` decimal(14,2) NOT NULL DEFAULT '0.00',
  `petugas_id` int DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `setoran`
--

INSERT INTO `setoran` (`id`, `user_id`, `tanggal`, `total_nilai`, `petugas_id`, `catatan`) VALUES
(1, 3, '2026-09-05 10:15:00', 26500.00, 1, 'Setoran langsung di kantor'),
(2, 3, '2026-09-18 14:30:00', 22400.00, 1, 'Setoran langsung di kantor'),
(3, 3, '2026-10-02 09:00:00', 28500.00, 1, 'Setoran melalui penjemputan'),
(4, 4, '2026-09-10 11:00:00', 50000.00, 1, 'Setoran langsung di kantor'),
(5, 4, '2026-09-28 15:45:00', 50000.00, 1, 'Setoran langsung di kantor'),
(6, 5, '2026-09-15 08:30:00', 56500.00, 1, 'Setoran langsung di kantor'),
(7, 5, '2026-10-05 13:00:00', 32000.00, 1, 'Setoran langsung di kantor'),
(8, 6, '2026-09-20 16:20:00', 27000.00, 1, 'Setoran langsung di kantor'),
(9, 3, '2026-10-16 16:02:06', 45000.00, 1, ''),
(10, 7, '2026-10-16 16:45:58', 40000.00, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('nasabah','pengurus','ketua') NOT NULL DEFAULT 'nasabah',
  `nik` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `code` int NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'notverified',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `name`, `email`, `password`, `google_id`, `avatar`, `role`, `nik`, `no_hp`, `alamat`, `code`, `status`, `aktif`, `created_at`) VALUES
(1, 'Budi Santoso', 'pengurus@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', NULL, NULL, 'pengurus', '3201010101010010', '081200000001', 'Kantor Bank Sampah Kali Laya', 0, 'verified', 1, '2026-10-16 08:44:46'),
(2, 'H. Suryana', 'ketua@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', NULL, NULL, 'ketua', '3201010101010011', '081200000002', 'Kantor Bank Sampah Kali Laya', 0, 'verified', 1, '2026-10-16 08:44:46'),
(3, 'Siti Aminah', 'nasabah@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', NULL, NULL, 'nasabah', '3201010101010001', '081234567001', 'Jl. Kali Laya No. 12, RT 01/RW 02', 0, 'verified', 1, '2026-10-16 08:44:46'),
(4, 'Ahmad Fauzi', 'ahmad@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', NULL, NULL, 'nasabah', '3201010101010002', '081234567002', 'Jl. Melati No. 5, RT 03/RW 02', 0, 'verified', 1, '2026-10-16 08:44:46'),
(5, 'Dewi Lestari', 'dewi@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', NULL, NULL, 'nasabah', '3201010101010003', '081234567003', 'Jl. Kenanga No. 8, RT 04/RW 01', 0, 'verified', 1, '2026-10-16 08:44:46'),
(6, 'Rudi Hartono', 'rudi@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', NULL, NULL, 'nasabah', '3201010101010004', '081234567004', 'Jl. Anggrek No. 3, RT 02/RW 03', 0, 'verified', 1, '2026-10-16 08:44:46'),
(7, 'Ya In', 'iny90891@gmail.com', NULL, '110604618126275042065', 'https://lh3.googleusercontent.com/a/ACg8ocKctZ1Sc4XLuGXtduUK0rxB1i-ckwRPXmNlpEazcLgGzYIg2Q=s96-c', 'nasabah', NULL, NULL, NULL, 0, 'verified', 1, '2026-10-16 09:44:55'),
(9, 'adnanfalaz', 'rayhanfalazadnan@gmail.com', '$2y$10$iBDy6vKdYjRWxi8PslajKO4ARqEOZV/779FXBomjbHD5pHU2vaIHu', NULL, NULL, 'nasabah', '1234567890123456', '081317477926', 'Jl. Kiwi Raya', 0, 'verified', 1, '2026-10-18 08:10:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detail_setoran` (`setoran_id`),
  ADD KEY `idx_detail_jenis` (`jenis_id`);

--
-- Indexes for table `edukasi`
--
ALTER TABLE `edukasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pencairan`
--
ALTER TABLE `pencairan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pencairan_user` (`user_id`),
  ADD KEY `idx_pencairan_setuju` (`disetujui_oleh`),
  ADD KEY `idx_pencairan_proses` (`diproses_oleh`);

--
-- Indexes for table `penjualan_offtaker`
--
ALTER TABLE `penjualan_offtaker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_offtaker_jenis` (`jenis_id`),
  ADD KEY `idx_offtaker_petugas` (`petugas_id`);

--
-- Indexes for table `permintaan_jemput`
--
ALTER TABLE `permintaan_jemput`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jemput_user` (`user_id`),
  ADD KEY `idx_jemput_petugas` (`petugas_id`);

--
-- Indexes for table `saldo_mutasi`
--
ALTER TABLE `saldo_mutasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mutasi_user` (`user_id`);

--
-- Indexes for table `setoran`
--
ALTER TABLE `setoran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_setoran_user` (`user_id`),
  ADD KEY `idx_setoran_petugas` (`petugas_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD UNIQUE KEY `uniq_google_id` (`google_id`),
  ADD UNIQUE KEY `uniq_nik` (`nik`),
  ADD UNIQUE KEY `uniq_no_hp` (`no_hp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `edukasi`
--
ALTER TABLE `edukasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pencairan`
--
ALTER TABLE `pencairan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `penjualan_offtaker`
--
ALTER TABLE `penjualan_offtaker`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permintaan_jemput`
--
ALTER TABLE `permintaan_jemput`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `saldo_mutasi`
--
ALTER TABLE `saldo_mutasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `setoran`
--
ALTER TABLE `setoran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  ADD CONSTRAINT `fk_detail_jenis` FOREIGN KEY (`jenis_id`) REFERENCES `jenis_sampah` (`id`),
  ADD CONSTRAINT `fk_detail_setoran` FOREIGN KEY (`setoran_id`) REFERENCES `setoran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pencairan`
--
ALTER TABLE `pencairan`
  ADD CONSTRAINT `fk_pencairan_proses` FOREIGN KEY (`diproses_oleh`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `fk_pencairan_setuju` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `fk_pencairan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `penjualan_offtaker`
--
ALTER TABLE `penjualan_offtaker`
  ADD CONSTRAINT `fk_offtaker_jenis` FOREIGN KEY (`jenis_id`) REFERENCES `jenis_sampah` (`id`),
  ADD CONSTRAINT `fk_offtaker_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `permintaan_jemput`
--
ALTER TABLE `permintaan_jemput`
  ADD CONSTRAINT `fk_jemput_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `fk_jemput_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `saldo_mutasi`
--
ALTER TABLE `saldo_mutasi`
  ADD CONSTRAINT `fk_mutasi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `setoran`
--
ALTER TABLE `setoran`
  ADD CONSTRAINT `fk_setoran_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `fk_setoran_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
