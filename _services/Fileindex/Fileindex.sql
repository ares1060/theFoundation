-- phpMyAdmin SQL Dump
-- version 3.2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 14. Juni 2010 um 22:09
-- Server Version: 5.1.37
-- PHP-Version: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `perpedes`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_fileindex`
--

CREATE TABLE IF NOT EXISTS `pp_fileindex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(100) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;