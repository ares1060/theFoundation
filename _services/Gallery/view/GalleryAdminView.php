<?php
	class GalleryAdminView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'Gallery';
			$this->dataHelper = $datahelper;
		}
		
		public function tplAdmincenter(){
			$t = new ViewDescriptor($this->_setting('tpl.admin/admincenter'));
			
			return $t->render();
		}
	}
?>