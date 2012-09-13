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
					
				if($s != '') {
					$tt = new SubViewDescriptor('more');
					$tt->addValue('id', $album->getId());
					$menu->addSubView($tt);
					unset($tt);
					
					$menu->addValue('moreClass', 'more');
				} else $menu->showSubView('not_more');
				
				$t->addSubView($menu);
				
				unset($menu);
				unset($f);
			}
			
			$t->addValue('firstAlbum', $firstAlbum);
			
			return $t->render();
		}
		
		public function tplFolder($id){
			$folder = $this->dataHelper->getFolderById($id, array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE));

			if($folder->getUserId() == $this->sp->ref('User')->getViewingUser()->getId()) {
				$images = $this->dataHelper->getImagesByFolderId($id);
				
				$tpl = new ViewDescriptor($this->_setting('tpl.admin/view_folder'));
				
				$tpl->addValue('count', count($images));
				
				foreach($images as $i){
					$t = new SubViewDescriptor('image');
					$t->addValue('name', $i->getName());
					$t->addValue('id', $i->getId());
					$t->addValue('path', $i->getPath());
					
					$tpl->addSubView($t);
					
					unset($t);
				}
				
// 				print_r($images);
				return $tpl->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
			
// 			return 'Folder: '.$id;
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
					
					if($s != '') {
						$tmp->addValue('moreClass', 'more');
						
						$tt = new SubViewDescriptor('more');
						$tt->addValue('id', $folder->getId());
						$tmp->addSubView($tt);
						unset($tt);
					} else $tmp->showSubView('not_more');

					$return .= $tmp->render();
				}
				
				return $return;
			}
		}
	}
?>