-- Create tables for superscan database `User_hashscan`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `account_scanfiles`
--

-- --------------------------------------------------------

--
-- Table structure for table `baseline`
--

CREATE TABLE IF NOT EXISTS `baseline` (
  `file_path` varchar(255) NOT NULL,
  `file_hash` char(40) NOT NULL,
  `file_last_mod` char(19),
  `acct` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT 'Not specified',
  PRIMARY KEY (`file_path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE IF NOT EXISTS `history` (
  `stamp`  char(19),
  `status` varchar(10) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `hash_org` char(40) DEFAULT NULL,
  `hash_new` char(40) DEFAULT NULL,
  `file_last_mod` char(19),
  `acct` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `scanned`
--

CREATE TABLE IF NOT EXISTS `scanned` (
  `scanned` char(19),
  `changes` int(11) NOT NULL DEFAULT '0',
  `elapsed` varchar( 12 ) NOT NULL DEFAULT  '0.0000',
  `iterations` MEDIUMINT NOT NULL DEFAULT  '0',
  `count_current` MEDIUMINT NOT NULL DEFAULT  '0',
  `acct` varchar(20) NOT NULL,
  PRIMARY KEY (`scanned`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

