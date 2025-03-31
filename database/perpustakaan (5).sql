-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Mar 2025 pada 15.04
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `p_role`
--

CREATE TABLE `p_role` (
  `id_p_role` int(11) NOT NULL,
  `nama_role` varchar(10) NOT NULL,
  `create_date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `p_role`
--

INSERT INTO `p_role` (`id_p_role`, `nama_role`, `create_date`) VALUES
(1, 'admin', '2016-10-29 21:03:10'),
(2, 'staff', '2016-10-29 21:03:20'),
(3, 'anggota', '2016-10-29 21:03:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_account`
--

CREATE TABLE `t_account` (
  `id_t_account` int(11) NOT NULL,
  `id_p_role` int(11) NOT NULL,
  `id_t_anggota` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL COMMENT 'MD5 hash',
  `create_date` datetime NOT NULL,
  `create_by` varchar(3) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username',
  `email` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_account`
--

INSERT INTO `t_account` (`id_t_account`, `id_p_role`, `id_t_anggota`, `username`, `password`, `create_date`, `create_by`, `update_date`, `update_by`, `email`) VALUES
(17, 2, NULL, 'gil', '$2y$10$VNiOmhOk2wUfXgdkUfG8h.YgLaJywZMo/Jtv31H3bE5Vyfakv3Ks6', '2025-01-31 00:00:00', 'min', '2025-02-06 11:27:49', 'gil', ''),
(16, 1, NULL, 'min', '$2y$10$oLwYqi7pmJyYPFdyJYC18uV4VS2dFjQ9O4uyFVGAcgVHS5LG/FYJW', '2025-01-30 00:00:00', 'sys', NULL, NULL, ''),
(20, 3, 16, 'Tasya', '$2y$10$7/yFR1MdgnIE.YO25twVl.35EXTz16ke91ARzV3eJHYDyPinv.oAi', '2025-02-10 00:00:00', 'min', NULL, NULL, ''),
(19, 3, 15, 'Dayvin', '$2y$10$aqkXG9URXODmbJ/TlDZNle1sFmKC1jjzPEVlpzol.XGjlKxUz972.', '2025-02-04 00:00:00', 'min', NULL, NULL, ''),
(24, 3, 20, 'Ghani', '$2y$10$hq0xeBtxXJBZDGXVSqjdlegTPWD7BxlSczp3Ke42IvMsh3pik/UYe', '2025-03-07 00:00:00', 'min', NULL, NULL, ''),
(23, 3, 19, 'Mus', '$2y$10$rf04SGgdbyu8mKYND5DcOOrVdvFVfvUUIjv..gbH3dJf3hKM1T1uS', '2025-02-24 08:50:24', 'Mus', NULL, NULL, ''),
(25, 2, NULL, 'ghani', '$2y$10$rIRkmzbOpxQ4m597/6Nlr.nbrxjLjwE5Q8xl0w8QB/PPGEOrudfuy', '2025-03-07 00:00:00', 'min', NULL, NULL, ''),
(29, 3, 21, 'shay', '$2y$10$.mtChwaJEC64BqIawCBehOTkjI31Be8lhuJiVTxHi.CXh6CabIk6O', '2025-03-13 08:15:10', 'sha', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_anggota`
--

CREATE TABLE `t_anggota` (
  `id_t_anggota` int(100) NOT NULL,
  `id_t_account` int(100) NOT NULL,
  `no_anggota` varchar(100) NOT NULL,
  `nama` varchar(25) NOT NULL,
  `tgl_daftar` date NOT NULL,
  `tgl_lahir` date NOT NULL,
  `jenis_kelamin` varchar(10) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `alamat` longtext NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `keterangan` varchar(64) DEFAULT NULL,
  `status` varchar(11) DEFAULT NULL COMMENT 'aktif, tidak aktif',
  `update_date` date DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username',
  `create_by` varchar(3) NOT NULL COMMENT 'Username',
  `create_date` date NOT NULL,
  `email` text NOT NULL,
  `allow_download` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_anggota`
--

INSERT INTO `t_anggota` (`id_t_anggota`, `id_t_account`, `no_anggota`, `nama`, `tgl_daftar`, `tgl_lahir`, `jenis_kelamin`, `no_telp`, `alamat`, `foto_profil`, `bio`, `keterangan`, `status`, `update_date`, `update_by`, `create_by`, `create_date`, `email`, `allow_download`) VALUES
(15, 19, 'AGT20250204', 'Dayvin Gavriel', '2025-02-04', '2025-01-29', 'Laki-laki', '081289301928', 'surabaya', NULL, NULL, '', 'Aktif', '2025-03-18', 'Day', 'min', '2025-02-04', 'gavrieldayvin@gmail.com', 1),
(16, 0, 'AGT20250210', 'Natasya', '2025-02-10', '2004-02-17', 'Perempuan', '0814839204', 'Sidoarjo', NULL, NULL, '', 'Tidak Aktif', '2025-03-07', 'min', 'min', '2025-02-10', '', 0),
(18, 0, 'AGT57852459', 'Jeniffer', '2025-02-10', '2004-01-18', 'Perempuan', '081289301928', 'Candi', NULL, NULL, '', 'Aktif', NULL, NULL, 'min', '2025-02-10', '', 1),
(19, 0, 'AGT20250224', 'Muslihin', '2025-02-24', '2001-11-10', 'Laki-laki', '0814892837', 'Gresik', NULL, NULL, NULL, 'Aktif', NULL, NULL, 'Mus', '2025-02-24', '', 1),
(21, 0, 'AGT20250313001', 'shaynie jovanca', '2025-03-13', '2000-12-10', 'Perempuan', '0987654321', 'Sidoarjo', NULL, NULL, NULL, 'Aktif', '2025-03-18', 'sha', 'sha', '2025-03-13', 'shayniejovanca123@gmail.com', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_bookmark`
--

CREATE TABLE `t_bookmark` (
  `id_bookmark` int(11) NOT NULL,
  `id_t_buku` int(11) DEFAULT NULL,
  `id_t_anggota` int(11) DEFAULT NULL,
  `tanggal_bookmark` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_buku`
--

CREATE TABLE `t_buku` (
  `id_t_buku` int(11) NOT NULL,
  `nama_buku` varchar(128) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `jenis` varchar(30) NOT NULL,
  `penulis` varchar(64) NOT NULL,
  `penerbit` varchar(64) NOT NULL,
  `bahasa` varchar(50) DEFAULT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `harga` int(8) NOT NULL COMMENT 'Untuk perhitungan denda',
  `kode_rak` varchar(3) NOT NULL,
  `stok` int(11) NOT NULL,
  `sinopsis` varchar(250) DEFAULT NULL,
  `preview_content` text DEFAULT NULL,
  `gambar` longtext DEFAULT NULL,
  `file_buku` varchar(255) DEFAULT NULL,
  `total_view` int(11) DEFAULT 0,
  `create_date` date NOT NULL,
  `create_by` varchar(3) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username',
  `total_halaman` int(11) DEFAULT 0,
  `batas_baca_guest` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_buku`
--

INSERT INTO `t_buku` (`id_t_buku`, `nama_buku`, `isbn`, `jenis`, `penulis`, `penerbit`, `bahasa`, `tahun_terbit`, `harga`, `kode_rak`, `stok`, `sinopsis`, `preview_content`, `gambar`, `file_buku`, `total_view`, `create_date`, `create_by`, `update_date`, `update_by`, `total_halaman`, `batas_baca_guest`) VALUES
(13, 'Corrupt', '1010293849', 'Umum', 'desi', 'Andi', 'Indonesia', '2012', 30000, '2', 42, 'bllalalla', '', '1742195427', '1741319629_Corrupt (Devils Night 1).pdf', 19, '0000-00-00', 'min', '2025-03-17 14:10:27', 'min', 384, 5),
(14, 'The Adventures of Philip and Sophie DOUBLE FEATURE', '1920483726', 'Komik', 'Drew Eldridge', 'Children', 'Inggris', '2019', 50000, '2', 46, 'Philip is a wild boy from the forest. Sophie is a girl with a special power. When pirates, an evil king and a dragon threaten their worlds, fate brings them together for an adventure of the ages.', '', '1741319730_Screenshot-2025-03-07-054609.png', '1741319730_The-Adventures-of-Philip-and-Sophie-DOUBLE-FEATURE.pdf', 23, '0000-00-00', 'min', NULL, NULL, 220, 5),
(15, 'Smart Strategies: AI Tools for Sales and Marketing Excellence', '2789187658', 'Pengembangan Diri', 'Tadeusz Murawski', 'Artificial Intelligence', 'Inggris', '2020', 45000, '3', 43, 'Unlock the power of AI with \\\"Smart Strategies: AI Tools for Sales and Marketing Excellence\\\" by Tadeusz Murawski. This comprehensive guide dives deep into the transformative potential of artificial intelligence, revealing how to optimize sales and m', '', '1741319831_Screenshot-2025-03-07-091650.png', '1741319831_Smart-Strategies-AI-Tools-for-Sales-and-Marketing-Excellence.pdf', 15, '0000-00-00', 'min', NULL, NULL, 47, 5),
(16, 'Friday\\\'s Fade', '5672938108', 'Novel', 'Ismail \\\"Ish\\\" Rashad', 'Mystery', 'Inggris', '2021', 100000, '4', 50, 'Fridays fade, but Friday\\\'s Fade is forever. Astrid Draven Evers had it all: a bright future, loyal friends, and a budding romance with Julian. But everything shattered the night she was lured to an exclusive underground party where she was drugged a', '', '1741319912_Screenshot-2025-03-07-102331.png', '1741319912_Friday-s-Fade.pdf', 11, '0000-00-00', 'min', NULL, NULL, 71, 5),
(18, 'Sistem Pendidikan Tinggi di Indonesia', '1792839102', 'Pengembangan Diri', 'Sando Sasako', 'Gramedia', 'Indonesia', '2016', 75000, '3', 48, 'This book begins with the current state of higher education system implemented in Indonesia. As ‘this’ business has grown to an industry that has ever-lasting growing demands, the government and the public have become fascinated that its raison d\\\'êt', '', '1741621076_Screenshot-2025-03-10-223740.png', NULL, 0, '0000-00-00', 'min', NULL, NULL, 194, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_denda`
--

CREATE TABLE `t_denda` (
  `id_t_denda` int(11) NOT NULL,
  `id_t_peminjaman` int(11) NOT NULL,
  `jumlah_denda` decimal(10,2) NOT NULL,
  `hari_terlambat` int(11) NOT NULL,
  `status_pembayaran` enum('Belum Dibayar','Sudah Dibayar') DEFAULT 'Belum Dibayar',
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_detil_pinjam`
--

CREATE TABLE `t_detil_pinjam` (
  `id_t_detil_pinjam` int(10) NOT NULL,
  `id_t_peminjaman` int(11) NOT NULL,
  `id_t_buku` int(11) NOT NULL,
  `tgl_kembali` datetime DEFAULT NULL,
  `kondisi` varchar(10) DEFAULT NULL COMMENT 'bagus, rusak, hilang',
  `denda` int(11) DEFAULT NULL COMMENT '30% dari harga buku jika rusak',
  `qty` int(8) NOT NULL,
  `keterangan` varchar(64) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Session',
  `create_date` date DEFAULT NULL,
  `create_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_detil_pinjam`
--

INSERT INTO `t_detil_pinjam` (`id_t_detil_pinjam`, `id_t_peminjaman`, `id_t_buku`, `tgl_kembali`, `kondisi`, `denda`, `qty`, `keterangan`, `update_date`, `update_by`, `create_date`, `create_by`) VALUES
(29, 0, 2, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-02-05', 'min'),
(30, 0, 2, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-02-05', 'min'),
(31, 0, 2, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-02-05', 'min'),
(32, 0, 4, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-02-05', 'min'),
(35, 55, 1, NULL, 'Rusak', 21450, 1, '', NULL, NULL, NULL, NULL),
(36, 61, 6, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL),
(37, 62, 6, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(38, 63, 2, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(39, 64, 1, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(40, 65, 4, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(41, 66, 4, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(42, 67, 1, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(43, 68, 1, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(44, 69, 6, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(45, 70, 6, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(46, 71, 1, NULL, 'Bagus', 0, 1, '', NULL, NULL, NULL, NULL),
(47, 72, 4, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL),
(48, 73, 4, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(49, 74, 6, NULL, 'Rusak', 4800, 2, '', NULL, NULL, '2025-02-10', 'min'),
(50, 77, 6, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(51, 77, 1, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(52, 78, 3, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(53, 79, 5, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(54, 80, 5, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(55, 81, 2, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(56, 82, 4, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(57, 83, 3, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(58, 84, 6, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-11', 'min'),
(59, 85, 7, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL),
(60, 86, 7, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(61, 87, 7, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(62, 88, 1, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(63, 89, 3, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(64, 90, 4, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(65, 91, 7, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(66, 92, 7, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(67, 93, 7, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(68, 94, 7, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-02-28', 'min'),
(69, 95, 3, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-02-28', 'min'),
(70, 96, 6, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(71, 97, 3, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-02-28', 'min'),
(72, 97, 7, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-02-28', 'min'),
(73, 98, 8, NULL, 'Baik', 0, 2, '', '2025-02-28 00:00:00', 'min', '2025-02-28', 'min'),
(74, 98, 7, NULL, 'Rusak', 45, 1, '', '2025-02-28 00:00:00', 'min', '2025-02-28', 'min'),
(75, 98, 6, NULL, 'Rusak', 4800, 1, '', '2025-02-28 00:00:00', 'min', '2025-02-28', 'min'),
(76, 99, 3, NULL, 'Baik', 0, 1, '-', '2025-03-01 00:00:00', 'min', '2025-02-28', 'min'),
(77, 100, 7, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-03-07', 'gil'),
(78, 100, 6, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-07', 'gil'),
(79, 101, 7, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-03-07', 'gil'),
(80, 101, 6, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-07', 'gil'),
(81, 102, 7, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-07', 'min'),
(82, 103, 15, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-03-07', 'min'),
(83, 103, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-07', 'min'),
(84, 104, 15, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-07', 'gil'),
(85, 104, 14, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-07', 'gil'),
(86, 105, 14, NULL, 'Rusak', 15000, 2, 'sobek', '2025-03-07 00:00:00', 'gil', '2025-03-07', 'min'),
(87, 105, 13, NULL, 'Baik', 0, 1, '-', '2025-03-07 00:00:00', 'gil', '2025-03-07', 'min'),
(88, 106, 13, NULL, 'Rusak', 9000, 1, '', '2025-03-11 00:00:00', 'mus', '2025-03-07', 'min'),
(89, 106, 14, NULL, 'Hilang', 50000, 1, '', '2025-03-11 00:00:00', 'mus', '2025-03-07', 'min'),
(90, 107, 14, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-03-11', 'mus'),
(91, 107, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-11', 'mus'),
(92, 108, 13, NULL, 'Rusak', 9000, 1, '', '2025-03-18 00:00:00', 'min', '2025-03-14', 'min'),
(93, 109, 18, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-14', 'min'),
(94, 110, 15, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-14', 'min'),
(95, 111, 15, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-14', 'min'),
(96, 112, 18, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-14', 'min'),
(97, 113, 15, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-14', 'min'),
(98, 114, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(99, 115, 15, NULL, 'Baik', 0, 1, '', '2025-03-18 00:00:00', 'day', '2025-03-16', 'min'),
(100, 116, 15, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(101, 117, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(102, 118, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(103, 119, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(104, 120, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(105, 121, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(106, 122, 13, NULL, 'Baik', NULL, 1, NULL, NULL, NULL, '2025-03-16', 'min'),
(107, 122, 14, NULL, 'Baik', NULL, 2, NULL, NULL, NULL, '2025-03-16', 'min');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_email_verification`
--

CREATE TABLE `t_email_verification` (
  `id` int(11) NOT NULL,
  `id_t_anggota` int(11) NOT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_kategori_buku`
--

CREATE TABLE `t_kategori_buku` (
  `id_t_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `create_by` varchar(50) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_kategori_buku`
--

INSERT INTO `t_kategori_buku` (`id_t_kategori`, `nama_kategori`, `create_by`, `create_date`) VALUES
(1, 'Umum', 'min', '2025-03-16 14:23:28'),
(2, 'Novel', 'min', '2025-03-16 14:23:28'),
(3, 'Pendidikan', 'min', '2025-03-16 14:23:28'),
(4, 'Teknologi', 'min', '2025-03-16 14:23:28'),
(6, 'Komedi', 'min', '2025-03-16 15:40:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_log_download`
--

CREATE TABLE `t_log_download` (
  `id_t_log_download` int(11) NOT NULL,
  `id_t_anggota` int(11) NOT NULL,
  `id_t_buku` int(11) NOT NULL,
  `tanggal_download` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_log_download`
--

INSERT INTO `t_log_download` (`id_t_log_download`, `id_t_anggota`, `id_t_buku`, `tanggal_download`) VALUES
(1, 15, 16, '2025-03-17 00:03:55'),
(2, 15, 16, '2025-03-17 00:03:58'),
(3, 15, 16, '2025-03-17 00:04:03'),
(4, 15, 16, '2025-03-17 00:12:59'),
(5, 15, 16, '2025-03-17 00:13:20'),
(6, 19, 16, '2025-03-17 07:52:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_peminjaman`
--

CREATE TABLE `t_peminjaman` (
  `id_t_peminjaman` int(11) NOT NULL,
  `no_peminjaman` varchar(100) NOT NULL,
  `id_t_staff` int(11) DEFAULT NULL,
  `id_t_anggota` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date DEFAULT NULL COMMENT 'By System',
  `status` varchar(20) DEFAULT 'Dipinjam',
  `total_denda` int(11) DEFAULT NULL,
  `create_date` date NOT NULL,
  `create_by` varchar(3) NOT NULL COMMENT 'Username Staff',
  `update_date` date DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username Staff'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_peminjaman`
--

INSERT INTO `t_peminjaman` (`id_t_peminjaman`, `no_peminjaman`, `id_t_staff`, `id_t_anggota`, `tgl_pinjam`, `tgl_kembali`, `status`, `total_denda`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
(105, 'PJM20250307001', NULL, 19, '2025-03-07', '2025-03-07', 'Sudah Kembali', NULL, '2025-03-07', 'min', NULL, NULL),
(106, 'PJM20250307002', NULL, 19, '2025-03-07', '2025-03-11', 'Sudah Kembali', NULL, '2025-03-07', 'min', NULL, NULL),
(107, 'PJM20250311001', NULL, 15, '2025-03-11', '2025-03-18', 'Dipinjam', NULL, '2025-03-11', 'mus', NULL, NULL),
(108, 'PJM20250314001', NULL, 19, '2025-03-14', '2025-03-18', 'Sudah Kembali', NULL, '2025-03-14', 'min', NULL, NULL),
(109, 'PJM20250314002', NULL, 21, '2025-03-14', '2025-03-21', 'Dipinjam', NULL, '2025-03-14', 'min', NULL, NULL),
(110, 'PJM20250314003', NULL, 18, '2025-03-14', '2025-03-21', 'Dipinjam', NULL, '2025-03-14', 'min', NULL, NULL),
(111, 'PJM20250314004', NULL, 18, '2025-03-14', '2025-03-21', 'Dipinjam', NULL, '2025-03-14', 'min', NULL, NULL),
(112, 'PJM20250314005', NULL, 15, '2025-03-14', '2025-03-21', 'Dipinjam', NULL, '2025-03-14', 'min', NULL, NULL),
(113, 'PJM20250314006', NULL, 19, '2025-03-14', '2025-03-15', 'Belum Kembali', NULL, '2025-03-14', 'min', NULL, NULL),
(114, 'PJM20250316001', NULL, 19, '2025-03-16', '2025-03-23', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL),
(115, 'PJM20250316002', NULL, 15, '2025-03-16', '2025-03-18', 'Sudah Kembali', NULL, '2025-03-16', 'min', NULL, NULL),
(116, 'PJM20250316003', NULL, 15, '2025-03-16', '2025-03-17', 'Belum Kembali', NULL, '2025-03-16', 'min', NULL, NULL),
(117, 'PJM20250316004', NULL, 18, '2025-03-16', '2025-03-23', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL),
(118, 'PJM20250316005', NULL, 18, '2025-03-16', '2025-03-23', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL),
(119, 'PJM20250316006', NULL, 18, '2025-03-16', '2025-03-23', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL),
(120, 'PJM20250316007', NULL, 18, '2025-03-16', '2025-03-23', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL),
(121, 'PJM20250316008', NULL, 18, '2025-03-16', '2025-03-23', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL),
(122, 'PJM20250316009', NULL, 21, '2025-03-16', '2025-03-18', 'Dipinjam', NULL, '2025-03-16', 'min', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_rating_balasan`
--

CREATE TABLE `t_rating_balasan` (
  `id_t_rating_balasan` int(11) NOT NULL,
  `id_rating` int(11) DEFAULT NULL,
  `balasan` text DEFAULT NULL,
  `create_by` varchar(50) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_rating_balasan_like`
--

CREATE TABLE `t_rating_balasan_like` (
  `id_t_rating_balasan_like` int(11) NOT NULL,
  `id_t_rating_balasan` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `jenis` enum('like','dislike') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_rating_buku`
--

CREATE TABLE `t_rating_buku` (
  `id_rating` int(11) NOT NULL,
  `id_t_buku` int(11) DEFAULT NULL,
  `id_t_anggota` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `ulasan` text DEFAULT NULL,
  `created_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_rating_buku`
--

INSERT INTO `t_rating_buku` (`id_rating`, `id_t_buku`, `id_t_anggota`, `rating`, `ulasan`, `created_date`) VALUES
(9, 14, 19, 5, 'SIP', '2025-03-07 11:34:29'),
(10, 16, 19, 4, 'Bagus', '2025-03-11 11:03:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_rating_like`
--

CREATE TABLE `t_rating_like` (
  `id_rating_like` int(11) NOT NULL,
  `id_rating` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `jenis` enum('like','dislike') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_rating_like`
--

INSERT INTO `t_rating_like` (`id_rating_like`, `id_rating`, `username`, `jenis`) VALUES
(18, 9, 'mus', 'like'),
(19, 10, 'mus', 'dislike');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_riwayat_baca`
--

CREATE TABLE `t_riwayat_baca` (
  `id_riwayat` int(11) NOT NULL,
  `id_t_buku` int(11) DEFAULT NULL,
  `id_t_anggota` int(11) DEFAULT NULL,
  `tanggal_baca` datetime DEFAULT current_timestamp(),
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `durasi` time DEFAULT NULL,
  `halaman_terakhir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_riwayat_baca`
--

INSERT INTO `t_riwayat_baca` (`id_riwayat`, `id_t_buku`, `id_t_anggota`, `tanggal_baca`, `waktu_mulai`, `waktu_selesai`, `durasi`, `halaman_terakhir`) VALUES
(86, 16, 19, '2025-03-10 00:00:00', '21:56:20', '21:56:29', '00:00:09', 34),
(88, 14, 19, '2025-03-11 00:00:00', '09:50:13', '09:50:19', '00:00:06', 12),
(89, 16, 19, '2025-03-11 00:00:00', '11:04:02', '11:04:14', '00:00:12', 15),
(90, 13, 19, '2025-03-15 00:00:00', '19:50:06', NULL, NULL, NULL),
(91, 13, 19, '2025-03-18 00:00:00', '13:45:54', '13:50:16', '00:04:22', 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_staff`
--

CREATE TABLE `t_staff` (
  `id_t_staff` int(11) NOT NULL,
  `id_t_account` int(11) NOT NULL,
  `nama` varchar(25) NOT NULL,
  `alamat` varchar(64) DEFAULT NULL,
  `status` varchar(100) NOT NULL COMMENT 'aktif/tidak aktif',
  `create_date` datetime NOT NULL,
  `create_by` varchar(3) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_staff`
--

INSERT INTO `t_staff` (`id_t_staff`, `id_t_account`, `nama`, `alamat`, `status`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
(2, 17, 'Gilbert ala', 'Surabaya', 'Aktif', '2025-01-31 00:00:00', 'min', '2025-03-07 00:00:00', 'min'),
(3, 25, 'Ghani', 'Gresik', 'Tidak Aktif', '2025-03-07 00:00:00', 'min', '2025-03-07 00:00:00', 'min');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_detil_pinjam`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_detil_pinjam` (
`id_t_peminjaman` int(11)
,`id_t_detil_pinjam` int(10)
,`id_t_buku` int(11)
,`nama_buku` varchar(128)
,`penulis` varchar(64)
,`harga` int(8)
,`tgl_kembali` datetime
,`qty` int(8)
,`kondisi` varchar(10)
,`denda` int(11)
,`keterangan` varchar(64)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_history_peminjaman`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_history_peminjaman` (
`id_t_anggota` int(100)
,`no_anggota` varchar(100)
,`nama` varchar(25)
,`tgl_daftar` date
,`tgl_terakhir_pinjam` date
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_katalog_buku`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_katalog_buku` (
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_peminjaman`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_peminjaman` (
`id_t_peminjaman` int(11)
,`no_peminjaman` varchar(100)
,`staff` varchar(25)
,`tgl_pinjam` date
,`tgl_kembali` date
,`no_anggota` varchar(100)
,`anggota` varchar(25)
,`username` varchar(50)
,`ID` int(11)
,`id_t_anggota` int(100)
,`jum` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_detil_pinjam`
--
DROP TABLE IF EXISTS `v_detil_pinjam`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_detil_pinjam`  AS SELECT `a`.`id_t_peminjaman` AS `id_t_peminjaman`, `b`.`id_t_detil_pinjam` AS `id_t_detil_pinjam`, `c`.`id_t_buku` AS `id_t_buku`, `c`.`nama_buku` AS `nama_buku`, `c`.`penulis` AS `penulis`, `c`.`harga` AS `harga`, `b`.`tgl_kembali` AS `tgl_kembali`, `b`.`qty` AS `qty`, `b`.`kondisi` AS `kondisi`, `b`.`denda` AS `denda`, `b`.`keterangan` AS `keterangan` FROM ((`t_peminjaman` `a` join `t_detil_pinjam` `b` on(`a`.`id_t_peminjaman` = `b`.`id_t_peminjaman`)) join `t_buku` `c` on(`b`.`id_t_buku` = `c`.`id_t_buku`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_history_peminjaman`
--
DROP TABLE IF EXISTS `v_history_peminjaman`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_history_peminjaman`  AS SELECT `b`.`id_t_anggota` AS `id_t_anggota`, `b`.`no_anggota` AS `no_anggota`, `b`.`nama` AS `nama`, `b`.`tgl_daftar` AS `tgl_daftar`, `a`.`tgl_pinjam` AS `tgl_terakhir_pinjam` FROM (`t_anggota` `b` left join `t_peminjaman` `a` on(`a`.`id_t_anggota` = `b`.`id_t_anggota`)) ORDER BY `a`.`tgl_pinjam` DESC ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_katalog_buku`
--
DROP TABLE IF EXISTS `v_katalog_buku`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_katalog_buku`  AS SELECT `b`.`id_t_buku` AS `id_t_buku`, `b`.`nama_buku` AS `nama_buku`, `b`.`isbn` AS `isbn`, `b`.`penulis` AS `penulis`, `b`.`penerbit` AS `penerbit`, `b`.`tahun_terbit` AS `tahun_terbit`, `b`.`bahasa` AS `bahasa`, `b`.`jenis` AS `jenis`, `b`.`sinopsis` AS `sinopsis`, `b`.`kata_kunci` AS `kata_kunci`, `b`.`gambar` AS `gambar`, `b`.`total_view` AS `total_view`, coalesce(avg(`r`.`rating`),0) AS `rating_rata_rata`, count(distinct `r`.`id_rating`) AS `jumlah_rating`, count(distinct `rb`.`id_riwayat`) AS `jumlah_dibaca` FROM ((`t_buku` `b` left join `t_rating_buku` `r` on(`b`.`id_t_buku` = `r`.`id_t_buku`)) left join `t_riwayat_baca` `rb` on(`b`.`id_t_buku` = `rb`.`id_t_buku`)) GROUP BY `b`.`id_t_buku` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_peminjaman`
--
DROP TABLE IF EXISTS `v_peminjaman`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_peminjaman`  AS SELECT `a`.`id_t_peminjaman` AS `id_t_peminjaman`, `a`.`no_peminjaman` AS `no_peminjaman`, ifnull(`c`.`nama`,'Admin') AS `staff`, `a`.`tgl_pinjam` AS `tgl_pinjam`, `a`.`tgl_kembali` AS `tgl_kembali`, `b`.`no_anggota` AS `no_anggota`, `b`.`nama` AS `anggota`, `d`.`username` AS `username`, `d`.`id_t_account` AS `ID`, `b`.`id_t_anggota` AS `id_t_anggota`, sum(`e`.`qty`) AS `jum` FROM ((((`t_peminjaman` `a` join `t_anggota` `b` on(`a`.`id_t_anggota` = `b`.`id_t_anggota`)) left join `t_staff` `c` on(`a`.`id_t_staff` = `c`.`id_t_staff`)) join `t_account` `d` on(`b`.`id_t_account` = `d`.`id_t_account`)) left join `t_detil_pinjam` `e` on(`e`.`id_t_peminjaman` = `a`.`id_t_peminjaman`)) GROUP BY `a`.`id_t_peminjaman` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `p_role`
--
ALTER TABLE `p_role`
  ADD PRIMARY KEY (`id_p_role`);

--
-- Indeks untuk tabel `t_account`
--
ALTER TABLE `t_account`
  ADD PRIMARY KEY (`id_t_account`);

--
-- Indeks untuk tabel `t_anggota`
--
ALTER TABLE `t_anggota`
  ADD PRIMARY KEY (`id_t_anggota`);

--
-- Indeks untuk tabel `t_bookmark`
--
ALTER TABLE `t_bookmark`
  ADD PRIMARY KEY (`id_bookmark`),
  ADD KEY `id_t_buku` (`id_t_buku`),
  ADD KEY `id_t_anggota` (`id_t_anggota`);

--
-- Indeks untuk tabel `t_buku`
--
ALTER TABLE `t_buku`
  ADD PRIMARY KEY (`id_t_buku`);

--
-- Indeks untuk tabel `t_denda`
--
ALTER TABLE `t_denda`
  ADD PRIMARY KEY (`id_t_denda`),
  ADD KEY `id_t_peminjaman` (`id_t_peminjaman`);

--
-- Indeks untuk tabel `t_detil_pinjam`
--
ALTER TABLE `t_detil_pinjam`
  ADD PRIMARY KEY (`id_t_detil_pinjam`);

--
-- Indeks untuk tabel `t_email_verification`
--
ALTER TABLE `t_email_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_t_anggota` (`id_t_anggota`);

--
-- Indeks untuk tabel `t_kategori_buku`
--
ALTER TABLE `t_kategori_buku`
  ADD PRIMARY KEY (`id_t_kategori`);

--
-- Indeks untuk tabel `t_log_download`
--
ALTER TABLE `t_log_download`
  ADD PRIMARY KEY (`id_t_log_download`),
  ADD KEY `id_t_anggota` (`id_t_anggota`),
  ADD KEY `id_t_buku` (`id_t_buku`);

--
-- Indeks untuk tabel `t_peminjaman`
--
ALTER TABLE `t_peminjaman`
  ADD PRIMARY KEY (`id_t_peminjaman`);

--
-- Indeks untuk tabel `t_rating_balasan`
--
ALTER TABLE `t_rating_balasan`
  ADD PRIMARY KEY (`id_t_rating_balasan`),
  ADD KEY `id_rating` (`id_rating`);

--
-- Indeks untuk tabel `t_rating_balasan_like`
--
ALTER TABLE `t_rating_balasan_like`
  ADD PRIMARY KEY (`id_t_rating_balasan_like`),
  ADD KEY `id_t_rating_balasan` (`id_t_rating_balasan`);

--
-- Indeks untuk tabel `t_rating_buku`
--
ALTER TABLE `t_rating_buku`
  ADD PRIMARY KEY (`id_rating`),
  ADD KEY `id_t_buku` (`id_t_buku`),
  ADD KEY `id_t_anggota` (`id_t_anggota`);

--
-- Indeks untuk tabel `t_rating_like`
--
ALTER TABLE `t_rating_like`
  ADD PRIMARY KEY (`id_rating_like`),
  ADD KEY `id_rating` (`id_rating`);

--
-- Indeks untuk tabel `t_riwayat_baca`
--
ALTER TABLE `t_riwayat_baca`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_t_buku` (`id_t_buku`),
  ADD KEY `id_t_anggota` (`id_t_anggota`);

--
-- Indeks untuk tabel `t_staff`
--
ALTER TABLE `t_staff`
  ADD PRIMARY KEY (`id_t_staff`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `p_role`
--
ALTER TABLE `p_role`
  MODIFY `id_p_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `t_account`
--
ALTER TABLE `t_account`
  MODIFY `id_t_account` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `t_anggota`
--
ALTER TABLE `t_anggota`
  MODIFY `id_t_anggota` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `t_bookmark`
--
ALTER TABLE `t_bookmark`
  MODIFY `id_bookmark` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `t_buku`
--
ALTER TABLE `t_buku`
  MODIFY `id_t_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `t_denda`
--
ALTER TABLE `t_denda`
  MODIFY `id_t_denda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `t_detil_pinjam`
--
ALTER TABLE `t_detil_pinjam`
  MODIFY `id_t_detil_pinjam` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT untuk tabel `t_email_verification`
--
ALTER TABLE `t_email_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `t_kategori_buku`
--
ALTER TABLE `t_kategori_buku`
  MODIFY `id_t_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `t_log_download`
--
ALTER TABLE `t_log_download`
  MODIFY `id_t_log_download` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `t_peminjaman`
--
ALTER TABLE `t_peminjaman`
  MODIFY `id_t_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT untuk tabel `t_rating_balasan`
--
ALTER TABLE `t_rating_balasan`
  MODIFY `id_t_rating_balasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `t_rating_balasan_like`
--
ALTER TABLE `t_rating_balasan_like`
  MODIFY `id_t_rating_balasan_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `t_rating_buku`
--
ALTER TABLE `t_rating_buku`
  MODIFY `id_rating` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `t_rating_like`
--
ALTER TABLE `t_rating_like`
  MODIFY `id_rating_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `t_riwayat_baca`
--
ALTER TABLE `t_riwayat_baca`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT untuk tabel `t_staff`
--
ALTER TABLE `t_staff`
  MODIFY `id_t_staff` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `t_bookmark`
--
ALTER TABLE `t_bookmark`
  ADD CONSTRAINT `t_bookmark_ibfk_1` FOREIGN KEY (`id_t_buku`) REFERENCES `t_buku` (`id_t_buku`),
  ADD CONSTRAINT `t_bookmark_ibfk_2` FOREIGN KEY (`id_t_anggota`) REFERENCES `t_anggota` (`id_t_anggota`);

--
-- Ketidakleluasaan untuk tabel `t_denda`
--
ALTER TABLE `t_denda`
  ADD CONSTRAINT `t_denda_ibfk_1` FOREIGN KEY (`id_t_peminjaman`) REFERENCES `t_peminjaman` (`id_t_peminjaman`);

--
-- Ketidakleluasaan untuk tabel `t_email_verification`
--
ALTER TABLE `t_email_verification`
  ADD CONSTRAINT `t_email_verification_ibfk_1` FOREIGN KEY (`id_t_anggota`) REFERENCES `t_anggota` (`id_t_anggota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `t_log_download`
--
ALTER TABLE `t_log_download`
  ADD CONSTRAINT `t_log_download_ibfk_1` FOREIGN KEY (`id_t_anggota`) REFERENCES `t_anggota` (`id_t_anggota`),
  ADD CONSTRAINT `t_log_download_ibfk_2` FOREIGN KEY (`id_t_buku`) REFERENCES `t_buku` (`id_t_buku`);

--
-- Ketidakleluasaan untuk tabel `t_rating_balasan`
--
ALTER TABLE `t_rating_balasan`
  ADD CONSTRAINT `t_rating_balasan_ibfk_1` FOREIGN KEY (`id_rating`) REFERENCES `t_rating_buku` (`id_rating`);

--
-- Ketidakleluasaan untuk tabel `t_rating_balasan_like`
--
ALTER TABLE `t_rating_balasan_like`
  ADD CONSTRAINT `t_rating_balasan_like_ibfk_1` FOREIGN KEY (`id_t_rating_balasan`) REFERENCES `t_rating_balasan` (`id_t_rating_balasan`);

--
-- Ketidakleluasaan untuk tabel `t_rating_buku`
--
ALTER TABLE `t_rating_buku`
  ADD CONSTRAINT `t_rating_buku_ibfk_1` FOREIGN KEY (`id_t_buku`) REFERENCES `t_buku` (`id_t_buku`),
  ADD CONSTRAINT `t_rating_buku_ibfk_2` FOREIGN KEY (`id_t_anggota`) REFERENCES `t_anggota` (`id_t_anggota`);

--
-- Ketidakleluasaan untuk tabel `t_rating_like`
--
ALTER TABLE `t_rating_like`
  ADD CONSTRAINT `t_rating_like_ibfk_1` FOREIGN KEY (`id_rating`) REFERENCES `t_rating_buku` (`id_rating`);

--
-- Ketidakleluasaan untuk tabel `t_riwayat_baca`
--
ALTER TABLE `t_riwayat_baca`
  ADD CONSTRAINT `t_riwayat_baca_ibfk_1` FOREIGN KEY (`id_t_buku`) REFERENCES `t_buku` (`id_t_buku`),
  ADD CONSTRAINT `t_riwayat_baca_ibfk_2` FOREIGN KEY (`id_t_anggota`) REFERENCES `t_anggota` (`id_t_anggota`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
