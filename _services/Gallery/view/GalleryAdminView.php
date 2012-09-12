<?php
	class GalleryAdminView extends TFCoreFunctions{
		protected $name;
		
		private  $dataHelper;

		function __construct($settings, GalleryDataHelper $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'Gallery';
			$this->dataHelper = $datahelper;
		}
		
		public function tplAdmincenter(){
			$t = new ViewDescriptor($this->_setting('tpl.admin/admincenter'));
			
			// get all album folders by active user sorted by default(id)
			$albums = $this->dataHelper->getFolders(array(GalleryDataHelper::STATUS_OFFLINE, GalleryDataHelper::STATUS_ONLINE), 0, 'default');
			
			$firstAlbum = -1;
			
			foreach($albums as $album){
				if($firstAlbum == -1) $firstAlbum = $album->getId();
				$menu = new SubViewDescriptor('side_menu');
				$menu->addValue('name', $album->getName());
				$menu->addValue('id', $album->getId());
				$menu->addValue('date', $album->getCreationDate());
				
				if($album->getStatus() == GalleryDataHelper::STATUS_OFFLINE) $menu->showSubView('hidden');
				if($album->getStatus() == GalleryDataHelper::STATUS_ONLINE) $menu->showSubView('visible');
				
				// get all subfolders and push them into subfolder var
				$this->depth = 1;
				
				$s = $this->recSubFolderCreation($album->getId());
				$menu->addValue('subfolder', $s);
					
				if($s != '') $menu->addValue('moreArrow', 'more');
				
				$t->addSubView($menu);
				
				unset($menu);
				unset($f);
			}
			
			$t->addValue('firstAlbum', $firstAlbum);
			
			return $t->render();
		}
		
		/* Recursive functions for subfolder */
		
		private function recSubFolderCreation($id) {
			$subfolders = $this->dataHelper->getFolders(array(GalleryDataHelper::STATUS_OFFLINE, GalleryDataHelper::STATUS_ONLINE), $id, 'default');
			if($this->depth <= $this->_setting('admin.subfolder.depth') && $subfolders != array()) {
				$this->depth ++;
				
				$return = '';
				
				foreach($subfolders as $folder) {
					$tmp = new ViewDescriptor($this->_setting('tpl.admin/part.subfolder'));
					
					if($folder->getStatus() == GalleryDataHelper::STATUS_OFFLINE) $tmp->showSubView('hidden');
					if($folder->getStatus() == GalleryDataHelper::STATUS_ONLINE) $tmp->showSubView('visible');
					
					$tmp->addValue('name', $folder->getName());
					$tmp->addValue('id', $folder->getId());
					$tmp->addValue('date', $folder->getCreationDate());
					
					$s = $this->recSubFolderCreation($folder->getId());
					$tmp->addValue('subfolder', $s);
					
					if($s != '') $tmp->addValue('moreArrow', 'class="more"');

					$return .= $tmp->render();
				}
				
				return $return;
			}
		}
	}
?>