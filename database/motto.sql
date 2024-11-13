-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2024 at 09:15 AM
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
-- Database: `motto`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `user_id` varchar(10) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `price` double DEFAULT 0,
  `quantity` int(11) DEFAULT 1,
  `subtotal` double DEFAULT 0,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `order_id` varchar(10) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `price` double DEFAULT 0,
  `quantity` int(11) DEFAULT 0,
  `subtotal` double DEFAULT 0,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`order_id`, `product_id`, `price`, `quantity`, `subtotal`, `last_date_operate`) VALUES
('ORD1', 'P001', 555, 1, 555, '2024-09-26 02:35:43'),
('ORD2', 'P006', 270, 1, 270, '2024-09-26 05:32:00'),
('ORD3', 'P008', 128, 1, 128, '2024-09-26 05:37:00'),
('ORD4', 'P010', 100, 2, 200, '2024-09-26 06:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` varchar(10) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `total_amount` double DEFAULT 0,
  `payment_method` varchar(10) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `status` varchar(25) NOT NULL,
  `refund_status` varchar(20) DEFAULT NULL,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `user_id`, `total_amount`, `payment_method`, `shipping_address`, `status`, `refund_status`, `last_date_operate`) VALUES
('ORD1', 'U2', 555, 'card', '6582 Jalan 5/10, Taman Seremban Jaya 70450 Seremban Negeri Sembilan', 'completed', '', '2024-09-26 02:35:43'),
('ORD2', 'U5', 270, 'card', '5, Lorong Masria 2 Taman Bunga Raya 53000 Kuala Lumpur Wilayah Persekutuan Kuala Lumpur', 'completed', NULL, '2024-09-26 05:32:00'),
('ORD3', 'U5', 128, 'card', '5 lorong masria 3  21000 6666655 Pulau Pinang', 'cancelled', 'approved', '2024-09-26 05:37:00'),
('ORD4', 'U7', 200, 'card', 'asdfasdf  34100 Ipoh Kuala Lumpur', 'completed', '', '2024-09-26 06:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `paramvv`
--

CREATE TABLE `paramvv` (
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `token_expired` datetime DEFAULT NULL,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` blob DEFAULT NULL,
  `type` varchar(25) NOT NULL,
  `category_type` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `release_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `description`, `type`, `category_type`, `price`, `stock_quantity`, `release_date`, `is_active`, `last_date_operate`) VALUES
('P001', 'QWER', 0x61647366, 'Game', 'action', 555, 1, '0000-00-00', 0, '2024-09-26 03:05:06'),
('P002', 'ZENLESS ZONE ZERO', 0x5a656e6c657373205a6f6e65205a65726f, 'Game', 'Action', 128, 0, '2023-10-18', 1, '2024-09-26 02:28:14'),
('P003', 'QWE', 0x717765717765, 'Accessories', 'Fight', 10, 12, '2020-10-29', 0, '2024-09-26 02:54:05'),
('P004', 'LEE', 0x313233, 'Game', 'Puzzle', 100, 1, '2008-12-18', 1, '2024-09-26 05:08:12'),
('P005', 'ROBLOX', 0x526f626c6f78, 'Game', 'Multiplayer', 10, 1, '2018-01-02', 1, '2024-09-26 05:08:59'),
('P006', 'BLACK WUKONG', 0x426c61636b2057756b6f6e67, 'Game', 'SinglePlayer', 270, 1, '2024-08-04', 1, '2024-09-26 05:10:31'),
('P007', 'VALORANT', 0x56616c6f72616e74, 'Game', 'Multiplayer', 88, 1, '2022-02-02', 1, '2024-09-26 05:11:16'),
('P008', 'NNITENDO SWITCH', 0x4e6e6974656e646f20537769746368, 'Accessories', 'Nnitendo Switch', 128, 9, '2023-11-03', 1, '2024-09-26 05:13:00'),
('P009', 'PS5', 0x505335, 'Accessories', 'PS5', 143.9, 20, '2007-09-18', 1, '2024-09-26 06:06:44'),
('P010', 'KENITEM', 0x4b656e4974656d, 'Accessories', 'Action', 100, 8, '2023-06-12', 1, '2024-09-26 06:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `product_image_video`
--

CREATE TABLE `product_image_video` (
  `product_id` varchar(10) NOT NULL,
  `url` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_image_video`
--

INSERT INTO `product_image_video` (`product_id`, `url`, `type`, `last_date_operate`) VALUES
('P002', '66f4c6be75860.jpg, 66f4c6be949f1.jpg, 66f4c6bea0205', 'photo', '2024-09-26 02:28:14'),
('P005', '66f4ec6b7ed51.jpg', 'photo', '2024-09-26 05:08:59'),
('P006', '66f4ecc75e3ca.jpg', 'photo', '2024-09-26 05:10:31'),
('P007', '66f4ecf450876.jpg', 'photo', '2024-09-26 05:11:16'),
('P008', '66f4ed5c4d41f.jpg', 'photo', '2024-09-26 05:13:00'),
('P009', '66f4f9f4d4945.jpg', 'photo', '2024-09-26 06:06:45'),
('P010', '66f504d3f08d6.jpg, 66f504d408876.jpg', 'photo', '2024-09-26 06:53:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(10) NOT NULL,
  `username` varchar(35) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `error_login` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `role` varchar(10) DEFAULT 'customer',
  `last_date_operate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone_number`, `password_hash`, `dob`, `address`, `profile_picture`, `error_login`, `is_active`, `is_verified`, `role`, `last_date_operate`) VALUES
('U1', 'JIMMYCHANKAHLOK', 'superAdmin@gmail.com', '0185709586', 'a642a77abd7d4f51bf9226ceaf891fcbb5b299b8', '1990-01-01', '123 Main St, City', 'defaultUser.png', 0, 1, 1, 'superAdmin', '2024-09-26 02:12:18'),
('U2', 'CHUNYIN', 'customer@gmail.com', '0155558888', '05b530ad0fb56286fe051d5f8be5b8453f1cd93f', '2002-02-02', ', , 31400, IPOH, Labuan', '66f4c96e8c9d8.jpg', 0, 1, 1, 'customer', '2024-09-26 02:14:31'),
('U3', 'JIEYANG', 'admin@gmail.com', '0135588995', '933f868ccf7ece7601793d3887f5522fbb341418', '2006-01-19', 'TARUMT', NULL, 0, 1, 1, 'admin', '2024-09-26 02:19:15'),
('U4', 'HP', 'hp@gmail.com', '01777777777', '0bbbc7bfe875bde0931ef7e217423112691e8cc8', NULL, NULL, NULL, 0, 1, 1, 'admin', '2024-09-26 04:14:46'),
('U5', 'JIAQIAN', 'jiaqian@gmail.com', '01122233344', '88ea39439e74fa27c09a4fc0bc8ebe6d00978392', '2003-01-18', ', , , , ', NULL, 0, 1, 1, 'customer', '2024-09-26 05:30:33'),
('U6', 'WEILIM', 'weilim@gmail.com', '0179586423', '88ea39439e74fa27c09a4fc0bc8ebe6d00978392', '2000-03-29', NULL, NULL, 0, 1, 1, 'customer', '2024-09-26 06:11:51'),
('U7', 'JIMMYCHAN', 'jimmychankahlok66@gmail.com', '0185709580', 'fc2387aee497b40ef3a89c5f6c93f354c75bd629', '2003-08-28', ', , , , ', '66f506916e409.png', 0, 1, 1, 'customer', '2024-09-26 06:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `wish_list`
--

CREATE TABLE `wish_list` (
  `product_id` varchar(10) NOT NULL,
  `user_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wish_list`
--

INSERT INTO `wish_list` (`product_id`, `user_id`) VALUES
('P002', 'U2'),
('P010', 'U7');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD KEY `id` (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_image_video`
--
ALTER TABLE `product_image_video`
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wish_list`
--
ALTER TABLE `wish_list`
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`),
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_image_video`
--
ALTER TABLE `product_image_video`
  ADD CONSTRAINT `product_image_video_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `wish_list`
--
ALTER TABLE `wish_list`
  ADD CONSTRAINT `wish_list_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `wish_list_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
