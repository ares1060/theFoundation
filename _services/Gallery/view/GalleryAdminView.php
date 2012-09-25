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
			$albums = $this->dataHelper->getFolders(array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE), 0, 'default');
			
			$firstAlbum = -1;
			
			foreach($albums as $album){
				if($firstAlbum == -1) $firstAlbum = $album->getId();
				$menu = new SubViewDescriptor('side_menu');
				$menu->addValue('name', $album->getName());
				$menu->addValue('id', $album->getId());
				$menu->addValue('date', $album->getCreationDate());
				
				if($album->getStatus() == GalleryDataHelper::STATUS_HIDDEN) { $menu->showSubView('hidden'); $menu->showSubView('hidden1'); }
				if($album->getStatus() == GalleryDataHelper::STATUS_ONLINE) { $menu->showSubView('visible'); $menu->showSubView('visible1'); }
				
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
		
		public function tplFolder($id, $page=-1){
			$folder = $this->dataHelper->getFolderById($id);

			if($folder != null && $folder->getUserId() == $this->sp->ref('User')->getViewingUser()->getId()) {
				$tpl = new ViewDescriptor($this->_setting('tpl.admin/view_folder'));
				
				// page calculation and pagina creation
				$per_page = $this->_setting('admin.per_page.images');
				$count = $this->dataHelper->getImageCountByFolder($id, array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE));
				
				$all_pages = ceil($count / $per_page);
				$final_page = ($page > 0 && $page <= $all_pages) ? $page : 1;
				
				$tpl->addValue('pagina_count', $all_pages);
				$tpl->addValue('pagina_active', ($page == -1) ? 1 : $page);
				$tpl->addValue('count', $count);
				
				$images = $this->dataHelper->getImagesByFolder($id, $final_page, array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE));
				
				foreach($images as $i){
					$t = new SubViewDescriptor('image');
					$t->addValue('name', $i->getName());
					$t->addValue('id', $i->getId());
					$t->addValue('path', $i->getPath());
					
					$tpl->addSubView($t);
					
					unset($t);
				}
				
				return $tpl->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
			
// 			return 'Folder: '.$id;
		}
		
		/* Recursive functions for subfolder */
		
		private function recSubFolderCreation($id) {
			$subfolders = $this->dataHelper->getFolders(array(GalleryDataHelper::STATUS_HIDDEN, GalleryDataHelper::STATUS_ONLINE), $id, 'default');
			if($this->depth <= $this->_setting('admin.subfolder.depth') && $subfolders != array()) {
				$this->depth ++;
				
				$return = '';
				
				foreach($subfolders as $folder) {
					$tmp = new ViewDescriptor($this->_setting('tpl.admin/part.subfolder'));
					
					if($folder->getStatus() == GalleryDataHelper::STATUS_HIDDEN) { $tmp->showSubView('hidden'); $tmp->showSubView('hidden1'); }
					if($folder->getStatus() == GalleryDataHelper::STATUS_ONLINE) { $tmp->showSubView('visible'); $tmp->showSubView('visible1'); }
					
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
				$this->depth --;
				
				return $return;
			}
		}
		
		public function tplUpload($selectedFolder) {
			$folder = $this->dataHelper->getFolderById($selectedFolder);
			
			$tpl = new ViewDescriptor($this->_setting('tpl.admin/upload'));
			
			return $tpl->render();
		}
	}
?>