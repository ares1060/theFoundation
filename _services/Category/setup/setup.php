<?php
//Db-tables
if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
          // delete old databases
$sql = '
        DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'category`;
		DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'category_tree`;
    ';
	$this->mysqlMultipleSetup($sql);
}
$sql = '
		--
		-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'category`
		--
		
		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'category` (
		  `c_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  `webname` varchar(100) NOT NULL,
		  `service_root` int(1) NOT NULL,
		  `img` int(11) NOT NULL,
		  `status` int(11) NOT NULL,
		  `desc` text NOT NULL,
		  PRIMARY KEY (`c_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;
		
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category` VALUES(1, \'root\', \'root\', 0);
		
		--
		-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'category_tree`
		--
		
		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'category_tree` (
		  `c_id` int(11) NOT NULL,
		  `left` int(11) NOT NULL,
		  `right` int(11) NOT NULL,
  		  `parent` int(11) NOT NULL,
  		  UNIQUE KEY `c_id` (`c_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				';
$error = $this->mysqlMultipleSetup($sql);

/** Rights **/
	// administer category
	$this->sp->ref('Rights')->addRight('Category', 'administer_category');
	$this->sp->ref('Rights')->authorizeGroup('Category', 'administer_category', User::getUserGroup('root'));
	$this->sp->ref('Rights')->authorizeGroup('Category', 'administer_category', User::getUserGroup('admin'));
	
	$this->sp->ref('Gallery')->newAlbum('category', '', Gallery::STATUS_SERVICE_ALBUM);
	
	$this->sp->ref('Gallery')->newFolder('category_Shop', $this->config['category_album_id'], '', Gallery::STATUS_ONLINE);
	$this->sp->ref('Gallery')->newFolder('category_Blog', $this->config['category_album_id'], '', Gallery::STATUS_ONLINE);
	
/** test daten **/
$sql = '
		--
		-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'category`
		--
		
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category` VALUES(1, \'root\', \'root\', 0, 0);
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category` VALUES(2, \'Shop\', \'shop\', 1, 0);
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category` VALUES(3, \'Blog\', \'blog\', 1, 0);

		--
		-- Daten fŸr Tabelle `pp_category_tree`
		--
		
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category_tree` VALUES(1, 1, 6, 0);
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category_tree` VALUES(2, 2, 3, 1);
		INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category_tree` VALUES(3, 4, 5, 1);
		';

$error = $this->mysqlMultipleSetup($sql);
?>