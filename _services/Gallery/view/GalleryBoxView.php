<?php

class GalleryBoxView extends TFCoreFunctions{
	protected $name;
	
	private $dataHelper;
	
	function __construct($settings, $datahelper){
		parent::__construct();
		$this->setSettingsCore($settings);
		$this->name = 'Gallery';
		$this->dataHelper = $datahelper;
	}
	
	function tplBox($folder, $subfolder_name, $page, $style=self::BOX_VIEW_MATRIX, $reloadFunctionName='', $useFunctionName='', $link=''){
		$subfolder = $this->dataHelper->getSubFolderByName($folder, $subfolder_name);

		if($subfolder != null && $this->checkRight('administerFolder', $subfolder->getId())) {//$subfolder->getUserId() == $this->sp->ref('User')->getViewingUser()->getId()){
			switch($style){
				default:
					$tpl = new ViewDescriptor($this->_setting('tpl.box/view_folder'));
					break;
			}
						
			$tpl->addValue('name', $subfolder->getName());
			$tpl->addValue('subfolder_id', $subfolder->getId());
			$tpl->addValue('subfolder_name', $subfolder_name);

			// javascript functionnames
			$tpl->addValue('useFunctionName', $useFunctionName);
				
			// page calculation and pagina creation
			$per_page = $this->_setting('box.per_page.images');
			$count = $this->dataHelper->getImageCountByFolder($subfolder, array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE));
			
			$all_pages = ceil($count / $per_page);
			$final_page = ($page > 0 && $page <= $all_pages) ? $page : 1;
			
			$tpl->addValue('pagina_count', $all_pages);
			$tpl->addValue('pagina_active', ($page == -1) ? 1 : $page);
			$tpl->addValue('count', $count);
			
			$tpl->addValue('max_file_size', $this->_setting('upload.max_file_size'));
			$tpl->addValue('max_uploads', $this->_setting('upload.max_uploads'));
			$tpl->addValue('types', $this->_setting('upload.valid_file_types'));
			
			$tpl->addValue('link', $link);
				
			$images = $this->dataHelper->getImagesByFolder($subfolder, $final_page, $this->_setting('box.per_page.images'), array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE));
			
			if($images != null){
				foreach($images as $i){
					$time = time();
					$t = new SubViewDescriptor('image');
					$t->addValue('name', $this->sp->ref('TextFunctions')->cropText($i->getName(), 10));
					$t->addValue('id', $i->getId());
					$t->addValue('time', $time);
					$t->addValue('path', $i->getPath());
						
					$tpl->addSubView($t);
						
					unset($t);
				}
			}
			
			return $tpl->render();
		}  else {
			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        	return '';
		}
	}
}

?>