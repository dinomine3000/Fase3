-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 13, 2026 at 12:55 AM
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
  `idRole` int(11) DEFAULT 1,
  `bio` text COLLATE utf8_unicode_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `auth-basic`
--

INSERT INTO `auth-basic` (`idUser`, `name`, `password`, `email`, `active`, `isBanned`, `contributions`, `idRole`, `bio`) VALUES
(1, 'dindin', 'umapalavrapasse', 'u.agente.lego.rafa@gmail.com', 1, 0, 25, 6, 'I am quite fond of **Shadow Slave**'),
(2, 'Nr2win', 'E*#2005rafa', 'nr2windows.gamebuds@gmail.com', 1, 0, 4, 3, 'I quite enjoy **Lord of Mysteries**');

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
('GameObjects'),
('Games'),
('Lore'),
('Speedruns'),
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
(4, 'Working', 'smtp.gmail.com', 465, 1, 30, 'skiperpedronuno@gmail.com', 'gcyqlgakmpwixuwk', 'skiperpedronuno@gmail.com', 'Portal Mail');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_posted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isSticky` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `forum_discussions`
--

INSERT INTO `forum_discussions` (`idDiscussion`, `title`, `slug`, `idUser`, `primaryCategory`, `created_at`, `last_posted_at`, `isSticky`) VALUES
(1, 'Bem-vindo ao novo Fórum de Discussão!', 'bem-vindo-ao-novo-forum-de-discussao', 1, 'test1', '2026-05-21 17:26:30', '2026-06-10 20:46:06', 1),
(2, 'Como integrar Markdown com as páginas Wiki?', 'como-integrar-markdown-com-as-paginas-wiki', 1, 'test1', '2026-05-21 17:26:30', '2026-05-21 17:26:30', 0),
(3, 'Suporte Geral e testes para a Categoria Grande', 'suporte-geral-e-testes-para-a-categoria-grande', 1, 'test2', '2026-05-21 17:26:30', '2026-05-21 17:26:30', 0);

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
(4, 3, 1, 'Tópico de teste criado exclusivamente para validar a listagem de categorias secundárias complexas (`secondaryBIg`). Tudo operacional!', '2026-05-21 17:26:30', 'comment'),
(5, 1, 1, 'undefined twin!!', '2026-06-10 20:46:06', 'comment');

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
(1, '/tmp/upload/contents/', 52428800, 'png', 80, 80, 3, 10);

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
  `title` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `images-details`
--

INSERT INTO `images-details` (`id`, `fileName`, `mimeFileName`, `typeFileName`, `imageFileName`, `imageMimeFileName`, `imageTypeFileName`, `thumbFileName`, `thumbMimeFileName`, `thumbTypeFileName`, `title`, `description`) VALUES
(1, '/tmp/upload/contents/images-config.sql', 'text', 'plain', '/tmp/upload/contents/default/Unknown-Large.jpg', 'image', 'jpeg', '/tmp/upload/contents/default/Unknown.jpg', 'image', 'jpeg', '9lana', 'No description available'),
(2, '/tmp/upload/contents/9lana_1.gif', 'image', 'gif', '/tmp/upload/contents/9lana_1.gif', 'image', 'gif', '/tmp/upload/contents/thumbs/9lana_1.gif', 'image', 'gif', '9lanaReal', 'No description available'),
(3, '/tmp/upload/contents/boothill bass.mp3', 'audio', 'mpeg', '/tmp/upload/contents/thumbs/default/Unknown-Large.jpg', 'image', 'jpeg', '/tmp/upload/contents/thumbs/default/Unknown.jpg', 'image', 'jpeg', 'testAudio', 'No description available'),
(4, '/tmp/upload/contents/Unknown.jpg', 'image', 'jpeg', '/tmp/upload/contents/Unknown.jpg', 'image', 'jpeg', '/tmp/upload/contents/thumbs/Unknown.jpeg', 'image', 'jpeg', 'defaultAudioCover', 'No description available'),
(5, '/tmp/upload/contents/boothill drums.mp3', 'audio', 'mpeg', '/tmp/upload/contents/thumbs/Unknown-Large.jpg', 'image', 'jpeg', '/tmp/upload/contents/thumbs/Unknown.jpg', 'image', 'jpeg', 'boothilDrums', 'No description available'),
(6, '/tmp/upload/contents/dont stop the music.mp3', 'audio', 'mpeg', '/tmp/upload/contents/Unknown.jpg', 'image', 'jpeg', '/tmp/upload/contents/thumbs/Unknown.jpeg', 'image', 'jpeg', 'dontStop', 'No description available'),
(7, '/tmp/upload/contents/lead your partner.mp3', 'audio', 'mpeg', '/tmp/upload/contents/Unknown.jpg', 'image', 'jpeg', '/tmp/upload/contents/thumbs/Unknown.jpeg', 'image', 'jpeg', 'leadYourPartner', 'No description available');

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
('test2', 'secondaryBIg', 'bug', 'yea ig', 0),
('test1', 'secondary1', 'fixed test', 'yea ig', 0),
('test1', 'secondary2', 'how to create a page', 'get good\r\n\r\n![9lana](getFileContents.php?id=2)', 3),
('test1', 'secondary2', 'markdownTest', 'hello#bighello*italicizedhello****', 1),
('test1', 'secondary2', 'markdownTest2', 'hellon#bighellon*italicizedhello*n***boldanditalic***\r\n\r\n[test1](http://localhost/works/webapp/wiki/viewPage.php?pageTitle=markdownTest)', 0),
('test1', 'secondary2', 'markdownTest3', 'hello\r\n\r\n# big hello\r\n\r\n*italicized hello*\r\n\r\n***bold and italic***', 0),
('test1', 'secondary2', 'markdownTest4', 'hello\r\n\r\n# big hello\r\n\r\n*italicized hello*\r\n\r\n***bold and italic***\r\n\r\n![9lana](getFileContents.php?id=2)\r\n\r\n:[song](getFileContents.php?id=7)\r\n\r\n\r\nyep luigi', 1);

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
(1, 'how to create a page'),
(1, 'markdownTest');

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
-- Indexes for table `forum_discussions`
--
ALTER TABLE `forum_discussions`
  ADD PRIMARY KEY (`idDiscussion`),
  ADD KEY `fk_discussion_user` (`idUser`),
  ADD KEY `fk_discussion_categories` (`primaryCategory`);

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
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email-accounts`
--
ALTER TABLE `email-accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `forum_discussions`
--
ALTER TABLE `forum_discussions`
  MODIFY `idDiscussion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `idPost` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `images-config`
--
ALTER TABLE `images-config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `images-details`
--
ALTER TABLE `images-details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  ADD CONSTRAINT `fk_discussion_categories` FOREIGN KEY (`primaryCategory`) REFERENCES `category-primary` (`primaryCategory`) ON UPDATE CASCADE,
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
