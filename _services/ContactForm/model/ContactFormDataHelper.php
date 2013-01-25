<?php
	class ContactFormDataHelper extends TFCoreFunctions {
		protected $name = 'ContactForm';
		
		function __construct($settings) {
			parent::__construct();
			$this->setSettingsCore($settings);
		}
		
		public function getContactformById($id) {
			if($id > -1) {
				$query = $this->mysqlArray('SELECT cf.id form_id, cf.name form_name, cf.mail form_mail, 
												   cf.u_id form_userid, cf.created form_created, 
						  						   cfe.id elem_id, cfe.name elem_name, cfe.label elem_label,
												   cfe.forced elem_forced, cfe.type elem_type, cfe.combogroup elem_group,
												   cfe.sort elem_sort, cfe.param elem_param
						
													FROM '.$GLOBALS['db']['db_prefix'].'contactform cf 
													LEFT JOIN '.$GLOBALS['db']['db_prefix'].'contactform_elements cfe ON cf.id = cfe.f_id
													WHERE cf.id = "'.mysql_real_escape_string($id).'" 
														AND cfe.status = "'.mysql_real_escape_string(ContactForm::STATUS_ONLINE).'" 
													ORDER BY cfe.sort ASC');
				
				if($query != null && $query != array()){
					
					$name = '';
					$mail= '';
					$id = -1;
					$created = 0;
					$u_id = -1;
					$elements = array();
					
					foreach($query as $row){
						if($id == -1){
							$id = $row['form_id'];
							$name = $row['form_name'];
							$created = $row['form_created'];
							$u_id = $row['form_userid'];
							$mail = $row['form_mail'];
						}
						
						$elements[] =  new ContactFormElement($row['elem_id'], $row['elem_type'], 
															  $row['elem_name'], $row['elem_label'], 
															  $row['elem_forced'], $row['elem_group'], 
															  $row['elem_sort'], $row['elem_param']);				
					}
					
					return new ContactFormForm($this, $id, $name, $mail, $u_id, $created, $elements);
					
				} else return null;
			} else return null;
		}
		
	}
?>