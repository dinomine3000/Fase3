-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 23, 2026 at 12:34 AM
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
(1, 'dindin', 'umapalavrapasse', 'u.agente.lego.rafa@gmail.com', 1, 0, 17, 6);

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
(0, 'guest', 0),
(1, 'user', 1),
(2, 'editor', 2),
(3, 'organizer', 3),
(4, 'moderator', 4),
(6, 'admin', 15);

-- --------------------------------------------------------

--
-- Table structure for table `category-notifications`
--

CREATE TABLE `category-notifications` (
  `userId` int(11) NOT NULL,
  `primaryCategory` varchar(64) NOT NULL,
  `secondaryCategory` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category-notifications`
--

INSERT INTO `category-notifications` (`userId`, `primaryCategory`, `secondaryCategory`) VALUES
(1, 'test1', 'secondary2');

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
-- Table structure for table `forum_discussions`
--

CREATE TABLE `forum_discussions` (
  `idDiscussion` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `idUser` int(11) NOT NULL,
  `primaryCategory` varchar(64) NOT NULL,
  `secondaryCategory` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_posted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isSticky` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `forum_discussions`
--

INSERT INTO `forum_discussions` (`idDiscussion`, `title`, `slug`, `idUser`, `primaryCategory`, `secondaryCategory`, `created_at`, `last_posted_at`, `isSticky`) VALUES
(1, 'Bem-vindo ao novo Fórum de Discussão!', 'bem-vindo-ao-novo-forum-de-discussao', 1, 'test1', 'secondary1', '2026-05-21 17:26:30', '2026-05-21 17:26:30', 1),
(2, 'Como integrar Markdown com as páginas Wiki?', 'como-integrar-markdown-com-as-paginas-wiki', 1, 'test1', 'secondary2', '2026-05-21 17:26:30', '2026-05-21 17:26:30', 0),
(3, 'Suporte Geral e testes para a Categoria Grande', 'suporte-geral-e-testes-para-a-categoria-grande', 1, 'test2', 'secondaryBIg', '2026-05-21 17:26:30', '2026-05-21 17:26:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forum_likes`
--

CREATE TABLE `forum_likes` (
  `idPost` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `forum_likes`
--

INSERT INTO `forum_likes` (`idPost`, `idUser`, `created_at`) VALUES
(1, 1, '2026-05-21 17:26:30');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `idPost` int(11) NOT NULL,
  `idDiscussion` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(32) NOT NULL DEFAULT 'comment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`idPost`, `idDiscussion`, `idUser`, `content`, `created_at`, `type`) VALUES
(1, 1, 1, 'Olá a todos! Este é o tópico inaugural do nosso fórum. Este espaço foi perfeitamente interligado com o sistema de permissões e utilizadores do ecossistema principal.', '2026-05-21 17:26:30', 'comment'),
(2, 1, 1, 'Muito bom! Verifiquei agora que o carregamento assíncrono via Fetch API e o isolamento de componentes está a funcionar de forma ágil.', '2026-05-21 17:26:30', 'comment'),
(3, 2, 1, 'Estava a analisar a tabela `page` e reparei que o conteúdo em formato Markdown (como o `markdownTest4`) possui quebras de linha reais. Qual parser recomendam usar no frontend?', '2026-05-21 17:26:30', 'comment'),
(4, 3, 1, 'Tópico de teste criado exclusivamente para validar a listagem de categorias secundárias complexas (`secondaryBIg`). Tudo operacional!', '2026-05-21 17:26:30', 'comment');

-- --------------------------------------------------------

--
-- Table structure for table `images-config`
--

CREATE TABLE `images-config` (
  `id` int(11) NOT NULL,
  `destination` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `maxFileSize` int(11) NOT NULL,
  `thumbType` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `thumbWidth` int(11) NOT NULL,
  `thumbHeight` int(11) NOT NULL,
  `numColls` int(11) NOT NULL,
  `cellspacing` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `images-config`
--

INSERT INTO `images-config` (`id`, `destination`, `maxFileSize`, `thumbType`, `thumbWidth`, `thumbHeight`, `numColls`, `cellspacing`) VALUES
(1, '/tmp/upload/contents', 52428800, 'png', 80, 80, 3, 10);

-- --------------------------------------------------------

--
-- Table structure for table `images-details`
--

CREATE TABLE `images-details` (
  `id` int(11) NOT NULL,
  `fileName` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `mimeFileName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `typeFileName` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `imageFileName` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `imageMimeFileName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `imageTypeFileName` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `thumbFileName` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `thumbMimeFileName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `thumbTypeFileName` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `latitude` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `longitude` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

CREATE TABLE `page` (
  `primaryCategory` varchar(64) NOT NULL,
  `secondaryCategory` varchar(64) NOT NULL,
  `pageTitle` varchar(64) NOT NULL,
  `content` longtext NOT NULL,
  `visibility` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `page`
--

INSERT INTO `page` (`primaryCategory`, `secondaryCategory`, `pageTitle`, `content`, `visibility`) VALUES
('test1', 'secondary2', 'how to create a page', 'get good\r\n\r\n![9lana](getFileContents.php?id=2)', 3),
('test1', 'secondary2', 'markdownTest', 'hello#bighello*italicizedhello****boldanditalic***', 0),
('test1', 'secondary2', 'markdownTest2', 'hellon#bighellon*italicizedhello*n***boldanditalic***', 0),
('test1', 'secondary2', 'markdownTest3', 'hello\r\n\r\n# big hello\r\n\r\n*italicized hello*\r\n\r\n***bold and italic***', 0),
('test1', 'secondary2', 'markdownTest4', 'hello\r\n\r\n# big hello\r\n\r\n*italicized hello*\r\n\r\n***bold and italic***\r\n\r\n![9lana](getFileContents.php?id=2)\r\n\r\n:[song](getFileContents.php?id=7)\r\n\r\nyep luigi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `page-changes`
--

CREATE TABLE `page-changes` (
  `pageTitle` varchar(64) NOT NULL,
  `editorId` int(11) DEFAULT NULL,
  `newContent` longtext NOT NULL,
  `changeId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `page-notifications`
--

CREATE TABLE `page-notifications` (
  `userId` int(11) NOT NULL,
  `pageTitle` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `page-notifications`
--

INSERT INTO `page-notifications` (`userId`, `pageTitle`) VALUES
(1, 'how to create a page');

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
-- Indexes for table `category-notifications`
--
ALTER TABLE `category-notifications`
  ADD PRIMARY KEY (`userId`,`primaryCategory`,`secondaryCategory`),
  ADD KEY `fk_notifCat` (`primaryCategory`,`secondaryCategory`);

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
-- Indexes for table `forum_discussions`
--
ALTER TABLE `forum_discussions`
  ADD PRIMARY KEY (`idDiscussion`),
  ADD KEY `fk_discussion_user` (`idUser`),
  ADD KEY `fk_discussion_categories` (`primaryCategory`,`secondaryCategory`);

--
-- Indexes for table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD PRIMARY KEY (`idPost`,`idUser`),
  ADD KEY `fk_like_user` (`idUser`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`idPost`),
  ADD KEY `fk_post_discussion` (`idDiscussion`),
  ADD KEY `fk_post_user` (`idUser`);

--
-- Indexes for table `images-config`
--
ALTER TABLE `images-config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images-details`
--
ALTER TABLE `images-details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`pageTitle`),
  ADD KEY `fk_page` (`primaryCategory`,`secondaryCategory`),
  ADD KEY `fk_visibilityRole` (`visibility`);

--
-- Indexes for table `page-changes`
--
ALTER TABLE `page-changes`
  ADD PRIMARY KEY (`changeId`),
  ADD KEY `fk_changePage` (`pageTitle`),
  ADD KEY `fk_editorUser` (`editorId`);

--
-- Indexes for table `page-notifications`
--
ALTER TABLE `page-notifications`
  ADD PRIMARY KEY (`userId`,`pageTitle`),
  ADD KEY `fk_notifPage` (`pageTitle`);

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
-- AUTO_INCREMENT for table `forum_discussions`
--
ALTER TABLE `forum_discussions`
  MODIFY `idDiscussion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `idPost` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `images-config`
--
ALTER TABLE `images-config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `images-details`
--
ALTER TABLE `images-details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page-changes`
--
ALTER TABLE `page-changes`
  MODIFY `changeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Constraints for table `category-notifications`
--
ALTER TABLE `category-notifications`
  ADD CONSTRAINT `fk_notifCat` FOREIGN KEY (`primaryCategory`,`secondaryCategory`) REFERENCES `category-secondary` (`primaryCategory`, `secondaryCategory`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifUserCat` FOREIGN KEY (`userId`) REFERENCES `auth-basic` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `category-secondary`
--
ALTER TABLE `category-secondary`
  ADD CONSTRAINT `fk_secondary` FOREIGN KEY (`primaryCategory`) REFERENCES `category-primary` (`primaryCategory`) ON UPDATE CASCADE;

--
-- Constraints for table `forum_discussions`
--
ALTER TABLE `forum_discussions`
  ADD CONSTRAINT `fk_discussion_categories` FOREIGN KEY (`primaryCategory`,`secondaryCategory`) REFERENCES `category-secondary` (`primaryCategory`, `secondaryCategory`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_discussion_user` FOREIGN KEY (`idUser`) REFERENCES `auth-basic` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD CONSTRAINT `fk_like_post` FOREIGN KEY (`idPost`) REFERENCES `forum_posts` (`idPost`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`idUser`) REFERENCES `auth-basic` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `fk_post_discussion` FOREIGN KEY (`idDiscussion`) REFERENCES `forum_discussions` (`idDiscussion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`idUser`) REFERENCES `auth-basic` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `fk_page` FOREIGN KEY (`primaryCategory`,`secondaryCategory`) REFERENCES `category-secondary` (`primaryCategory`, `secondaryCategory`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_visibilityRole` FOREIGN KEY (`visibility`) REFERENCES `auth-roles` (`idRole`) ON UPDATE CASCADE;

--
-- Constraints for table `page-changes`
--
ALTER TABLE `page-changes`
  ADD CONSTRAINT `fk_changePage` FOREIGN KEY (`pageTitle`) REFERENCES `page` (`pageTitle`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_editorUser` FOREIGN KEY (`editorId`) REFERENCES `auth-basic` (`idUser`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `page-notifications`
--
ALTER TABLE `page-notifications`
  ADD CONSTRAINT `fk_notifPage` FOREIGN KEY (`pageTitle`) REFERENCES `page` (`pageTitle`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifUser` FOREIGN KEY (`userId`) REFERENCES `auth-basic` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
