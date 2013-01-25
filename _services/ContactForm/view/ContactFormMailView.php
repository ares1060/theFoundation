<?php

	class ContactFormMailView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'ContactForm';
			$this->dataHelper = $datahelper;
		}
		
		public function sendForm() {
			if(isset($_POST['tf_form_id'])){
				
				$_SESSION['ContactForm_error'] = false;
				
				$form = $this->dataHelper->getContactformById($_POST['tf_form_id']);
		
				$mail = new ViewDescriptor($this->_setting('tpl.view/mail'));
				
				foreach($form->getElements() as $element){
					//check forced
					if($element->isForced() && (!isset($_POST['elements'][$element->getId()]) || $_POST['elements'][$element->getId()] == '' )){
						$this->_msg(str_replace('{name}', $element->getName(), $this->_('_Please Fillout {name}')), Messages::ERROR);
						// save error in session so that contactform will have the input data
						// even after reload
						$_SESSION['ContactForm_error'] = true;
					}
						
					$tmp = new SubViewDescriptor('element');
					$tmp->addValue('name', $element->getName());
					$tmp->addValue('label', $element->getLabel());
					$tmp->addValue('id', $element->getId());
					
					// check set elements for right content
					if(isset($_POST['elements'][$element->getId()])){
						//TODO: check Mail, check Tel, check Dropdowns, check Numbers, check Town, check PLZ, ...
						
						// do value replace
						$tmp->addValue('value', $_POST['elements'][$element->getId()]);
					}
					
					$mail->addSubView($tmp);
					unset($tmp);
				}
		
				if(!$_SESSION['ContactForm_error']) {
					//send mail
// 					print_r($_POST);
					
					if($this->sp->ref('Mail')->send($this->_setting('mail.email'), $this->_('_Contact Form'), $mail->render())){
						$this->_msg($this->_('_Submitted Sucessfully'), Messages::INFO);
					} else {
						$this->_msg($this->_('_Could not submit', 'core'), Messages::ERROR);
						$_SESSION['ContactForm_error'] = true;
					}
				}
					
			}
		}
	}	
?>