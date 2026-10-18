-- =====================================================================
--  DATABASE: db_kalilaya
--  Aplikasi Bank Sampah Kali Laya Berseri
--  Skema mengikuti PRD (Bagian 8) + kompatibel dengan auth yang sudah ada
--  (controllerUserData.php memakai: id_user, name, email, password,
--   code, status, role)
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `db_kalilaya`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_kalilaya`;

-- ---------------------------------------------------------------------
-- Tabel: users
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `saldo_mutasi`;
DROP TABLE IF EXISTS `pencairan`;
DROP TABLE IF EXISTS `detail_setoran`;
DROP TABLE IF EXISTS `setoran`;
DROP TABLE IF EXISTS `permintaan_jemput`;
DROP TABLE IF EXISTS `penjualan_offtaker`;
DROP TABLE IF EXISTS `edukasi`;
DROP TABLE IF EXISTS `jenis_sampah`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('nasabah','pengurus','ketua') NOT NULL DEFAULT 'nasabah',
  `nik` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `code` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'notverified',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_google_id` (`google_id`),
  UNIQUE KEY `uniq_nik` (`nik`),
  UNIQUE KEY `uniq_no_hp` (`no_hp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: jenis_sampah
-- ---------------------------------------------------------------------
CREATE TABLE `jenis_sampah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_per_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: setoran
-- ---------------------------------------------------------------------
CREATE TABLE `setoran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL,
  `total_nilai` decimal(14,2) NOT NULL DEFAULT 0.00,
  `petugas_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_setoran_user` (`user_id`),
  KEY `idx_setoran_petugas` (`petugas_id`),
  CONSTRAINT `fk_setoran_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
  CONSTRAINT `fk_setoran_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: detail_setoran
-- ---------------------------------------------------------------------
CREATE TABLE `detail_setoran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setoran_id` int(11) NOT NULL,
  `jenis_id` int(11) NOT NULL,
  `berat_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_per_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_detail_setoran` (`setoran_id`),
  KEY `idx_detail_jenis` (`jenis_id`),
  CONSTRAINT `fk_detail_setoran` FOREIGN KEY (`setoran_id`) REFERENCES `setoran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_jenis` FOREIGN KEY (`jenis_id`) REFERENCES `jenis_sampah` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: permintaan_jemput
-- ---------------------------------------------------------------------
CREATE TABLE `permintaan_jemput` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `alamat` text NOT NULL,
  `jadwal` datetime NOT NULL,
  `estimasi` varchar(255) DEFAULT NULL,
  `status` enum('Diminta','Dijadwalkan','Selesai','Dibatalkan') NOT NULL DEFAULT 'Diminta',
  `catatan` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_jemput_user` (`user_id`),
  KEY `idx_jemput_petugas` (`petugas_id`),
  CONSTRAINT `fk_jemput_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
  CONSTRAINT `fk_jemput_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: saldo_mutasi
-- ---------------------------------------------------------------------
CREATE TABLE `saldo_mutasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tipe` enum('kredit','debit') NOT NULL,
  `nominal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `saldo_akhir` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ref_type` varchar(20) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mutasi_user` (`user_id`),
  CONSTRAINT `fk_mutasi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: pencairan
-- ---------------------------------------------------------------------
CREATE TABLE `pencairan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nominal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('menunggu','disetujui','ditolak','selesai') NOT NULL DEFAULT 'menunggu',
  `disetujui_oleh` int(11) DEFAULT NULL,
  `catatan_ketua` text DEFAULT NULL,
  `diproses_oleh` int(11) DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pencairan_user` (`user_id`),
  KEY `idx_pencairan_setuju` (`disetujui_oleh`),
  KEY `idx_pencairan_proses` (`diproses_oleh`),
  CONSTRAINT `fk_pencairan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
  CONSTRAINT `fk_pencairan_setuju` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id_user`),
  CONSTRAINT `fk_pencairan_proses` FOREIGN KEY (`diproses_oleh`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: penjualan_offtaker
-- ---------------------------------------------------------------------
CREATE TABLE `penjualan_offtaker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `nama_offtaker` varchar(150) NOT NULL,
  `jenis_id` int(11) NOT NULL,
  `berat_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `petugas_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_offtaker_jenis` (`jenis_id`),
  KEY `idx_offtaker_petugas` (`petugas_id`),
  CONSTRAINT `fk_offtaker_jenis` FOREIGN KEY (`jenis_id`) REFERENCES `jenis_sampah` (`id`),
  CONSTRAINT `fk_offtaker_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: edukasi
-- ---------------------------------------------------------------------
CREATE TABLE `edukasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
--  SEED DATA DEMO
--  Password semua akun demo: kalilaya123
-- =====================================================================

INSERT INTO `users`
(`id_user`, `name`, `email`, `password`, `role`, `nik`, `no_hp`, `alamat`, `code`, `status`, `aktif`) VALUES
(1, 'Budi Santoso', 'pengurus@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', 'pengurus', '3201010101010010', '081200000001', 'Kantor Bank Sampah Kali Laya', 0, 'verified', 1),
(2, 'H. Suryana', 'ketua@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', 'ketua', '3201010101010011', '081200000002', 'Kantor Bank Sampah Kali Laya', 0, 'verified', 1),
(3, 'Siti Aminah', 'nasabah@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', 'nasabah', '3201010101010001', '081234567001', 'Jl. Kali Laya No. 12, RT 01/RW 02', 0, 'verified', 1),
(4, 'Ahmad Fauzi', 'ahmad@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', 'nasabah', '3201010101010002', '081234567002', 'Jl. Melati No. 5, RT 03/RW 02', 0, 'verified', 1),
(5, 'Dewi Lestari', 'dewi@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', 'nasabah', '3201010101010003', '081234567003', 'Jl. Kenanga No. 8, RT 04/RW 01', 0, 'verified', 1),
(6, 'Rudi Hartono', 'rudi@kalilaya.test', '$2b$10$dih/0YMPBQ8kd.u8YAaV2OQiAHQLkN51kt7yvAKMIjgGjGFWufNnK', 'nasabah', '3201010101010004', '081234567004', 'Jl. Anggrek No. 3, RT 02/RW 03', 0, 'verified', 1);

INSERT INTO `jenis_sampah` (`id`, `nama`, `kategori`, `harga_per_kg`, `stok_kg`) VALUES
(1, 'Botol Plastik PET', 'Plastik', 4000.00, 120.00),
(2, 'Kardus', 'Kertas', 2500.00, 300.00),
(3, 'Kaleng Aluminium', 'Logam', 12000.00, 45.00),
(4, 'Botol Kaca', 'Kaca', 1500.00, 80.00),
(5, 'Kertas HVS', 'Kertas', 2000.00, 150.00),
(6, 'Tembaga', 'Logam', 65000.00, 8.00),
(7, 'Besi Tua', 'Logam', 5000.00, 60.00),
(8, 'Ember Plastik', 'Plastik', 3000.00, 40.00);

INSERT INTO `setoran` (`id`, `user_id`, `tanggal`, `total_nilai`, `petugas_id`, `catatan`) VALUES
(1, 3, '2026-09-05 10:15:00', 26500.00, 1, 'Setoran langsung di kantor'),
(2, 3, '2026-09-18 14:30:00', 22400.00, 1, 'Setoran langsung di kantor'),
(3, 3, '2026-10-02 09:00:00', 28500.00, 1, 'Setoran melalui penjemputan'),
(4, 4, '2026-09-10 11:00:00', 50000.00, 1, 'Setoran langsung di kantor'),
(5, 4, '2026-09-28 15:45:00', 50000.00, 1, 'Setoran langsung di kantor'),
(6, 5, '2026-09-15 08:30:00', 56500.00, 1, 'Setoran langsung di kantor'),
(7, 5, '2026-10-05 13:00:00', 32000.00, 1, 'Setoran langsung di kantor'),
(8, 6, '2026-09-20 16:20:00', 27000.00, 1, 'Setoran langsung di kantor');

INSERT INTO `detail_setoran` (`setoran_id`, `jenis_id`, `berat_kg`, `harga_per_kg`, `subtotal`) VALUES
(1, 1, 3.50, 4000.00, 14000.00),
(1, 2, 5.00, 2500.00, 12500.00),
(2, 3, 1.20, 12000.00, 14400.00),
(2, 5, 4.00, 2000.00, 8000.00),
(3, 1, 6.00, 4000.00, 24000.00),
(3, 4, 3.00, 1500.00, 4500.00),
(4, 7, 10.00, 5000.00, 50000.00),
(5, 2, 20.00, 2500.00, 50000.00),
(6, 3, 2.00, 12000.00, 24000.00),
(6, 6, 0.50, 65000.00, 32500.00),
(7, 1, 8.00, 4000.00, 32000.00),
(8, 4, 10.00, 1500.00, 15000.00),
(8, 8, 4.00, 3000.00, 12000.00);

INSERT INTO `saldo_mutasi` (`user_id`, `tipe`, `nominal`, `saldo_akhir`, `ref_type`, `ref_id`, `keterangan`, `tanggal`) VALUES
(3, 'kredit', 26500.00, 26500.00, 'setoran', 1, 'Setoran sampah', '2026-09-05 10:15:00'),
(3, 'kredit', 22400.00, 48900.00, 'setoran', 2, 'Setoran sampah', '2026-09-18 14:30:00'),
(3, 'kredit', 28500.00, 77400.00, 'setoran', 3, 'Setoran sampah', '2026-10-02 09:00:00'),
(3, 'debit', 30000.00, 47400.00, 'pencairan', 1, 'Pencairan saldo', '2026-10-08 10:00:00'),
(4, 'kredit', 50000.00, 50000.00, 'setoran', 4, 'Setoran sampah', '2026-09-10 11:00:00'),
(4, 'kredit', 50000.00, 100000.00, 'setoran', 5, 'Setoran sampah', '2026-09-28 15:45:00'),
(5, 'kredit', 56500.00, 56500.00, 'setoran', 6, 'Setoran sampah', '2026-09-15 08:30:00'),
(5, 'kredit', 32000.00, 88500.00, 'setoran', 7, 'Setoran sampah', '2026-10-05 13:00:00'),
(6, 'kredit', 27000.00, 27000.00, 'setoran', 8, 'Setoran sampah', '2026-09-20 16:20:00');

INSERT INTO `pencairan` (`id`, `user_id`, `nominal`, `status`, `disetujui_oleh`, `catatan_ketua`, `diproses_oleh`, `tanggal`, `tanggal_disetujui`, `tanggal_selesai`) VALUES
(1, 3, 30000.00, 'selesai', 2, 'Disetujui.', 1, '2026-10-08 09:00:00', '2026-10-08 09:30:00', '2026-10-08 10:00:00'),
(2, 3, 20000.00, 'menunggu', NULL, NULL, NULL, '2026-10-14 08:00:00', NULL, NULL),
(3, 4, 40000.00, 'menunggu', NULL, NULL, NULL, '2026-10-13 11:00:00', NULL, NULL),
(4, 5, 50000.00, 'disetujui', 2, 'Disetujui, silakan diproses kasir.', NULL, '2026-10-12 10:00:00', '2026-10-12 12:00:00', NULL),
(5, 6, 10000.00, 'menunggu', NULL, NULL, NULL, '2026-10-15 09:30:00', NULL, NULL),
(6, 6, 5000.00, 'ditolak', 2, 'Pengajuan ganda, mohon tunggu proses sebelumnya selesai.', NULL, '2026-09-30 10:00:00', '2026-09-30 11:00:00', NULL);

INSERT INTO `permintaan_jemput` (`id`, `user_id`, `alamat`, `jadwal`, `estimasi`, `status`, `catatan`, `petugas_id`) VALUES
(1, 3, 'Jl. Kali Laya No. 12, RT 01/RW 02', '2026-10-17 09:00:00', 'Plastik, Kardus (± 10 kg)', 'Diminta', 'Mohon dijemput pagi.', NULL),
(2, 5, 'Jl. Kenanga No. 8, RT 04/RW 01', '2026-10-18 10:00:00', 'Kaleng, Kaca (± 8 kg)', 'Dijadwalkan', 'Ada banyak kaca.', 1),
(3, 4, 'Jl. Melati No. 5, RT 03/RW 02', '2026-10-10 08:00:00', 'Besi tua (± 15 kg)', 'Selesai', 'Sudah dijemput.', 1);

INSERT INTO `penjualan_offtaker` (`id`, `tanggal`, `nama_offtaker`, `jenis_id`, `berat_kg`, `harga`, `total`, `petugas_id`) VALUES
(1, '2026-10-01', 'PT Daur Ulang Nusantara', 1, 100.00, 4500.00, 450000.00, 1),
(2, '2026-10-06', 'UD Kertas Jaya', 2, 250.00, 2800.00, 700000.00, 1),
(3, '2026-10-12', 'CV Logam Mulia', 3, 40.00, 13500.00, 540000.00, 1);

INSERT INTO `edukasi` (`id`, `judul`, `isi`, `gambar`, `tanggal`) VALUES
(1, 'Cara Memilah Sampah Rumah Tangga', 'Pilah sampah menjadi tiga kelompok utama: organik (sisa makanan, daun), anorganik yang bisa didaur ulang (plastik, kertas, kaleng, kaca), dan residu (sampah yang tidak dapat didaur ulang). Pisahkan wadah plastik agar memudahkan penimbangan di bank sampah.', NULL, '2026-09-01'),
(2, 'Mengubah Sampah Plastik Menjadi Bernilai', 'Botol plastik PET memiliki nilai jual tinggi. Bilas botol hingga bersih, lepaskan tutup dan label, lalu pipihkan agar hemat tempat. Semakin bersih dan kering, semakin tinggi harga yang diterima di bank sampah Kali Laya Berseri.', NULL, '2026-09-15'),
(3, 'Manfaat Bank Sampah bagi Lingkungan', 'Bank sampah mengurangi volume sampah ke TPA, menghemat sumber daya, dan menambah penghasilan warga. Setiap kilogram sampah yang Anda setorkan membantu menjaga kebersihan sungai Kali Laya dan lingkungan sekitar.', NULL, '2026-10-01');

COMMIT;
