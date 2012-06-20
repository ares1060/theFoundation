<?php
	//default user: Root:root (name:root)    	
	if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
        // delete old databases
        $sql = '
        	DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'user`;
        	DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata`;
        	DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdatagroup`;
        	DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata_group`;
        	DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata_user`;
        	DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'usergroup`;
        ';	
        $this->mysqlMultipleSetup($sql);
        }
        $sql = '-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'user`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'user` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `nick` varchar(50) NOT NULL,
			  `pwd` varchar(32) NOT NULL,
			  `hash` varchar(180) NOT NULL,
			  `group` int(11) NOT NULL,
			  `email` varchar(100) NOT NULL,
			  `activate` varchar(100) NOT NULL,
			  `status` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `nick` (`nick`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
			
			-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdata` (
			  `ud_id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(100) NOT NULL,
			  `desc` text NOT NULL,
			  `default` text NOT NULL,
			  `type` int(11) NOT NULL,
			  `visible` int(11) NOT NULL,
			  `help` text NOT NULL,
			  `g_id` int(11) NOT NULL,
			  PRIMARY KEY (`ud_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
			
			--
			-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata`
			--
			
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata` VALUES(1, \'Vorname\', \'\', \'\', 0, 1, \'\', 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata` VALUES(2, \'Nachname\', \'\', \'\', 0, 1, \'\', 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata` VALUES(3, \'Userimage\', \'\', \'1\', 3, 1, \'\', 2);
			
			-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdatagroup`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdatagroup` (
			  `g_id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(100) NOT NULL,
			  `beschreibung` text NOT NULL,
			  UNIQUE KEY `g_id` (`g_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
			
			--
			-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdatagroup`
			--
			
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdatagroup` VALUES(1, \'Daten\', \'\');
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdatagroup` VALUES(2, \'Userimage\', \'\');
			
			-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata_group`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdata_group` (
			  `g_id` int(11) NOT NULL,
			  `d_id` int(11) NOT NULL,
			  UNIQUE KEY `g_id` (`g_id`,`d_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			
			--
			-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata_group`
			--
			
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(1, 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(1, 2);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(1, 3);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(2, 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(2, 2);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(2, 3);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(3, 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(3, 2);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(3, 3);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(4, 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(4, 2);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(4, 3);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(5, 1);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(5, 2);
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(5, 3);

			-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata_user`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdata_user` (
			  `u_id` int(11) NOT NULL,
			  `d_id` int(11) NOT NULL,
			  `value` text NOT NULL,
			  UNIQUE KEY `u_id` (`u_id`,`d_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			
			-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'usergroup`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'usergroup` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(100) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
			
			--
			-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'usergroup`
			--
			
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(1, \'root\');
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(2, \'admin\');
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(3, \'user\');
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(4, \'moderator\');
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(5, \'guest\');';
        $b = $this->mysqlMultipleSetup($sql);
        
        $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'user` VALUES (1, "root", "", "a3597971769fc171e38fb92ff3cd4cc429370b618342836ff7a2eb61fe7d6f70ead7dd6586c2044d759ab962b6fbb96d48981259e592e3c79b559d84a79fe64a#me:fpeH2cc68;p9npeQ/Qemi0UQ%Wu!g4Hweu=US4JsPUxqa-Oe", 1, "", "", 1);');
        
        $db_error = !($b);
        if(!$db_error) {
        $error = array();
        
        // create Rights
        $error[] = $this->sp->ref('Rights')->addRight('User', 'can_change_viewing_user');
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'can_change_viewing_user', 1); // change viewing users is allowed to root

        $error[] = $this->sp->ref('Rights')->addRight('User', 'usercenter');
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'usercenter', 1); // authorize Root to make administer Users
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'usercenter', 2); // authorize Root to make administer Users
        
        $error[] = $this->sp->ref('Rights')->addRight('User', 'administer_user');
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'administer_user', 1); // authorize Root to create/delete/edit Users
   
        $error[] = $this->sp->ref('Rights')->addRight('User', 'administer_group');
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'administer_group', 1); // authorize Root to create/edit and see any user
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'administer_group', 2); // authorize Admin to create/edit and see any user
        $error[] = $this->sp->ref('Rights')->unauthorizeGroup('User', 'administer_group', 2, '1'); // unauthorize Admins to create/edit and see Roots    \__ only root can create
        $error[] = $this->sp->ref('Rights')->unauthorizeGroup('User', 'administer_group', 2, '2'); // unauthorize Admins to create/edit and see Admins   /   Roots and Admins
        
        $error[] = $this->sp->ref('Rights')->addRight('User', 'administer_data');
        $error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'administer_data', 1); // authorize Root to create/delete/edit userdata
        
        return true;
	} else return false;
?>