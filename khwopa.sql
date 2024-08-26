-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2024 at 11:48 AM
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
-- Database: `khwopa`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorys`
--

CREATE TABLE `categorys` (
  `cid` int(11) NOT NULL,
  `c_name` varchar(500) NOT NULL,
  `c_img` varchar(5000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorys`
--

INSERT INTO `categorys` (`cid`, `c_name`, `c_img`) VALUES
(28, 'Pants', 'category_image_1721836810.jpg'),
(29, 'Jacket', 'category_image_1721836824.jpg'),
(31, 'Tshirt', 'category_image_1721912169.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `oid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `o_totalAmount` int(11) NOT NULL,
  `o_shippingAddress` varchar(500) NOT NULL,
  `o_orderStatus` varchar(500) NOT NULL DEFAULT 'pending',
  `o_quantity` int(11) NOT NULL,
  `o_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`oid`, `uid`, `pid`, `o_totalAmount`, `o_shippingAddress`, `o_orderStatus`, `o_quantity`, `o_date`) VALUES
(1, 2, 1, 8071, ' Aut sint ipsum dicta, A necessitatibus odi', 'completed', 1, '2024-07-28 19:25:45'),
(2, 2, 1, 24213, ' Aut sint ipsum dicta, A necessitatibus odi', 'completed', 3, '2024-07-28 20:53:02'),
(3, 2, 1, 8071, ' Aut sint ipsum dicta, A necessitatibus odi', 'completed', 1, '2024-07-28 21:01:09'),
(4, 2, 2, 3870, ' Aut sint ipsum dicta, A necessitatibus odi', 'pending', 5, '2024-07-30 10:43:47'),
(5, 2, 2, 3870, ' Aut sint ipsum dicta, A necessitatibus odi', 'pending', 5, '2024-08-05 09:30:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `pid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `p_name` varchar(500) NOT NULL,
  `p_model` varchar(500) NOT NULL,
  `p_brand` varchar(500) NOT NULL,
  `p_description` varchar(500) NOT NULL,
  `p_price` int(11) NOT NULL,
  `p_stocksQuantity` int(11) NOT NULL DEFAULT 0,
  `p_dateAndTime` datetime NOT NULL DEFAULT current_timestamp(),
  `p_image` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`pid`, `cid`, `p_name`, `p_model`, `p_brand`, `p_description`, `p_price`, `p_stocksQuantity`, `p_dateAndTime`, `p_image`) VALUES
(1, 29, 'Aiko Humphrey', 'Quibusdam rerum est ', 'Iste pariatur Velit', 'Nam neque earum sit', 8071, 5, '2024-07-28 18:24:01', 'product_image_1722170341.jpg'),
(2, 31, 'Silas Mcclure', 'Rem excepteur volupt', 'Ut non nostrud fugia', 'Magna aperiam pariat', 774, 5, '2024-07-28 21:21:31', 'product_image_1722180991.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `rid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `r_ratingValue` int(11) NOT NULL,
  `r_comment` varchar(500) NOT NULL,
  `r_dateAndTime` datetime NOT NULL DEFAULT current_timestamp(),
  `r_revievStatus` int(11) NOT NULL DEFAULT 0 COMMENT '0 = not verified\r\n1= verified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`rid`, `uid`, `pid`, `r_ratingValue`, `r_comment`, `r_dateAndTime`, `r_revievStatus`) VALUES
(1, 2, 2, 5, 'kjabsdkjbasd;kjb;kajsd', '2024-08-05 10:37:34', 0);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `sid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `s_quantity` int(11) NOT NULL,
  `s_in_out` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=in 1=out',
  `s_entryDate` datetime NOT NULL DEFAULT current_timestamp(),
  `s_productPrice` int(11) NOT NULL COMMENT 'total priice of ordered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`sid`, `pid`, `s_quantity`, `s_in_out`, `s_entryDate`, `s_productPrice`) VALUES
(1, 1, 5, 0, '2024-07-28 18:24:06', 807),
(2, 1, 1, 1, '2024-07-28 19:26:05', 8071),
(3, 1, 3, 1, '2024-07-28 21:00:10', 24213),
(4, 2, 5, 0, '2024-07-28 21:21:45', 774),
(5, 1, 1, 1, '2024-07-30 10:42:36', 8071),
(6, 1, 5, 0, '2024-08-01 09:46:25', 8071);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(11) NOT NULL,
  `first_name` varchar(500) NOT NULL,
  `last_name` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `password` varchar(5000) NOT NULL,
  `phone` int(11) NOT NULL,
  `role` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=admin, 1=Customer',
  `district` varchar(500) NOT NULL,
  `city` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `first_name`, `last_name`, `email`, `password`, `phone`, `role`, `district`, `city`) VALUES
(1, 'Beau', 'Barrett', 'admin@gmail.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 0, 'Excepturi incididunt', 'At ut provident des'),
(2, 'Kiayada', 'Bernard', 'suman@gmail.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 1, 'Aut sint ipsum dicta', 'A necessitatibus odi'),
(3, 'Alan', 'Bauer', 'hikexuhacu@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 1, 'Ullamco qui dolore f', 'Et ut suscipit ab ut'),
(4, 'Gareth', 'Wynn', 'baby@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 1, 'Tenetur necessitatib', 'Et eum alias archite'),
(5, 'Willow', 'Warner', 'cawiki@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 1, 'Totam exercitationem', 'Odio quae neque quia'),
(6, 'Dawn', 'Cabrera', 'fysefuw@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 1, 'Esse nemo aliqua Do', 'Excepteur ipsa prov'),
(7, 'Demetria', 'Campbell', 'mejuqugim@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 1234567890, 1, 'Aut reprehenderit a', 'Aspernatur nisi occa'),
(8, 'Stewart', 'Cook', 'lucacyp@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 2147483647, 1, 'Est qui ipsum et om', 'In quae totam aut ad'),
(9, 'Yolanda', 'Frank', 'kifagoza@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 2147483647, 1, 'Hic eaque commodo ea', 'Quisquam dignissimos'),
(10, 'Erich', 'White', 'sezygegov@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 2147483647, 1, 'Dolor cupiditate est', 'Doloremque quia nost'),
(11, 'Callie', 'Suarez', 'cihehu@mailinator.com', '3a2a5ce900c7489c2112302b646bdef3', 2147483647, 1, 'Culpa ut et rerum mo', 'Illum illum aut ve'),
(12, 'Ifeoma', 'Harris', 'abc@gmail.com', '3a2a5ce900c7489c2112302b646bdef3', 2147483647, 1, 'Ut magni repudiandae', 'Sunt minim consequa');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorys`
--
ALTER TABLE `categorys`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`oid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `cid` (`cid`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`rid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `stocks_ibfk_1` (`pid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorys`
--
ALTER TABLE `categorys`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `oid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `categorys` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
