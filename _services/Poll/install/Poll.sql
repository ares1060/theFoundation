-- phpMyAdmin SQL Dump
-- version 2.11.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 11. Februar 2011 um 18:00
-- Server Version: 5.0.41
-- PHP-Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `perpedes`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_poll_option`
--

CREATE TABLE `pp_poll_option` (
  `p_id` int(11) NOT NULL,
  `o_id` int(11) NOT NULL auto_increment,
  `title_de` varchar(255) NOT NULL,
  `desc_de` text NOT NULL,
  PRIMARY KEY  (`o_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `pp_poll_option`
--

INSERT INTO `pp_poll_option` VALUES(1, 1, 'Lamm', 'sehr guten Lammbraten');
INSERT INTO `pp_poll_option` VALUES(1, 2, 'Schwein', 'sehr sehr gutes Schwein');
INSERT INTO `pp_poll_option` VALUES(1, 3, 'Fisch', 'sehr guten Fisch');
INSERT INTO `pp_poll_option` VALUES(1, 4, 'Gem&uuml;se', 'nur Gem&uuml;se');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_poll_poll`
--

CREATE TABLE `pp_poll_poll` (
  `p_id` int(11) NOT NULL auto_increment,
  `title_de` varchar(255) NOT NULL,
  `desc_de` text NOT NULL,
  `time` datetime NOT NULL,
  `u_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `auth` int(11) NOT NULL,
  `begin_d` datetime NOT NULL,
  `end_d` datetime NOT NULL,
  PRIMARY KEY  (`p_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_poll_poll`
--

INSERT INTO `pp_poll_poll` VALUES(1, 'Men&uuml; Auswahl', 'Hier kannst Du dein gew&uuml;nschtes Men&uuml; ausw&auml;hlen.', '2011-02-11 16:23:31', 1, 1, 0, '2011-02-10 00:00:00', '2011-02-22 17:29:00');
INSERT INTO `pp_poll_poll` VALUES(2, 'Noch eine Poll', 'asdfasdfasdfasdfasdfasdf\r\nasd\r\nfa\r\nsdf\r\na\r\nsdf\r\na\r\nsdf\r\na\r\nsd', '2011-02-11 17:36:26', 1, 1, 0, '2011-02-10 17:36:29', '2011-02-26 17:36:33');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_poll_selects`
--

CREATE TABLE `pp_poll_selects` (
  `o_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY  (`o_id`,`u_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_poll_selects`
--

