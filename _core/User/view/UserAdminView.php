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
			return 'data';
		}
	
		public function tplProfileNotifications() {
			return 'notifications';
		}
	
		public function tplProfilePrivacy() {
			return 'prvacy';
		}
	}
?>