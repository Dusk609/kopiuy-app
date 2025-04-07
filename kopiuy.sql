-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 07, 2025 at 05:26 AM
-- Server version: 8.0.41
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kopiuy`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `jalan` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kota` varchar(50) NOT NULL,
  `provinsi` varchar(50) NOT NULL,
  `negara` varchar(50) NOT NULL,
  `pos_kode` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `name`, `number`, `email`, `jalan`, `alamat`, `kota`, `provinsi`, `negara`, `pos_kode`, `is_default`, `created_at`) VALUES
(1, 1, 'Dusk', '212', 'test@gmail.com', 'sasa', 'sasas', 'sasa', 'sasa', 'Indonesia', '12121', 1, '2025-04-02 13:27:01'),
(2, 1, 'Dusk', '32323', 'tes@gmail.com', 'dsdsd', 'dsdsdsd', 'dsdsd', 'dsds', 'Indonesia', '32323', 0, '2025-04-02 14:15:51'),
(3, 2, 'Dusk', '3131', 'asd@gmail.com', 'sasa', 'sasa', 'ssasa', 'sasa', 'Indonesia', '13131', 1, '2025-04-02 15:37:33'),
(4, 4, '', '', '', '', '', '', '', 'Indonesia', '', 0, '2025-04-02 20:21:15'),
(5, 4, 'Iop', '53353535', 'iop@gmail.com', 'Bulak', 'Ashiap', 'Nice', 'Jawa', 'Indonesia', '21313', 1, '2025-04-02 22:52:08'),
(6, 5, 'Ishak', '089678059678', 'ishak@gmail.com', 'Waru', 'Griyo Mapan sentosa DC 18', 'Sidoarjo', 'Jawa Timur', 'Indonesia', '61256', 1, '2025-04-02 22:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Utama', 'admin@kopiuy.com', 'admin', NULL, '2025-04-02 18:41:39', '2025-04-02 20:17:29'),
(2, 'staff1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Staff Gudang', 'staff1@kopiuy.com', 'admin', NULL, '2025-04-02 18:44:36', '2025-04-02 18:44:36'),
(3, 'cs1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Customer Service', 'cs@kopiuy.com', 'admin', NULL, '2025-04-02 18:44:36', '2025-04-02 18:44:36'),
(4, 'ishak', '$2y$10$wfWeiA/OX/F7jh.j0Nxd3.tzYsdmv68qFhSUkYMyMBTpsHKGw1Nbu', 'ishak', 'ishak@gmail.com', 'admin', NULL, '2025-04-02 18:48:12', '2025-04-02 20:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(8, 1, 4, 2, '2025-04-02 15:05:46'),
(21, 3, 2, 1, '2025-04-02 16:16:22'),
(22, 2, 6, 1, '2025-04-02 16:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `address_id` int NOT NULL,
  `item_count` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `user_id`, `address_id`, `item_count`, `total_price`, `payment_method`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 42000.00, 'COD', 'pending', NULL, '2025-04-02 13:56:10', '2025-04-02 16:11:53'),
(2, 1, 1, 4, 159000.00, 'COD', 'completed', NULL, '2025-04-02 14:04:22', '2025-04-07 04:20:40'),
(3, 1, 1, 1, 42000.00, 'COD', 'pending', NULL, '2025-04-02 14:05:13', '2025-04-02 16:11:53'),
(4, 2, 3, 2, 86000.00, 'Transfer Bank', 'processing', '', '2025-04-02 15:44:55', '2025-04-02 21:35:47'),
(5, 2, 3, 1, 38000.00, 'COD', 'pending', NULL, '2025-04-02 15:46:47', '2025-04-02 16:11:53'),
(6, 2, 3, 1, 44000.00, 'COD', 'processing', NULL, '2025-04-02 15:52:09', '2025-04-02 21:39:34'),
(7, 2, 3, 1, 42000.00, 'COD', 'processing', NULL, '2025-04-02 15:52:24', '2025-04-02 21:40:19'),
(8, 2, 3, 2, 76000.00, 'COD', 'completed', 'mantap', '2025-04-02 15:52:57', '2025-04-07 04:21:18'),
(9, 2, 3, 2, 88000.00, 'COD', 'completed', '', '2025-04-02 15:59:54', '2025-04-02 21:35:41'),
(10, 2, 3, 1, 42000.00, 'Kasir', 'cancelled', NULL, '2025-04-02 16:00:56', '2025-04-02 16:13:12'),
(11, 2, 3, 1, 54000.00, 'COD', 'cancelled', NULL, '2025-04-02 16:01:53', '2025-04-02 16:12:14'),
(12, 2, 3, 1, 40000.00, 'COD', 'cancelled', NULL, '2025-04-02 16:04:35', '2025-04-02 16:12:06'),
(13, 2, 3, 1, 45000.00, 'COD', 'processing', 'mantap', '2025-04-02 16:15:29', '2025-04-02 21:32:39'),
(14, 4, 4, 1, 45000.00, 'COD', 'pending', 'sasa', '2025-04-02 20:21:15', '2025-04-02 21:47:00'),
(15, 4, 5, 5, 270000.00, 'COD', 'completed', NULL, '2025-04-07 04:55:10', '2025-04-07 04:55:24'),
(16, 4, 5, 1, 42000.00, 'Transfer', 'pending', NULL, '2025-04-07 05:05:59', '2025-04-07 05:05:59'),
(17, 4, 5, 3, 138000.00, 'COD', 'completed', NULL, '2025-04-07 05:07:58', '2025-04-07 05:22:21'),
(18, 4, 5, 1, 46000.00, 'COD', 'completed', NULL, '2025-04-07 05:23:02', '2025-04-07 05:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `image`) VALUES
(1, 4, 2, 'Asian Dolce', 42000.00, 1, 'menu-2.png'),
(2, 4, 5, 'Dark Choco Cream', 44000.00, 1, 'menu-5.png'),
(3, 5, 7, 'Vanilla Latte', 38000.00, 1, 'menu-7.png'),
(4, 6, 5, 'Dark Choco Cream', 44000.00, 1, 'menu-5.png'),
(5, 7, 2, 'Asian Dolce', 42000.00, 1, 'menu-2.png'),
(6, 8, 7, 'Vanilla Latte', 38000.00, 2, 'menu-7.png'),
(7, 9, 5, 'Dark Choco Cream', 44000.00, 2, 'menu-5.png'),
(8, 10, 2, 'Asian Dolce', 42000.00, 1, 'menu-2.png'),
(9, 11, 1, 'Caramel Macchiato', 54000.00, 1, 'menu-1.png'),
(10, 12, 8, 'Caffe Americano', 40000.00, 1, 'menu-8.png'),
(11, 13, 3, 'Vanilla Cream', 45000.00, 1, 'menu-3.png\r\n'),
(12, 14, 3, 'Vanilla Cream', 45000.00, 1, 'menu-3.png\r\n'),
(13, 15, 1, 'Caramel Macchiato', 54000.00, 5, 'menu-1.png'),
(14, 16, 2, 'Asian Dolce', 42000.00, 1, ''),
(15, 17, 10, 'Hot Chocolate', 46000.00, 3, 'menu-10.png'),
(16, 18, 10, 'Hot Chocolate', 46000.00, 1, 'menu-10.png');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `stock`, `description`, `created_at`) VALUES
(1, 'Caramel Macchiato', 54000.00, 'menu-1.png', 100, 'sasasasas', '2025-04-02 13:19:45'),
(2, 'Asian Dolce', 42000.00, 'menu-2.png', 0, NULL, '2025-04-02 13:19:45'),
(3, 'Vanilla Cream', 45000.00, 'menu-3.png\r\n', 0, NULL, '2025-04-02 13:19:45'),
(4, 'Chocolate Chip Cream', 51000.00, 'menu-4.png', 0, NULL, '2025-04-02 13:19:45'),
(5, 'Dark Choco Cream', 44000.00, 'menu-5.png', 0, NULL, '2025-04-02 13:19:45'),
(6, 'Cappucino', 42000.00, 'menu-6.png', 0, NULL, '2025-04-02 13:19:45'),
(7, 'Vanilla Latte', 38000.00, 'menu-7.png', 0, NULL, '2025-04-02 13:19:45'),
(8, 'Caffe Americano', 40000.00, 'menu-8.png', 1, NULL, '2025-04-02 13:19:45'),
(9, 'Frappuccino', 35000.00, 'menu-9.png', 0, NULL, '2025-04-02 13:19:45'),
(10, 'Hot Chocolate', 46000.00, 'menu-10.png', 6, '', '2025-04-02 13:19:45');

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_inventory`
-- (See below for the actual view)
--
CREATE TABLE `product_inventory` (
`id` int
,`image` varchar(255)
,`name` varchar(255)
,`price` decimal(10,2)
,`stock` int
,`times_ordered` bigint
,`total_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `sales_report`
-- (See below for the actual view)
--
CREATE TABLE `sales_report` (
`customer` varchar(50)
,`item_count` bigint
,`order_date` date
,`order_id` int
,`payment_method` varchar(50)
,`products` text
,`status` enum('pending','processing','completed','cancelled')
,`total_price` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'tes', 'tes@gmail.com', '$2y$10$36.GYjtU.HGwcwVbWost7uPxldiDDH7KifsmV0Ca.wSiogA/1r3lO', '2025-04-02 13:23:09'),
(2, 'asd', 'asd@gmail.com', '$2y$10$dt3gNehwvs5FiOzlbSa5EOIvI7zV2JzhDxLwhgqZjxi1zzT10oiW.', '2025-04-02 15:37:15'),
(3, 'qwe', 'qwe@gmail.com', '$2y$10$2kz5DKdC4c.DD8SSbHDkyeo2emDBulF0GSngnQaSH3BPPk1/ctCTK', '2025-04-02 16:16:08'),
(4, 'iop', 'iop@gmail.com', '$2y$10$C1c9a7mBduxDaEpfH4MvsOSfoT3ALogF4.lqm/e8sX.6NprGuJg/G', '2025-04-02 16:17:05'),
(5, 'ishak', 'ishak@gmail.com', '$2y$10$OHujOsBHszJOp58GiVY7jeht/Niwje8gxOcqHjb2gMS9DrRaO9COW', '2025-04-02 22:53:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_address_user` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_admin_login` (`username`,`password`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `fk_order_address` (`address_id`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_order_date` (`created_at`),
  ADD KEY `idx_order_user` (`user_id`,`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orderitems_order` (`order_id`),
  ADD KEY `fk_orderitems_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `product_inventory`
--
DROP TABLE IF EXISTS `product_inventory`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_inventory`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, `p`.`price` AS `price`, `p`.`stock` AS `stock`, `p`.`image` AS `image`, count(`oi`.`id`) AS `times_ordered`, ifnull(sum(`oi`.`quantity`),0) AS `total_sold` FROM (`products` `p` left join `order_items` `oi` on((`p`.`id` = `oi`.`product_id`))) GROUP BY `p`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `sales_report`
--
DROP TABLE IF EXISTS `sales_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sales_report`  AS SELECT `o`.`id` AS `order_id`, `u`.`username` AS `customer`, cast(`o`.`created_at` as date) AS `order_date`, `o`.`status` AS `status`, `o`.`payment_method` AS `payment_method`, `o`.`total_price` AS `total_price`, count(`oi`.`id`) AS `item_count`, group_concat(`oi`.`product_name` separator ', ') AS `products` FROM ((`order` `o` join `users` `u` on((`o`.`user_id` = `u`.`id`))) join `order_items` `oi` on((`o`.`id` = `oi`.`order_id`))) GROUP BY `o`.`id` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
