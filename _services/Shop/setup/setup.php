<?php
//Db-tables
if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
          // delete old databases
$sql = '
        DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'shop_countries`;
		DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'shop_products`;
    ';
	$this->mysqlMultipleSetup($sql);
}
$sql = '
		--
		-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'shop_countries`
		--
		
		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'shop_countries` (
		  `c_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(300) NOT NULL,
		  `mwst` int(11) NOT NULL COMMENT \'%\',
		  PRIMARY KEY (`c_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


		--
		-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'shop_products`
		--
		
		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'shop_products` (
		  `p_id` int(11) NOT NULL AUTO_INCREMENT,
		  `status` int(11) NOT NULL,
		  `name` varchar(300) NOT NULL,
		  `desc` text NOT NULL,
		  `price` double NOT NULL,
		  `stock` int(11) NOT NULL,
		  `weight` double NOT NULL COMMENT \'g\',
		  `datum` int(11) NOT NULL,
		  `u_id` int(11) NOT NULL COMMENT \'creator\',
		  `cat` int(11) NOT NULL,
		  `download` int(1) NOT NULL,
		  `filesize` int(11) NOT NULL,
		  `file_hash` varchar(32) NOT NULL,
		  `dimensions` varchar(50) NOT NULL COMMENT \'mm (format: tiefe-hoehe-breite)\',
		  `stock_nr` varchar(100) NOT NULL,
		  `t_nr` int(11) NOT NULL COMMENT \'tax id\',
  		  `img_id` int(11) NOT NULL,		  
  		  PRIMARY KEY (`p_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

		';
$error = $this->mysqlMultipleSetup($sql);

	/** Rights **/
	// administer category
	$this->sp->ref('Rights')->addRight('Shop', 'administer_product');
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'administer_product', User::getUserGroup('root'));
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'administer_product', User::getUserGroup('admin'));
	
	$this->sp->ref('Rights')->addRight('Shop', 'add_product');
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'add_product', User::getUserGroup('root'));
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'add_product', User::getUserGroup('admin'));
	
	$this->sp->ref('Rights')->addRight('Shop', 'administer_orders');
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'administer_orders', User::getUserGroup('root'));
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'administer_orders', User::getUserGroup('admin'));
	
	$this->sp->ref('Rights')->addRight('Shop', 'administer_others');
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'administer_others', User::getUserGroup('root'));
	$this->sp->ref('Rights')->authorizeGroup('Shop', 'administer_others', User::getUserGroup('admin'));
        	
	/** daten **/
	$this->sp->ref('Gallery')->newAlbum('shop', '', Gallery::STATUS_SERVICE_ALBUM);
	$this->sp->ref('Gallery')->newFolder('wysiwyg', $this->config['gallery_album_id'], '', Gallery::STATUS_ONLINE);
	
	/** test daten **/
	$this->sp->ref('Gallery')->newFolder('product_1', $this->config['gallery_album_id'], '', Gallery::STATUS_ONLINE);
	
	$sql = '
			INSERT INTO `pp_shop_products` VALUES(1, 0, \'Rucksack | Hund\', \'Ein sch&ouml;ner Hunderucksack\', 45, 2, 300, 0, 1, 43, 0, 0, \'\', \'400\', 0, 0);--
	';
	$error = $this->mysqlMultipleSetup($sql, ';--');

?>