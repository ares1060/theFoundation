<?php
	require_once($GLOBALS['config']['root'].'_services/eFiling/model/eFiling_form.php');
	require_once($GLOBALS['config']['root'].'_services/eFiling/model/eFiling_datagroup.php');
	require_once($GLOBALS['config']['root'].'_services/eFiling/model/eFiling_data.php');
	require_once($GLOBALS['config']['root'].'_services/eFiling/model/eFiling_filing.php');
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class eFiling extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
    	private $forms;
    	
    	const TYPE_STRING = 0;
    	const TYPE_INT = 1;
    	const TYPE_TEXTFIELD = 2;
    	const TYPE_DATE = 3;
    	const TYPE_CHECK = 4;
    	const TYPE_SOZNR = 5;
        const TYPE_TEXT = 6;
        const TYPE_EMAIL = 7;
        
        const STATUS_GESCHICKT = 0;
        const STATUS_BESTAETIGT = 1;
        const STATUS_STORNIERT = 2;
    	
        function __construct(){
        	$this->name = 'eFiling';
        	$this->config_file = $GLOBALS['config']['root'].'_services/eFiling/config.eFiling.php';
            parent::__construct();
        }
        
        /****************************** standard functions ******************************/
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$hash = isset($args['hash']) ? $args['hash'] : '';
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';

        	switch($chapter){
        		case 'view_form':
        			return $this->tplRenderForm($id);
        			break;
        		case 'view_group':
        			return $this->tplRenderDatagroup($id);
        			break;
        		case 'view_thanks':
        			return $this->tplRenderThanks($hash);
        			break;
        		default:
        			return '';
        			break;
        	}
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::admin()
         */
        public function admin($args){
        	$action = isset($args['action']) ? $args['action'] : '';
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$form = isset($args['form']) ? $args['form'] : -1;
        	$group = isset($args['group']) ? $args['group'] : -1;
        	$page = isset($args['page']) ? $args['page'] : '';
        	$order = isset($args['order']) ? $args['order'] : '';
        	$status = isset($args['status']) ? $args['status'] : '';
        	
        	switch($chapter) {
        		/* ---- filings --- */
        		case 'edit_filing':
        			return $this->tplAdminEditFiling($id);
        			break;
        		case 'filings':
        			return $this->tplAdminFilings($page, $form);
        			break;
        		/* ---- forms --- */
        		case 'forms':
        			return $this->tplAdminForms($page);
        			break;
        		case 'new_form':
        			return $this->tplAdminNewForm();
        			break;
        		case 'edit_form':
        			return $this->tplAdminEditForm($id);
        			break;
        		/* ---- group --- */
        		case 'group':
        			return $this->tplAdminGroup($page);
        			break;
        		case 'view_group':
        			return $this->tplRenderDatagroup($id);
        			break;
        		case 'edit_group':
        			return $this->tplAdminEditGroup($id);
        			break;
        		case 'new_group':
        			return $this->tplAdminNewGroup();
        			break;
        		/* ---- data --- */
        		case 'data':
        			return $this->tplAdminData($page);
        			break;
        		case 'new_data':
        			return $this->tplAdminNewData();
        			break;
        		case 'edit_data':
        			return $this->tplAdminEditData($id, $group);
        			break;
        		/* ---- stuff --- */
        		case 'get_availableDataGroups':
        			return $this->tplAvailableDataGroups($form);
        			break;
        		default:
        			switch($action) {
        				case 'moveGroupInForm':
        					return $this->moveGroupInForm($id, $form, $order);
        					break;
        				case 'moveDataInGroup':
        					return $this->moveDataInGroup($id, $group, $order);
        					break;
        				case 'addGroupToForm':
        					return $this->addGroupToForm($id, $form);
        					break;
        				case 'deleteGroupFromForm':
        					return $this->deleteGroupFromForm($id, $form);
        					break;
        				case 'deleteData':
        					return $this->deleteData($id);
        					break;
        				case 'deleteGroup':
        					return $this->deleteGroup($id);
        					break;
        				case 'deleteForm':
        					return $this->deleteForm($id);
        					break;
        				case 'set_filing_status':
        					return $this->setFilingStatus($id, $status);
        					break;
        				default:
        					return $this->tplAdmin();
        					break;
        			}
        	}
        	
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::run()
         */
        public function run($args){
            return false;
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::data()
         */
        public function data($args){
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	//Db-tables
       	 	if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
          		// delete old databases
        		$sql = '
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_data`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_filings`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_filing_data`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_form`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup`;
        		';
        		$this->mysqlMultipleSetup($sql);
        	}
        	
        	$sql = '-- --------------------------------------------------------
					--
					-- Tabellenstruktur für Tabelle `pp_efiling_data`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'efiling_data` (
					  `d_id` int(11) NOT NULL AUTO_INCREMENT,
					  `g_id` int(11) NOT NULL,
					  `name` varchar(300) NOT NULL,
					  `info` text NOT NULL,
					  `type` int(11) NOT NULL,
					  `public` int(1) NOT NULL,
					  `order` int(11) NOT NULL,
  					  `send` int(1) NOT NULL,
  					  `needed` int(1) NOT NULL,
  					  PRIMARY KEY (`d_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur für Tabelle `pp_efiling_datagroup`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` (
					  `g_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(300) NOT NULL,
					  `beschreibung` text NOT NULL,
					  PRIMARY KEY (`g_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur für Tabelle `pp_efiling_filings`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'efiling_filings` (
					  `ff_id` int(11) NOT NULL AUTO_INCREMENT COMMENT \'filing id\',
					  `f_id` int(11) NOT NULL COMMENT \'form id\',
					  `datum` int(11) NOT NULL,
					  `status` int(1) NOT NULL,
					  `secbckup` text NOT NULL,
					  `filing_hash` varchar(56) NOT NULL,
					  `preview` text NOT NULL,
					  PRIMARY KEY (`ff_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur für Tabelle `pp_efiling_form`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'efiling_form` (
					  `f_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(300) NOT NULL,
					  `beschreibung` text NOT NULL,
					  `info` text NOT NULL,
					  `from` int(11) NOT NULL,
					  `to` int(11) NOT NULL,
					  `preview` varchar(300) NOT NULL,
					  PRIMARY KEY (`f_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur für Tabelle `pp_efiling_form_datagroup`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` (
					  `f_id` int(11) NOT NULL,
					  `g_id` int(11) NOT NULL,
					  `order` int(11) NOT NULL,
					  UNIQUE KEY `f_id` (`f_id`,`g_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
        	
        	$error = $this->mysqlMultipleSetup($sql);
        	
        	// Rights
        	$this->sp->ref('Rights')->addRight('eFiling', 'administer_forms');
        	$this->sp->ref('Rights')->authorizeGroup('eFiling', 'administer_forms', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('eFiling', 'administer_forms', User::getUserGroup('admin'));
        	
        	$this->sp->ref('Rights')->addRight('eFiling', 'administer_filings');
        	$this->sp->ref('Rights')->authorizeGroup('eFiling', 'administer_filings', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('eFiling', 'administer_filings', User::getUserGroup('admin'));
        	
        	$this->sp->ref('Rights')->addRight('eFiling', 'delete_filings');
        	$this->sp->ref('Rights')->authorizeGroup('eFiling', 'delete_filings', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('eFiling', 'delete_filings', User::getUserGroup('admin'));
        	
        	/** test daten **/
        	$sql = '       	
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form` VALUES(1, \'SoLa 2012\', \'Das ist ein &auml;&auml;&uuml;&uuml;&szlig;&szlig;\r\n\', \'\', 1325376090, 1335827130, \'\');--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form` VALUES(2, \'SoLa 2011\', \'\', \'\', 1299629000, 1299801804, \'\');--
				
				
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(1, 1, \'Familienname\', \'\', 0, 0, 0, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(2, 1, \'Vorname\', \'\', 0, 0, 1, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(3, 1, \'Geburtsdatum\', \'\', 3, 0, 2, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(4, 1, \'Adresse\', \'\', 0, 0, 3, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(5, 1, \'PLZ\', \'\', 1, 0, 4, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(6, 1, \'Ort\', \'\', 0, 0, 5, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(7, 2, \'kann schwimmen\', \'\', 4, 0, 1, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(8, 2, \'ist FSME geimpft\', \'\', 4, 0, 2, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(9, 2, \'Mein Kind\', \'\', 0, 6, 0, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(17, 1, \'Telefonnummer\', \'\', 0, 0, 6, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(20, 1, \'Mitversichert bei\', \'\', 0, 0, 11, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(21, 1, \'Sozialvers. Nr.\', \'\', 5, 0, 10, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(22, 1, \'Krankenkasse\', \'\', 0, 0, 13, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(23, 1, \'Notfallsstelefonnr.\', \'\', 0, 0, 9, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(24, 1, \'Sozialvers. Nr.\', \'\', 5, 0, 12, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(25, 2, \'ist Tetanus geimpft\', \'0\', 4, 0, 14, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(26, 2, \'hat Asthma\', \'0\', 4, 0, 14, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(27, 2, \'ist Diabetiker\', \'0\', 4, 0, 15, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(28, 2, \'muss regelm&auml;&szlig;ig Medikamente nehmen\', \'&nbsp;\', 4, 0, 16, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(29, 2, \'ist allergisch gegen\', \'0\', 2, 0, 17, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(30, 2, \'sonstige Informationen\', \'0\', 2, 0, 18, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(31, 1, \'Disclaimer\', \'Ich nehme mit meiner Unterschrift zur Kenntnis, dass die Lagerleitung fü&uuml;r Krankheit, Unfall und Sachbesch&auml;digung, die durch eigenm&auml;chtiges Handeln meines Kindes oder durch h&ouml;here Gewalt entstanden sind, keine Haftung &uuml;bernimmt.\r\nF&uuml;r daraus resultierende Kosten trage ich selbst die volle Haftung.\r\nWeiters bin ich damit einverstanden, dass mein Kind auf dem Lager mit Privatfahrzeugen bef&ouml;rdert wird.\r\nIch akzeptiere vertrauensvoll die Anordnungen der Lagerleitung die mein Kind betreffen, auch dann, wenn es das Lager auf meine Kosten verlassen m&uuml;sste.\', 6, 0, 14, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(32, 12, \'Anmeldetext\', \'Die Anmeldung zum SoLa2011 erfolgt verbindlich durch die &Uuml;berweisung des Gesamtbetrags.<br />\r\n<br />\r\nBitte den Anmeldeabschnitt m&ouml;glichst bald im JS-Heim abgeben!\', 6, 0, 1, 0, 0, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` VALUES(33, 1, \'EMail\', \'\', 7, 0, 2, 1, 0);--
				
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` VALUES(1, \'Anmeldung\', \'Grunddaten f&uuml;r eine Anmeldung auf ein Sommerlager/Jugendlager oder Wildegg.\');--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` VALUES(2, \'Weitere Informationen\', \'FSME, schwimmen, etc\');--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` VALUES(11, \'Kleingedrucktes\', \'&nbsp;\');--
				
				
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` VALUES(1, 1, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` VALUES(1, 2, 1);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` VALUES(2, 1, 0);--
				INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` VALUES(2, 2, 1);--
			';
        	
        	$error = $error && $this->mysqlMultipleSetup($sql, ';--');
        	
        	return $error;
        }
        
        public function handleAdminPost() {
        	$action = isset($_POST['action']) ? $_POST['action'] : '';
        	
        	//print_r($_POST);
        	
        	switch($action) {
        		case 'edit_form':
        			return $this->executeEditForm();
        			break;
        		case 'edit_group':
        			return $this->executeEditGroup();
        			break;
        		case 'edit_data':
        			return $this->executeEditData();
        			break;
        		case 'new_group':
        			return $this->executeNewGroup();
        			break;
        		case 'new_form':
        			return $this->executeNewForm();
        			break;
        		case 'new_data':
        			return $this->executeNewData();
        			break;
        	}
        }
        
        /****************************** end of standard functions ******************************/
        /* ============================  Executers  ============================  */
        public function executeNewFiling() {
        	if(isset($_POST['ef_confirm']) && $_POST['ef_confirm'] == 'yes' && isset($_POST['link'])){
	        	if(isset($_POST['ef_form_id']) && isset($_POST['ef_data']) && is_array($_POST['ef_data'])){
	        		$form = $this->getForm($_POST['ef_form_id']);
	        		
	        		$error = array();
	        		$notify = array();
	        		
	        		$hash = $this->sp->ref('TextFunctions')->hashString(microtime().$_POST['ef_form_id'], $this->sp->ref('TextFunctions')->generatePassword(20, 10, 2, 0), 'sha1');
	        		
	        		$filing_id = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_filings` (f_id, datum, status, filing_hash) 
	        										VALUES ("'.mysql_real_escape_string($_POST['ef_form_id']).'", "'.time().'", "'.self::STATUS_GESCHICKT.'", "'.mysql_real_escape_string($hash).'")');
	        		
	        		if($filing_id !== false){
	        			$backup = '';
	        			$preview = '';
	        			
	        			/* -- generate backup (real Filing text) ---*/
		        		foreach($form->getContent() as $dg){
		        			$backup .= $this->tplRenderDatagroup($dg->getId(), 'confirmation');
		        		}
		        		/* -- generate Preview -- */
		        		foreach($form->getPreview() as $p){
		        			$d = $this->getData($p);
							$val = isset($_POST['ef_data'][$d->getId()]) ? $_POST['ef_data'][$d->getId()] : null;
							
		        			switch($d->getType()){
		        				case self::TYPE_CHECK:
			        					$val == ($val!=null && $val == 'on') ? '1' : '0';
			        					break;
		        			}
		        			
		        			$preview .= str_replace(array('{val}', '{id}', '{name}'),
		        								array($val, $d->getId(), $d->getName()), $this->config['standard_preview_replace']);
		        			
		        		}
		        		
		        		/* -- search for errors --*/
		        		foreach($this->getDataForDatagroup($dg->getId()) as $d){
		        				
		        			$val = isset($_POST['ef_data'][$d->getId()]) ? $_POST['ef_data'][$d->getId()] : null;
		        			switch($d->getType()){
		        				case self::TYPE_TEXT:
		        					// do nothing
		        					break;
		        				case self::TYPE_INT:
		        					if($val == '' || $val != (int)$val) {
		        						$this->_msg(str_replace(array('{name}'), array($d->getName()), $this->_('_wrong val (int)')), Messages::ERROR);
		        						$error[] = true;
		        					}
		        					break;
		        				case self::TYPE_DATE:
		        					//TODO: checkDate
		        					break;
		        				case self::TYPE_EMAIL:
		        					if($val == '' || $this->sp->ref('TextFunctions')->isEMail($val)){
		        						$this->_msg(str_replace(array('{name}'), array($d->getName()), $this->_('_wrong val (email)')), Messages::ERROR);
		        						$error[] = true;
		        					}
		        					break;
		        				case self::TYPE_SOZNR:
		        					$val = $this->sp->ref('TextFunctions')->renderUmlaute($val);
		        					if($val == ''){
		        						$this->_msg(str_replace(array('{name}'), array($d->getName()), $this->_('_wrong val (soznr)')), Messages::ERROR);
		        						$error[] = true;
		        					}
		        					break;
		        				case self::TYPE_STRING:
		        					$val = $this->sp->ref('TextFunctions')->renderUmlaute($val);
		        					if($val == ''){
		        						$this->_msg(str_replace(array('{name}'), array($d->getName()), $this->_('_wrong val (string)')), Messages::ERROR);
		        						$error[] = true;
		        					}
		        					break;
		        				case self::TYPE_TEXTFIELD:
		        					$val = $this->sp->ref('TextFunctions')->renderUmlaute($val);
		        					if($val == ''){
		        						$this->_msg(str_replace(array('{name}'), array($d->getName()), $this->_('_wrong val (string)')), Messages::ERROR);
		        						$error[] = true;
		        					}
		        					break;
		        				default:
		        					break;
		        				
		        			}
		        		}
		        		$this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_filings` 
		        							SET `secbckup`="'.mysql_real_escape_string($backup).'",
		        							`preview`="'.mysql_real_escape_string($preview).'"
		        							WHERE ff_id="'.mysql_real_escape_string($filing_id).'"');
		        		
						if(in_array(true, $error)) {
		        			$this->deleteFiling($filing_id);
		        			$this->_msg($this->_('_Filing error'), Messages::ERROR);
		        			unset($_POST['ef_confirm']); // to display the form page 
		        			return false;
		        		} else {
		        			$this->_msg($this->_('_Filing successfull'), Messages::INFO);
		        			//echo str_replace('{hash}', substr($hash, 0, strpos($hash, '#')), $_POST['link']);
		        			
		        			//TODO: notify per email
		        			$mail = '';
		        			foreach($notify as $n){
		        				$mail .= $n.'+';
		        			}
		        			$this->debugVar($notify);
		        			$this->debugVar('TODO: Mailto '.$mail);
		        			header('Location: '.str_replace(array('{hash}', '{id}'), 
		        							array(substr($hash, 0, strpos($hash, '#')), $filing_id)
		        							, $_POST['link'])); // locate to the hash link
		        			return true;
		        		}
	        		} else {
	        			return false;
	        		}
	        	}
        	}
        }
        public function executeEditForm() {
        	if(isset($_POST['id']) && 
        		isset($_POST['ef_from']) &&
        		isset($_POST['ef_to']) &&
        		isset($_POST['ef_name']) &&
        		isset($_POST['ef_desc']) &&
        		$this->checkRight('administer_forms')){
        		
        			$id = $_POST['id'];
        			$from = $this->sp->ref('TextFunctions')->getTimeFromString($_POST['ef_from'], '/(?P<h>\d+):(?P<min>\d+):(?P<s>\d+) \| (?P<d>\d+).(?P<m>\d+).(?P<y>\d+)/');
        			$to = $this->sp->ref('TextFunctions')->getTimeFromString($_POST['ef_to'], '/(?P<h>\d+):(?P<min>\d+):(?P<s>\d+) \| (?P<d>\d+).(?P<m>\d+).(?P<y>\d+)/');
        			$name = $_POST['ef_name'];
        			$desc = $_POST['ef_desc'];
        			
        			if($this->editForm($id, $from, $to, $name, $desc)){
        				$this->_msg($this->_('_Form update success'), Messages::INFO);
        				header('Location: '.$GLOBALS['abs_root'].'/_admincenter/tf/efiling/');
        				break;
        			} else {
        				$this->_msg($this->_('_Form update error'), Messages::ERROR);
        				return true;
        			}
        	}
        }
    	public function executeEditGroup() {
        	if(isset($_POST['id']) && 
        		isset($_POST['ef_name']) &&
        		isset($_POST['ef_desc']) &&
        		$this->checkRight('administer_forms')){
        		
        			$id = $_POST['id'];
        			$name = $_POST['ef_name'];
        			$desc = $_POST['ef_desc'];
        			
        			if($this->editGroup($id, $name, $desc)){
        				$this->_msg($this->_('_Group update success'), Messages::INFO);
        				header('Location: '.$GLOBALS['abs_root'].'/_admincenter/tf/efiling/');
        				break;
        			} else {
        				$this->_msg($this->_('_Group update error'), Messages::ERROR);
        				return true;
        			}
        	}
        }
        public function executeNewForm() {
        	if(isset($_POST['ef_from']) &&
        		isset($_POST['ef_to']) &&
        		isset($_POST['ef_name']) &&
        		isset($_POST['ef_desc']) &&
        		$this->checkRight('administer_forms')){
        		
        			$from = $this->sp->ref('TextFunctions')->getTimeFromString($_POST['ef_from'], '/(?P<h>\d+):(?P<min>\d+):(?P<s>\d+) \| (?P<d>\d+).(?P<m>\d+).(?P<y>\d+)/');
        			$to = $this->sp->ref('TextFunctions')->getTimeFromString($_POST['ef_to'], '/(?P<h>\d+):(?P<min>\d+):(?P<s>\d+) \| (?P<d>\d+).(?P<m>\d+).(?P<y>\d+)/');
        			$name = $_POST['ef_name'];
        			$desc = $_POST['ef_desc'];
        			
        			if($this->newForm($from, $to, $name, $desc)){
        				$this->_msg($this->_('_Form insert success'), Messages::INFO);
        				header('Location: '.$GLOBALS['abs_root'].'/_admincenter/tf/efiling/');
        				break;
        			} else {
        				$this->_msg($this->_('_Form insert error'), Messages::ERROR);
        				return true;
        			}
        	}
        }
        public function executeNewGroup() {
        	if(isset($_POST['ef_name']) &&
        		isset($_POST['ef_desc']) &&
        		$this->checkRight('administer_forms')){

        		$name = $_POST['ef_name'];
        		$desc = $_POST['ef_desc'];
        		
        		if($this->newGroup($name, $desc)){
        			$this->_msg($this->_('_Group insert success'), Messages::INFO);
        			header('Location: '.$GLOBALS['abs_root'].'/_admincenter/tf/efiling/');
        			break;
        		} else {
        			$this->_msg($this->_('_Group insert error'), Messages::ERROR);
        			return true;
        		}
        	}
        }
        
        public function executeNewData() {
        	if(isset($_POST['ef_name']) &&
        		isset($_POST['ef_group']) &&
        		isset($_POST['ef_type']) &&
        		isset($_POST['ef_info']) &&
        		$this->checkRight('administer_forms')){

        		$name = $_POST['ef_name'];
        		$group = $_POST['ef_group'];
        		$type = $_POST['ef_type'];
        		$info = $_POST['ef_info'];
        		$send = (isset($_POST['ef_send']) && $_POST['ef_send'] == 'on' ) ? 1 : 0; 
        		
        		if($this->newData($name, $group, $type, $info, $send)){
        			$this->_msg($this->_('_Data insert success'), Messages::INFO);
        			header('Location: '.$GLOBALS['abs_root'].'/_admincenter/tf/efiling/');
        			break;
        		} else {
        			$this->_msg($this->_('_Data insert error'), Messages::ERROR);
        			return true;
        		}
        	}
        }
        
        public function executeEditData() {
        	
        if(isset($_POST['ef_name']) &&
        		isset($_POST['ef_gid']) &&
        		isset($_POST['ef_id']) &&
        		isset($_POST['ef_group']) &&
        		isset($_POST['ef_type']) &&
        		isset($_POST['ef_info']) &&
        		$this->checkRight('administer_forms')){

        		$id = $_POST['ef_id'];
        		$name = $_POST['ef_name'];
        		$group = $_POST['ef_group'];
        		$type = $_POST['ef_type'];
        		$info = $_POST['ef_info'];

        		if($this->editData($id, $name, $group, $type, $info)){
        			$this->_msg($this->_('_Data edit success'), Messages::INFO);
        			header('Location: '.$GLOBALS['abs_root'].'/_admincenter/tf/efiling/#!/chapter/group/page/1/action/edit/id/'.$_POST['ef_gid'].'/');
        			break;
        		} else {
        			$this->_msg($this->_('_Data edit error'), Messages::ERROR);
        			return true;
        		}
        	}
        }
        /* ============================  Getter  ============================  */
        private function getFilingByHash($hash){
        	$g = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_filings` WHERE filing_hash LIKE "'.mysql_real_escape_string($hash).'"');
        	if($g != ''){
        		return new eFiling_filing($g['ff_id'], $g['f_id'], $g['datum'], $g['status'], $g['preview'], $g['secbckup']);
        	} else {
        		return null;
        	}
        }
        private function getFiling($id) {
        	$g = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_filings` WHERE ff_id="'.mysql_real_escape_string($id).'"');
        	if($g != ''){
        		return new eFiling_filing($g['ff_id'], $g['f_id'], $g['datum'], $g['status'], $g['preview'], $g['secbckup']);
        	} else {
        		return null;
        	}
        }
        private function getForm($id, $refresh=false){
        	if($id != -1){
        		if($refresh || !isset($this->forms) || !isset($this->forms[$id])) $this->getForms(-1, true);

	        	return isset($this->forms[$id]) ? $this->forms[$id] : null;
        	} else return null;
        }
        /**
         * returnes data for Group $id
         * @param $id
         */
        private function getGroup($id){
        	if($id != -1){
        		
        		$g = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` WHERE g_id="'.mysql_real_escape_string($id).'"');
        		if($g != ''){
        			$group = new eFiling_datagroup($g['g_id'], $g['name'], $g['beschreibung']);
	        		$group->setContent($this->getDataForDatagroup($g['g_id']));
        		}
        		return $group;
        		
        	} else return null;
        }
        /**
         * returnes data $id
         * @param $id
         */
        private function getData($id) {
        	if($id != -1){
        		
        		$d = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_data` WHERE d_id="'.mysql_real_escape_string($id).'"');
        		if($d != ''){
        			$data = new eFiling_data($d['d_id'], $d['g_id'], $d['name'], $d['info'], $d['type'], ($d['public']==1), $d['order'], ($d['send']==1));
        		}
        		return $data;
        		
        	} else return null;
        }
    	/**
         * returnes data $id
         * @param $id
         */
       /* private function getDataForFiling($id, $f_id) {
        	if($id > 0){
        		$d = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_filing_data` fd
        									LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'efiling_data` d ON fd.d_id = d.d_id
        									WHERE fd.d_id="'.mysql_real_escape_string($id).'" AND fd.ff_id="'.mysql_real_escape_string($f_id).'"');
        		
        		if($d != ''){
        			$data = new eFiling_data($d['d_id'], $d['g_id'], $d['name'], $d['info'], $d['type'], ($d['public']==1), $d['order'], ($d['send']==1));
        			$data->setValue($d['value']);
        		}
        		return $data;
        		
        	} else return null;
        }�*/
        /**
         * returnes array of filings
         * @param unknown_type $page
         * @param unknown_type $form
         */
     	private function getFilings($page=-1, $form=-1){
     		if($this->checkRight('administer_filings')){
     			$per_page = $this->config['per_page']['admin']['filings'];
        		$limit = ($page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
        		
        		$form = ($form == -1) ? '' : ' WHERE ff.f_id="'.$form.'" ';
     			
     			$s = $this->mysqlArray('SELECT *, ff.preview fpreview FROM `'.$GLOBALS['db']['db_prefix'].'efiling_filings` ff
     											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'efiling_form` f ON ff.f_id = f.f_id '.$form.' ORDER BY ff.datum DESC '.$limit);
     			
     			$return = array();
     			if($s != ''){
	     			foreach($s as $f){
	     				$return[] = new eFiling_filing($f['ff_id'], $f['f_id'], $f['datum'], $f['status'], $f['fpreview'], $f['backup']);
	     			}
     			}
     			
     			return $return;
     		} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        } 
        /**
         * returnes count of all filings
         */
        private function getFilingsCount($form = -1){
        	$form = ($form == -1) ? '' : ' WHERE f_id="'.$form.'"';
        	
        	$q = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'efiling_filings` '.$form);
        	if($q != ''){
        		return $q['count'];
        	} else return 0;
        }
        /**
         * returnes array of forms 
         * result will be cached in $this->forms
         * @param $page 
         * @param $refresh | if true cache will be reloaded
         */
        private function getForms($page=-1, $refresh=false) {
        	if($this->checkRight('administer_forms')){
        		$from = ($page == -1) ? 0 : ($page-1)*$this->config['per_page']['admin']['forms'];
        		$length = ($page == -1) ? $this->getFormCount() : $this->config['per_page']['admin']['forms'];
        		
        		if($refresh || !isset($this->forms)){
        			//$per_page = $this->config['per_page']['admin']['forms'];
        			//$limit = ($page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
        		
        			$this->forms = array();
        			
	        		$q = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form` ');
	        		if($q != array() && $q != ''){
	        			foreach($q as $f){
	        				$form = new eFiling_form($f['f_id'], $f['name'], $f['beschreibung'], $f['from'], $f['to'], $f['preview']);
	        				$form->setContent($this->getDataGroupsFor($f['f_id'], $refresh));
	        				$this->forms[$f['f_id']] = $form;
	        			}
	        		}
        		}
        		return array_slice($this->forms, $from, $length);
        		
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        /**
         * returnes count of all forms
         */
        private function getFormCount(){
        	$q = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form` ');
        	if($q != ''){
        		return $q['count'];
        	} else return 0;
        }
        /**
         * returnes count of all datagroups
         */
        private function getDatagroupCount(){
        	$q = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` ');
        	if($q != ''){
        		return $q['count'];
        	} else return 0;
        }
        
        /**
         * returnes Data array for Datagroup $id
         * @param unknown_type $id
         */
        private function getDataForDatagroup($id) {
        	$q = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_data` 
        									WHERE g_id = "'.mysql_real_escape_string($id).'" ORDER BY `order` ASC');
       		
        	$tmp = array();
       		
        	if($q != array() && $q != ''){
        		foreach($q as $dg){
					$tmp[] = new eFiling_data($dg['d_id'], $dg['g_id'], $dg['name'], $dg['info'], $dg['type'], ($dg['public'] == '1'), $dg['order'], ($dg['send'] == '1'));				
        		}
        	}
        	return $tmp;
        }
        /**
         * returnes Datagroup object for given id
         * @param $id
         */
        private function getDatagroup($id){
        	$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` 
        									WHERE g_id = "'.mysql_real_escape_string($id).'"');
        	
        	$tmp = null;
        	
        	if($q != '' && $q != array()){
        		
        		$tmp = new eFiling_datagroup($q['g_id'], $q['name'], $q['beschreibung']);
        		$tmp->setContent($this->getDataForDatagroup($id));
        	
        	}

        	return $tmp;
        }
    	/**
         * returnes Datagroup object for given id
         * @param $id
         */
        private function getDatagroups($page=1, $not_in_form=-1){
        	
        	$per_page = $this->config['per_page']['admin']['groups'];
        	$limit = ($page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
        		
        	if(($not_in_form != -1)) {
        		$q = $this->mysqlArray('SELECT *, g.g_id id FROM `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` g
        										LEFT JOIN (SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` WHERE f_id="'.mysql_real_escape_string($not_in_form).'") fg ON g.g_id = fg.g_id 
        										WHERE fg.f_id IS NULL '.$limit);
        	} else {
        		$q = $this->mysqlArray('SELECT *, g_id id FROM `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` '.$limit);
        	}
        	
        	
        	
        	$tmp = array();
        	
        	if($q != '' && $q != array()){
        		foreach($q as $group){
        			$t = new eFiling_datagroup($group['id'], $group['name'], $group['beschreibung']);
        			$tmp[] = $t;
        		}
        	}

        	return $tmp;
        }
        /**
         * returnes DataGroups for given form id
         * @param $id
         * @param $refresh
         */
        private function getDataGroupsFor($id, $refresh=false){
        	/*if(!$refresh && isset($this->forms) && isset($this->forms[$id])){
        		return (isset($this->forms[$id])) ? $this->forms[$id]->getContent() : null;
        	} else {*/
        		$q = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` fd
        										LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` d ON fd.g_id = d.g_id 
        									WHERE fd.f_id = "'.mysql_real_escape_string($id).'" ORDER BY fd.order');
        		$tmp = array();
        		
        		
        		if($q != array() && $q != ''){
        			foreach($q as $dg){
        				$t = new eFiling_datagroup($dg['g_id'], $dg['name'], $dg['beschreibung']);
        				$t->setOrder($dg['order']);
						$tmp[] = $t;
        			}
        		}
        		
        		//if(isset($this->forms[$id])) $this->forms[$id]->setContent($tmp);

        		return $tmp;
        	//}
        }
        
        /**
         * returnes order of DataGroup
         * @param $id
         * @param $form
         */
        private function getDataGroupOrderForForm($id, $form){
        	$q = $this->mysqlRow('SELECT `order` FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` 
        									WHERE `g_id` = "'.mysql_real_escape_string($id).'" AND `f_id`="'.mysql_real_escape_string($form).'"');
        	if($q != array() && $q != ''){
        		return $q['order'];
        	} else return -1;
        }
        
        /**
         * returnes Datagroup by Form id and Order
         * @param $form
         * @param $order
         */
        private function getDatagroupByFormAndOrder($form, $order){
        	$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` fg
        										LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` g ON fg.g_id = g.g_id
        									WHERE fg.order = "'.mysql_real_escape_string($order).'" AND fg.f_id="'.mysql_real_escape_string($form).'"');
        	
        	if($q != array() && $q != ''){
        		$t = new eFiling_datagroup($q['g_id'], $q['name'], $q['beschreibung']);
        		$t->setOrder($q['order']);
        		return $t;
        	} else return null;
        }
        
    	/**
         * returnes datagroup object by form and order
         * the function will calculate the nearest Object whitch is not $not_data
         * @param $group
         * @param $order
         * @param $not_data
         */
        private function getNearestGroupByFormAndOrder($form, $order, $not_group){
        	$order1 = $this->getDataGroupOrderForForm($not_group, $form); // old order
        	$mm = ($order1 < $order) ? '+' : '-'; 
        	$s = ($order1 < $order) ? '>' : '<';
        	
        	$q = $this->mysqlRow('SELECT *, dg.g_id id FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup`  dg
        								LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` d ON dg.g_id = d.g_id
        								WHERE dg.f_id="'.mysql_real_escape_string($form).'" AND dg.order'.$s.'"'.mysql_real_escape_string($order1).'"
        								ORDER BY ('.mysql_real_escape_string($order).$mm.'dg.order) ASC LIMIT 1');

        	if($q != array() && $q != ''){
        		$t = new eFiling_datagroup($q['id'], $q['name'], $q['beschreibung']);
        			
        		return $t;
        	} else return null;
        }
        /**
         * returnes Data by Group id and Order
         * @param $form
         * @param $order
         */
        private function getDataByGroupAndOrder($group, $order) {
        	$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_data` WHERE g_id="'.mysql_real_escape_string($group).'" AND `order`="'.mysql_real_escape_string($order).'"');

        	if($q != array() && $q != ''){
        		$t = new eFiling_data($q['d_id'], $q['g_id'], $q['name'], $q['ingo'], $q['type'], ($q['public']==1), $q['order'], ($q['send']==1));
        		return $t;
        	} else return null;
        	
        }
        
        /**
         * returnes data object by group and order
         * the function will calculate the nearest Object whitch is not $not_data
         * @param $group
         * @param $order
         * @param $not_data
         */
        private function getNearestDataByGroupAndOrder($group, $order, $not_data){
        	$order1 = $this->getData($not_data)->getOrder(); // old order
        	$mm = ($order1 < $order) ? ' + ' : ' - '; 
        	$s = ($order1 < $order) ? '>' : '<';
        	
        	$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_data` 
        								WHERE g_id="'.mysql_real_escape_string($group).'" AND `order`'.$s.'"'.mysql_real_escape_string($order1).'"
        								ORDER BY ('.mysql_real_escape_string($order).$mm.'`order`) ASC LIMIT 1');

        	if($q != array() && $q != ''){
        		$t = new eFiling_data($q['d_id'], $q['g_id'], $q['name'], $q['ingo'], $q['type'], ($q['public']==1), $q['order'], ($q['send']==1));
        		return $t;
        	} else return null;
        }
        
        /**
         * returnes if group used in given form
         * @param $group
         * @param $form
         */
        private function isUsedAtForm($group, $form){
        	return ($this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` WHERE g_id="'.mysql_real_escape_string($group).'" AND f_id="'.mysql_real_escape_string($form).'"') != '');
        }
        
        /* ============================  Updater/Inserter  ============================  */
        public function setFilingStatus($id, $status) {
        	if($this->checkRight('administer_filings')){
        		if($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_filings` SET `status`= "'.mysql_real_escape_string($status).'" 
        									WHERE ff_id="'.mysql_real_escape_string($id).'"')){
        		
        			$this->_msg($this->_('_filing update success'), Messages::INFO);
        			return true;
        		} else {
        			$this->_msg($this->_('_filing update error'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        /**
         * handles Group moving in Form
         * @param unknown_type $id
         * @param unknown_type $form
         * @param unknown_type $newOrder
         */
        public function moveGroupInForm($id, $form, $newOrder){
        	if($this->checkRight('administer_forms')){
        		$group = $this->getDatagroup($id);
        		if($group != null){
        			$group->setOrder($this->getDataGroupOrderForForm($id, $form));
	        		
	        		$group2 = $this->getNearestGroupByFormAndOrder($form, $newOrder, $id);

	        		if($group2 != null){
	        			if($this->switchGroupOrder($group, $group2, $form)){
	        				$this->_msg($this->_('_Moving Sucess'), Messages::INFO);
	        				return true;
	        			} else {
	        				$this->_msg($this->_('_Error Moving'), Messages::ERROR);
	        				return false;
	        			}
	        		} else {
        				$this->_msg($this->_('_Error Moving'), Messages::ERROR);
	        			return false;
	        		}
        		} else {
        			$this->_msg($this->_('_Error Moving'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        
        /**
         * switches the Order of two Groups in given form
         * @param unknown_type $id
         * @param unknown_type $id2
         * @param $form
         */
        private function switchGroupOrder($group, $group2, $form){
        	if(get_class($group) !== 'eFiling_datagroup') {
        		$group = $this->getDatagroup($group);
        		if($group != null){
        			$group->setOrder($this->getDataGroupOrderForForm($group->getId(), $form));
        		}
        	} else $group->setOrder($this->getDataGroupOrderForForm($group->getId(), $form));
        	if(get_class($group2) !== 'eFiling_datagroup') {
        		$group2 = $this->getDatagroup($group2);
        		if($group2 != null){
        			$group2->setOrder($this->getDataGroupOrderForForm($group2->getId(), $form));
        		}
        	} else $group2->setOrder($this->getDataGroupOrderForForm($group2->getId(), $form));
        	
        	if($group != null && $group2 != null){
        		$old_position = mysql_real_escape_string($group->getOrder());
        		$new_position = mysql_real_escape_string($group2->getOrder());

        		return ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` 
        									SET `order`= IF(`order`="'.$old_position.'", "'.$new_position.'", "'.$old_position.'")
        									WHERE `f_id`="'.mysql_real_escape_string($form).'" AND 
        											(`g_id`="'.mysql_real_escape_string($group->getId()).'" OR 
        											`g_id`="'.mysql_real_escape_string($group2->getId()).'")') !== false);
        	} else return false;
        }
   		/**
    	 * handles Group moving in Form
         * @param unknown_type $id
         * @param unknown_type $form
         * @param unknown_type $newOrder
         */
        public function moveDataInGroup($id, $group, $newOrder){
        	if($this->checkRight('administer_forms')){
        		$data = $this->getData($id);
        		
        		if($data != null){
        			//$data->setOrder($this->getDataGroupOrderForForm($id, $form));
	        		
	        		$data2 = $this->getNearestDataByGroupAndOrder($group, $newOrder, $id);

	        		if($data2 != null){
	        			if($this->switchDataOrder($data, $data2, $group)){
	        				$this->_msg($this->_('_Moving Sucess'), Messages::INFO);
	        				return true;
	        			} else {
	        				$this->_msg($this->_('_Error Moving'), Messages::ERROR);
	        				return false;
	        			}
	        		} else {
        				$this->_msg($this->_('_Error Moving'), Messages::ERROR);
	        			return false;
	        		}
        		} else {
        			$this->_msg($this->_('_Error Moving'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        
        
        
    	/**
         * switches the Order of two Dataobjects in given group
         * @param unknown_type $id
         * @param unknown_type $id2
         * @param $form
         */
        private function switchDataOrder($data, $data2, $group){
        	if(get_class($data) !== 'eFiling_data') {
        		$data = $this->getData($data);
        	}
        	if(get_class($data2) !== 'eFiling_data') {
        		$data2 = $this->getDatagroup($data2);
        	}
        	
        	if($data != null && $data2 != null){
        		$old_position = mysql_real_escape_string($data->getOrder());
        		$new_position = mysql_real_escape_string($data2->getOrder());
        		
        		return ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_data` 
        									SET `order`= IF(`order`="'.$old_position.'", "'.$new_position.'", "'.$old_position.'")
        									WHERE `g_id`="'.mysql_real_escape_string($group).'" AND 
        											(`d_id`="'.mysql_real_escape_string($data->getId()).'" OR 
        											`d_id`="'.mysql_real_escape_string($data2->getId()).'")') !== false);
        	} else return false;
        }
        
        /**
         * handles edit Form
         * @param $id
         * @param $from
         * @param $to
         * @param $name
         * @param $desc
         */
        private function editForm($id, $from, $to, $name, $desc){
        	if($this->checkRight('administer_forms')){
        		return ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_form` 
        									SET `from`="'.mysql_real_escape_string($from).'",
        									`to`="'.mysql_real_escape_string($to).'",
        									`name`="'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($name)).'",
        									`beschreibung`="'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($desc)).'"
        									WHERE f_id="'.mysql_real_escape_string($id).'"') !== false);
        	} else return false;
        }
        
        /**
         * handles edit Group
         * @param $id
         * @param $name
         * @param $desc
         */
        private function editGroup($id, $name, $desc) {
        	if($this->checkRight('administer_forms')){
        		return ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` 
        									SET `name`="'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($name)).'",
        									`beschreibung`="'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($desc)).'"
        									WHERE g_id="'.mysql_real_escape_string($id).'"') !== false);
        	} else return false;
        }
        
        /**
         * updates data in database
         * @param $id
         * @param $name
         * @param $group
         * @param $type
         * @param $info
         */
        private function editData($id, $name, $group, $type, $info){
        	if($this->checkRight('administer_forms')){

        		return ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'efiling_data` 
        									SET `name`="'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($name)).'",
        									`info`="'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($info)).'",
        									`g_id`="'.mysql_real_escape_string($group).'",
        									`type`="'.mysql_real_escape_string($type).'"
        									WHERE d_id="'.mysql_real_escape_string($id).'"') !== false);
        	} else return false;
        }
        
        /*private function insertFiling($filing_id, $data_id, $val){
        	$b = ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_filing_data` (`ff_id`, `d_id`, `value`) 
        										VALUES ("'.mysql_real_escape_string($filing_id).'", "'.mysql_real_escape_string($data_id).'", "'.mysql_real_escape_string($val).'")') !== false);
        	echo mysql_error();
        	return $b;
        }*/
        
        private function deleteFiling($id){
        	return ($this->mysqlInsert('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_filing` WHERE ff_id="'.mysql_real_escape_string($id).'"') !== false);
        }
       
        /**
         * inserts new Group into database
         * @param $name
         * @param $desc
         */
        private function newGroup($name, $desc) {
        	if($this->checkRight('administer_forms')){
        		return ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` 
        									(`name`,`beschreibung`) VALUES("'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($name)).'",
        									"'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($desc)).'")') !== false);
        	} else return false;
        }
    	
    	/**
         * inserts new Form into database
         * @param $name
         * @param $desc
         */
        private function newForm($from, $to, $name, $desc){
        	if($this->checkRight('administer_forms')){
        		return ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form` 
        									(`from`, `to`, `name`,`beschreibung`) VALUES("'.mysql_real_escape_string($from).'",
        									"'.mysql_real_escape_string($to).'",
        									"'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($name)).'",
        									"'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($desc)).'")') !== false);
        	} else return false;
        }
        
        /**
         * add New Data to database
         * @param unknown_type $name
         * @param unknown_type $group
         * @param unknown_type $type
         * @param unknown_type $info
         */
        private function newData($name, $group, $type, $info, $send){
        	if($this->checkRight('administer_forms')){
        		$max = $this->mysqlRow('SELECT MAX(`order`) max FROM `'.$GLOBALS['db']['db_prefix'].'efiling_data` WHERE g_id="'.mysql_real_escape_string($group).'"');
        		$max = ($max != '') ? $max['max']+1 : 0;
        		return ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_data` 
        									(`name`, `g_id`, `type`,`info`, `order`, `send`) VALUES("'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($name)).'",
        									"'.mysql_real_escape_string($group).'",
        									"'.mysql_real_escape_string($type).'",
        									"'.mysql_real_escape_string($this->sp->ref('TextFunctions')->renderUmlaute($info)).'",
        									"'.mysql_real_escape_string($max).'",
        									"'.mysql_real_escape_string($send).'")') !== false);
        	} else return false;
        }
        
        /**
         * Adds Group to FOrm
         * Enter description here ...
         * @param unknown_type $id
         * @param unknown_type $form
         * @return boolean|multitype:
         */
        private function addGroupToForm($id, $form){
        	if($this->checkRight('administer_forms')){
        		if(!$this->isUsedAtForm($id, $form)) {
        			
        			$max = $this->mysqlRow('SELECT (max(`order`)+1) max FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` WHERE f_id="'.mysql_real_escape_string($form).'"');
        			
        			$max = ($max != '') ? $max['max'] : '0';

        			return ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` (`g_id`, `f_id`, `order`)
        											VALUES("'.mysql_real_escape_string($id).'",
        											"'.mysql_real_escape_string($form).'", 
        											"'.mysql_real_escape_string($max).'")') != false);
        		
        		} else {
        			$this->_msg($this->_('_Group already used'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        /**
         * removes Group from Form
         * @param $id
         * @param $form
         */
        private function deleteGroupFromForm($id, $form){
        	if($this->checkRight('administer_forms')){
        		if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` WHERE g_id="'.mysql_real_escape_string($id).'" AND f_id="'.mysql_real_escape_string($form).'"') !== false){
        			$this->_msg($this->_('_delete success'), Messages::INFO);
        			return true;
        		} else {
        			$this->_msg($this->_('_delete error'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
    	/**
         * removes Data 
         * @param $id
         */
        private function deleteData($id){
        	if($this->checkRight('administer_forms')){
        		if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_data` WHERE d_id="'.mysql_real_escape_string($id).'"') !== false){
        			$this->_msg($this->_('_delete success'), Messages::INFO);
        			return true;
        		} else {
        			$this->_msg($this->_('_delete error'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        /**
         * deletes Group and all connected Data
         * @param $id
         */
        private function deleteGroup($id) {
        	if($this->checkRight('administer_forms')){
        		$data = $this->getDataForDatagroup($id);
        		$error = array();
        		
        		foreach($data as $d){
        			$error[] = !$this->deleteData($d->getId());
        		}
        		
        		if(!in_array(true, $error)){
        			if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` WHERE g_id="'.mysql_real_escape_string($id).'"') !== false){
        				if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_datagroup` WHERE g_id="'.mysql_real_escape_string($id).'"') !== false){
        					$this->_msg($this->_('_delete success'), Messages::INFO);
        					return true;
        				} else {
        					$this->_msg($this->_('_delete error'), Messages::ERROR);
        					return false;
        				}
        			} else {
        				$this->_msg($this->_('_delete error'), Messages::ERROR);
        				return false;
        			}
        		} else {
        			$this->_msg($this->_('_delete error'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
    	/**
         * deletes Form and all connected Data
         * @param $id
         */
        private function deleteForm($id) {
        	if($this->checkRight('administer_forms')){
        		
        		if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form_datagroup` WHERE f_id="'.mysql_real_escape_string($id).'"') !== false){
        			if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'efiling_form` WHERE f_id="'.mysql_real_escape_string($id).'"') !== false){
        				$this->_msg($this->_('_delete success'), Messages::INFO);
        				return true;
        			} else {
        				$this->_msg($this->_('_delete error'), Messages::ERROR);
        				return false;
        			}
        		} else {
        			$this->_msg($this->_('_delete error'), Messages::ERROR);
        			return false;
        		}
        		
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        /* ============================  Deleter  ============================  */
        
        /* ============================  Template  ============================  */
        public function tplGetGroupSelect($sel=-1) {        	
        	$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('ef_group');
        	$dropdown->setId('ef_group');
        	
        	foreach($this->getDatagroups(-1) as $group){
        		$dropdown->addOption($group->getName(), $group->getId(), $sel==$group->getId());
        	}
        	
        	return $dropdown->render();
        }
        public function tplGetStatusSelect($sel=-1){
        	$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('ef_select');
        	$dropdown->setId('ef_select');

        	$dropdown->addOption($this->_('_posted'), self::STATUS_GESCHICKT, $sel==self::STATUS_GESCHICKT);
        	$dropdown->addOption($this->_('_approved'), self::STATUS_BESTAETIGT, $sel==self::STATUS_BESTAETIGT);
        	$dropdown->addOption($this->_('_canceled'), self::STATUS_STORNIERT, $sel==self::STATUS_STORNIERT);
        	
        	return $dropdown->render();
        }
        /* ============================  Template  -  Admin  ============================  */
        public function tplAdmin() {
        	if($this->checkRight('administer_forms')){
	        	$tpl = new ViewDescriptor($this->config['tpl']['admin/main']);
	        	
	        	return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        public function tplAdminFilings($page=1, $form=-1){
        	if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/filings']);
        	
        		$count = $this->getFilingsCount($form);
        		
        		$per_page = $this->config['per_page']['admin']['filings'];
	        	$number_of_pages = (ceil($count/$per_page) == 0) ? 1 : ceil($count/$per_page);
	        	$page = ($page==-1 || $page > $number_of_pages) ? 1: $page;
        		
	        	$tpl->addValue('pagina_active', $page);
	        	$tpl->addValue('pagina_count', $number_of_pages);
	        	
        		$filings = $this->getFilings($page, $form);
        		
        		$forms = $this->getForms(-1);
        		if($forms != array()){
        			foreach($forms as $f){
        				$s = new SubViewDescriptor('forms');
        				$s->addValue('id', $f->getId());
        				$s->addValue('name', $f->getName());
        				$s->addValue('selected', ($f->getId()==$form) ? 'selected="selected"': '');
        				$tpl->addSubView($s);
        				unset($s);
        			}
        		}
        		
        		if($filings != array()){
        			foreach($filings as $f){
        				$form = $this->getForm($f->getForm());
        				$s = new SubViewDescriptor('filing');
        				
        				$s->addValue('id', $f->getId());
        				$s->addValue('form_name', $form->getName());
        				$s->addValue('date', $this->sp->ref('TextFunctions')->getDateAgo($f->getDatum()));
        				$s->addValue('status', $f->getStatus());
        				$s->addValue('preview', $f->getPreview());
        				
        				$tpl->addSubView($s);
        				unset($s);
        				unset($preview);
        			}
        		}
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        
    	public function tplAdminForms($page=1){
    		if($this->checkRight('administer_forms')){
    			$tpl = new ViewDescriptor($this->config['tpl']['admin/forms']);
        	
	        	$forms = $this->getForms($page);
	        	
	        	$count = $this->getFormCount();
	        	
	        	$per_page = $this->config['per_page']['admin']['forms'];
	        	$number_of_pages = (ceil($count/$per_page) == 0) ? 1 : ceil($count/$per_page);
	        	$page = ($page==-1 || $page > $number_of_pages) ? 1: $page;
	        	        	
	        	$tpl->addValue('pagina_active', $page);
	        	$tpl->addValue('pagina_count', $number_of_pages);
	        	
	        	foreach($forms as $form){
	        		$f = new SubViewDescriptor('form');
	        		
	        		$f->addValue('id', $form->getId());
	        		$f->addValue('name', $form->getName());
	        		$f->addValue('from', date('d.m.Y', $form->getFrom()));
	        		$f->addValue('to', date('d.m.Y', $form->getTo()));
	        		$f->addValue('active', $form->isActive() ? 'yes' : 'no');
	        		$f->addValue('count', $form->getContentCount());
	
	        		$tpl->addSubView($f);
	        		unset($f);
	        	}
	        	
	        	return $tpl->render();
    		} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        public function tplAdminGroup($page=1){
        if($this->checkRight('administer_forms')){
    			$tpl = new ViewDescriptor($this->config['tpl']['admin/groups']);
        	
	        	$groups = $this->getDatagroups($page);
	        	
	        	$count = $this->getDatagroupCount();
	        	
	        	$per_page = $this->config['per_page']['admin']['groups'];
	        	$number_of_pages = (ceil($count/$per_page) == 0) ? 1 : ceil($count/$per_page);
	        	$page = ($page==-1 || $page > $number_of_pages) ? 1: $page;
	        	        	
	        	$tpl->addValue('pagina_active', $page);
	        	$tpl->addValue('pagina_count', $number_of_pages);
	        	
	        	foreach($groups as $group){
	        		$f = new SubViewDescriptor('form');
	        		
	        		$f->addValue('id', $group->getId());
	        		$f->addValue('name', $group->getName());
	        		$f->addValue('desc', $group->getDesc());
	        		$f->addValue('count', $group->getContentCount());
	
	        		$tpl->addSubView($f);
	        		unset($f);
	        	}
	        	
	        	return $tpl->render();
    		} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
    	public function tplAdminData($page=1){
    		if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/data']);
        	
        		return $tpl->render();
    		} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        /* ---- new ---- */
        public function tplAdminNewForm() {
    		if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/new_form']);
        	
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
    
        public function tplAdminNewData() {
    		if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/new_data']);
        		
        		$tpl->addValue('groups', $this->tplGetGroupSelect());
        		
        		return $tpl->render();
    		} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        } 
        
        public function tplAdminNewGroup() {
    		if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/new_group']);
        	
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        /* ---- edit ---- */
	    public function tplAdminEditFiling($id){
	    	if($this->checkRight('administer_forms')){
	        	$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_filing']);
	        	
	        	$filing = $this->getFiling($id);
	        	
	        	if($filing != null){
	        		$tpl->addValue('id', $filing->getId());
	        		$tpl->addValue('preview', $filing->getPreview());
	        		$tpl->addValue('filing_text', $filing->getBackup());
	        		$tpl->addValue('status', $this->tplGetStatusSelect($filing->getStatus()));
	        		$tpl->addValue('status_id', $filing->getStatus());
	        		
	        		return $tpl->render();
	        	} else {
	        		$this->_msg($this->_('_Filing not found'), Messages::ERROR);
	        		return '';
	        	}

	    	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        public function tplAdminEditForm($id) {
        	if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_form']);
        	
        		$form = $this->getForm($id, true); // refresh cache
        		
        		if($form != null){
        			
        			$tpl->addValue('id', $form->getId());
        			$tpl->addValue('name', $form->getName());
        			$tpl->addValue('from', date('H:m:s | d.m.Y', $form->getFrom()));
        			$tpl->addValue('to', date('H:m:s | d.m.Y', $form->getTo()));
        			$tpl->addValue('desc', $this->sp->ref('TextFunctions')->stripBr($form->getDesc()));

        			$i=0;
        			$content = $form->getContent();
        			
        			foreach($content as $g){
        				$s = new SubViewDescriptor('groups');
        				
        				/* --- add moving dynamics --- */
						if($i==0){  // top
							$s->showSubView('Top');
						}
						if($i < count($content)-1){ // not bottom
							$r = new SubViewDescriptor('notBottom');
							
							$r->addValue('id', $g->getId());
							$r->addValue('newOrder', $g->getOrder()+1);
							
							$s->addSubView($r);
							unset($r);
						}
						if($i > 0){ // not top
							$r = new SubViewDescriptor('notTop');
							
							$r->addValue('id', $g->getId());
							$r->addValue('newOrder', $g->getOrder()-1);
							
							$s->addSubView($r);
							unset($r);
						}
						$i++;
        				/* --- end add moving dynamics --- */
						
        				$s->addValue('id', $g->getId());
        				$s->addValue('name', $g->getName());
        				
        				$tpl->addSubView($s);
        				unset($s);
        			}
        			
        			if($i==0) $tpl->showSubView('noDatagroups');
        			
        			return $tpl->render();
        		} else {
        			$this->_msg($this->_('_Id not found'), Messages::ERROR);
        			return '';
        		}
        		
        		
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
     	public function tplAdminEditGroup($id) {
        	if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_group']);
        	
        		$group = $this->getGroup($id); // refresh cache
        		
        		if($group != null){
        			
        			$tpl->addValue('id', $group->getId());
        			$tpl->addValue('name', $group->getName());
        			$tpl->addValue('desc', $this->sp->ref('TextFunctions')->stripBr($group->getDesc()));

        			$i=0;
        			$content = $group->getContent();

        			foreach($content as $g){
        				$s = new SubViewDescriptor('data');
        				
        				$s->addValue('id', $g->getId());
        				
        				/* --- add moving dynamics --- */
						if($i==0){  // top
							$s->showSubView('Top');
						}
						if($i < count($content)-1){ // not bottom
							$r = new SubViewDescriptor('notBottom');
							
							$r->addValue('id', $g->getId());
							$r->addValue('newOrder', $g->getOrder()+1);
							
							$s->addSubView($r);
							unset($r);
						}
						if($i > 0){ // not top
							$r = new SubViewDescriptor('notTop');
							
							$r->addValue('id', $g->getId());
							$r->addValue('newOrder', $g->getOrder()-1);
							
							$s->addSubView($r);
							unset($r);
						}
						$i++;
        				/* --- end add moving dynamics --- */						
						
						/* --- add datafields --- */
						switch($g->getType()){
							case self::TYPE_STRING:
								$ss = new SubViewDescriptor('eFiling_dy_string');
								break;
							case self::TYPE_CHECK:
								$ss = new SubViewDescriptor('eFiling_dy_check');
								break;
							case self::TYPE_DATE:
								$ss = new SubViewDescriptor('eFiling_dy_date');
								break;
							case self::TYPE_INT:
								$ss = new SubViewDescriptor('eFiling_dy_int');
								break;
							case self::TYPE_SOZNR:
								$ss = new SubViewDescriptor('eFiling_dy_soznr');
								break;
							case self::TYPE_TEXT:
								$ss = new SubViewDescriptor('eFiling_dy_text');
								break;
							case self::TYPE_TEXTFIELD:
								$ss = new SubViewDescriptor('eFiling_dy_textfield');
								break;
							case self::TYPE_EMAIL:
								$ss = new SubViewDescriptor('eFiling_dy_email');
								break;
							default:
								$ss = null;
								break;
						}
						
        				$ss->addValue('id', $g->getId());
        				$ss->addValue('name', $g->getName());
        				$ss->addValue('info', $g->getInfo());
        				
        				$s->addSubView($ss);
        				$tpl->addSubView($s);
        				unset($s);
        				unset($ss);
        			}
        			
        			if($i==0) $tpl->showSubView('noDatagroups');
        			
        			return $tpl->render();
        		} else {
        			$this->_msg($this->_('_Id not found'), Messages::ERROR);
        			return '';
        		}
        		
        		
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        public function tplAdminEditData($id, $gid){
       	 	if($this->checkRight('administer_forms')){
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_data']);
        		
        		$data = $this->getData($id);
        		
        		$tpl->addValue('id', $data->getId());
        		$tpl->addValue('name', $data->getName());
        		$tpl->addValue('send', ($data->getSend())? 'checked="checked"' : '');
        		$tpl->addValue('info', $this->sp->ref('TextFunctions')->stripBr($data->getInfo()));
        		$tpl->addValue('gid', $gid);
        		
        		$tpl->addValue('type_'.$data->getType(), 'selected="selected"');
        		
        		$tpl->addValue('groups', $this->tplGetGroupSelect($data->getGroup()));
        		
        		return $tpl->render();
    		} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        /* ------- stuff ----- -*/
		/**
		 * returnes rendered template vor available dataGroups for given Form
		 * used by admincenter editForm
		 * @param unknown_type $form
		 */
        public function tplAvailableDataGroups($form) {
        	if($this->checkRight('administer_forms')){
        		$groups = $this->getDatagroups(-1, $form);
        		
        		$tpl = new ViewDescriptor($this->config['tpl']['admin/available_groups']);

        		foreach($groups as $g){
        			$s = new SubViewDescriptor('group');
        			
        			$s->addValue('id', $g->getId());
        			$s->addValue('name', $this->sp->ref('TextFunctions')->cropText($g->getName(), 90));
        			$s->addValue('desc', $g->getDesc());
        			
        			$tpl->addSubView($s);
        			unset($s);
        		}
        		
        		if(count($groups) == 0) $tpl->showSubView('noGroups');
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
        	}
        }
        /* ============================  Template  -  Frontend  ============================  */
        
		public function tplRenderDatagroup($group, $type=''){
			$confirmation = ($type == 'confirmation');
			
			$group = (get_class($group) == 'eFiling_datagroup') ? $group : $this->getDataGroup($group);
			
			if($group != null){
				$tpl = ($confirmation) ? new ViewDescriptor($this->config['tpl']['front/datagroup_confirm']) : new ViewDescriptor($this->config['tpl']['front/datagroup']);
				
				$tpl->addValue('name', $group->getName());
				$tpl->addValue('id', $group->getId());
				$tpl->addValue('desc', $group->getDesc());
				
				$c = $group->getContent();
				$i = count($c);
				
				if($group != null){
					foreach($c as $d){
						$s = new SubViewDescriptor('data');
						$umlaute = false;
						/* --- add datafields --- */
						switch($d->getType()){
							case self::TYPE_STRING:
								$ss = new SubViewDescriptor('eFiling_dy_string');
								$umlaute = true;
								break;
							case self::TYPE_CHECK:
								$ss = new SubViewDescriptor('eFiling_dy_check');
								break;
							case self::TYPE_DATE:
								$ss = new SubViewDescriptor('eFiling_dy_date');
								break;
							case self::TYPE_INT:
								$ss = new SubViewDescriptor('eFiling_dy_int');
								break;
							case self::TYPE_SOZNR:
								$ss = new SubViewDescriptor('eFiling_dy_soznr');
								$umlaute = true;
								break;
							case self::TYPE_TEXT:
								$ss = new SubViewDescriptor('eFiling_dy_text');
								$umlaute = true;
								break;
							case self::TYPE_TEXTFIELD:
								$ss = new SubViewDescriptor('eFiling_dy_textfield');
								$umlaute = true;
								break;
							case self::TYPE_EMAIL:
								$ss = new SubViewDescriptor('eFiling_dy_email');
								$umlaute = true;
								break;
							default:
								$ss = null;
								break;
						}
						
						if($ss != null){
							$ss->addValue('id', $d->getId());
							$ss->addValue('name', $d->getName());
							$ss->addValue('info', $d->getInfo());
							$ss->addValue('isPublic', $d->isPublic());
							if($confirmation){
								if($d->getType() == self::TYPE_TEXT) $i--;

								if($d->getType() == self::TYPE_CHECK){
									$ss->addValue('val', $_POST['ef_data'][$d->getId()]);
									$ss->showSubView((isset($_POST['ef_data'][$d->getId()]) && $_POST['ef_data'][$d->getId()]=='on')
										? 'eFiling_dy_check_yes' : 'eFiling_dy_check_no');
								} else {
									$val = isset($_POST['ef_data'][$d->getId()]) ? $_POST['ef_data'][$d->getId()] : '--not found--';
									$val = ($umlaute) ? $this->sp->ref('TextFunctions')->renderUmlaute($val): $val;
									$ss->addValue('val', $val);
								}
							}
							
							$s->addSubView($ss);
							$tpl->addSubView($s);
							
							unset($s);
							unset($ss);
						}
					}
					return ($i>0) ? $tpl->render() : '';
				} else {
					$this->_msg($this->_('_Id not found'), Messages::ERROR);
        			return '';
				}
			} else {
				$this->_msg($this->_('_Id not found'), Messages::ERROR);
        		return '';
			}
			
		}
        public function tplRenderForm($id){
        	$form = $this->getForm($id);
        	if($form != null){
        		if($form->isActive()) {
        			$tpl = new ViewDescriptor($this->config['tpl']['front/form']);
        			
        			$t1 = (!isset($_POST['ef_confirm'])) ? new SubViewDescriptor('form') : new SubViewDescriptor('confirmation'); 
        			        			
        			$t1->addValue('id', $form->getId());
        			$t1->addValue('name', $form->getName());
        			$t1->addValue('desc', $form->getDesc());
        			
        			$r = '';
	        		foreach($form->getContent() as $g){
	        			$r .= $this->tplRenderDatagroup($g->getId(), isset($_POST['ef_confirm']) ? 'confirmation' : '');
	        		}
	        		
	        		$t1->addValue('content', $r);
	        		
	        		$tpl->addSubView($t1);
	        		
	        		return $tpl->render();
        		} else {
        			$this->_msg($this->_('_form not active'), Messages::ERROR);
        			return '';
        		}
        	}
        }
        
        public function tplRenderThanks($hash) {
        	if($hash != ''){
        		$filing = $this->getFilingByHash($hash);
        		if($filing != null){
        			$tpl = new ViewDescriptor($this->config['tpl']['front/thanks']);
        			
        			$form = $this->getForm($filing->getForm());
        			
        			$tpl->addValue('title', $form->getName());
        			$tpl->addValue('desc', $form->getDesc());
        			$tpl->addValue('hash', $hash);
        			$tpl->addValue('content', $filing->getBackup());
        			
	        		return $tpl->render();
        		} else {
        			$this->_msg($this->_('_wrong filing id'), Messages::ERROR);
        			return '';
        		}
        	} else return '';
        }
    }
?>