<?php
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class Likes extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
         
        function __construct(){
        	$this->name = 'Likes';
        	$this->config_file = $GLOBALS['config']['root'].'_services/Likes/config.Likes.php';
            
        	parent::__construct();
            
            if(isset($this->config['loc_file'])) $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$action = isset($args['action']) ? $args['action'] : 'likes';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$group = isset($args['group']) ? $args['group'] : -1;
        	$template = isset($args['l_template']) ? $args['l_template'] : $this->config['default_template'];
        	
        	switch($action){
        		case 'script':
        			return $this->getScript();
        			break;
        		case 'form':
        			return $this->getForm($id, $group, $template);
        			break;
        		case 'likes':
        			return $this->getLikes($id, $group, $template);
        			break;
        	}
        	return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::admin()
         */
        public function admin($args){
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
        	$action = isset($args['action']) ? $args['action'] : 'likes';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$group = isset($args['group']) ? $args['group'] : -1;
        	$template = isset($args['l_template']) ? $args['l_template'] : $this->config['default_template'];
        	
        	switch($action){
        		case 'vote_positive':
        			if($this->like($id, $group)){
        				return $this->getLikes($id, $group, $template);
        			} else return 'no';
        			break;
        		case 'vote_negative':
        			if($this->dislike($id, $group)){
        				return $this->getLikes($id, $group, $template);
        			} else return 'no';
        			break;
        	}
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	$query = '--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'likes`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'likes` (
					  `id` int(11) NOT NULL,
					  `group` varchar(100) NOT NULL,
					  `likes` int(11) NOT NULL,
					  UNIQUE KEY `id` (`id`,`group`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
        	
        	return $this->mysqlSetup($query);        	
        }
        
        /**
         * renders script part of likes service
         */
        public function getScript() {
        	$script = new ViewDescriptor($this->config['tpl']['script']);
        	return $script->render();
        }
        
        /**
         * returnes voting form of specified template or default template
         * @param int $id
         * @param string $group
         * @param string $template
         */
        public function getForm($id, $group, $template='') {
        	if($template == '') $template = $this->config['default_template'];
        	$form = new ViewDescriptor($this->config['tpl_root'].$template.'/'.$this->config['tpl']['form']);
        	$form->addValue('id', $id);
        	$form->addValue('group', $group);
        	
        	return $form->render();
        }
        
        /**
         * returnes likes of spcified id and group wit template $template
         * @param $id
         * @param $group
         * @param $template
         */
    	public function getLikes($id, $group, $template='') {
        	if($template == '') $template = $this->config['default_template'];
        	$ar = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'likes` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'";');
			
        	$rating = new ViewDescriptor($this->config['tpl_root'].$template.'/'.$this->config['tpl']['likes']);
        	$rating->addValue('id', $id);
        	$rating->addValue('group', $group);

        	if(is_array($ar)){
        		
        		if($ar['likes'] > 0) {
        			
        			$a = new SubViewDescriptor('positive_rating');
        			$rating->addSubView($a);
        			$a->addValue('likes', $ar['likes']);
        			$a->addValue('id', $id);
        			$a->addValue('group', $group);
        			unset($a);
        		
        		} elseif ($ar['likes'] < 0) {

        			$a = new SubViewDescriptor('negative_rating');
        			$rating->addSubView($a);
        			$a->addValue('likes', $ar['likes']);
        			$a->addValue('id', $id);
        			$a->addValue('group', $group);
        			unset($a);
        		
        		} else {
        			$a = new SubViewDescriptor('neutral_rating');
        			$rating->addSubView($a);
        			$a->addValue('id', $id);
        			$a->addValue('group', $group);
        			unset($a);
        		}
        		
        	} else {
        		$a = new SubViewDescriptor('neutral_rating');
        		$rating->addSubView($a);
        		$a->addValue('id', $id);
        		$a->addValue('group', $group);
        		unset($a);
        	}
        	
        	return $rating->render();	
        }
        
        /**
         * 
         * Will like a like-item
         * @param $id
         * @param $group
         */
        public function like($id, $group){
        	//TODO: not everyone should be allowed to vote (cookie)
        	$ar = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'likes` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'";');
        	if(is_array($ar)){
        		$query = $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'likes` SET `likes` = `likes` + 1 WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'";');
        		if($query) return true;
        		else return false;
        	} else {
        		$query = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'likes` (`likes`, `id`, `group`) VALUES ("1", "'.mysql_real_escape_string($id).'", "'.mysql_real_escape_string($group).'");');
        		if($query) return true;
        		else return false;
        	}
        	return false;
        }
        
        /**
         * 
         * Will dislike a like-item
         * @param $id
         * @param $group
         */
        public function dislike($id, $group){
        	//TODO: not everyone should be allowed to vote (cookie)
        	$ar = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'likes` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'";');
        	if(is_array($ar)){
        		$query = $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'likes` SET `likes` = `likes` - 1 WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'";');
        		if($query) return true;
        		else return false;
        	} else {
        		$query = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'likes` (`likes`, `id`, `group`) VALUES ("-1", "'.mysql_real_escape_string($id).'", "'.mysql_real_escape_string($group).'");');
        		if($query) return true;
        		else return false;
        	}
        	return false;
        }
    }
?>