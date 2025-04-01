-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2023 at 11:30 AM
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
-- Database: `kopiuy`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `name`, `price`, `image`, `quantity`) VALUES
(13, 'Caramel Macchiato', '54000', 'menu-1.png', '4'),
(14, 'Vanilla Cream', '45000', 'menu-3.png\r\n', '5'),
(15, 'Chocolate Chip Cream', '51000', 'menu-4.png', '6'),
(16, 'Vanilla Latte', '38000', 'menu-7.png', '2'),
(17, 'Asian Dolce', '42000', 'menu-2.png', '2'),
(18, 'Hot Chocolate', '46000', 'menu-10.png', '3');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `metode` varchar(100) NOT NULL,
  `jalan` varchar(100) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `provinsi` varchar(100) NOT NULL,
  `negara` varchar(100) NOT NULL,
  `pos_kode` int(10) NOT NULL,
  `total_products` varchar(255) NOT NULL,
  `total_price` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `name`, `number`, `email`, `metode`, `jalan`, `alamat`, `kota`, `provinsi`, `negara`, `pos_kode`, `total_products`, `total_price`) VALUES
(68, 'Ishak', 'eqwe', 'Ishak@gmail.com', 'paypal', 'eqe', 'wqeq', 'eq', 'wwq', 'wwrq', 0, 'Asian Dolce (4) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (1) ', '438'),
(69, 'sasa', 'sasa', 'sasas@gmail', 'cash on delivery', 'asasas', '', '', '', '', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(70, 'sasa', 'sasa', 'sasas@gmail', 'cash on delivery', 'asasas', '', '', '', '', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(71, 'sasa', 'sasa', 'sasas@gmail', 'cash on delivery', 'asasas', '', '', '', '', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(72, 'sasa', 'sasa', 'sasas@gmail', 'cash on delivery', 'asasas', '', '', '', '', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(73, 'sasa', 'sasa', 'sasas@gmail', 'cash on delivery', 'asasas', '', '', '', '', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(74, 'sasa', 'sasa', 'sasas@gmail', 'cash on delivery', 'asasas', '', '', '', '', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(75, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(76, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(77, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(78, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(79, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(80, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(81, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(82, 'sasa', 'sasa', 'sasasas@gmail.com', 'Visa', 'asa', 'sas', 'sasas', 'asasas', 'sasasa', 0, 'Asian Dolce (3) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) , Dark Choco Cream (2) ', '440'),
(83, 'Dusk', 'dada', 'Dusk@gmail.com', 'COD', 'dads', 'dadas', 'dsada', 'dada', 'dasd', 0, 'Asian Dolce (1) , Frappuccino (1) , Chocolate Chip Cream (3) , Vanilla Latte (1) ', '268'),
(84, 'jkj', 'sasa', 'sas@gmail.com', 'Credit Card', 'asasa', 'sas', 'sas', 'asasa', 'asasa', 0, 'Caramel Macchiato (4) , Vanilla Cream (5) , Chocolate Chip Cream (6) , Vanilla Latte (2) , Asian Dolce (2) , Hot Chocolate (1) ', '953'),
(85, 'gd', 'gdfg', 'gggd@gmail.com', 'COD', 'dfgdgdf', 'g', 'g', 'dgfg', 'dfgf', 0, 'Caramel Macchiato (4) , Vanilla Cream (5) , Chocolate Chip Cream (6) , Vanilla Latte (2) , Asian Dolce (2) , Hot Chocolate (3) ', '1045'),
(86, 'ds', 'sdds', 'sds@gmail.com', 'COD', 'dsds', 'sdsd', 'dsdsd', 'dsdsd', 'sdsds', 0, 'Caramel Macchiato (4) , Vanilla Cream (5) , Chocolate Chip Cream (6) , Vanilla Latte (2) , Asian Dolce (2) , Hot Chocolate (3) ', '1045');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`) VALUES
(1, 'Caramel Macchiato', '54000', 'menu-1.png'),
(2, 'Asian Dolce', '42000', 'menu-2.png'),
(3, 'Vanilla Cream', '45000', 'menu-3.png\r\n'),
(4, 'Chocolate Chip Cream', '51000', 'menu-4.png'),
(5, 'Dark Choco Cream', '44000', 'menu-5.png'),
(6, 'Cappucino', '42000', 'menu-6.png'),
(7, 'Vanilla Latte', '38000', 'menu-7.png'),
(8, 'Caffe Americano', '40000', 'menu-8.png'),
(9, 'Frappuccino', '35000', 'menu-9.png'),
(10, 'Hot Chocolate', '46000', 'menu-10.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'Ishak Immanuel', 'ishakimmanuel@gmail.com', 'ishak123'),
(2, 'dusk', 'dusk@gmail.com', '$2y$10$2KXuC6dvxYdVUPcmF7zGMe2Lc8GiT1Nznt6ruLy5RZ4vaTj8PJVPi'),
(3, 'sakura', 'sakura@gmail.com', '$2y$10$vvY0gtv4e6AQ3jqCV7XcLul8qtTKDDAE9wp0nYJHC6bFF39NmN5ja'),
(4, 'saa', 'sasa', '$2y$10$lQ2SkjKTnXC2DSnNTylHheLMOK3UdxujwxLM6qEf3rFBPLbZtgNtq'),
(5, 'hhaha', 'hhaha', '$2y$10$M8qGS86.WAcuk6Z519x86.h82UCCDAyU7XYeTu8sbcXtEq3mvweJ.'),
(6, 'as', 'as', '$2y$10$Q0/eAy8HlLc/SAZNSR6qIOSnIkJhbUF2JuZeSIla6ejB0K/czK6Y6'),
(7, 'qw', 'qw', '$2y$10$NnSbB1RGovkd.NT6nK5toer.KMeqEazSEVMNC4Q6DKDuozljOuTbm'),
(8, 'er', 'er', '$2y$10$It61DhqZQOA5VzAP6MHeTucEJ404YEUZibTc5F4TAwhZbt8OMYOZm'),
(9, 'bn', 'bn', '$2y$10$vVILl6MHXZq9/KLdHuvHZ.WHiz9bin4b10nAHnUNvUgu/Tcs4CuCq'),
(10, 'kl', 'kl', '$2y$10$lCj1pVPRICiSaFNc7MBNde70scgFGDzC1a0qQtjBrUGe8qyfAdFpW'),
(11, 'bg', 'bg', '$2y$10$cI2D6KPNx5oytXJgVW8Q..pBxqiiFdBO5jNIy4Y7dZBJ/r9IWdRuu'),
(12, 'kipuy', 'kipuy@gmail.com', '$2y$10$NzDrmtcdA1qG5LqVc/clIOTRjx.ey13a4TZixHiytmOiTWkDFHauC'),
(13, 'asd', 'asd', '$2y$10$wXehm8gNFpnZv3cD0kGULe9dMFHoMvjLwUgj9jt3ecsTcMqDU5cZa'),
(14, 'asd123', 'asd@gmail.com', '$2y$10$wdG7uAdKVYgD5VkcW78W3.r1wbKgQ3plZ04jKHNPRh8GZlHQI4h1O');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
