SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `shoutbox` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `shoutbox`;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `shoutbox_shouts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Guest',
  `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `time` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=42;