-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 10, 2017 at 08:10 AM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lug_finished_h`
--

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `number` varchar(5) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `name` varchar(63) NOT NULL,
  `active_exercise` datetime DEFAULT NULL,
  `layout` varchar(63) CHARACTER SET ascii DEFAULT NULL COMMENT 'describes the layout of workstations in the room in the form of: <#rows> <#ws_row1> <#ws_row2> ...',
  PRIMARY KEY (`number`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `active_exercise` (`active_exercise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`number`, `name`, `active_exercise`, `layout`) VALUES
('2-21', 'Hannover', NULL, '3 2 1'),
('2-22', 'Frankfurt', NULL, '1 2 3 4'),
('2-23', 'Kiel', NULL, '3 1 3'),
('2-24', 'Düsseldorf', NULL, '4 4 2'),
('2-25', 'Stuttgart', NULL, '5 3 1 4 5');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_exercises` FOREIGN KEY (`active_exercise`) REFERENCES `exercises` (`start`) ON DELETE SET NULL ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
