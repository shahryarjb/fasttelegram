-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 20, 2016 at 11:26 PM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test_crocodil`
--

-- --------------------------------------------------------

--
-- Table structure for table `kr4ef_telegram`
--

CREATE TABLE `kr4ef_telegram` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `pic` text NOT NULL,
  `article_id` int(11) NOT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '0',
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `kr4ef_telegram`
--

INSERT INTO `kr4ef_telegram` (`id`, `message`, `pic`, `article_id`, `published`, `url`) VALUES
(50, 'fff', '', 46, 1, 'http://static.bigstockphoto.com/images/homepage/2016_popular_photo_categories.jpg'),
(51, 'ooooo', '', 47, 1, 'http://static.bigstockphoto.com/images/homepage/2016_popular_photo_categories.jpg'),
(52, 'یییییییییی', 'images/d9aa3acf1aaee6c3ccaadd175e3804c7-310x432.jpg', 48, 1, ''),
(53, 'yyyyyy', 'images/Free-Shabby-Floral-Tags-by-FPTFY-1.png', 49, 1, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kr4ef_telegram`
--
ALTER TABLE `kr4ef_telegram`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kr4ef_telegram`
--
ALTER TABLE `kr4ef_telegram`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
