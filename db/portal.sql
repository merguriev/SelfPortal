-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 01, 2017 at 01:03 PM
-- Server version: 10.1.22-MariaDB-
-- PHP Version: 7.1.3-3+deb.sury.org~yakkety+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portal`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `addsite` (IN `site_name` TEXT, IN `rhost` TEXT, IN `rport` INT, IN `user_id` INT, IN `domain_id` INT, IN `stop_date` DATE)  MODIFIES SQL DATA
BEGIN
    DECLARE ifexsists INT DEFAULT 0;
    SELECT COUNT(*) into ifexsists FROM (SELECT IP & 0xffffffff ^ ((0x1 << ( 32 - Mask) ) -1 ) `from_ip`, IP | ((0x100000000 >> Mask ) -1) `to_ip` from blacklist HAVING INET_ATON(rhost)>`from_ip` and INET_ATON(rhost)<`to_ip`) AS T;
    IF ifexsists = 0 THEN INSERT INTO `proxysites` (`site_id`,`site_name`,`rhost`,`rport`,`user_id`,`domain_id`,`stop_date`,`status`) VALUES(NULL,site_name,rhost,rport,user_id,domain_id,stop_date,"Enabled"); END IF;
  END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `checkip` (IN `rhost` TEXT)  MODIFIES SQL DATA
BEGIN DECLARE ifexsists INT DEFAULT 0; SELECT COUNT(*) into ifexsists FROM (SELECT IP & 0xffffffff ^ ((0x1 << ( 32 - Mask) ) -1 ) `from_ip`, IP | ((0x100000000 >> Mask ) -1) `to_ip` from blacklist HAVING INET_ATON(rhost)>`from_ip` and INET_ATON(rhost)<`to_ip`) AS T; IF ifexsists = 0 THEN SELECT "false" AS result;ELSE SELECT "true" AS result; END IF; END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updatesite` (IN `site_name` TEXT, IN `rhost` TEXT, IN `rport` INT, IN `domain_id` INT, IN `stop_date` DATE, IN `site_id` INT)  MODIFIES SQL DATA
BEGIN
    DECLARE ifexsists INT DEFAULT 0;
    SELECT COUNT(*) into ifexsists FROM (SELECT IP & 0xffffffff ^ ((0x1 << ( 32 - Mask) ) -1 ) `from_ip`, IP | ((0x100000000 >> Mask ) -1) `to_ip` from blacklist HAVING INET_ATON(rhost)>`from_ip` and INET_ATON(rhost)<`to_ip`) AS T;
    IF ifexsists = 0 THEN
      UPDATE `proxysites` SET `site_name`= site_name,`rhost`= rhost,`rport`= rport,`domain_id`=domain_id,`stop_date`= stop_date WHERE `proxysites`.`site_id`= site_id;
    END IF;
  END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `blacklist`
--

CREATE TABLE `blacklist` (
  `ip_id` int(6) UNSIGNED NOT NULL,
  `IP` int(8) UNSIGNED NOT NULL,
  `Mask` tinyint(2) NOT NULL DEFAULT '32'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `blacklist`
--

INSERT INTO `blacklist` (`ip_id`, `IP`, `Mask`) VALUES
(1, 3232235520, 24);

-- --------------------------------------------------------

--
-- Table structure for table `domains`
--

CREATE TABLE `domains` (
  `domain_id` int(6) UNSIGNED NOT NULL,
  `domain` varchar(60) DEFAULT NULL,
  `shared` tinyint(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `proxysites`
--

CREATE TABLE `proxysites` (
  `site_id` int(6) UNSIGNED NOT NULL,
  `site_name` varchar(60) NOT NULL,
  `rhost` varchar(16) NOT NULL,
  `rport` varchar(5) NOT NULL,
  `user_id` int(6) NOT NULL,
  `domain_id` int(6) NOT NULL,
  `stop_date` date NOT NULL,
  `status` varchar(16) DEFAULT 'Enabled'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `public_keys`
--

CREATE TABLE `public_keys` (
  `key_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `public_key` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(6) UNSIGNED NOT NULL,
  `SID` text NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `department` text,
  `publickey` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vms`
--

CREATE TABLE `vms` (
  `vm_id` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `exp_date` date NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'Enabled'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`ip_id`);

--
-- Indexes for table `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`domain_id`),
  ADD UNIQUE KEY `domain` (`domain`);

--
-- Indexes for table `proxysites`
--
ALTER TABLE `proxysites`
  ADD PRIMARY KEY (`site_id`),
  ADD UNIQUE KEY `site_name` (`site_name`,`domain_id`) USING BTREE;

--
-- Indexes for table `public_keys`
--
ALTER TABLE `public_keys`
  ADD PRIMARY KEY (`key_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vms`
--
ALTER TABLE `vms`
  ADD PRIMARY KEY (`vm_id`(256));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blacklist`
--
ALTER TABLE `blacklist`
  MODIFY `ip_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `domains`
--
ALTER TABLE `domains`
  MODIFY `domain_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;
--
-- AUTO_INCREMENT for table `proxysites`
--
ALTER TABLE `proxysites`
  MODIFY `site_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `public_keys`
--
ALTER TABLE `public_keys`
  MODIFY `key_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
