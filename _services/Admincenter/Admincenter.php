<?php
	/**
	 * Admincenter Service
	 * handles navigation, tpl rendering, installation, activation and setting editing of services
	 * @author Matthias (scrapy1060@gmail.com)
	 * @version 0.1 
     * @name: Admincenter 
     */
    class Admincenter extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
         
        function __construct(){
        	$this->name = 'Admincenter';
        	//$this->config_file = $GLOBALS['config']['root'].'_services/Admincenter/config.Admincenter.php';
        	$this->ini_file = $GLOBALS['to_root'].'_services/Admincenter/Admincenter.ini';
        	parent::__construct();
           // if(isset($this->_setting('loc_file'))) $this->sp->run('Localization', array('load'=>$this->_setting('loc_file')));
            
            /* -- till db service management works -- */
			$this->config['activated_services'] = array('gallery', 'efiling', 'shop', 'user');
			$this->config['setup_services'] = array('gallery', 'efiling', 'tags', 'category', 'shop');
			return $this->config['services'] = array(
					array('id'=>1, 'display'=>'Blog', 'name'=>'blog', 'image'=>'blog_big.png', 'class'=>'Blog', 'config_hash'=>''),
					array('id'=>2, 'display'=>'Galerie', 'name'=>'gallery', 'image'=>'gallery_big_1.png', 'class'=>'Gallery', 'config_hash'=>''),
					array('id'=>5, 'display'=>'G&auml;stebuch', 'name'=>'guestbook', 'image'=>'guestbook_big.png', 'class'=>'Guestbook', 'config_hash'=>''),
					array('id'=>6, 'display'=>'Kommentare', 'name'=>'comments', 'image'=>'comments_big.png', 'class'=>'Comment', 'config_hash'=>''),
					array('id'=>7, 'display'=>'Rating', 'name'=>'rating', 'image'=>'rating_big.png', 'class'=>'Rating', 'config_hash'=>''),
					array('id'=>9, 'display'=>'eFiling', 'name'=>'efiling', 'image'=>'efiling_big.png', 'class'=>'eFiling', 'config_hash'=>''),
					array('id'=>11, 'display'=>'Tags', 'name'=>'tags', 'image'=>'tags_big.png', 'class'=>'Tags', 'config_hash'=>''),
					array('id'=>12, 'display'=>'Category', 'name'=>'category', 'image'=>'category_big.png', 'class'=>'Category', 'config_hash'=>''),
					array('id'=>10, 'display'=>'Shop', 'name'=>'shop', 'image'=>'shop_big.png', 'class'=>'Shop', 'config_hash'=>'6177c69fdafd48052b385b0057639bf0'),
					array('id'=>4, 'display'=>'User', 'name'=>'user', 'image'=>'user_big.png', 'class'=>'User', 'config_hash'=>''));
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
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	
        	switch($chapter){
        		case 'settings':
        			return $this->tplSettings();
        			break;
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
        	return true;
        }   
        /* ============================  getter  ============================  */
        private function getServices() {
        	return $this->config['services'];
        }
        public function getActiveServices() {
        	$r = array();
        	foreach($this->getServices() as $s){
        		if($this->isActivated($s['name'])){
        			$r[] = $s;
        		}
        	}
        	return $r;
        }
        /* ============================  checker  ============================  */
		/**
		 * returnes true if give service is activated
		 * @param unknown_type $service
		 */
        private function isActivated($service){
        	return in_array($service, $this->config['activated_services']);
        }
        /****************************** end of standard functions ******************************/
     	/* ============================  Template  -  Admin  ============================  */
        public function tplAdmin() {
        	if($this->sp->ref('User')->isLoggedIn()){
				header('Content-Type:text/html; charset=UTF-8');        		
				
				$service = isset($_GET['service']) ? $_GET['service'] : 'overview';

        		$tpl = new ViewDescriptor($this->_setting('tpl.admincenter.main'));
        		
        		// hardcoded - just root can change identity
        		if($this->sp->ref('User')->getLoggedInUser()->getGroup()->getName() == 'root'){
        			$vu = $this->sp->ref('User')->getViewingUser();
        			$u = $this->sp->ref('User')->getLoggedInUser();
        			
        			$user = new SubViewDescriptor('view_as');
        			
        			if($vu->getId() != $u->getId()){
        				$tmp = new SubViewDescriptor('view_as_enabled');
        				$tmp->addValue('nick', ($vu->getNick() == '') ? '--' : $vu->getNick());
        				$user->addSubView($tmp);
        			} else {
        				$user->showSubView('view_as_disabled');
        			}
        			
        			$users = $this->sp->ref('User')->getUsers();
        			foreach($users as $u1){
        				if($u1->getId() != $u->getId()){
	        				$tmp = new SubViewDescriptor('view_as_user');
	        				$tmp->addValue('id', $u1->getId());
	        				$tmp->addValue('u_nick', ($u1->getNick() == '') ? '--' : $u1->getNick());
	        				$tmp->addValue('u_email', $u1->getEMail());
	        				$user->addSubView($tmp);
	        				unset($tmp);
        				}
        			}
        			$tpl->addSubView($user);
        		}
        		
        		$tpl->addValue('site_title', $this->_setting('site_title'));
        		
        		if($this->sp->ref('User')->getLoggedInUser()->getNick() == 'root' && isset($_SESSION['User']['defaultPwd']) && $service != 'profile' && $this->_setting('show_defaultPwd_alert')) $tpl->showSubView('defaultPassword');
        		
        		
        		$selected_menu = array();
        		
        		$services = $this->getServices();
        		foreach($services as $se){
        			if($this->isActivated($se['name'])){
        				$s = new SubViewDescriptor('menu_big_services');
        				$s1 = new SubViewDescriptor('menu_small_services');
        				
        				if($se['name'] == $service){
        					$selected_menu = $se;
        					$s->addValue('selected', 'sel');
        					$s1->addValue('selected', 'sel');
        				}
        				
        				$s->addValue('id', $se['id']);
        				$s->addValue('name', $se['name']);
        				$s->addValue('display', $se['display']);
        				$s->addValue('image', $se['image']);
        				
        				$s1->addValue('id', $se['id']);
        				$s1->addValue('name', $se['name']);
        				$s1->addValue('display', $se['display']);
        				$s1->addValue('image', $se['image']);
        				
        				$tpl->addSubView($s);
        				$tpl->addSubView($s1);
        				unset($s);
        				unset($s1);
        			}
        		}
        		
        		// special menu items
        		if($service == 'profile') {
        			$tpl->addValue('menu_profile', 'sel');
        			//$tpl->addValue('content', $this->sp->ref('User')->tplAdminProfile());
        			$tpl->addValue('content', $this->sp->ref('User')->admin(array('chapter'=>'profile')));
        		} else if($service == 'overview') {
        			$tpl->addValue('menu_overview', 'sel');
        			$tpl->addValue('content', $this->tplOverview());
        			
        		} else if($service == 'about') {
        			$tpl->addValue('menu_about', 'sel');
        			$tpl->addValue('content', $this->tplAbout());

        		} else {
        			// normal menu item
        			$o = $this->sp->ref($selected_menu['class']);

        			if(method_exists($o, 'handleAdminPost') && $_POST != array()) $o->handleAdminPost();
        			
        			$tpl->addValue('content', $o->admin($_GET));
        		}
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
           	}
        }
        
        public function tplSettings_($service, $setting_file){
        	if($service == 'Admincenter'){
        		$tpl = new ViewDescriptor($this->_setting('tpl.admincenter.settings'));
        		
        		// add Database Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_database'));
        		$s->addValue('id', 'Database');
        		$tpl->addSubView($s);
        		
        		$setting = $this->tplSetting_('Database', $this->sp->db->getSettings());
        		
        		// add Localization Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_localization'));
        		$s->addValue('id', 'Localization');
        		$tpl->addSubView($s);
        		
        		$setting .= $this->tplSetting_('Localization', $this->sp->loc->getSettings());
        		
        		// add Messages Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_messages'));
        		$s->addValue('id', 'Messages');
        		$tpl->addSubView($s);
        		
        		$setting .= $this->tplSetting_('Messages', $this->sp->ref('Messages')->getSettings());
        		
        		// add Rights Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_rights'));
        		$s->addValue('id', 'Rights');
        		$tpl->addSubView($s);
        		
        		$setting .= $this->tplSetting_('Rights', $this->sp->ref('Rights')->getSettings());
        		
        		// add Template Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_template'));
        		$s->addValue('id', 'Template');
        		$tpl->addSubView($s);
        		
        		$setting .= $this->tplSetting_('Template', $this->sp->ref('Template')->getSettings());
        		
        		// add User Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_user'));
        		$s->addValue('id', 'User');
        		$tpl->addSubView($s);
        		
        		$setting .= $this->tplSetting_('User', $this->sp->ref('User')->getSettings());
        		
        		$tpl->addValue('settings', $setting);
        		$tpl->addValue('service', $service);
        		$tpl->addValue('first_id', 'Database');
        		
        		// add rights tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_admincenter rights'));
        		$s->addValue('id', $service.'_rights');
        		$tpl->addSubView($s);
        		
        		return $tpl->render();
        	} else {
        		$tpl = new ViewDescriptor($this->_setting('tpl.admincenter.settings'));
        		
        		// add Setting Tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_settings'));
        		$s->addValue('id', $service);
        		$tpl->addSubView($s);
        		
        		$tpl->addValue('settings', $this->tplSetting_($service, $setting_file));
        		$tpl->addValue('service', $service);
        		$tpl->addValue('first_id', $service);
        		
        		// add rights tab
        		$s = new SubViewDescriptor('menu');
        		$s->addValue('name', $this->_('_rights'));
        		$s->addValue('id', $service.'_rights');
        		$tpl->addSubView($s);
        		
        		return $tpl->render();
        	}
        }
        
        private function tplSetting_($service, $setting_file){
        	if(isset($_SESSION['User']) && $this->sp->ref('Settings')->isAllowedToEditSettings($service)){				
        		$tpl = new ViewDescriptor($this->_setting('tpl.admincenter.settings_content'));
        		$tpl->addValue('service', $service);

        		foreach($setting_file->getGroups() as $g){
        			if(!$g->isHidden() || $this->sp->ref('Settings')->isAllowedToEditHiddenSettings($service)){
	        			$s = new SubViewDescriptor('groups');
	        			
	        			$s->addValue('name', $g->getDisplayName());
	        			$s->addValue('desc', $g->getDesc());
	        			
	        			$content_count = 0;
	        			foreach($g->getValues() as $v){
	        				if(!$v->isHidden() || $this->sp->ref('Settings')->isAllowedToEditHiddenSettings($service)){
		        				$s1 = new SubViewDescriptor('values');
		        				
		        				$s1->addValue('info', $v->getInfo());
		        				$s1->addValue('name', $v->getName());
		        				$s1->addValue('value', $v->getValue());
		        				$s1->addValue('service', $service);
		        				$s1->addValue('group', $g->getName());
		        				$s1->addValue('id', str_replace(array('.', ' ', '/'), array('_', '-', '_'), $g->getName().'_'.$v->getName()));
		        				
		        				switch($v->getType()){
		        					case SettingFile::TYPE_BOOLEAN:
		        						$input = $this->sp->ref('UIWidgets')->getWidget('Checkbox');
		        						if($v->getValue()) $input->setChecked();
		        						break;
		        					case SettingFile::TYPE_INT:
		        						$input = $this->sp->ref('UIWidgets')->getWidget('Counter');
		        						//$input->setClass('input_int');
		        						break;
		        					case SettingFile::TYPE_SELECT:
		        						$input = $this->sp->ref('UIWidgets')->getWidget('Select');
		        						foreach($v->getOptions() as $o){
		        							$input->addOption($o, $o, $o==$v->getValue());
		        						}
		        						break;
		        					default:
		        						$input = $this->sp->ref('UIWidgets')->getWidget('InputField');
		        						break;
		        				}
		        				
		        				if($input != null){
		        					$input->setId(str_replace(array('.', ' ', '/'), array('_', '-', '_'), $g->getName().'_'.$v->getName()));
		        					if($v->getType() != SettingFile::TYPE_SELECT) $input->setValue($v->getValue());
		        					$input->setLabel($v->getName());
		        					
		        					$s1->addValue('input', $input->render());
		        				}
		        				
		        				$s->addSubView($s1);
		        				unset($s1);
		        				$content_count++;
	        				}
	        			}
	        			
	        			if($content_count > 0) $tpl->addSubView($s);
	        			unset($s);
        			}
        		}

        		return $tpl->render();
	        		
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
           	}
        }
        
        public function tplOverview() {
        	if(isset($_SESSION['User'])){
        		$tpl = new ViewDescriptor($this->_setting('tpl.admincenter.overview'));
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
           	}
        }
        
        public function tplAbout(){
        	return 'about';
        }
        
        private function installService($name){
        	return $this->sp->ref($name)->setup();
        }
        
        /**
         * installs known services
         */
        public function installActivatedServices() {
        	$GLOBALS['installation'] = true;
        	$re = array();
        	$services = $this->getServices();
        	foreach($services as $se){
        		if(in_array(($se['name']), $this->config['setup_services'])){
        			$re[$se['display']] = ($this->installService($se['class']));
        		}
        	}
        	return $re;
        }
    }
?>
