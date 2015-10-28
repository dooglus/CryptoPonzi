-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 12 Lut 2014, 17:02
-- Server version: 5.5.35-0+wheezy1
-- PHP Version: 5.4.4-14+deb7u7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `max`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `amount` DECIMAL(15 , 8 ) NOT NULL,
    `topay` DECIMAL(15 , 8 ) NOT NULL,
    `address` VARCHAR(64) NOT NULL,
    `state` INT(11) NOT NULL DEFAULT '0',
    `tx` VARCHAR(255) NOT NULL,
    `out` VARCHAR(255) NOT NULL,
    `date` INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARSET=LATIN1 AUTO_INCREMENT=36;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
