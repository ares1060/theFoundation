<?php

	class ContactFormAdminView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'ContactForm';
			$this->dataHelper = $datahelper;
		}
	}	
?>