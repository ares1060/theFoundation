<?php

	class ContactFormUserView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();

			$this->setSettingsCore($settings);
			$this->name = 'ContactForm';
			$this->dataHelper = $datahelper;
		}
		
		public function tplContactFormById($id) {
			$form = $this->dataHelper->getContactformById($id);

			if($id > 0 && $form != null) {
				
				$tpl = new ViewDescriptor($this->_setting('tpl.view/form'));

				foreach($form->getElements() as $element){
					$tmp1 = new SubViewDescriptor('element');

					switch($element->getType()){
						case 0;
						case 2;
						case 3;
						case 5;
							$tmp = new SubViewDescriptor('type_0');
							break;
						default:
							$tmp = new SubViewDescriptor('type_'.$element->getType());
							break;
					}
					
					if($element->getParam('w') == 'small') {
						$tmp->addValue('small', 'class="small"');
					}
					
					// if submit failed get old input data
					if(isset($_SESSION['ContactForm_error']) && $_SESSION['ContactForm_error'] == true &&
							isset($_POST['elements']) && isset($_POST['elements'][$element->getId()])) {
						
						$tmp->addValue('value', $_POST['elements'][$element->getId()]);
					}
					
					$tmp->addValue('name', $element->getName());
					$tmp->addValue('label', $element->getLabel());
					$tmp->addValue('id', $element->getId());
					$tmp->addValue('form_id', $form->getId());
					if($element->isForced()) $tmp->showSubView('forced');
						
					$tmp1->addSubView($tmp);
					$tpl->addSubView($tmp1);
					unset($tmp);
					unset($tmp1);
					//print_r($element);
				}
				
				$tpl->addValue('form_id', $form->getId());
				
				return $tpl->render();
			} else {
				$this->_msg($this->_('_Form not found'), Messages::ERROR);
				return '';
			}
		}
		
	}	
?>