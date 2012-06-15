-- phpMyAdmin SQL Dump
-- version 2.11.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 30. April 2011 um 21:55
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
-- Tabellenstruktur für Tabelle `pp_blog_category`
--

CREATE TABLE `pp_blog_category` (
  `k_id` int(11) NOT NULL auto_increment,
  `name_de` varchar(30) default NULL,
  PRIMARY KEY  (`k_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `pp_blog_category`
--

INSERT INTO `pp_blog_category` VALUES(1, 'php');
INSERT INTO `pp_blog_category` VALUES(2, 'news');
INSERT INTO `pp_blog_category` VALUES(3, 'programming');
INSERT INTO `pp_blog_category` VALUES(4, 'fun');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_blog_entry`
--

CREATE TABLE `pp_blog_entry` (
  `e_id` int(11) NOT NULL auto_increment,
  `title_de` varchar(255) default NULL,
  `desc_de` text,
  `content_de` text,
  `author` int(11) default NULL,
  `creation_date` datetime default NULL,
  `last_edit_date` datetime default NULL,
  `status` int(11) default NULL,
  `comments` int(11) NOT NULL,
  PRIMARY KEY  (`e_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_blog_entry`
--

INSERT INTO `pp_blog_entry` VALUES(1, 'Erster Blogeintrag', 'Das ist eine Description zum ersten Blog eintrag', 'Das ist der Content des ersten Blogeintrag', 1, '2010-11-03 20:32:11', NULL, 1, 1);
INSERT INTO `pp_blog_entry` VALUES(2, 'Das ist noch ein Test', 'Hier werden [br] besonders Tags und Kategorien gesteste sowie auch grundlegende [br] [b]BBCode[/b] Techniken', 'Hier gehts noch ein bischen weiter', 1, '2010-11-04 15:41:23', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_blog_entry_category`
--

CREATE TABLE `pp_blog_entry_category` (
  `e_id` int(11) default NULL,
  `k_id` int(11) default NULL,
  KEY `e_id_idxfk_1` (`e_id`),
  KEY `k_id_idxfk` (`k_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_blog_entry_category`
--

INSERT INTO `pp_blog_entry_category` VALUES(1, 1);
INSERT INTO `pp_blog_entry_category` VALUES(2, 1);
INSERT INTO `pp_blog_entry_category` VALUES(2, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_blog_entry_tags`
--

CREATE TABLE `pp_blog_entry_tags` (
  `t_id` int(11) default NULL,
  `e_id` int(11) default NULL,
  KEY `t_id_idxfk` (`t_id`),
  KEY `e_id_idxfk` (`e_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_blog_entry_tags`
--

INSERT INTO `pp_blog_entry_tags` VALUES(2, 1);
INSERT INTO `pp_blog_entry_tags` VALUES(2, 2);
INSERT INTO `pp_blog_entry_tags` VALUES(1, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_blog_tags`
--

CREATE TABLE `pp_blog_tags` (
  `t_id` int(11) NOT NULL auto_increment,
  `name_de` varchar(255) default NULL,
  PRIMARY KEY  (`t_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_blog_tags`
--

INSERT INTO `pp_blog_tags` VALUES(1, 'php');
INSERT INTO `pp_blog_tags` VALUES(2, 'blog');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_guestbook`
--

CREATE TABLE `pp_guestbook` (
  `gbe_id` int(11) NOT NULL auto_increment,
  `author` varchar(50) NOT NULL,
  `titel` varchar(100) NOT NULL,
  `datum` datetime NOT NULL,
  `ip` varchar(12) NOT NULL,
  `status` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `homepage` varchar(50) NOT NULL,
  `inhalt` varchar(300) NOT NULL,
  PRIMARY KEY  (`gbe_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

--
-- Daten für Tabelle `pp_guestbook`
--

INSERT INTO `pp_guestbook` VALUES(1, 'Ares', 'Das ist ein Guestbooktest', '2010-04-11 17:38:12', '0.0.0.0', 1, 'a@b.com', 'apple.com', 'Das ist der Testinhalt');
INSERT INTO `pp_guestbook` VALUES(2, 'Ares', 'Noch ein Test', '2010-04-11 17:55:10', '0.0.0.0', 0, 'a@b.com', '', 'das ist noch ein lustiger inhalt');
INSERT INTO `pp_guestbook` VALUES(3, 'Ares', 'das ist ein Formtest', '2010-04-11 18:12:38', '', 0, 'a@b.com', 'google.com', 'mal schaun ob das so funktioniert');
INSERT INTO `pp_guestbook` VALUES(4, 'Ares', 'das ist ein Formtest', '2010-04-11 18:12:49', '', 0, 'a@b.com', 'google.com', 'mal schaun ob das so funktioniert');
INSERT INTO `pp_guestbook` VALUES(5, 'Ares', 'SchÃ¶nes Design', '2010-04-14 01:06:16', '', 0, 'test@me.com', '', 'Ja das ist wirklich ein schÃ¶nes Design<br />\r\nUnd das ist ein br\r\n<br />\r\nNoch ist dasja mÃ¶glich');
INSERT INTO `pp_guestbook` VALUES(8, 'test', 'test', '2010-04-15 19:36:38', '', 0, 'test', 'test', 'tets');
INSERT INTO `pp_guestbook` VALUES(10, 'test1', 'test', '2010-04-15 19:38:14', '', 0, 'test', 'test', 'tests');
INSERT INTO `pp_guestbook` VALUES(12, 'Ares', 'das ist ein titel', '2010-04-15 19:39:13', '', 0, 'a@b.com', 'homepage', 'das ist ein Inhalt');
INSERT INTO `pp_guestbook` VALUES(22, 'test', 'test', '2010-05-03 14:24:18', '', 0, 'test', 'test', 'test');
INSERT INTO `pp_guestbook` VALUES(21, 'asdf', 'asdf', '2010-05-03 14:23:10', '', 0, 'adf', 'asdf', 'asdf');
INSERT INTO `pp_guestbook` VALUES(20, 'Ich', 'das ist ein GÃ¤stebucheintrag', '2010-05-01 02:28:43', '', 0, 'ich@me.com', '', 'das ist dessen Inhalt -> haha');
INSERT INTO `pp_guestbook` VALUES(23, 'asdfjÃ¶', 'kj', '2010-05-03 18:16:04', '', 0, 'lkj', 'lkjhalsdkjfhl', 'n.asdfasdfasdfasdfa\nsdf\nasd\nf\nasd\nfa\nsdf\na\nsdf\na\nsdf\na\nsdf\na\nsdfa\nsdf\nas\ndf\nasd\nf\nasdf');
INSERT INTO `pp_guestbook` VALUES(24, 'asdfasdf', '', '2010-05-03 18:16:15', '', 1, '', '', '');
INSERT INTO `pp_guestbook` VALUES(25, 'asdfasdf', 'asdf', '2010-05-03 18:16:20', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(26, 'asdfasdf', 'asdf', '2010-05-03 18:16:24', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(27, 'asdfasdf', 'asdf', '2010-05-03 18:16:25', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(28, 'asdfasdf', 'asdf', '2010-05-03 18:16:25', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(29, 'asdfasdf', 'asdf', '2010-05-03 18:16:26', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(30, 'asdfasdf', 'asdf', '2010-05-03 18:16:27', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(31, 'asdfasdf', 'asdf', '2010-05-03 18:16:27', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(32, 'asdfasdf', 'asdf', '2010-05-03 18:16:28', '', 0, 'asdfasdf', 'qdsf', 'asdfasdfasd\nfas\ndfa\nsdf\na\nsdf\na\nsdf\nasdf');
INSERT INTO `pp_guestbook` VALUES(33, 'jaspoidufk', 'z', '2010-05-03 18:18:34', '', 1, 'pouz', 'loiu', 'k');
INSERT INTO `pp_guestbook` VALUES(34, 'jaspoidufk', 'z', '2010-05-03 18:18:35', '', 1, 'pouz', 'loiu', 'k');
INSERT INTO `pp_guestbook` VALUES(35, 'asdf', 'asdf', '2010-05-03 18:19:18', '', 1, 'asdf', 'asdf', 'asdf');
INSERT INTO `pp_guestbook` VALUES(36, 'asdf', 'asdf', '2010-05-05 16:00:26', '', 1, 'adf', 'adsf', 'asdf');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_guestbook_reply`
--

CREATE TABLE `pp_guestbook_reply` (
  `reply_to_id` int(11) NOT NULL,
  `author` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `datum` datetime NOT NULL,
  `ip` varchar(12) NOT NULL,
  `status` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `homepage` varchar(50) NOT NULL,
  `inhalt` varchar(300) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_guestbook_reply`
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


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_rating`
--

CREATE TABLE `pp_rating` (
  `id` int(11) NOT NULL,
  `group` varchar(50) NOT NULL,
  `rating` float NOT NULL,
  `rating_count` int(11) NOT NULL,
  PRIMARY KEY  (`id`,`group`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_rating`
--

INSERT INTO `pp_rating` VALUES(3, 'xxx', 3.6, 5);
INSERT INTO `pp_rating` VALUES(1, 'xxx', 2.83333, 6);
INSERT INTO `pp_rating` VALUES(5, 'xxx', 4.48149, 27);
INSERT INTO `pp_rating` VALUES(4, 'xxx', 3.4091, 22);
INSERT INTO `pp_rating` VALUES(2, 'xxx', 3.2, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_rating_ips`
--

CREATE TABLE `pp_rating_ips` (
  `id` int(11) NOT NULL,
  `group` varchar(50) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_rating_ips`
--

INSERT INTO `pp_rating_ips` VALUES(1, 'xxx', '::1', '2010-04-30 21:53:01');
INSERT INTO `pp_rating_ips` VALUES(5, 'xxx', '::1', '2010-04-30 21:52:05');
INSERT INTO `pp_rating_ips` VALUES(4, 'xxx', '::1', '2010-04-30 21:52:04');
INSERT INTO `pp_rating_ips` VALUES(1, 'xxx', '::1', '2010-04-30 21:52:01');
INSERT INTO `pp_rating_ips` VALUES(2, 'xxx', '::1', '2010-04-30 21:51:55');
INSERT INTO `pp_rating_ips` VALUES(3, 'xxx', '::1', '2010-04-30 21:51:55');
INSERT INTO `pp_rating_ips` VALUES(2, 'xxx', '::1', '2010-04-30 21:53:03');
INSERT INTO `pp_rating_ips` VALUES(3, 'xxx', '::1', '2010-04-30 21:53:04');
INSERT INTO `pp_rating_ips` VALUES(4, 'xxx', '::1', '2010-04-30 21:53:05');
INSERT INTO `pp_rating_ips` VALUES(5, 'xxx', '::1', '2010-04-30 21:53:05');
INSERT INTO `pp_rating_ips` VALUES(1, 'xxx', '::1', '2010-05-01 02:14:42');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_services`
--

CREATE TABLE `pp_services` (
  `name` varchar(50) NOT NULL,
  `main_file` varchar(50) NOT NULL,
  `core` tinyint(4) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `pp_services`
--

INSERT INTO `pp_services` VALUES('Htmlwrapper', '', 0);
INSERT INTO `pp_services` VALUES('Pagina', '', 0);
INSERT INTO `pp_services` VALUES('Guestbook', '', 0);
INSERT INTO `pp_services` VALUES('Menu', '', 0);
INSERT INTO `pp_services` VALUES('Rating', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_user`
--

CREATE TABLE `pp_user` (
  `id` int(11) NOT NULL auto_increment,
  `nick` varchar(50) NOT NULL,
  `pwd` varchar(32) NOT NULL,
  `group` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_user`
--

INSERT INTO `pp_user` VALUES(1, 'Test', '912ec803b2ce49e4a541068d495ab570', 0, '');
INSERT INTO `pp_user` VALUES(2, 'Test1', '5a105e8b9d40e1329780d62ea2265d8a', 0, '');
