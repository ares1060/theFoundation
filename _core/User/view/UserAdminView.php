<?php
	class UserAdminView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'User';
			$this->dataHelper = $datahelper;
		}
		/* ======   INTERFACE ADMINCENTER ======= */
		/**
		 * returnes renderes Dropdown of all Visible User groups
		 * @param unknown_type $id
		 */
		public function tplGetGroupDropdown($id){
			$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('eu_group');
        	$dropdown->setId('eu_group');
        	
        	$groups = $this->dataHelper->getGroups();

        	foreach($groups as $group){
        		if($this->checkRight('administer_group', $group->getId()) || $sel==$group->getId()) $dropdown->addOption($group->getName(), $group->getId(), $id==$group->getId());
        	}
        	
        	return $dropdown->render();
		}
		/**
		 * returnes rendered Status Dropdown
		 * @param unknown_type $status
		 */
		public function tplGetStatusDropdown($status=-1) {
           	$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('eu_status');
        	$dropdown->setId('eu_status');
        	
        	$dropdown->addOption($this->_('_Status: Active', 'core'), User::STATUS_ACTIVE, $status==User::STATUS_ACTIVE);
        	$dropdown->addOption($this->_('_Status: Blocked', 'core'), User::STATUS_BLOCKED, $status==User::STATUS_BLOCKED);
        	$dropdown->addOption($this->_('_Status: Deleted', 'core'), User::STATUS_DELETED, $status==User::STATUS_DELETED);
        	$dropdown->addOption($this->_('_Status: Has to activate', 'core'), User::STATUS_HAS_TO_ACTIVATE, $status==User::STATUS_HAS_TO_ACTIVATE);
        	return $dropdown->render();
        }
		
		/* ======   Template Profile ======= */
		public function tplProfile() {
			if($this->sp->ref('User')->isLoggedIn()){
				$view = new ViewDescriptor($this->_setting('usercenter.profile'));
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
		
		public function tplProfileData() {
			if($this->sp->ref('User')->isLoggedIn()){
				$view = new ViewDescriptor($this->_setting('usercenter.profile_data'));
			
				$u = $this->sp->ref('User')->getLoggedInUser();

				if($u != null){
					$view->addValue('nick', $u->getNick());
					$view->addValue('email', $u->getEmail());
					
					return $view->render();
				} else return 'error';
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
	
		public function tplProfileNotifications() {
			if($this->sp->ref('User')->isLoggedIn()){
				$view = new ViewDescriptor($this->_setting('usercenter.profile_notification'));
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}	
		}
	
		public function tplProfilePrivacy() {
			if($this->sp->ref('User')->isLoggedIn()){
				$view = new ViewDescriptor($this->_setting('usercenter.profile_privacy'));
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
		/* ======   Usercenter ======= */
		public function tplUsercenter() {
			if($this->sp->ref('User')->isLoggedIn() && $this->checkRight('usercenter')){
				$view = new ViewDescriptor($this->_setting('usercenter.main'));
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
		/* ======   User ======= */
		public function tplUser($page=1) {
			if($this->sp->ref('User')->isLoggedIn() && $this->checkRight('usercenter')){
				if($page < -1) $page = 0;
        		
				$view = new ViewDescriptor($this->_setting('usercenter.user'));
				
        		$view->addValue('pagina_active', $page);
        		$view->addValue('pagina_count', ceil($this->dataHelper->getAllUserCount(-1, -1)/$this->_setting('perpage.user')));
        			
        		$user = $this->dataHelper->getUsers($page);
				
        		foreach($user as $u){
        			$stpl = new SubViewDescriptor('user');
        			
        			$stpl->addValue('id', $u->getId());
        			$stpl->addValue('nick', $u->getNick());
        			$stpl->addValue('email', $u->getEmail());
        			$stpl->addValue('status', $u->getStatus());
        			$stpl->addValue('group', $u->getGroup()->getName());
        			$stpl->addValue('group_id', $u->getGroup()->getId());
        			
        			if(($this->checkRight('administer_group', $u->getGroup()->getId()) || $this->checkRight('edit_user', $u->getId()))) {
        				$sub = new SubViewDescriptor('edit');
        				$sub->addValue('id', $u->getId());
        				
        				$stpl->addSubView($sub);
        				unset($sub);
        			}
        			
        			$view->addSubView($stpl);
        			unset($stpl);
        		}
				
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}	
		}
		public function tplUserEdit($id) {
			$user = $this->dataHelper->getUser($id);
			if($this->sp->ref('User')->isLoggedIn() && $this->checkRight('usercenter') &&  ($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup()->getId()))){
				$view = new ViewDescriptor($this->_setting('usercenter.edit_user'));
				
				$view->addValue('id', $user->getId());
        		$view->addValue('nick', $user->getNick());
        		$view->addValue('email', $user->getEMail());
        		$view->addValue('group', $this->tplGetGroupDropdown($user->getGroup()->getId()));
        		$view->addValue('groupId', $user->getGroup()->getId());
				
        		$s = new SubViewDescriptor('status');
        		$s->addValue('status', $this->tplGetStatusDropdown($user->getStatus()));
        		$view->addSubView($s);
        		unset($s);
        		//TODO: Userdata
        		
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}
		}
		public function tplUserNew() {
			if($this->sp->ref('User')->isLoggedIn() && $this->checkRight('usercenter') &&  ($this->checkRight('administer_user'))){
				$view = new ViewDescriptor($this->_setting('usercenter.new_user'));
				
        		$view->addValue('group', $this->tplGetGroupDropdown(-1));
        		$view->addValue('status', $this->tplGetStatusDropdown(-1));
				
        		//TODO: Userdata
        		
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}
		}
		/* ======   Userdata ======= */
		public function tplUserData($page=1){
			if($this->sp->ref('User')->isLoggedIn() && $this->checkRight('usercenter')){
				if($page < -1) $page = 0;
        		
				$view = new ViewDescriptor($this->_setting('usercenter.userdata'));
				
        		$view->addValue('pagina_active', $page);
        		$view->addValue('pagina_count', ceil($this->dataHelper->getAllUserDataCount(-1, -1)/$this->_setting('perpage.user_data')));
        			
        		$user = $this->dataHelper->getUserData($page);
        		$usergroups = $this->dataHelper->getGroups();

        		foreach($usergroups as $ug){
        			$t = new SubViewDescriptor('usergroups_header');
        			$t->addValue('id', $ug->getId());
        			$t->addValue('name', $ug->getName());
        			
        			$view->addSubView($t);
        			unset($t);
        		}
        		
        		foreach($user as $u){
        			$stpl = new SubViewDescriptor('userdata');
        			
        			$stpl->addValue('id', $u->getId());
        			$stpl->addValue('name', $u->getName());
        			$stpl->addValue('group', $u->getGroup()->getName());
        			$stpl->addValue('group_id', $u->getGroup()->getId());
        			$stpl->addValue('type', $u->getType());
        			$stpl->addValue('info', $u->getInfo());
        			$stpl->addValue('vis_register', ($u->getVisibleAtRegister()) ? 'yes' : 'no');
        			$stpl->addValue('vis_edit', ($u->getVisibleAtEdit()) ? 'yes' : 'no');
        			$stpl->addValue('vis_login', ($u->getVisibleAtLogin()) ? 'yes' : 'no');
        			
        			$stpl->addValue('group_id', $u->getGroup()->getId());
        			
        			foreach($usergroups as $ug){
        				$t = new SubViewDescriptor('userdata_group');
        				$t->addValue('enabled', ($u->usedByGroup($ug->getId())) ? 'ja' : 'nein');
        				
        				$stpl->addSubView($t);
        				unset($t);
        			}
        			
        			$view->addSubView($stpl);
        			unset($stpl);
        		}
				
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}	
			return 'userdata_new';
		}
		
	}
?>