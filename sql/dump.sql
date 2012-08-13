-- Server version: 5.1.57-log
-- PHP Version: 5.3.5

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bill`
--

-- --------------------------------------------------------

--
-- Table structure for table `balance`
--

CREATE TABLE IF NOT EXISTS `balance` (
  `uid` int(10) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `locked` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `balance_history`
--

CREATE TABLE IF NOT EXISTS `balance_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `bill_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `old_balance` decimal(10,2) NOT NULL,
  `uid` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `robokassa_history`
--

CREATE TABLE IF NOT EXISTS `robokassa_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `bid` bigint(20) DEFAULT NULL,
  `sum` decimal(10,2) DEFAULT NULL,
  `bill_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `currency` text CHARACTER SET utf8 COLLATE utf8_bin,
  `account` text CHARACTER SET utf8 COLLATE utf8_bin,
  `cur_sum` decimal(10,2) DEFAULT NULL,
  `our_currency` text CHARACTER SET utf8 COLLATE utf8_bin,
  `paid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
