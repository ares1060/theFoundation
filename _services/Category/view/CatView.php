<?php
	class CatView extends TFCoreFunctions{
		private $config;
		protected $name;
		private $tree;
		
		
		function __construct($config, $tree){
			parent::__construct();
			$this->config = $config;
			$this->name = 'Category';
			$this->tree = $tree;
		}
		
		/**
		 * renders status dropdown 
		 * @param unknown_type $status
		 */
		public function tplStatusDropdown($status) {
			$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('cat_status');
        	$dropdown->setId('cat_status');
        	
        	$dropdown->addOption($this->_('_status_online'), Category::STATUS_ONLINE, $status==Category::STATUS_ONLINE);
        	$dropdown->addOption($this->_('_status_hidden'), Category::STATUS_OFFLINE, $status==Category::STATUS_OFFLINE);
        	
        	return $dropdown->render();
		}

		/**
		 * loads basic Template and adds Recursiv Tree rendering
		 * @param CatTreeNode $tree
		 */
		public function tplCategoryTreeAdmin(CatTreeNode $tree, $cat, $style, $service, $param){
			$t = new ViewDescriptor($this->config['tpl']['admin/categories_view']);
			
			$t->addValue('service', $service);
			$t->addValue('param', $param);
			
			foreach($tree->getChildren() as $child){
				$s = new SubViewDescriptor('children');
				
				$s->addValue('content', $this->renderCategoryTreeRec($child, $this->config['tpl']['admin/category_view_rec'], $cat, $service, $style, $style));

				$t->addSubView($s);
				unset($s);
			}
			return $t->render();
		} 
		
		/**
		 * renders Category Tree recursively
		 * @param CatTreeNode $tree
		 * @param unknown_type $first
		 */
		private function renderCategoryTreeRec(CatTreeNode $tree, $template, $cat, $service, $style, $first=true){
			$t = new ViewDescriptor($template);
			
			if($tree->getChildren() != array()){
				$c = '';
				foreach($tree->getChildren() as $child){
					$c .= $this->renderCategoryTreeRec($child, $template, $cat, $service, $style, false);
				}
				$t->addValue('children', $c);
			}
			switch($style){
				case 'radio':
					$s1 = new SubViewDescriptor('radio');
					break;
				default:
					$s1 = new SubViewDescriptor('divs');
					break;
			}
		
			$img = $this->sp->ref('Gallery')->getImage($tree->getCategory()->getImg());

			if($s1 != null){
				
				$s1->addValue('name', $tree->getCategory()->getName());
				$s1->addValue('id', $tree->getCategory()->getId());
				$s1->addValue('img_id', $tree->getCategory()->getImg());
					
				$s1->addValue('count', count($tree->getChildren()));
				$s1->addValue('parent_id', $tree->getParentId());
				$s1->addValue('service', $service);
				
				if($tree->getCategory()->getStatus() != Category::STATUS_ONLINE) $s1->showSubView('offline');
				
				if((int)$cat == $tree->getCategory()->getId()) {
					$s1->addValue('selected_class', 'sel');
					$s1->addValue('selected_with_class_attr', 'class="sel"');
					$s1->addValue('selected_radio', 'checked="checked"');
					$s1->addValue('selected_check', 'checked="checked"');
				}
				$t->addSubView($s1);
				unset($s1);
			}
			if($img != null && $template == $this->config['tpl']['admin/category_rec']) {
				$s2 = new SubViewDescriptor('img_exists');
				$s2->addValue('img_path', $img->getPath());
				$t->addSubView($s2);
				unset($s2);
			}
		
			$t->addValue('name', $tree->getCategory()->getName());
			$t->addValue('id', $tree->getCategory()->getId());
			$t->addValue('img_id', $tree->getCategory()->getImg());
			$t->addValue('count', count($tree->getChildren()));
			$t->addValue('parent_id', $tree->getParentId());
			$t->addValue('service', $service);
			if($tree->getCategory()->getStatus() != Category::STATUS_ONLINE) $t->showSubView('offline');
			
			if($first) $t->addValue('first', 'first');

			return $t->render();
		}
		
		/**
		 * renders Admincenter for a service
		 * @param CatTreeNode $tree
		 * @param $service
		 */
		public function tplAdmincenter(CatTreeNode $tree, $service){
			if($this->checkRight('administer_category', $service)){
        		$t = new ViewDescriptor($this->config['tpl']['admin/categories']);
			
				$t->addValue('service', $service);
				
				foreach($tree->getChildren() as $child){
					$s = new SubViewDescriptor('children');
					
					$s->addValue('content', $this->renderCategoryTreeRec($child, $this->config['tpl']['admin/category_rec'], -1, $service, '//divs'));
					
					$t->addSubView($s);
					unset($s);
				}
	
				return $t->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
        	}
        	
		}
		
		public function tplEditCategory($node, $service){
			if($this->checkRight('administer_category', $service)){
				$t = new ViewDescriptor($this->config['tpl']['admin/edit_category']);
				
				$img = $this->sp->ref('Gallery')->getImage($node->getCategory()->getImg());
				
				$t->addValue('name', $node->getCategory()->getName());
				$t->addValue('id', $node->getCategory()->getId());
				$t->addValue('img_id', $node->getCategory()->getImg());
				$t->addValue('img_path', $img == null ? '' : $img->getPath());
				$t->addValue('node_id', $node->getId());
				$t->addValue('webname', $node->getCategory()->getWebName());
				$t->addValue('service', $this->tree->getServiceForNodeId($node->getId()));
				$t->addValue('status', $this->tplStatusDropdown($node->getCategory()->getStatus()));
				
				$desc = $this->sp->ref('UIWidgets')->getWidget('Wysiwyg');
        		$desc->setId('cat_desc');
        		$desc->setName('cat_desc');
        		//$desc->setAddImages($this->name, $product->getId());
        		$desc->setValue($node->getCategory()->getDesc());
        		
        		$t->addValue('desc_textarea', $desc->render());
				
				return $t->render();
			} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
        	}
		}
		
		
	}
?>