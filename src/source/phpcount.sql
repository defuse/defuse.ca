-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 04, 2011 at 06:31 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phpcount`
--

-- --------------------------------------------------------

--
-- Table structure for table `hits`
--

CREATE TABLE IF NOT EXISTS `hits` (
  `pageid` varchar(100) NOT NULL,
  `isunique` tinyint(1) NOT NULL,
  `hitcount` int(10) unsigned NOT NULL,
  KEY `pageid` (`pageid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hits`
--


-- --------------------------------------------------------

--
-- Table structure for table `nodupes`
--

CREATE TABLE IF NOT EXISTS `nodupes` (
  `ids_hash` char(64) NOT NULL,
  `time` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`ids_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nodupes`
--

