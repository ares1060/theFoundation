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
		
		public function tplProfile() {
			$view = new ViewDescriptor($this->_setting('usercenter.profile'));
			return $view->render();
		}
		
		public function tplProfileData() {
			
			$view = new ViewDescriptor($this->_setting('usercenter.profile_data'));
			
			$u = $this->sp->ref('User')->getLoggedInUser();
			
			$view->addValue('nick', $u->getNick());
			$view->addValue('email', $u->getEmail());
					
			return $view->render();
		}
	
		public function tplProfileNotifications() {
			$view = new ViewDescriptor($this->_setting('usercenter.profile_notification'));
			return $view->render();
		}
	
		public function tplProfilePrivacy() {
			$view = new ViewDescriptor($this->_setting('usercenter.profile_privacy'));
			return $view->render();
		}
	}
?>