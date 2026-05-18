CREATE DATABASE IF NOT EXISTS `smiwiki`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `smiwiki`;
CREATE USER IF NOT EXISTS 'smiwiki'@'localhost' IDENTIFIED BY 'segredo';
CREATE USER IF NOT EXISTS 'smiwiki'@'%'         IDENTIFIED BY 'segredo';

GRANT ALL PRIVILEGES ON `smiwiki`.* TO 'smiwiki'@'localhost';
GRANT ALL PRIVILEGES ON `smiwiki`.* TO 'smiwiki'@'%';

FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS `auth_basic` (
    `idUser` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(60) NOT NULL UNIQUE,
    `email` VARCHAR(128) NOT NULL UNIQUE,
    `password` VARCHAR(64) NOT NULL,
    `user_type`     ENUM(
                        'convidado',
                        'utilizador',
                        'editor',
                        'simpatizante',
                        'moderador',
                        'administrador'
                    )               NOT NULL
                    DEFAULT 'utilizador',
 
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `is_banned`     TINYINT(1)      NOT NULL DEFAULT 0,
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
 
 
CREATE TABLE IF NOT EXISTS `auth_challenge` (
    `idUser` INT NOT NULL,
    `registerDate` TIMESTAMP NOT NULL,
    `challenge` VARCHAR(32) NOT NULL,
 
    FOREIGN KEY (`idUser`) REFERENCES `smi`.`auth-basic` (`idUser`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;