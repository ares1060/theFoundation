-- phpMyAdmin SQL Dump
-- version 2.11.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. Februar 2011 um 23:55
-- Server Version: 5.0.41
-- PHP-Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `wedding_gallery`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_checklist`
--

CREATE TABLE `pp_checklist` (
  `cl_id` int(11) NOT NULL auto_increment,
  `title_de` varchar(255) NOT NULL,
  `desc_de` text NOT NULL,
  `u_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY  (`cl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_checklist_item`
--

CREATE TABLE `pp_checklist_item` (
  `cli_id` int(11) NOT NULL auto_increment,
  `cl_id` int(11) NOT NULL,
  `title_de` varchar(255) NOT NULL,
  `desc_de` text NOT NULL,
  `time` datetime NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY  (`cli_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_checklist_item_user`
--

CREATE TABLE `pp_checklist_item_user` (
  `cli_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY  (`cli_id`,`u_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
