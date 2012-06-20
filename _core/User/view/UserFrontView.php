<?php
	class UserFrontView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'User';
			$this->dataHelper = $datahelper;
		}
		
		/**
		 * returnes login form
		 * @param $target
		 */
		public function tplLogin($target='') {
			$render = new ViewDescriptor($this->_setting('tpl.login_form'));
			$render->addValue('target', $target);
			return $render->render();
		}
	}
?>