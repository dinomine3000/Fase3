-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 21, 2026 at 04:26 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webapp`
--
CREATE DATABASE IF NOT EXISTS `webapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `webapp`;

-- --------------------------------------------------------

--
-- Table structure for table `auth-basic`
--

CREATE TABLE `auth-basic` (
  `idUser` int(11) NOT NULL,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `isBanned` tinyint(1) NOT NULL DEFAULT 0,
  `contributions` int(11) NOT NULL DEFAULT 0,
  `idRole` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `auth-basic`
--

INSERT INTO `auth-basic` (`idUser`, `name`, `password`, `email`, `active`, `isBanned`, `contributions`, `idRole`) VALUES
(1, 'dindin', 'umapalavrapasse', 'u.agente.lego.rafa@gmail.com', 1, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `auth-challenge`
--

CREATE TABLE `auth-challenge` (
  `idUser` int(11) NOT NULL,
  `challenge` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth-roles`
--

CREATE TABLE `auth-roles` (
  `idRole` int(11) NOT NULL,
  `friendlyName` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `roleLevel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `auth-roles`
--

INSERT INTO `auth-roles` (`idRole`, `friendlyName`, `roleLevel`) VALUES
(1, 'user', 1),
(2, 'editor', 2),
(3, 'organizer', 3),
(4, 'moderator', 4),
(6, 'admin', 15);

-- --------------------------------------------------------

--
-- Table structure for table `category-primary`
--

CREATE TABLE `category-primary` (
  `primaryCategory` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category-primary`
--

INSERT INTO `category-primary` (`primaryCategory`) VALUES
('test1'),
('test2');

-- --------------------------------------------------------

--
-- Table structure for table `category-secondary`
--

CREATE TABLE `category-secondary` (
  `primaryCategory` varchar(64) NOT NULL,
  `secondaryCategory` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category-secondary`
--

INSERT INTO `category-secondary` (`primaryCategory`, `secondaryCategory`) VALUES
('test1', 'secondary1'),
('test1', 'secondary2'),
('test2', 'secondaryBIg');

-- --------------------------------------------------------

--
-- Table structure for table `email-accounts`
--

CREATE TABLE `email-accounts` (
  `id` int(11) NOT NULL,
  `accountName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `smtpServer` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL,
  `useSSL` tinyint(4) NOT NULL,
  `timeout` int(11) NOT NULL,
  `loginName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `email-accounts`
--

INSERT INTO `email-accounts` (`id`, `accountName`, `smtpServer`, `port`, `useSSL`, `timeout`, `loginName`, `password`, `email`, `displayName`) VALUES
(4, 'Working', 'smtp.gmail.com', 465, 1, 30, 'skiperpedronuno@gmail.com', 'gcyqlgakmpwixuwk', 'skiperpedronuno@gmail.com', 'Main Mail');

-- --------------------------------------------------------

--
-- Table structure for table `email-contacts`
--

CREATE TABLE `email-contacts` (
  `id` int(11) NOT NULL,
  `displayName` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `email-contacts`
--

INSERT INTO `email-contacts` (`id`, `displayName`, `email`) VALUES
(1, 'Carlos Gonçalves - IPL', 'cgoncalves@deetc.isel.ipl.pt'),
(2, 'Carlos Gonçalves - ISEL', 'carlos.goncalves@isel.pt'),
(3, 'Carlos Gonçalves - IPL', 'cgoncalves@deetc.isel.ipl.pt'),
(4, 'Carlos Gonçalves - ISEL', 'carlos.goncalves@isel.pt');

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

CREATE TABLE `page` (
  `primaryCategory` varchar(64) NOT NULL,
  `secondaryCategory` varchar(64) NOT NULL,
  `pageTitle` varchar(64) NOT NULL,
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `page`
--

INSERT INTO `page` (`primaryCategory`, `secondaryCategory`, `pageTitle`, `content`) VALUES
('test1', 'secondary2', 'markdownTest', 'hello#bighello*italicizedhello****boldanditalic***'),
('test1', 'secondary2', 'markdownTest2', 'hellon#bighellon*italicizedhello*n***boldanditalic***'),
('test1', 'secondary2', 'markdownTest3', 'hello\r\n\\n\r\n# big hello\r\n\\n\r\n*italicized hello*\r\n\\n\r\n***bold and italic***'),
('test1', 'secondary2', 'markdownTest4', 'hello\r\n\r\n# big hello\r\n\r\n*italicized hello*\r\n\r\n***bold and italic***');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth-basic`
--
ALTER TABLE `auth-basic`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_userRoleId` (`idRole`);

--
-- Indexes for table `auth-challenge`
--
ALTER TABLE `auth-challenge`
  ADD KEY `fk_idUser` (`idUser`);

--
-- Indexes for table `auth-roles`
--
ALTER TABLE `auth-roles`
  ADD PRIMARY KEY (`idRole`);

--
-- Indexes for table `category-primary`
--
ALTER TABLE `category-primary`
  ADD PRIMARY KEY (`primaryCategory`);

--
-- Indexes for table `category-secondary`
--
ALTER TABLE `category-secondary`
  ADD PRIMARY KEY (`primaryCategory`,`secondaryCategory`);

--
-- Indexes for table `email-accounts`
--
ALTER TABLE `email-accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email-contacts`
--
ALTER TABLE `email-contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`pageTitle`),
  ADD KEY `fk_page` (`primaryCategory`,`secondaryCategory`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth-basic`
--
ALTER TABLE `auth-basic`
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email-accounts`
--
ALTER TABLE `email-accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email-contacts`
--
ALTER TABLE `email-contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth-basic`
--
ALTER TABLE `auth-basic`
  ADD CONSTRAINT `fk_userRoleId` FOREIGN KEY (`idRole`) REFERENCES `auth-roles` (`idRole`) ON UPDATE CASCADE;

--
-- Constraints for table `auth-challenge`
--
ALTER TABLE `auth-challenge`
  ADD CONSTRAINT `fk_idUser` FOREIGN KEY (`idUser`) REFERENCES `auth-basic` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `category-secondary`
--
ALTER TABLE `category-secondary`
  ADD CONSTRAINT `fk_secondary` FOREIGN KEY (`primaryCategory`) REFERENCES `category-primary` (`primaryCategory`) ON UPDATE CASCADE;

--
-- Constraints for table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `fk_page` FOREIGN KEY (`primaryCategory`,`secondaryCategory`) REFERENCES `category-secondary` (`primaryCategory`, `secondaryCategory`) ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;