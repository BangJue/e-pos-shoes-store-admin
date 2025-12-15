-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251201.40f7317dad
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 09, 2025 at 04:03 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `epos`
--

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`id`, `username`, `password`, `nama_lengkap`) VALUES
(1, 'admin', 'admin123', 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `id_transaksi` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `qty` int NOT NULL,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `qty`, `subtotal`) VALUES
(1, 1, 2, 1, 1450000),
(2, 2, 3, 3, 4500000),
(3, 3, 2, 4, 5800000),
(4, 4, 3, 1, 1500000),
(5, 5, 4, 2, 3000000),
(6, 6, 2, 1, 1450000);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT 'Sepatu',
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `kategori`, `harga`, `stok`, `gambar`, `created_at`) VALUES
(2, 'Adidas Samba', 'Adidas', 1450000, 20, 'adidas samba.png', '2025-12-04 12:36:58'),
(3, 'Puma Speedcat', 'Puma', 1500000, 5, 'speedcatt.png', '2025-12-04 12:43:56'),
(4, 'Air Jordan 1 High', 'Nike', 1500000, 11, 'air jordan 11.png', '2025-12-08 13:01:02'),
(5, 'Vanz OLD SKOOL', 'Vanz', 750000, 120, 'Vanz OLD SKOOL.png', '2025-12-08 13:08:06'),
(6, 'Skecher Air Ventura', 'Skecher', 1450000, 0, 'skechser air ventura.png', '2025-12-08 15:03:45'),
(8, 'NB 530', 'New Balance', 1699000, 35, 'nb_530-removebg-preview.png', '2025-12-09 07:00:47');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `tanggal` date NOT NULL,
  `total_bayar` int NOT NULL,
  `kasir` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal`, `total_bayar`, `kasir`) VALUES
(1, '2025-12-06', 1450000, 'admin'),
(2, '2025-12-08', 4500000, 'admin'),
(3, '2025-12-08', 5800000, 'admin'),
(4, '2025-12-08', 1500000, 'admin'),
(5, '2025-12-08', 3000000, 'admin'),
(6, '2025-12-09', 1450000, 'admin');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_transaksi_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `view_transaksi_lengkap` (
`id_transaksi` int
,`tanggal` date
,`nama_produk` varchar(100)
,`gambar` varchar(255)
,`qty` int
,`subtotal` int
,`total_bayar` int
,`kasir` varchar(50)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- --------------------------------------------------------

--
-- Structure for view `view_transaksi_lengkap`
--
DROP TABLE IF EXISTS `view_transaksi_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_transaksi_lengkap`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`tanggal` AS `tanggal`, `p`.`nama_produk` AS `nama_produk`, `p`.`gambar` AS `gambar`, `d`.`qty` AS `qty`, `d`.`subtotal` AS `subtotal`, `t`.`total_bayar` AS `total_bayar`, `t`.`kasir` AS `kasir` FROM ((`detail_transaksi` `d` join `transaksi` `t` on((`d`.`id_transaksi` = `t`.`id_transaksi`))) join `produk` `p` on((`d`.`id_produk` = `p`.`id_produk`))) ORDER BY `t`.`id_transaksi` DESC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
