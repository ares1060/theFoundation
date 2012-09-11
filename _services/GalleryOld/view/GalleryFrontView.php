<?php
	class GalleryFrontView extends TFCoreFunctions {
		protected $name;
		
		private $config;
		private $dataHandler;
		
		const VIEWTYPE_BROWSER = 'browser';
		const VIEWTYPE_SPLIT = 'split';
		const VIEWTYPE_VIEWER = 'viewer';

		function __construct($config, $datahandler){
			parent::__construct();
			$this->config = $config;
			$this->name = 'Gallery';
			$this->dataHandler = $datahandler;
		}	
		
		/** ========= SMALL Galleries ========== **/
		
		/**
		 * returnes renderes folder by id or folder name and album id
		 * @param unknown_type $album_id
		 * @param unknown_type $folder_
		 * @param unknown_type $page
		 * @param unknown_type $sort
		 * @param unknown_type $sortDA
		 * @param unknown_type $type
		 * @param unknown_type $perPage
		 */
		public function tplSmallFolder($album_id, $folder_, $page=1, $sort='', $sortDA='', $type='', $perPage=-1, $justOnePage=true){
			$folder = $this->dataHandler->getFolderById($folder_);
			if($folder == null) $folder = $this->dataHandler->getFolderByAlbumAndName($album_id, $folder_);

			if($folder != null){
				$type = ($type == '') ? self::VIEWTYPE_BROWSER : $type;
				
				$tpl = new ViewDescriptor($this->config['tpl']['view/'.$type.'/folder']);
				$tpl->addValue('album_id', $album_id);
				$tpl->addValue('folder_id', $folder->getId());
				$tpl->addValue('folder_name', $folder->getName());
				
				$images = $this->dataHandler->getImagesByFolder($folder->getId(), $page, $perPage, $sort, $sortDA, Gallery::STATUS_ONLINE);
				$all_images = $this->dataHandler->getImagesByFolder($folder->getId(), -1, -1, '', '', Gallery::STATUS_ONLINE);
				$more_images = count($all_images) - count($images);
				
				
				$used = array();
				
				foreach($images as $img){
					$s = new SubViewDescriptor('image');
					$s->addValue('id', $img->getId());
					$s->addValue('name', $img->getName());
					$s->addValue('path', $img->getPath());
					
					$tpl->addSubView($s);
					unset($s);
					
					$used[] = $img->getId();
				}
				
				// add more_links for lightbox
				foreach($all_images as $img){
					if(!in_array($img->getId(), $used)){
						$s = new SubViewDescriptor('more_links');
						
						$s->addValue('id', $img->getId());
						$s->addValue('name', $img->getName());
						$s->addValue('path', $img->getPath());
						
						$tpl->addSubView($s);
						unset($s);
					}
				}
				
				if($justOnePage && $more_images > 0) {
					$s = new SubViewDescriptor('showmore');
					$s->addValue('more_images', $more_images);
					
					$tpl->addSubView($s);
					unset($s);
				}
				return $tpl->render();
			} else {
				$this->_msg($this->_('_folder not found'), Messages::ERROR);
				return '';
			}
		}
	}
?>