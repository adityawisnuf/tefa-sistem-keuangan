-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2024 at 08:20 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `data_sistem_keuangan`
--

-- --------------------------------------------------------

--
-- Table structure for table `amount`
--

CREATE TABLE `amount` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paymentAmount` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anggaran`
--

CREATE TABLE `anggaran` (
  `id` int(11) NOT NULL,
  `nama_anggaran` varchar(255) NOT NULL,
  `nominal` double NOT NULL DEFAULT 0,
  `nominal_diapprove` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_pengajuan` datetime NOT NULL,
  `target_terealisasikan` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=diajukan; 2=diapprove; 3=terealisasikan; 4=gagal terealisasikan;',
  `pengapprove` varchar(255) DEFAULT NULL,
  `pengapprove_jabatan` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aset`
--

CREATE TABLE `aset` (
  `id` int(11) NOT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` double(10,2) NOT NULL DEFAULT 0.00,
  `kondisi` varchar(255) DEFAULT NULL,
  `penggunaan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` char(7) NOT NULL,
  `regency_id` char(4) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emailverif`
--

CREATE TABLE `emailverif` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kantin`
--

CREATE TABLE `kantin` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` double(8,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=aktif; 0=nonaktif;',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kantin_produk`
--

CREATE TABLE `kantin_produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `kantin_produk_kategori_id` bigint(20) UNSIGNED NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `foto_produk` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga_pokok` int(10) UNSIGNED NOT NULL,
  `harga_jual` int(10) UNSIGNED NOT NULL,
  `stok` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'tidak_aktif',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kantin_produk_kategori`
--

CREATE TABLE `kantin_produk_kategori` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kantin_transaksi`
--

CREATE TABLE `kantin_transaksi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','proses','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  `tanggal_pemesanan` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kantin_transaksi_detail`
--

CREATE TABLE `kantin_transaksi_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kantin_produk_id` bigint(20) UNSIGNED NOT NULL,
  `kantin_transaksi_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` bigint(20) NOT NULL,
  `sekolah_id` bigint(20) NOT NULL,
  `jurusan` varchar(255) DEFAULT NULL,
  `kelas` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laundry`
--

CREATE TABLE `laundry` (
  `id` int(11) NOT NULL,
  `berat` varchar(255) NOT NULL,
  `harga` double(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laundry_layanan`
--

CREATE TABLE `laundry_layanan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `nama_layanan` varchar(255) NOT NULL,
  `foto_layanan` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` int(10) UNSIGNED NOT NULL,
  `tipe` enum('satuan','kiloan') NOT NULL,
  `satuan` enum('pcs','kg') NOT NULL,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'tidak_aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laundry_transaksi`
--

CREATE TABLE `laundry_transaksi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','proses','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  `tanggal_pemesanan` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laundry_transaksi_detail`
--

CREATE TABLE `laundry_transaksi_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `laundry_layanan_id` bigint(20) UNSIGNED NOT NULL,
  `laundry_transaksi_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2017_05_02_140432_create_provinces_tables', 2),
(6, '2017_05_02_140444_create_regencies_tables', 2),
(7, '2017_05_02_142019_create_districts_tables', 2),
(8, '2017_05_02_143454_create_villages_tables', 2),
(9, '2019_05_11_000000_create_otps_table', 2),
(10, '2024_08_01_061242_update_kelas_add_deleted_at', 2),
(11, '2024_08_01_061507_update_pembayaran_add_deleted_at', 2),
(12, '2024_08_01_061557_update_pembayaran_kategori_add_deleted_at', 2),
(13, '2024_08_02_073207_update_users_add_role_comment', 2),
(14, '2024_08_03_073857_update_table_pendaftar', 2),
(15, '2024_08_03_102153_update_ppdb_delete_dokumen_pendaftar_id', 2),
(16, '2024_08_03_103035_update_siswa_add_village_id', 2),
(17, '2024_08_06_083835_create_siswa_wallet_riwayat_table', 2),
(18, '2024_08_06_084526_create_usaha_table', 2),
(19, '2024_08_06_084741_create_kantin_produk_kategori_table', 2),
(20, '2024_08_06_084810_create_kantin_produk_table', 2),
(21, '2024_08_06_091036_create_usaha_pengajuan_table', 2),
(22, '2024_08_06_091802_create_kantin_transaksi_table', 2),
(23, '2024_08_06_092802_create_kantin_transaksi_detail_table', 2),
(24, '2024_08_06_094623_create_laundry_layanan_table', 2),
(25, '2024_08_06_100521_create_laundry_transaksi_table', 2),
(26, '2024_08_06_100809_create_laundry_transaksi_detail_table', 2),
(27, '2024_08_06_225719_email', 2),
(28, '2024_08_07_122729_update_aset_add_nominal_and_type', 2),
(29, '2024_08_07_165040_delete_village_id', 2),
(30, '2024_08_07_195919_create_pembayaran_ppdb', 2),
(31, '2024_08_08_075934_add_constraint_village_id', 2),
(32, '2024_08_08_085432_add_constraint_ppdb_id', 2),
(33, '2024_08_08_100442_add_constraint_pembayaran_id', 2),
(34, '2024_08_08_100516_add_constraint_merchant_order_id', 2),
(35, '2024_08_11_190423_create_notifications_table', 2),
(36, '2024_08_14_080813_update_column_pembayaran', 2),
(37, '2024_08_14_082544_update_column_ppdb', 2),
(38, '2024_08_15_103931_add_column_pembayaran_duitku', 2),
(39, '2024_08_19_083436_create_amount_tables', 2),
(40, '2024_08_19_104409_add_column_hutang', 2),
(41, '2024_08_25_080626_create_pengumuman_table', 2),
(42, '2024_09_21_222646_update_pengeluaran_add_status', 2),
(43, '2024_09_30_132649_change_siswa_pembayaran', 2),
(44, '2024_10_21_083532_update_column_pembayaran_duitku', 3),
(45, '2024_08_27_070414_add_nominal_diapprove_column', 4),
(48, '2024_09_23_110825_add_logo_column_to_sekolah', 5),
(50, '2024_09_30_191336_add_fields_to_pembayaran_table', 6),
(52, '2024_10_11_132441_add_columns_to_pembayaran_siswa_cicilan_table', 7),
(53, '2024_10_11_133808_create_pembayaran_cicilan_table', 8),
(54, '2024_12_05_140729_update_siswa_village_id_length', 9);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orangtua`
--

CREATE TABLE `orangtua` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `validity` int(11) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `pembayaran_kategori_id` int(11) NOT NULL,
  `kelas_id` bigint(20) DEFAULT NULL,
  `siswa_id` bigint(20) DEFAULT NULL,
  `nominal` double DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=aktif; 0=nonaktif;',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `pembayaran_ke` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_cicilan`
--

CREATE TABLE `pembayaran_cicilan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pembayaran_siswa_cicilan_id` int(11) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `nominal_dibayar` decimal(15,2) NOT NULL,
  `status` enum('lunas','belum_lunas') NOT NULL DEFAULT 'belum_lunas',
  `transaction_response` text NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_duitku`
--

CREATE TABLE `pembayaran_duitku` (
  `merchant_order_id` varchar(255) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `transaction_response` longtext DEFAULT NULL,
  `data_user_response` longtext DEFAULT NULL,
  `callback_response` longtext DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT '01',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_kategori`
--

CREATE TABLE `pembayaran_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jenis_pembayaran` tinyint(4) NOT NULL COMMENT '1=bulanan; 2=tahunan;',
  `tanggal_pembayaran` varchar(255) NOT NULL COMMENT 'DD untuk bulanan; DD-MM untuk tahunan;',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=aktif; 0=nonaktif;',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_ppdb`
--

CREATE TABLE `pembayaran_ppdb` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ppdb_id` int(11) NOT NULL,
  `pembayaran_id` int(11) NOT NULL,
  `nominal` double(10,2) NOT NULL DEFAULT 0.00,
  `merchant_order_id` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL COMMENT '1=selesai; 0=belum selesai;',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_siswa`
--

CREATE TABLE `pembayaran_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` bigint(20) NOT NULL,
  `pembayaran_id` int(11) NOT NULL,
  `nominal` double NOT NULL DEFAULT 0,
  `merchant_order_id` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=selesai; 0=belum selesai;',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_siswa_cicilan`
--

CREATE TABLE `pembayaran_siswa_cicilan` (
  `id` int(11) NOT NULL,
  `pembayaran_siswa_id` int(11) NOT NULL,
  `nominal_cicilan` double NOT NULL DEFAULT 0,
  `merchant_order_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `jumlah_cicilan` int(11) DEFAULT NULL,
  `tanggal_cicilan` date DEFAULT NULL,
  `total_cicilan` decimal(15,2) DEFAULT NULL,
  `status` enum('ongoing','complete') NOT NULL DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftar`
--

CREATE TABLE `pendaftar` (
  `id` int(11) NOT NULL,
  `ppdb_id` int(11) NOT NULL,
  `nama_depan` varchar(255) NOT NULL,
  `nama_belakang` varchar(255) DEFAULT NULL,
  `jenis_kelamin` tinyint(4) NOT NULL COMMENT '1=laki-laki; 2=perempuan;',
  `nik` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `nisn` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(255) NOT NULL,
  `tgl_lahir` datetime NOT NULL,
  `alamat` text NOT NULL,
  `village_id` char(10) DEFAULT NULL,
  `nama_ayah` varchar(255) NOT NULL,
  `nama_ibu` varchar(255) NOT NULL,
  `tgl_lahir_ayah` datetime NOT NULL,
  `tgl_lahir_ibu` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftar_akademik`
--

CREATE TABLE `pendaftar_akademik` (
  `id` int(11) NOT NULL,
  `ppdb_id` int(11) NOT NULL,
  `sekolah_asal` varchar(255) DEFAULT NULL,
  `tahun_lulus` datetime DEFAULT NULL,
  `jurusan_tujuan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftar_dokumen`
--

CREATE TABLE `pendaftar_dokumen` (
  `id` int(11) NOT NULL,
  `ppdb_id` int(11) NOT NULL,
  `akte_kelahiran` text DEFAULT NULL,
  `kartu_keluarga` text DEFAULT NULL,
  `ijazah` text DEFAULT NULL,
  `raport` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `pengeluaran_kategori_id` int(11) NOT NULL,
  `keperluan` varchar(255) NOT NULL,
  `nominal` double NOT NULL DEFAULT 0,
  `diajukan_pada` datetime NOT NULL,
  `disetujui_pada` datetime DEFAULT NULL COMMENT 'NULL ketika belum disetujui',
  `status` enum('pending','declined','accepted') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran_kategori`
--

CREATE TABLE `pengeluaran_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=aktif; 0=nonaktif;',
  `tipe_utang` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `pesan_ditolak` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppdb`
--

CREATE TABLE `ppdb` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=mendaftar; 2=telah membayar; 3=telah terdaftar; 4=ditolak',
  `merchant_order_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provinces`
--

CREATE TABLE `provinces` (
  `id` char(2) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regencies`
--

CREATE TABLE `regencies` (
  `id` char(4) NOT NULL,
  `province_id` char(2) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sekolah`
--

CREATE TABLE `sekolah` (
  `id` bigint(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `nip_kepsek` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama_depan` varchar(255) NOT NULL,
  `nama_belakang` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `village_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tempat_lahir` varchar(255) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `kelas_id` bigint(20) NOT NULL,
  `orangtua_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa_wallet`
--

CREATE TABLE `siswa_wallet` (
  `id` int(11) NOT NULL,
  `siswa_id` bigint(20) NOT NULL,
  `nominal` double(11,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa_wallet_riwayat`
--

CREATE TABLE `siswa_wallet_riwayat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `siswa_wallet_id` int(11) NOT NULL,
  `merchant_order_id` varchar(255) DEFAULT NULL,
  `tipe_transaksi` enum('pemasukan','pengeluaran') NOT NULL,
  `nominal` double NOT NULL,
  `tanggal_riwayat` datetime NOT NULL DEFAULT '2024-10-16 15:36:48',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usaha`
--

CREATE TABLE `usaha` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama_usaha` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `no_telepon` varchar(255) NOT NULL,
  `no_rekening` varchar(255) NOT NULL,
  `saldo` double NOT NULL DEFAULT 0,
  `status_buka` enum('buka','tutup') NOT NULL DEFAULT 'tutup',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usaha_pengajuan`
--

CREATE TABLE `usaha_pengajuan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah_pengajuan` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `alasan_penolakan` varchar(255) DEFAULT NULL,
  `tanggal_pengajuan` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'OrangTua' COMMENT 'Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `villages`
--

CREATE TABLE `villages` (
  `id` char(10) NOT NULL,
  `district_id` char(7) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amount`
--
ALTER TABLE `amount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `anggaran`
--
ALTER TABLE `anggaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `aset`
--
ALTER TABLE `aset`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD KEY `districts_regency_id_foreign` (`regency_id`),
  ADD KEY `districts_id_index` (`id`);

--
-- Indexes for table `emailverif`
--
ALTER TABLE `emailverif`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emailverif_email_unique` (`email`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `kantin`
--
ALTER TABLE `kantin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kantin_produk`
--
ALTER TABLE `kantin_produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kantin_produk_usaha_id_foreign` (`usaha_id`),
  ADD KEY `kantin_produk_kantin_produk_kategori_id_foreign` (`kantin_produk_kategori_id`);

--
-- Indexes for table `kantin_produk_kategori`
--
ALTER TABLE `kantin_produk_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kantin_transaksi`
--
ALTER TABLE `kantin_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kantin_transaksi_siswa_id_foreign` (`siswa_id`),
  ADD KEY `kantin_transaksi_usaha_id_foreign` (`usaha_id`);

--
-- Indexes for table `kantin_transaksi_detail`
--
ALTER TABLE `kantin_transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kantin_transaksi_detail_kantin_produk_id_foreign` (`kantin_produk_id`),
  ADD KEY `kantin_transaksi_detail_kantin_transaksi_id_foreign` (`kantin_transaksi_id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `sekolah_id` (`sekolah_id`);

--
-- Indexes for table `laundry`
--
ALTER TABLE `laundry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laundry_layanan`
--
ALTER TABLE `laundry_layanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laundry_layanan_usaha_id_foreign` (`usaha_id`);

--
-- Indexes for table `laundry_transaksi`
--
ALTER TABLE `laundry_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laundry_transaksi_siswa_id_foreign` (`siswa_id`),
  ADD KEY `laundry_transaksi_usaha_id_foreign` (`usaha_id`);

--
-- Indexes for table `laundry_transaksi_detail`
--
ALTER TABLE `laundry_transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laundry_transaksi_detail_laundry_layanan_id_foreign` (`laundry_layanan_id`),
  ADD KEY `laundry_transaksi_detail_laundry_transaksi_id_foreign` (`laundry_transaksi_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `orangtua`
--
ALTER TABLE `orangtua`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otps_id_index` (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pembayaran_kategori_id` (`pembayaran_kategori_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `pembayaran_cicilan`
--
ALTER TABLE `pembayaran_cicilan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayaran_cicilan_pembayaran_siswa_cicilan_id_foreign` (`pembayaran_siswa_cicilan_id`);

--
-- Indexes for table `pembayaran_duitku`
--
ALTER TABLE `pembayaran_duitku`
  ADD PRIMARY KEY (`merchant_order_id`),
  ADD UNIQUE KEY `merchant_order_id` (`merchant_order_id`);

--
-- Indexes for table `pembayaran_kategori`
--
ALTER TABLE `pembayaran_kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `pembayaran_ppdb`
--
ALTER TABLE `pembayaran_ppdb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayaran_ppdb_ppdb_id_foreign` (`ppdb_id`),
  ADD KEY `pembayaran_ppdb_pembayaran_id_foreign` (`pembayaran_id`),
  ADD KEY `pembayaran_ppdb_merchant_order_id_foreign` (`merchant_order_id`);

--
-- Indexes for table `pembayaran_siswa`
--
ALTER TABLE `pembayaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `pembayaran_id` (`pembayaran_id`),
  ADD KEY `merchant_order_id` (`merchant_order_id`);

--
-- Indexes for table `pembayaran_siswa_cicilan`
--
ALTER TABLE `pembayaran_siswa_cicilan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `pembayaran_siswa_id` (`pembayaran_siswa_id`),
  ADD KEY `merchant_order_id` (`merchant_order_id`);

--
-- Indexes for table `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `ppdb_id` (`ppdb_id`);

--
-- Indexes for table `pendaftar_akademik`
--
ALTER TABLE `pendaftar_akademik`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `ppdb_id` (`ppdb_id`);

--
-- Indexes for table `pendaftar_dokumen`
--
ALTER TABLE `pendaftar_dokumen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `ppdb_id` (`ppdb_id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `pengeluaran_kategori_id` (`pengeluaran_kategori_id`);

--
-- Indexes for table `pengeluaran_kategori`
--
ALTER TABLE `pengeluaran_kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengumuman_user_id_foreign` (`user_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `ppdb`
--
ALTER TABLE `ppdb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `merchant_order_id` (`merchant_order_id`),
  ADD KEY `ppdb_user_id_foreign` (`user_id`);

--
-- Indexes for table `provinces`
--
ALTER TABLE `provinces`
  ADD KEY `provinces_id_index` (`id`);

--
-- Indexes for table `regencies`
--
ALTER TABLE `regencies`
  ADD KEY `regencies_province_id_foreign` (`province_id`),
  ADD KEY `regencies_id_index` (`id`);

--
-- Indexes for table `sekolah`
--
ALTER TABLE `sekolah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `orangtua_id` (`orangtua_id`);

--
-- Indexes for table `siswa_wallet`
--
ALTER TABLE `siswa_wallet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `siswa_wallet_riwayat`
--
ALTER TABLE `siswa_wallet_riwayat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_wallet_riwayat_siswa_wallet_id_foreign` (`siswa_wallet_id`),
  ADD KEY `siswa_wallet_riwayat_merchant_order_id_foreign` (`merchant_order_id`);

--
-- Indexes for table `usaha`
--
ALTER TABLE `usaha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usaha_user_id_foreign` (`user_id`);

--
-- Indexes for table `usaha_pengajuan`
--
ALTER TABLE `usaha_pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usaha_pengajuan_usaha_id_foreign` (`usaha_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `villages`
--
ALTER TABLE `villages`
  ADD KEY `villages_district_id_foreign` (`district_id`),
  ADD KEY `villages_id_index` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amount`
--
ALTER TABLE `amount`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anggaran`
--
ALTER TABLE `anggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aset`
--
ALTER TABLE `aset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emailverif`
--
ALTER TABLE `emailverif`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kantin`
--
ALTER TABLE `kantin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kantin_produk`
--
ALTER TABLE `kantin_produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kantin_produk_kategori`
--
ALTER TABLE `kantin_produk_kategori`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kantin_transaksi`
--
ALTER TABLE `kantin_transaksi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kantin_transaksi_detail`
--
ALTER TABLE `kantin_transaksi_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laundry`
--
ALTER TABLE `laundry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laundry_layanan`
--
ALTER TABLE `laundry_layanan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laundry_transaksi`
--
ALTER TABLE `laundry_transaksi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laundry_transaksi_detail`
--
ALTER TABLE `laundry_transaksi_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `orangtua`
--
ALTER TABLE `orangtua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pembayaran_cicilan`
--
ALTER TABLE `pembayaran_cicilan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran_kategori`
--
ALTER TABLE `pembayaran_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembayaran_ppdb`
--
ALTER TABLE `pembayaran_ppdb`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pembayaran_siswa`
--
ALTER TABLE `pembayaran_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran_siswa_cicilan`
--
ALTER TABLE `pembayaran_siswa_cicilan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pendaftar`
--
ALTER TABLE `pendaftar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pendaftar_akademik`
--
ALTER TABLE `pendaftar_akademik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pendaftar_dokumen`
--
ALTER TABLE `pendaftar_dokumen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `pengeluaran_kategori`
--
ALTER TABLE `pengeluaran_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppdb`
--
ALTER TABLE `ppdb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sekolah`
--
ALTER TABLE `sekolah`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `siswa_wallet`
--
ALTER TABLE `siswa_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa_wallet_riwayat`
--
ALTER TABLE `siswa_wallet_riwayat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usaha`
--
ALTER TABLE `usaha`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `usaha_pengajuan`
--
ALTER TABLE `usaha_pengajuan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_regency_id_foreign` FOREIGN KEY (`regency_id`) REFERENCES `regencies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `kantin_produk`
--
ALTER TABLE `kantin_produk`
  ADD CONSTRAINT `kantin_produk_kantin_produk_kategori_id_foreign` FOREIGN KEY (`kantin_produk_kategori_id`) REFERENCES `kantin_produk_kategori` (`id`),
  ADD CONSTRAINT `kantin_produk_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`);

--
-- Constraints for table `kantin_transaksi`
--
ALTER TABLE `kantin_transaksi`
  ADD CONSTRAINT `kantin_transaksi_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `kantin_transaksi_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`);

--
-- Constraints for table `kantin_transaksi_detail`
--
ALTER TABLE `kantin_transaksi_detail`
  ADD CONSTRAINT `kantin_transaksi_detail_kantin_produk_id_foreign` FOREIGN KEY (`kantin_produk_id`) REFERENCES `kantin_produk` (`id`),
  ADD CONSTRAINT `kantin_transaksi_detail_kantin_transaksi_id_foreign` FOREIGN KEY (`kantin_transaksi_id`) REFERENCES `kantin_transaksi` (`id`);

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`);

--
-- Constraints for table `laundry_layanan`
--
ALTER TABLE `laundry_layanan`
  ADD CONSTRAINT `laundry_layanan_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`);

--
-- Constraints for table `laundry_transaksi`
--
ALTER TABLE `laundry_transaksi`
  ADD CONSTRAINT `laundry_transaksi_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `laundry_transaksi_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`);

--
-- Constraints for table `laundry_transaksi_detail`
--
ALTER TABLE `laundry_transaksi_detail`
  ADD CONSTRAINT `laundry_transaksi_detail_laundry_layanan_id_foreign` FOREIGN KEY (`laundry_layanan_id`) REFERENCES `laundry_layanan` (`id`),
  ADD CONSTRAINT `laundry_transaksi_detail_laundry_transaksi_id_foreign` FOREIGN KEY (`laundry_transaksi_id`) REFERENCES `laundry_transaksi` (`id`);

--
-- Constraints for table `orangtua`
--
ALTER TABLE `orangtua`
  ADD CONSTRAINT `orangtua_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`pembayaran_kategori_id`) REFERENCES `pembayaran_kategori` (`id`),
  ADD CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `pembayaran_cicilan`
--
ALTER TABLE `pembayaran_cicilan`
  ADD CONSTRAINT `pembayaran_cicilan_pembayaran_siswa_cicilan_id_foreign` FOREIGN KEY (`pembayaran_siswa_cicilan_id`) REFERENCES `pembayaran_siswa_cicilan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran_ppdb`
--
ALTER TABLE `pembayaran_ppdb`
  ADD CONSTRAINT `pembayaran_ppdb_merchant_order_id_foreign` FOREIGN KEY (`merchant_order_id`) REFERENCES `pembayaran_duitku` (`merchant_order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ppdb_pembayaran_id_foreign` FOREIGN KEY (`pembayaran_id`) REFERENCES `pembayaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ppdb_ppdb_id_foreign` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pembayaran_siswa`
--
ALTER TABLE `pembayaran_siswa`
  ADD CONSTRAINT `pembayaran_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `pembayaran_siswa_ibfk_2` FOREIGN KEY (`pembayaran_id`) REFERENCES `pembayaran` (`id`),
  ADD CONSTRAINT `pembayaran_siswa_ibfk_3` FOREIGN KEY (`merchant_order_id`) REFERENCES `pembayaran_duitku` (`merchant_order_id`);

--
-- Constraints for table `pembayaran_siswa_cicilan`
--
ALTER TABLE `pembayaran_siswa_cicilan`
  ADD CONSTRAINT `pembayaran_siswa_cicilan_ibfk_1` FOREIGN KEY (`pembayaran_siswa_id`) REFERENCES `pembayaran_siswa` (`id`),
  ADD CONSTRAINT `pembayaran_siswa_cicilan_ibfk_2` FOREIGN KEY (`merchant_order_id`) REFERENCES `pembayaran_duitku` (`merchant_order_id`);

--
-- Constraints for table `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD CONSTRAINT `pendaftar_ibfk_1` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`),
  ADD CONSTRAINT `pendaftar_ibfk_2` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`);

--
-- Constraints for table `pendaftar_akademik`
--
ALTER TABLE `pendaftar_akademik`
  ADD CONSTRAINT `pendaftar_akademik_ibfk_1` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`),
  ADD CONSTRAINT `pendaftar_akademik_ibfk_2` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`);

--
-- Constraints for table `pendaftar_dokumen`
--
ALTER TABLE `pendaftar_dokumen`
  ADD CONSTRAINT `pendaftar_dokumen_ibfk_1` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`),
  ADD CONSTRAINT `pendaftar_dokumen_ibfk_2` FOREIGN KEY (`ppdb_id`) REFERENCES `ppdb` (`id`);

--
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`pengeluaran_kategori_id`) REFERENCES `pengeluaran_kategori` (`id`);

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ppdb`
--
ALTER TABLE `ppdb`
  ADD CONSTRAINT `ppdb_ibfk_1` FOREIGN KEY (`merchant_order_id`) REFERENCES `pembayaran_duitku` (`merchant_order_id`),
  ADD CONSTRAINT `ppdb_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `regencies`
--
ALTER TABLE `regencies`
  ADD CONSTRAINT `regencies_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `siswa_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `siswa_ibfk_4` FOREIGN KEY (`orangtua_id`) REFERENCES `orangtua` (`id`);

--
-- Constraints for table `siswa_wallet_riwayat`
--
ALTER TABLE `siswa_wallet_riwayat`
  ADD CONSTRAINT `siswa_wallet_riwayat_merchant_order_id_foreign` FOREIGN KEY (`merchant_order_id`) REFERENCES `pembayaran_duitku` (`merchant_order_id`),
  ADD CONSTRAINT `siswa_wallet_riwayat_siswa_wallet_id_foreign` FOREIGN KEY (`siswa_wallet_id`) REFERENCES `siswa_wallet` (`id`);

--
-- Constraints for table `usaha`
--
ALTER TABLE `usaha`
  ADD CONSTRAINT `usaha_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `usaha_pengajuan`
--
ALTER TABLE `usaha_pengajuan`
  ADD CONSTRAINT `usaha_pengajuan_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`);

--
-- Constraints for table `villages`
--
ALTER TABLE `villages`
  ADD CONSTRAINT `villages_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
