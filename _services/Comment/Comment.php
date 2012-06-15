<?php
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class Comment extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
         
        function __construct(){
        	$this->name = 'Comment';
        	$this->config_file = $GLOBALS['config']['root'].'_services/Comment/config.Comment.php';
            
        	parent::__construct();
            
            $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$action = isset($args['action']) ? $args['action'] : 'list';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$group = isset($args['group']) ? $args['group'] : -1;
        	$page = isset($args['page']) ? $args['page'] : 1;
        	
        	switch($action){
        		/*case 'view':
        			return $this->viewComments($id, $group, $page);
        			break;*/
				case 'list':
        			return $this->viewComments($id, $group, $page);
        			break;
        		case 'form':
        			return $this->viewForm($id, $group);
        			break;
        		default:
        			return '';
        	}
        	echo 'Comments';
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
        	$type = isset($args['type']) ? $args['type'] : 'vote_positive';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	
        	if($type == 'vote_positive'){
	        	if($id > 0){
	        		return ($this->positiveRate($id)) ? 'yes' : 'no';
	        	}
        	} elseif ($type == 'vote_negative'){
	        	if($id > 0){
	        		return ($this->negativeRate($id)) ? 'yes' : 'no';
	        	}
           	}
        	
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	$query = '--
				-- Tabellenstruktur fuer Tabelle `'.$GLOBALS['db']['db_prefix'].'comment`
				--
				
				CREATE TABLE IF NOT EXISTS `'.$GLOBALS['db']['db_prefix'].'comment` (
				  `c_id` int(11) NOT NULL AUTO_INCREMENT,
				  `id` int(11) NOT NULL,
				  `group` varchar(100) NOT NULL,
				  `author` varchar(100) NOT NULL,
				  `email` varchar(100) NOT NULL,
				  `homepage` varchar(100) NOT NULL,
				  `title` varchar(100) NOT NULL,
				  `content` text NOT NULL,
				  `ip` varchar(15) NOT NULL,
				  `datum` int(11) NOT NULL
				  PRIMARY KEY (`c_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
				
				-- --------------------------------------------------------
				
				--
				-- Tabellenstruktur für Tabelle `'.$GLOBALS['db']['db_prefix'].'comment_group`
				--
				
				CREATE TABLE IF NOT EXISTS `'.$GLOBALS['db']['db_prefix'].'comment_group` (
				  `cg_id` int(11) NOT NULL AUTO_INCREMENT,
				  `g_name` varchar(100) NOT NULL,
				  PRIMARY KEY (`cg_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
        	';
        	return $this->mysqlSetup($query);
        }
        
        public function viewComments($id, $group, $page=1){
        	if($id != -1 && $group != ''){
	        	$comments = new ViewDescriptor($this->config['tpl']['view/list']);
	        	$comments->addValue('id', $id);
	        	$comments->addValue('group', $group);
	        	
	        	/*$comment_ar = $this->mysqlRow('SELECT COUNT(*) as count FROM '.$GLOBALS['db']['db_prefix'].'comment AS c
	        										LEFT JOIN '.$GLOBALS['db']['db_prefix'].'comment_group AS g ON c.group = g.cg_id
	        										WHERE c.id="'.$id.'" AND (g.g_name="'.$group.'" OR c.group="'.$group.'")');*/
	        	$comment_ar = $this->mysqlRow('SELECT COUNT(*) as count FROM `'.$GLOBALS['db']['db_prefix'].'comment`
	        										WHERE `id`="'.$id.'" AND `group`="'.$group.'"');
	        	
	        	if(is_array($comment_ar)){
	        		if($comment_ar['count'] == 0){
	        			$comments->showSubView('noComments');
	        		}	else {
		        		$count = $comment_ar['count'];
		        		if($count == 0) $count = 1;
						$pages = ceil($count/$this->config['per_page_list']);
						$page = ($page <= $pages) ? $page : $pages;
						$page = ($page >= 1) ? $page : 1;
		        		
			        	/*$comment_ar = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'comment AS c
			        										LEFT JOIN '.$GLOBALS['db']['db_prefix'].'comment_group AS g ON c.group = g.cg_id
			        										WHERE c.id="'.$id.'" AND (g.g_name="'.$group.'" OR c.group="'.$group.'") ORDER BY c.datum DESC
			        										LIMIT '.($page-1)*$this->config['per_page_list'].', '.$this->config['per_page_list'].';');*/
			        	$comment_ar = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment`
			        										WHERE `id`="'.$id.'" AND `group`="'.$group.'" ORDER BY `datum` DESC
			        										LIMIT '.($page-1)*$this->config['per_page_list'].', '.$this->config['per_page_list'].';');
			        	
			        	if(is_array($comment_ar)){
			        		
			        		foreach($comment_ar as $comment){
			        			$com = new SubViewDescriptor('comment');
			        			$comments->addSubView($com);
			        			
			        			// ---- Display Homepage if entered
			        			if($comment['homepage'] != ''){
			        				$a = new SubViewDescriptor('homepage');
			        				$a->addValue('author', $comment['author']);
			        				$a->addValue('homepage', $comment['homepage']);
			        				$com->addSubView($a);
			        				unset($a);
			        			} else {
			        				$a = new SubViewDescriptor('noHomepage');
			        				$a->addValue('author', $comment['author']);
			        				$com->addSubView($a);
			        				unset($a);
			        			}
				                
			        			// -- Other Replaces
			        			$com->addValue('content', $this->sp->ref('TextFunctions')->renderBBCode($comment['content']));
			        			$com->addValue('title', $comment['title']);
			        			$com->addValue('date', $this->sp->ref('TextFunctions')->getDateAgo($comment['datum']));
			        			$com->addValue('id', $comment['c_id']);
			        			
			        			unset($com);
			        		}
			        		
			        		
		        			$pag = new SubViewDescriptor('pagina');
			                $pag->addValue('pagina_count', $pages);
			                $pag->addValue('pagina_active', $page);
			                
		        			$comments->addSubView($pag);
			                unset($pag);
			        	}
		        	}
	        	} else {
		        	
		        }
	        	return $comments->render();
        	} else return '';
        }
        
        public function viewForm($id, $group){
        	$GLOBALS['extra_css'][] = 'services/comments.css';
        	
	        $comments = new ViewDescriptor($this->config['tpl']['view/form']);

	        if($this->isAllowed($id, $group)){
        		$a = new SubViewDescriptor('allowed');
        		$comments->addSubView($a);
		        $a->addValue('id', $id);
		        $a->addValue('group', $group);
		        unset($a);
        	} else {
        		$a = new SubViewDescriptor('not_allowed');
        		$comments->addSubView($a);
        		unset($a);
        	}
		    return $comments->render();
        }
        
        public function addComment($id, $group, $author, $email, $homepage, $title, $comment){
        	//if($this->config['captcha'] && ) -> captcha
        	
        	if($this->isAllowed($id, $group)){
        	
		        if($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'comment (`id`, `group`, `author`, `email`, `homepage`, `content`, `ip`, `datum`, `title`) 
		        								VALUES ("'.mysql_real_escape_string($id).'", 
		        										"'.mysql_real_escape_string($group).'", 
		        										"'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($author)).'", 
		        										"'.mysql_real_escape_string($email).'", 
		        										"'.mysql_real_escape_string($homepage).'", 
		        										"'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($comment)).'", 
		        										"'.$this->getRealIpAddr().'", 
		        										"'.time().'", 
		        										"'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($title)).'")')) {
		        	
		        	$this->_msg($this->_('Comment successfully added'), Messages::INFO);
		        	return true;
		        } else {
		        	$this->_msg($this->_('Comment could not be added'), Messages::ERROR);
		        	return false;
		        }
        	}
        					
        }
        
        public function checkNewComment() {
        	//print_r($_POST);
        	if(isset($_POST['id']) &&
        		isset($_POST['group']) &&
        		isset($_POST['author']) &&
        		isset($_POST['email']) &&
        		isset($_POST['homepage']) &&
        		isset($_POST['title']) &&
        		isset($_POST['comment'])){
        		
        		$id = mysql_real_escape_string($_POST['id']);
        		$group = mysql_real_escape_string($_POST['group']);
        		$author = mysql_real_escape_string($_POST['author']);
        		$email = mysql_real_escape_string($_POST['email']);
        		$homepage = mysql_real_escape_string($_POST['homepage']);
        		$title = mysql_real_escape_string($_POST['title']);
        		$comment = mysql_real_escape_string($_POST['comment']);
        		
        		//$replyTo = (isset($_POST['replyTo']) && $_POST['replyTo']
        		
        		return $this->addComment($id, $group, $author, $email, $homepage, $title, $comment);	
        	} else return '';
        }
        
	    function getRealIpAddr(){
		    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
		      $ip=$_SERVER['HTTP_CLIENT_IP'];
		    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //to check ip is pass from proxy
		      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		    } else {
		      $ip=$_SERVER['REMOTE_ADDR'];
		    }
		    return $ip;
		}
		
		function getCommentCount($id, $group){
			/*$query = $this->mysqlRow('SELECT COUNT(*) as count	 
										FROM `'.$GLOBALS['db']['db_prefix'].'comment`  AS c
										LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'comment_group` AS g ON c.group = g.cg_id
										WHERE c.id="'.mysql_real_escape_string($id).'" AND (g.g_name="'.mysql_real_escape_string($group).'" OR c.group="'.mysql_real_escape_string($group).'")');*/
			$query = $this->mysqlRow('SELECT COUNT(*) as count	 
										FROM `'.$GLOBALS['db']['db_prefix'].'comment`
										WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
						
			if(is_array($query)) $count = $query['count'];
			else $count = 0;
			
			return $count;
		}
		
		/**
		 * Adds id+group to Blacklist -> it is not possible to post comments
		 * @param unknown_type $id
		 * @param unknown_type $group
		 */
		public function addToBlacklist($id, $group){
			$query = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment_blacklist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			if(is_array($query)) {
				if(!isset($query['id'])){
					$query = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'comment_blacklist` (`id`, `group`) VALUES ("'.mysql_real_escape_string($id).'", "'.mysql_real_escape_string($group).'")');
					if($query){
						return $this->removeFromWhitelist($id, $group);
					} else return false;
				} else return true;
			} else return false;
		}
		
		/**
		 * Will remove a id+group from the comments blacklist
		 * @param unknown_type $id
		 * @param unknown_type $group
		 */
    	public function removeFromBlacklist($id, $group){
			$query = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment_blacklist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			if(is_array($query) && isset($query['id'])){
				return $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'comment_blacklist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			} else return true;
   		}
   		
		/**
		 * Adds id+group to Whitelist -> it is not possible to post comments
		 * @param unknown_type $id
		 * @param unknown_type $group
		 */
   	 	public function addToWhitelist($id, $group){
			$query = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment_whitelist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			if(is_array($query)) {
				if(!isset($query['id'])){
					$query = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'comment_whitelist` (`id`, `group`) VALUES ("'.mysql_real_escape_string($id).'", "'.mysql_real_escape_string($group).'")');
					if($query){
						return $this->removeFromBlacklist($id, $group);
					} else return false;
				} else return true;
			} else return false;
		}
		
   		/**
		 * Will remove a id+group from the comments whitelist
		 * @param unknown_type $id
		 * @param unknown_type $group
		 */
    	public function removeFromWhitelist($id, $group){
			$query = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment_whitelist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			if(is_array($query) && isset($query['id'])){
				return $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'comment_whitelist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			} else return true;
   		}
        
   		/**
   		 * checks if id+group is allowed to post commands
   		 * @param $id
   		 * @param $group
   		 */
   		public function isAllowed($id, $group){
			$blacklist = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment_blacklist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');
			$whitelist = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'comment_whitelist` WHERE `id`="'.mysql_real_escape_string($id).'" AND `group`="'.mysql_real_escape_string($group).'"');

			if(is_array($blacklist) && is_array($whitelist)){
				return !(isset($blacklist[0]['id'])) || (!(isset($blacklist[0]['id'])) && isset($whitelist[0]['id']));
			} else return false;
   		}
    }
?>