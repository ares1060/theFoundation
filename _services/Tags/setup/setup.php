<?php
//Db-tables
if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
          // delete old databases
$sql = '
        DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'tags`;
		DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'tags_link`;
        DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup`;
    ';
	$this->mysqlMultipleSetup($sql);
}
$sql = '
		--
		-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'tags`
		--
		
		
		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'tags` (
		  `t_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  PRIMARY KEY (`t_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		
		-- --------------------------------------------------------
		
		--
		-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'tags_link`
		--
		
		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'tags_link` (
		  `t_id` int(11) NOT NULL,
		  `service` varchar(100) NOT NULL,
		  `param` varchar(100) NOT NULL,
		  UNIQUE KEY `t_id` (`t_id`,`service`,`param`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
$error = $this->mysqlMultipleSetup($sql);

/** Rights **/

	$this->sp->ref('Rights')->addRight('Tags', 'administer_tags');
	$this->sp->ref('Rights')->authorizeGroup('Tags', 'administer_tags', User::getUserGroup('root'));
	$this->sp->ref('Rights')->authorizeGroup('Tags', 'administer_tags', User::getUserGroup('admin'));
        	
/** test daten **/

var_dump($error);
	?>