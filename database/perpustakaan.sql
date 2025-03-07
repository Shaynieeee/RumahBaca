-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Mar 2025 pada 15.55
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
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_account`
--

INSERT INTO `t_account` (`id_t_account`, `id_p_role`, `id_t_anggota`, `username`, `password`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
(17, 2, NULL, 'gil', '$2y$10$VNiOmhOk2wUfXgdkUfG8h.YgLaJywZMo/Jtv31H3bE5Vyfakv3Ks6', '2025-01-31 00:00:00', 'min', '2025-02-06 11:27:49', 'gil'),
(16, 1, NULL, 'min', '$2y$10$oLwYqi7pmJyYPFdyJYC18uV4VS2dFjQ9O4uyFVGAcgVHS5LG/FYJW', '2025-01-30 00:00:00', 'sys', NULL, NULL),
(20, 3, 16, 'Tasya', '$2y$10$7/yFR1MdgnIE.YO25twVl.35EXTz16ke91ARzV3eJHYDyPinv.oAi', '2025-02-10 00:00:00', 'min', NULL, NULL),
(19, 3, 15, 'Dayvin', '$2y$10$aqkXG9URXODmbJ/TlDZNle1sFmKC1jjzPEVlpzol.XGjlKxUz972.', '2025-02-04 00:00:00', 'min', NULL, NULL),
(23, 3, 19, 'Mus', '$2y$10$rf04SGgdbyu8mKYND5DcOOrVdvFVfvUUIjv..gbH3dJf3hKM1T1uS', '2025-02-24 08:50:24', 'Mus', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_anggota`
--

CREATE TABLE `t_anggota` (
  `id_t_anggota` int(11) NOT NULL,
  `id_t_account` int(11) NOT NULL,
  `no_anggota` varchar(11) NOT NULL,
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
  `create_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_anggota`
--

INSERT INTO `t_anggota` (`id_t_anggota`, `id_t_account`, `no_anggota`, `nama`, `tgl_daftar`, `tgl_lahir`, `jenis_kelamin`, `no_telp`, `alamat`, `foto_profil`, `bio`, `keterangan`, `status`, `update_date`, `update_by`, `create_by`, `create_date`) VALUES
(15, 19, 'AGT20250204', 'Dayvin Gavriel', '2025-02-04', '2025-01-29', 'Laki-laki', '081289301928', 'surabaya', NULL, NULL, '', 'Aktif', NULL, NULL, 'min', '2025-02-04'),
(16, 0, 'AGT20250210', 'Natasya', '2025-02-10', '2004-02-17', 'Perempuan', '0814839204', 'Sidoarjo', NULL, NULL, '', 'Aktif', NULL, NULL, 'min', '2025-02-10'),
(18, 0, 'AGT57852459', 'Jeniffer', '2025-02-10', '2004-01-18', 'Perempuan', '081289301928', 'Candi', NULL, NULL, '', 'Aktif', NULL, NULL, 'min', '2025-02-10'),
(19, 0, 'AGT20250224', 'Muslihin', '2025-02-24', '2001-11-10', 'Laki-laki', '0814892837', 'Gresik', NULL, NULL, NULL, 'Aktif', NULL, NULL, 'Mus', '2025-02-24');

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
  `kata_kunci` varchar(255) DEFAULT NULL,
  `preview_content` text DEFAULT NULL,
  `gambar` longtext DEFAULT NULL,
  `format_buku` varchar(10) DEFAULT NULL,
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

INSERT INTO `t_buku` (`id_t_buku`, `nama_buku`, `isbn`, `jenis`, `penulis`, `penerbit`, `bahasa`, `tahun_terbit`, `harga`, `kode_rak`, `stok`, `sinopsis`, `kata_kunci`, `preview_content`, `gambar`, `format_buku`, `file_buku`, `total_view`, `create_date`, `create_by`, `update_date`, `update_by`, `total_halaman`, `batas_baca_guest`) VALUES
(1, 'Mudah Membuat Portal Berita Online dengan PHP dan MySQL', NULL, 'Komputer', 'Wahana Komputer', 'Andi', NULL, '2012', 71500, 'R1', 1, 'Buku PAS: Mudah Membuat Portal Berita Online dengan PHP dan MySQL ini menjelaskan tentang pembuatan portal berita online menggunakan PHP dan MySQL. Buku ini ditujukan bagi Anda yang tertarik dalam bidang pemrograman website, khususnya PHP', NULL, NULL, 'Mudah-Membuat-Portal-Berita-Online-Dengan-PHP-MySQL.jpg', NULL, NULL, 0, '2016-10-30', 'adm', '2016-10-18 08:10:28', NULL, 0, 5),
(2, '100 Quotes Simple Thinking about Blood Type', NULL, 'Pengembangan Diri', 'Park Dong Sun', 'Penerbit Haru', NULL, '2016', 65000, 'R2', 0, 'Buku ilustrasi ini berisi 100 kutipan sifat golongan darah A, B, AB, dan O. Dilengkapi juga dengan komik yang belum pernah dipublikasikan di buku sebelumnya.', NULL, NULL, '100 Quotes Simple Thinking about Blood Type.jpg', NULL, NULL, 0, '2016-10-30', 'adm', NULL, NULL, 0, 5),
(3, 'Hujan', NULL, 'Novel', 'Tere Liye', 'Gramedia Pustaka Utama', NULL, '2016', 57800, 'R3', 2, NULL, NULL, NULL, 'hujan.jpg', NULL, NULL, 0, '2016-10-30', 'adm', NULL, NULL, 0, 5),
(4, 'Cheeky Romance', NULL, 'Novel', 'Kim Eun Jeong', 'Penerbit Haru', NULL, '2012', 65000, 'R3', 0, 'Wanita yang  tingkahnya tidak terduga, “si ibu hamil nasional”, vs laki-laki yang selalu dianggap sempurna,“si dokter nasional”.', NULL, NULL, 'Cheeky Romance.jpg', NULL, NULL, 0, '2016-10-30', 'adm', NULL, NULL, 0, 5),
(5, 'The Hidden Prince', NULL, 'Novel', 'Jjea Mayang', 'Sinar Kejora', NULL, '2015', 50000, 'R3', 1, 'Kim Jong Woon, seorang pencuri yang wajah tampannya terekam dan tersebar melalui kamera pengawas dan menjadi daftar pencarian polisi alias buronan, memutuskan untuk menyamar menjadi seorang pelayan wanita bernama Kim Jong Rin di sebuah rumah mewah', NULL, NULL, 'the hidden prince.jpg', NULL, NULL, 0, '2016-10-30', 'adm', NULL, NULL, 0, 5),
(6, 'Designer`S Revenge,The', NULL, 'Komik', 'Miyuki Yorita', 'M&C', NULL, '2014', 16000, 'R04', 4, '-', NULL, NULL, 'Designer`S Revenge,The.jpg', NULL, NULL, 0, '2016-11-01', 'adm', NULL, NULL, 0, 5),
(7, 'Corrupt', '0101010101010', 'Komik', 'desi', 'ntah', 'Indonesia', '2015', 150, '5', 18, '0', 'ku', NULL, '1739779378_Pink Valentine\'s Day Party Poster.png', '0', '1739779378_Corrupt (Devils Night 1).pdf', 42, '2025-02-17', '1', '2025-03-06 12:55:02', 'min', 384, 15);

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
(76, 99, 3, NULL, 'Baik', 0, 1, '-', '2025-03-01 00:00:00', 'min', '2025-02-28', 'min');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_peminjaman`
--

CREATE TABLE `t_peminjaman` (
  `id_t_peminjaman` int(11) NOT NULL,
  `no_peminjaman` varchar(10) NOT NULL,
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
(62, 'PJM2025028', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(63, 'PJM2025024', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(64, 'PJM2025029', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(65, 'PJM2025029', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(66, 'PJM2025024', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(67, 'PJM2025021', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(68, 'PJM2025025', 2, 15, '2025-02-07', '2025-02-14', 'Belum Kembali', NULL, '2025-02-07', 'gil', NULL, NULL),
(69, 'PJM2025025', NULL, 15, '2025-02-10', '2025-02-17', 'Belum Kembali', NULL, '2025-02-10', 'min', NULL, NULL),
(70, 'PJM2025026', NULL, 15, '2025-02-10', '2025-02-17', 'Belum Kembali', NULL, '2025-02-10', 'min', NULL, NULL),
(71, 'PJM2025026', NULL, 15, '2025-02-10', '2025-02-13', 'Belum Kembali', NULL, '2025-02-10', 'min', NULL, NULL),
(72, '2025021000', NULL, 18, '2025-02-10', '2025-02-17', 'Belum Kembali', NULL, '2025-02-10', 'min', NULL, NULL),
(73, '2025021000', NULL, 16, '2025-02-10', '2025-02-17', 'Belum Kembali', NULL, '2025-02-10', 'min', NULL, NULL),
(74, '2025021000', NULL, 16, '2025-02-10', '2025-02-11', 'Sudah Kembali', NULL, '2025-02-10', 'min', NULL, NULL),
(77, 'PJM2025021', NULL, 18, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(78, 'PJM2025021', NULL, 16, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(79, 'PJM2025021', 16, 16, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(80, 'PJM2025021', 16, 16, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(81, 'PJM2025021', 16, 16, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(82, 'PJ20250201', 16, 15, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(83, 'PA20250202', 16, 18, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(84, 'PA20250202', NULL, 18, '2025-02-11', '2025-02-18', 'Belum Kembali', NULL, '2025-02-11', 'min', NULL, NULL),
(85, 'PJM2025027', NULL, 0, '2025-02-25', '2025-03-04', 'Belum Kembali', NULL, '2025-02-25', 'min', NULL, NULL),
(86, 'PA20250202', NULL, 0, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(87, 'PA20250202', NULL, 0, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(88, 'PA20250202', NULL, 0, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(89, 'PA20250202', NULL, 0, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(90, 'PA20250202', NULL, 0, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(91, 'PA20250202', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(92, 'PA20250202', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(93, 'PA20250203', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(94, 'PA20250203', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(95, 'PA20250203', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(96, 'PA20250203', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(97, 'PJM2025022', NULL, 19, '2025-02-28', '2025-03-07', 'Dipinjam', NULL, '2025-02-28', 'min', NULL, NULL),
(98, 'PJM2025022', NULL, 19, '2025-02-28', '2025-02-28', 'Sudah Kembali', NULL, '2025-02-28', 'min', NULL, NULL),
(99, 'PJM2025022', NULL, 16, '2025-02-28', NULL, 'Belum Kembali', NULL, '2025-02-28', 'min', NULL, NULL);

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

--
-- Dumping data untuk tabel `t_rating_balasan`
--

INSERT INTO `t_rating_balasan` (`id_t_rating_balasan`, `id_rating`, `balasan`, `create_by`, `create_date`, `is_admin`) VALUES
(1, 7, 'nfdndnd', 'min', '2025-03-05 05:32:46', 1);

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
(4, 6, 16, 3, 'ok', '2025-02-19 16:09:38'),
(5, 1, 16, 1, 'no', '2025-02-20 09:15:07'),
(6, 7, 19, 3, 'SIP', '2025-02-26 09:16:41'),
(7, 2, 19, 5, 'mantapp', '2025-02-26 10:50:22'),
(8, 7, 16, 5, 'WAW', '2025-03-05 14:23:59');

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
(1, 6, 'min', 'like'),
(2, 5, 'min', 'dislike'),
(3, 7, 'min', 'dislike'),
(4, 4, 'min', 'like'),
(7, 4, 'Tasya', 'like'),
(8, 8, 'mus', 'like'),
(12, 4, 'mus', 'like'),
(14, 6, 'mus', 'like'),
(16, 6, 'Tasya', 'like'),
(17, 8, 'Tasya', 'like');

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
  `durasi` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_riwayat_baca`
--

INSERT INTO `t_riwayat_baca` (`id_riwayat`, `id_t_buku`, `id_t_anggota`, `tanggal_baca`, `waktu_mulai`, `waktu_selesai`, `durasi`) VALUES
(3, 7, 16, '2025-02-20 00:00:00', '09:36:23', '09:36:45', '00:00:22'),
(6, 7, 16, '2025-02-20 00:00:00', '13:23:07', '13:33:06', '00:09:59'),
(7, 7, 19, '2025-02-26 00:00:00', '09:16:49', '09:17:36', '00:00:47'),
(8, 7, 16, '2025-03-04 00:00:00', '20:52:11', '20:52:35', '00:00:24'),
(9, 7, 16, '2025-03-04 00:00:00', '20:52:41', '20:52:48', '00:00:07'),
(10, 7, 16, '2025-03-04 00:00:00', '20:53:08', '20:53:18', '00:00:10'),
(11, 7, 19, '2025-03-05 00:00:00', '20:18:25', '20:18:36', '00:00:11'),
(12, 7, 16, '2025-03-06 00:00:00', '08:15:58', '08:16:22', '00:00:24'),
(13, 7, 16, '2025-03-06 00:00:00', '08:59:44', '09:01:09', '00:01:25'),
(14, 7, 16, '2025-03-06 00:00:00', '09:02:12', '09:02:43', '00:00:31'),
(15, 7, 16, '2025-03-06 00:00:00', '13:13:51', '13:14:16', '00:00:25'),
(16, 7, 16, '2025-03-06 00:00:00', '13:19:24', '13:21:59', '00:02:35'),
(17, 7, 16, '2025-03-06 00:00:00', '13:21:59', '13:22:11', '00:00:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_staff`
--

CREATE TABLE `t_staff` (
  `id_t_staff` int(11) NOT NULL,
  `id_t_account` int(11) NOT NULL,
  `nama` varchar(25) NOT NULL,
  `alamat` varchar(64) DEFAULT NULL,
  `status` varchar(10) NOT NULL COMMENT 'aktif/tidak aktif',
  `create_date` datetime NOT NULL,
  `create_by` varchar(3) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(3) DEFAULT NULL COMMENT 'Username'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `t_staff`
--

INSERT INTO `t_staff` (`id_t_staff`, `id_t_account`, `nama`, `alamat`, `status`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
(2, 17, 'Gilbert ala', 'Surabaya', 'Aktif', '2025-01-31 00:00:00', 'min', '2025-02-06 05:08:50', 'min');

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
`id_t_anggota` int(11)
,`no_anggota` varchar(11)
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
`id_t_buku` int(11)
,`nama_buku` varchar(128)
,`isbn` varchar(20)
,`penulis` varchar(64)
,`penerbit` varchar(64)
,`tahun_terbit` year(4)
,`bahasa` varchar(50)
,`jenis` varchar(30)
,`sinopsis` varchar(250)
,`kata_kunci` varchar(255)
,`gambar` longtext
,`total_view` int(11)
,`rating_rata_rata` decimal(14,4)
,`jumlah_rating` bigint(21)
,`jumlah_dibaca` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_peminjaman`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_peminjaman` (
`id_t_peminjaman` int(11)
,`no_peminjaman` varchar(10)
,`staff` varchar(25)
,`tgl_pinjam` date
,`tgl_kembali` date
,`no_anggota` varchar(11)
,`anggota` varchar(25)
,`username` varchar(50)
,`ID` int(11)
,`id_t_anggota` int(11)
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
-- Indeks untuk tabel `t_detil_pinjam`
--
ALTER TABLE `t_detil_pinjam`
  ADD PRIMARY KEY (`id_t_detil_pinjam`);

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
  MODIFY `id_t_account` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `t_anggota`
--
ALTER TABLE `t_anggota`
  MODIFY `id_t_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `t_bookmark`
--
ALTER TABLE `t_bookmark`
  MODIFY `id_bookmark` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `t_buku`
--
ALTER TABLE `t_buku`
  MODIFY `id_t_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `t_detil_pinjam`
--
ALTER TABLE `t_detil_pinjam`
  MODIFY `id_t_detil_pinjam` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT untuk tabel `t_peminjaman`
--
ALTER TABLE `t_peminjaman`
  MODIFY `id_t_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

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
  MODIFY `id_rating` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `t_rating_like`
--
ALTER TABLE `t_rating_like`
  MODIFY `id_rating_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `t_riwayat_baca`
--
ALTER TABLE `t_riwayat_baca`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `t_staff`
--
ALTER TABLE `t_staff`
  MODIFY `id_t_staff` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
