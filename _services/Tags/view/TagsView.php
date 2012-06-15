<?php
	class TagsView extends TFCoreFunctions {
		private $config;
		private $dataHelper;
		
		function __construct($config, TagsHelper &$dataHelper){
        	parent::__construct();
        	$this->config = $config;
        	$this->dataHelper = $dataHelper;
        }
        
        /**
         * returnes rendered Template for Tags by Service and Param
         * 
         * @param $service
         * @param $param
         */
        public function getTags($service, $param, $link=''){
        	$tags = $this->dataHelper->getTagsByService($service, $param);
        	$tpl = new ViewDescriptor($this->tpl('service_tags'));
        	
        	

        	foreach($tags as $tag){
        		$t = new SubViewDescriptor('tag');
        		
        		$t->addValue('id', $tag->getId());
        		$t->addValue('service', $service);
        		$t->addValue('name', $tag->getName());
        		$t->addValue('webname', $tag->getWebname());
        		
        		$t->addValue('link', str_replace(array('{id}', '{name}', '{webname}'),
        										array($tag->getId(), $tag->getName(), $this->sp->ref('TextFunctions')->string2Web($tag->getName())), $link));
        		
        		$tpl->addSubView($t);
        		unset($t);
        	}
        	return $tpl->render();
        }
        
		/**
         * returnes rendered Template for TagCloud by Service and Param
         * 
         * @param $service
         * @param $param
         */
        public function getTagCloud($service, $param){
        	$tags = $this->dataHelper->getTagsByService($service, $param);
        	
        	shuffle($tags);
        	
        	$count = array();
        	$max_count = 0;
        	
        	foreach($tags as $tag){
        		$c = $this->dataHelper->getTagCount($tag->getId(), $service);
        		$count[$tag->getId()] = $c;
        		if($c > $max_count) $max_count = $c;
        	}
        	
        	$tpl = new ViewDescriptor($this->tpl('service_tag_cloud'));

        	foreach($tags as $tag){
        		$t = new SubViewDescriptor('tag');
        		
        		$t->addValue('id', $tag->getId());
        		$t->addValue('service', $service);
        		$t->addValue('name', $tag->getName());
        		$t->addValue('size', round($this->config['max_tag_cloud_size']/($max_count/$count[$tag->getId()])));
        		
        		$tpl->addSubView($t);
        		unset($t);
        	}
        	return $tpl->render();
        }
        
        
        private function tpl($name){
        	return (isset($this->config['tpl'][$name])) ? $this->config['tpl'][$name] : '';
        }
        
		/**
         * returnes rendered Template for Tags by Service and Param
         * 
         * @param $service
         * @param $param
         */
        public function getAdminTags($service, $param){
        	$tags = $this->dataHelper->getTagsByService($service, $param);
        	$tpl = new ViewDescriptor($this->tpl('service_admin_tags'));
        	$tpl->addValue('service', $service);
        	$tpl->addValue('param', $param);
        	
        	foreach($tags as $tag){
        		$t = new SubViewDescriptor('tag');
        		
        		$t->addValue('id', $tag->getId());
        		$t->addValue('service', $service);
        		$t->addValue('param', $param);
        		$t->addValue('name', $tag->getName());
        		
        		$tpl->addSubView($t);
        		unset($t);
        	}
        	return $tpl->render();
        }
	}
?>