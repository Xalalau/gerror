-- --------------------------------------------------------
-- Servidor:                     localhost
-- Versão do servidor:           10.8.3-MariaDB-1:10.8.3+maria~jammy - mariadb.org binary distribution
-- OS do Servidor:               debian-linux-gnu
-- HeidiSQL Versão:              12.0.0.6468
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `gmoderror` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `gmoderror`;

CREATE TABLE IF NOT EXISTS `config` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `value` varchar(50) DEFAULT NULL,
  UNIQUE KEY `config_key_IDX` (`key`) USING BTREE,
  KEY `config_idx_IDX` (`idx`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `config` (`key`, `value`) VALUES
	('auth', 'admin'),

CREATE TABLE IF NOT EXISTS `gm_construct_13_beta` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `is_server` tinyint(4) NOT NULL DEFAULT 0,
  `is_client` tinyint(4) NOT NULL DEFAULT 0,
  `map` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `stack` varchar(2000) NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
