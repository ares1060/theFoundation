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
		/**
		 * returnes renderes Register form for given group
		 * @param unknown_type $group
		 */
		public function tplRegister($group){
			$render = new ViewDescriptor($this->_setting('tpl.register_form-'.$group));
			$render->addValue('group', $group);
		
			$data = $this->dataHelper->getUserDataForGroup($group);
			$groups = array();
			if($data != null){
				foreach($data as $d){
					
					// save by name to display special group exactly where you want them
					
					if(!isset($groups[$d->getGroup()->getId()])) {
						$groups[$d->getGroup()->getId()]['view'] = new SubViewDescriptor('userDataGroup.'.$d->getGroup()->getName());
						$groups[$d->getGroup()->getId()]['view']->addValue('group_name', $d->getGroup()->getName());
						$groups[$d->getGroup()->getId()]['view']->addValue('group_id', $d->getGroup()->getId());
//						print_r($groups[$d->getGroup()->getId()]);
						$groups[$d->getGroup()->getId()]['used'] = false;
					}
					
					if($d->isVisibleAtRegister()) {
						$groups[$d->getGroup()->getId()]['used'] = true;
						
						$tmp = new SubViewDescriptor('userDataGroup.'.$d->getGroup()->getName().'.'.$d->getType());
						if($d->isForcedAtRegister()) $tmp->showSubView('forced');
						$tmp->addValue('id', $d->getId());
						$tmp->addValue('group', $d->getGroup()->getName());
						$tmp->addValue('group_id', $d->getGroup()->getId());
						$tmp->addValue('info', $d->getInfo());
						$tmp->addValue('name', $d->getName());
						
						$groups[$d->getGroup()->getId()]['view']->addSubView($tmp);
					}
					
					
					
					/*
					$groups[$d->getGroup()->getId()]->addValue('data_'.$d->getId().'_id', $d->getId());
					$groups[$d->getGroup()->getId()]->addValue('data_'.$d->getId().'_name', $d->getName());
					//echo 'data_'.$d->getId().'_subview_'.$d->getGroup()->getId();
					
					$tmp = new SubViewDescriptor('data_'.$d->getId().'_subview_'.$d->getGroup()->getId());
					$tmp->addValue('data_'.$d->getId().'_id', $d->getId());
					$tmp->addValue('data_'.$d->getId().'_name', $d->getName());

					$groups[$d->getGroup()->getId()]->addSubView($tmp);
					
					$groups[$d->getGroup()->getId()]->addValue('data_'.$d->getId().'_type', $d->getType());
					$groups[$d->getGroup()->getId()]->addValue('data_'.$d->getId().'_info', $d->getInfo());
					$groups[$d->getGroup()->getId()]->addValue('data_'.$d->getId().'_required', ($d->getVisibleAtRegister() == User::VISIBILITY_FORCED) ? 'true' : 'false');
					*/
				}
				
				foreach($groups as $group){
					if($group['used']) $render->addSubView($group['view']);
				}
			}
			return $render->render();
		}
		/* ======   User Menu ======= */
		public function tplUserMenu() {
			if($this->sp->ref('User')->isLoggedIn()){
				$view = new ViewDescriptor($this->_setting('tpl.usermenu_loggedin'));
			
				$u = $this->sp->ref('User')->getLoggedInUser();

				if($u != null){
					$view->addValue('id', $u->getId());
					$view->addValue('nick', $u->getNick());
					$view->addValue('email', $u->getEmail());
					$view->addValue('group', $u->getGroup()->getId());
					
					return $view->render();
				} else return 'error';
			} else {
				$view = new ViewDescriptor($this->_setting('tpl.usermenu_loggedout'));
			
				return $view->render();
			}
		}
	}
?>