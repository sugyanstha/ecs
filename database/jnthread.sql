-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2024 at 03:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jnthread`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aid` int(11) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aid`, `email`, `password`) VALUES
(1, 'superadmin', 'jnthread');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `cid` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `cid`, `created_at`) VALUES
(1, 4, '2024-10-21 12:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(5, 'Hoodies'),
(6, 'Sweatshirt'),
(1, 'T-shirt');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `district` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `mobile` bigint(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_otp_hash` varchar(64) DEFAULT NULL,
  `rest_otp_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cid`, `name`, `district`, `city`, `mobile`, `email`, `password`, `reset_otp_hash`, `rest_otp_expires_at`) VALUES
(4, 'Sugyan Shrestha', 'BKT', 'Thimi', 9812345678, 'sugyan@gmail.com', '$2y$10$hql/7fyPInRqEl.PqhpSZOWXt51wHOsXDi9dfLfM90hFUiuGhklAG', NULL, NULL),
(7, 'sfg', 'sdh', 'sfg', 1234567890, 'a@gmail.com', '$2y$10$fKeidRP5xFwM6NIHTn.b9u795QnYILlp9XxHA7DGV2fQUXmY/ej/2', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(7, 7, 7, 1, 234.00),
(8, 8, 7, 1, 234.00),
(9, 9, 7, 1, 234.00),
(10, 10, 7, 1, 234.00),
(21, 21, 7, 1, 234.00),
(22, 22, 7, 1, 234.00),
(23, 23, 17, 1, 345.00),
(24, 24, 17, 1, 345.00),
(25, 25, 7, 1, 234.00),
(26, 26, 7, 1, 234.00),
(27, 27, 7, 1, 234.00),
(28, 28, 17, 1, 345.00),
(29, 29, 7, 1, 234.00),
(30, 30, 7, 1, 234.00),
(34, 34, 7, 4, 234.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `cid` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','shipped','delivered','canceled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `cid`, `total_price`, `status`, `created_at`, `updated_at`) VALUES
(7, 4, 234.00, 'canceled', '2024-10-06 16:42:59', '2024-10-06 16:45:14'),
(8, 4, 234.00, 'delivered', '2024-10-07 17:14:07', '2024-10-07 17:18:26'),
(9, 4, 234.00, 'canceled', '2024-10-13 14:59:02', '2024-10-13 16:44:44'),
(10, 4, 234.00, 'canceled', '2024-10-14 11:06:36', '2024-10-14 11:07:17'),
(21, 4, 234.00, 'canceled', '2024-10-14 11:36:53', '2024-10-15 15:12:47'),
(22, 4, 234.00, 'canceled', '2024-10-14 12:58:25', '2024-10-15 15:12:22'),
(23, 4, 345.00, 'canceled', '2024-10-15 13:27:09', '2024-10-15 15:12:09'),
(24, 4, 345.00, 'delivered', '2024-10-15 15:51:31', '2024-10-15 16:23:44'),
(25, 4, 234.00, 'delivered', '2024-10-16 14:57:25', '2024-10-20 16:56:52'),
(26, 4, 234.00, 'canceled', '2024-10-20 16:34:07', '2024-10-21 12:45:52'),
(27, 4, 234.00, 'canceled', '2024-10-21 12:00:28', '2024-10-21 12:46:12'),
(28, 4, 345.00, 'canceled', '2024-10-21 12:00:35', '2024-10-21 12:46:06'),
(29, 4, 234.00, 'canceled', '2024-10-21 12:14:28', '2024-10-21 12:50:38'),
(30, 4, 234.00, 'canceled', '2024-10-21 12:16:05', '2024-10-21 12:56:03'),
(34, 4, 936.00, 'pending', '2024-10-21 12:48:00', '2024-10-21 12:48:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock`, `category_id`, `image_url`, `created_at`, `updated_at`) VALUES
(7, 'sdgdf', 'dfhdfg', 234.00, 15, 5, '../img/ECS DFD Level-1.drawio.png', '2024-10-06 16:29:19', '2024-10-21 13:02:24'),
(17, 'dghrftg', 'dhghgfdh', 345.00, 1, 6, '../img/1728998592_ECS_ERD.drawio.png', '2024-10-15 13:23:12', '2024-10-21 12:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `cid`, `rating`, `comment`, `created_at`) VALUES
(1, 7, 4, 1, 'this is review', '2024-10-21 11:49:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cid` (`cid`);

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `cid` (`cid`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cid` (`cid`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `cid` (`cid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`);

--
-- Constraints for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`),
  ADD CONSTRAINT `cartitems_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
