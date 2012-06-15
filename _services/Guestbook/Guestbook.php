<?php
    class Guestbook extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
        const ONLINE = 0;
        const GESPERRT = 1;
        const REVIEW = 2;
        const GEMELDET = 3;
         
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Guestbook/config.Guestbook.php');
            $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        /**
         *  args['file'] .... file
         */
        public function view($args) {
            //list Eintraege (page)
            $target = isset($args['target']) ? $args['target'] : '';
            $action = (isset($args['action'])) ? $args['action'] : '';
            $page = (isset($args['page'])) ? $args['page'] : 1;
            
            if($action == 'list'){
                return $this->getList($target, $page);
            } else if($action == 'form'){
                return $this->getForm($target);
            } else if($action == 'pagina'){
            	return $this->getPagina($target, $page);
            }
            return '';
        }
        public function admin($args){
            $page = isset($args['page']) ? $args['page'] : 'info';
            $main = new ViewDescriptor($this->config['tpl']['admin_main']);
            if($page == 'info'){
                $main->addValue('submenu', $this->sp->view('Menu', array('file'=>$this->config['tpl']['admin_submenu'], 'replace_count'=>10, 'replace_active'=>0)));
                $sub = $this->AdminGetInfo($page);
            } else if($page == 'admin'){
                $main->addValue('submenu', $this->sp->view('Menu', array('file'=>$this->config['tpl']['admin_submenu'], 'replace_count'=>10, 'replace_active'=>1)));
                $sub = $this->AdminGetAdmin($page);
            }
            $main->addValue('subcontent', $sub->render());
            return $main->render();
        }
        public function run($args){

            return false;
        }
        public function data($args){
            $action = isset($args['action']) ? $args['action'] : '';
            if($action == 'add'){
                // addEintrag
                return $this->AddEntry($args);
            } else if($action=='admin_edit'){
                $this->AdminEdit($args);
            }
            
            // delete Eintrag
            // block Eintrag
            // melde Eintrag
            return '';
        }
        
        public function getList($target, $page) {
        	$count = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."guestbook WHERE status='".Guestbook::ONLINE."'"));
            $count = $count['count'];
            $pages = ceil($count/$this->config['perPage']);
            $page = ($page <= $pages) ? $page : $pages;
            $page = ($page >= 1) ? $page : 1;
            $array = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'guestbook WHERE status=\''.Guestbook::ONLINE.'\'  ORDER BY gbe_id DESC LIMIT '.($page-1)*$this->config['perPage'].', '.$this->config['perPage'].';', 'type'=>'array'));

            $main = new ViewDescriptor($this->config['tpl']['main']);
            $main->addValue('target', $target);

            foreach($array as &$row){
                $entry = new SubViewDescriptor('entry');
                $entry->addValue('author', $row['author']);
                $entry->addValue('titel', $row['titel']);
                $entry->addValue('inhalt', $this->sp->ref('TextFunctions')->renderBBCode($row['inhalt']));
                $entry->addValue('datum', $row['datum']);
                $entry->addValue('email', $row['email']);
                $entry->addValue('homepage', $row['homepage']);
                $entry->addValue('id', $row['gbe_id']);
                $main->addSubView($entry);
                unset($entry);
            }
            unset($row);
            return $main->render();	
        }
        
        public function getForm($target){
        	$render = new ViewDescriptor($this->config['tpl']['form']);
            $render->addValue('target', $target);
            $render->addValue('msg_success', $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('ENTRY_SUCCESS', 'Guestbook')))));
            $render->addValue('msg_error', $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('ENTRY_ERROR', 'Guestbook')))));
            return $render->render();
        }
        
        public function getPagina($target, $page){
        	$count = $this->sp->db->data(array('query'=>'SELECT COUNT(*) count FROM '.$GLOBALS['db']['db_prefix'].'guestbook WHERE status=\''.Guestbook::ONLINE.'\''));
			$count = $count['count'];
			$pages = ceil($count/$this->config['perPage']);
			$page = ($page <= $pages) ? $page : $pages;
			$page = ($page >= 1) ? $page : 1;
			$root_count = (count(explode('/', $GLOBALS['config']['root']))-2);
			$self_count = (count(explode('/', $_SERVER["PHP_SELF"]))-2);
			$toroot = '';
			for($i=0;$i<$root_count-$self_count;$i++){
				$toroot .= '../';
			}
        	$sort = isset($args['sort']) ? $args['sort'] : 'desc';
            //return $this->sp->view('Pagina', array('count'=>$pages, 'active'=>$page, 'url'=>  $toroot.ltrim($_SERVER["PHP_SELF"].'?page={page}', '/'), 'sort'=>$sort));
        	return $this->sp->view('Pagina', array('count'=>$pages, 'active'=>$page, 'url'=>  $target.'?page={page}', 'sort'=>$sort));
        }
        
        public function AdminGetInfo($page){
        	$sub = new ViewDescriptor($this->config['tpl']['admin_info']);
                
            $count_online = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."guestbook WHERE status='".Guestbook::ONLINE."'"));
            $count_gesperrt = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."guestbook WHERE status='".Guestbook::GESPERRT."'"));
            $count_review = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."guestbook WHERE status='".Guestbook::REVIEW."'"));
            $count_gemeldet = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."guestbook WHERE status='".Guestbook::GEMELDET."'"));
                
            $sub->addValue('count_online', $count_online['count']);
            $sub->addValue('count_gesperrt', $count_gesperrt['count']);
            $sub->addValue('count_review', $count_review['count']);
            $sub->addValue('count_gemeldet', $count_gemeldet['count']);
                
            return $sub;
        }
        
        public function AdminGetAdmin($page){
				$sub = new ViewDescriptor($this->config['tpl']['admin_admin']);
                
                $posts = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'guestbook ORDER BY gbe_id DESC', 'type'=>'array'));

                foreach($posts as &$post){
                    $entry = new SubViewDescriptor('entry');

                    $entry->addValue('id', $post['gbe_id']);
                    $entry->addValue('titel', $post['titel']);
                    $entry->addValue('author', $post['author']);
                    $entry->addValue('status', $post['status']);
                    $entry->addValue('inhalt', $post['inhalt']);
                    $entry->addValue('datum', $post['datum']);
                    $entry->addValue('email', $post['email']);
                    $entry->addValue('homepage', $post['homepage']);
                    
                    $entry->addValue('status_0', ($post['status']==Guestbook::ONLINE) ? 'checked="checked"' : '');
                    $entry->addValue('status_1', ($post['status']==Guestbook::GESPERRT) ? 'checked="checked"' : '');
                    $entry->addValue('status_2', ($post['status']==Guestbook::REVIEW) ? 'checked="checked"' : '');
                    $entry->addValue('status_3', ($post['status']==Guestbook::GEMELDET) ? 'checked="checked"' : '');
                    
                    $sub->addSubView($entry);
                }
                $sub->addValue('message_succ', $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('ADMIN_ENTRY_SUCCESS', 'Guestbook')))));
                $sub->addValue('message_error', $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::ERROR, 'msg'=>$this->_('ADMIN_ENTRY_ERROR', 'Guestbook')))));
        	
                return $sub;
        }
        
        public function AddEntry($args){
        	print_r($args);
            $type = isset($args['type']) ? $args['type'] : 'post';
        	if($type == 'post'){
            	$author = isset($_POST['gb_author']) ? mysql_real_escape_string($_POST['gb_author']): '';
                $email = isset($_POST['gb_email']) ? mysql_real_escape_string($_POST['gb_email']): '';
                $homepage = isset($_POST['gb_homepage']) ? mysql_real_escape_string($_POST['gb_homepage']): '';
                $titel = isset($_POST['gb_titel']) ? mysql_real_escape_string($_POST['gb_titel']): '';
                $inhalt = isset($_POST['gb_inhalt']) ? mysql_real_escape_string($_POST['gb_inhalt']): '';
            } else if($type == 'args') {
                $author = isset($args['gb_author']) ? mysql_real_escape_string($args['gb_author']): '';
                $email = isset($args['gb_email']) ? mysql_real_escape_string($args['gb_email']): '';
                $homepage = isset($args['gb_homepage']) ? mysql_real_escape_string($args['gb_homepage']): '';
                $titel = isset($args['gb_titel']) ? mysql_real_escape_string($args['gb_titel']): '';
                $inhalt = isset($args['gb_inhalt']) ? mysql_real_escape_string($args['gb_inhalt']): '';                
           	} else {
                return 'Error';
                /*$author='';
              	$email = '';
               	$homepage = '';
                $titel='';
                $inhalt ='';*/
            }
            $status = ($this->config['checkNewMessages']) ? Guestbook::REVIEW : Guestbook::ONLINE;
            $ip ='';
            echo 'insert';
            if($this->sp->db->bool("INSERT INTO ".$GLOBALS['db']['db_prefix']."guestbook (author, email, homepage, titel, inhalt, datum, status, ip) VALUES ('".$author."', '".$email."', '".$homepage."', '".$titel."', '".$inhalt."', NOW(), '".$status."', '".$ip."')")){
               	return mysql_insert_id();
                //Todo Message
                // echo 'JA';
            } else {
          	    return 'Error';
                //Todo Message
                //echo 'NEIN';
            }
        }
        
        public function AdminEdit($args){
        	$id = isset($args['id']) ? $args['id'] : -1;
          	$type = isset($args['type']) ? $args['type'] : Guestbook::REVIEW;
            if($this->sp->db->bool("UPDATE ".$GLOBALS['db']['db_prefix']."guestbook SET status='".$type."' WHERE gbe_id='".$id."';")){
            	return 'YES';
            } else return 'Error';
        }
        
    	public function setup(){
        	
        }
    }
?>