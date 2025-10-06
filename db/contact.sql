-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 05:01 PM
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
-- Database: `contactbw`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `Contact_Id` int(11) NOT NULL,
  `Name` text NOT NULL,
  `Phone_No` text NOT NULL,
  `Message` text NOT NULL,
  `Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`Contact_Id`, `Name`, `Phone_No`, `Message`, `Date`) VALUES
(1, 'TestUser1', '1234567890', 'DemoText1', '2025-10-06 13:13:24'),
(2, 'TestUser2', '1234567890', 'DemoText2', '2025-10-06 13:13:48'),
(3, 'TestUser3', '7904617924', 'DemoText3', '2025-10-06 16:21:26'),
(6, 'TestUser4', '7904617924', 'DemoText4', '2025-10-06 16:24:28'),
(7, 'TestUser5', '7904617924', 'DemoText5', '2025-10-06 16:27:09'),
(8, 'TestUser5', '7904617924', 'DemoText5', '2025-10-06 16:27:58'),
(9, 'TestUser6', '7904617924', 'DemoText6', '2025-10-06 16:28:54'),
(10, 'TestUser7', '7904617924', 'DemoText7', '2025-10-06 16:31:27'),
(11, 'TestUser8', '7904617924', 'DemoText8', '2025-10-06 16:32:01'),
(12, 'TestUser9', '7904617924', 'DemoText9', '2025-10-06 16:36:19'),
(13, 'TestUser10', '7904617924', 'DemoText10', '2025-10-06 16:37:17'),
(14, 'TestUser11', '7904617924', 'DemoText11', '2025-10-06 16:38:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`Contact_Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `Contact_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
