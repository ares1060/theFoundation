<?php	
	require_once 'model/ShopDataHelper.php';
	require_once 'model/ShopProduct.php';
	require_once 'model/ShopCart.php';
	
	require_once 'view/ShopAdminView.php';
	require_once 'view/ShopFrontendView.php';
	require_once 'view/ShopCartView.php';
	//require_once 'view/ShopFrontView.php';
	
	/**
     * Shop service
     * small shop system
     * 
     * @author Matthias (scrapy1060@gmail.com)
     * @version: version 0.1
     * @name: Shop
     * 
     * @requires: Pagina, Tags, Categories, Gallery
     */
    class Shop extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
    	private $viewAdmin;
    	private $viewUser;
    	private $viewFrontend;
    	private $viewCart;
    	
    	private $dataHelper;
    	private $cart;
         
        function __construct(){
        	$this->name = 'Shop';
        	$this->config_file = $GLOBALS['to_root'].'/_services/Shop/config.Shop.php';
        	$this->ini_file = $GLOBALS['to_root'].'/_services/Shop/Shop.ini';
            parent::__construct();
            $this->dataHelper = new ShopDataHelper($this->settings);
            $this->viewAdmin = new ShopAdminView($this->settings, $this->dataHelper);
            $this->viewFrontend = new ShopFrontendView($this->settings,  $this->dataHelper);
            
            // because unserialize will not get dataHelper right it will be added afterwards
            // we have to save serialzied object in session because otherwise incomplete class will be unserialized
            if(isset($_SESSION['shop']['cart'])){
            	$this->cart = unserialize($_SESSION['shop']['cart']);
            	$this->cart->setSettingsCore($this->settings);
            	$this->cart->setDataHelper($this->dataHelper);
            } else {
            	$this->cart = new ShopCart();
            	$this->cart->setSettingsCore($this->settings);
            	$this->cart->setDataHelper($this->dataHelper);
            	$this->saveCartToSession();
            }
            $this->viewCart = new ShopCartView($this->settings, $this->dataHelper, $this->cart);
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$action= isset($args['action']) ? $args['action']: '';
        	$name = isset($args['name']) ? $args['name'] : -1;
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$count = isset($args['count']) ? $args['count'] : 0;
        	$page = isset($args['page']) ? $args['page'] : -1;
        	
        	switch($action){
        		case 'category':
        			if($id > -1) return $this->viewFrontend->tplViewCategoryById($id, $page);
        			else return $this->viewFrontend->tplViewCategoryByWebname($name, $page);
        			break;
        		case 'product':
        			return $this->viewFrontend->tplViewProductByLink($id);
        			break;
        		case 'tag':
        			return $this->viewFrontend->tplViewTagByWebname($name);
        			break;
        		case 'cart_small':
        			return $this->viewCart->tplCartSmall();
        			break;
        		case 'cart_add':
        			$r =  $this->cart->addToCart($id, $count);
        			$this->saveCartToSession();
        			return $r;
        			break;
        		case 'cart':
        			return $this->viewCart->tplCart();
        			break;
        		default:
        			// main categories
        			return $this->viewFrontend->tplViewMainCategories($page);
        			break;
        	}
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::admin()
         */
        public function admin($args){     
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	$page = isset($args['page']) ? $args['page'] : -1;
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$action= isset($args['action']) ? $args['action']: '';
        	
        	// Category Vars
        	$parent= isset($args['parent']) ? $args['parent']: -1;
        	$after= isset($args['after']) ? $args['after']: -1;
        	$before= isset($args['before']) ? $args['before']: -1;
        	$cat= isset($args['cat']) ? $args['cat']: -1;
        	
        	// Tags vars
        	$tags =  isset($args['tags']) ? $args['tags']: '';
        	$tag =  isset($args['tag']) ? $args['tag']: '';
        	
        	// Image vars
        	$link = isset($args['link']) ? $args['link'] : '';
        	$click = isset($args['click']) ? $args['click'] : '';
        	$reloadFunction = isset($args['reloadFunction']) ? $args['reloadFunction'] : '';
        	$useFunction = isset($args['useFunction']) ? $args['useFunction'] : '';
        	$image = isset($args['image']) ? $args['image'] : '';
        	
        	switch($chapter){
        		case 'products_overview':
        			return $this->viewAdmin->tplProductsOverview($page);
        			break;
        		case 'products_new':
        			return $this->viewAdmin->tplProductNew();
        			break;
        		case 'products_edit':
        			return $this->viewAdmin->tplProductEdit($id);
        			break;
        		case 'category':
        			return $this->sp->ref('Category')->tplGetCategoryAdmincenter($this->name);
        			break;
        		case 'settings':
        			return $this->tplSettings();
        			break;
        		default:
        			switch($action){
        				case 'deleteProduct':
        					return $this->dataHelper->deleteProduct($id);
        					break;
        				// category actions
        				case 'changeCategoryOrder':
        					return $this->sp->ref('Category')->changeCategoryOrder($this->name, $id, $parent, $after, $before);
        					break;
        				case 'deleteCategory':
        					return $this->sp->ref('Category')->deleteCategory($id, $this->name);
        					break;
        				case 'showCategoryEdit':
        					return $this->sp->ref('Category')->tplEditCategory($id, $this->name);
        					break;
        				case 'setCategory':
        					// link to own categoryupdate function 
        					// because categories get saved in own table and not in the category sevice
							if($id == 'new') {
								$n_id = $this->dataHelper->newProduct(ShopDataHelper::STATUS_HIDDEN, '', '', 0, 0, 0, false, '0x0x0');
								if($n_id !== false){
									if($this->dataHelper->setProductCategory($n_id, $cat)) {
										return $n_id;
									} else return false;
								} else return false;
								
							} else return $this->dataHelper->setProductCategory($id, $cat); 
        					break;
        				// Tags actions
        				case 'deleteTag':
        					return $this->sp->ref('Tags')->deleteTag($tag, $this->name, $id);
        					break;
        				case 'saveTag':
        					return $this->sp->ref('Tags')->tag($tag, $this->name, $id);
        					break;
        				case 'saveTags':
        					return $this->sp->ref('Tags')->saveRawTags($tags, $this->name, $id);
        					break;
        				// image actions
        				case 'setProductImage':
        					if($this->dataHelper->setProductImage($id, $image)) {
        						return $this->sp->ref('Gallery')->getImage($image)->getPath();
        					} else {
        						$this->_msg($this->_('product update error'), Messages::ERROR);
        						return false;
        					}
        					break;
        				case 'wysiwyg_image':
        					return $this->sp->ref('Gallery')->addOnWysiwygFolder($this->_setting('gallery_album_id', 'main'), 'wysiwyg', $page, -1, -1, Gallery::BOX_VIEW_MATRIX, $reloadFunction, $useFunction);
        					break;
        				case 'loadProductImages':
        					return $this->sp->ref('Gallery')->getBoxFolderTpl($this->_setting('gallery_album_id', 'main'), 'product_'.$id, $page, Gallery::BOX_VIEW_MATRIX, $reloadFunction, $useFunction);
//         					return $this->sp->ref('Gallery')->getBoxFolderTpl($this->_setting('gallery_album_id', 'main'), 'product_'.$id, $page, $click, -1, -1, Gallery::BOX_VIEW_MATRIX, $reloadFunction, $useFunction);
        					break;
        				case 'loadProductImagesUpload':
        					$folder = ($id == 'new') ? 'new' : 'product_'.$id;
        					return $this->sp->ref('Gallery')->addOnUpload($this->_setting('gallery_album_id', 'main'), $folder, $link);
        					break;
        				case 'loadWysiwygUpload':
        					return $this->sp->ref('Gallery')->addOnUpload($this->_setting('gallery_album_id', 'main'), 'wysiwyg', $link);
        					break;
        				default:
        					return $this->viewAdmin->tplAdmincenter();
        					break;
        			}
        		break;
        	}
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::run()
         */
        public function run($args){
            return false;
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::data()
         */
        public function data($args){
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	$error = true;
        	include_once('setup/setup.php');
        	return $error;
        }
        
        /**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
        	/* -- Category posts -- */
        	$this->sp->ref('Category')->handleAdminPost($this->name);
        	// edit product
        	if(isset($_POST['pr_id']) && 
        		isset($_POST['pr_status']) && 
        		isset($_POST['pr_name']) && 
                isset($_POST['pr_desc']) && 
        		isset($_POST['pr_price']) && 
                isset($_POST['pr_weight']) && 
                isset($_POST['pr_stock']) && 
                isset($_POST['pr_dimensions_width']) && 
                isset($_POST['pr_dimensions_height']) && 
                isset($_POST['pr_dimensions_depth']) &&
                isset($_POST['pr_stock_nr']) 
                ){
             		if($this->dataHelper->updateProduct($_POST['pr_id'], 
             				$_POST['pr_status'], 
             				$_POST['pr_name'], 
             				$_POST['pr_desc'], 
             				$_POST['pr_price'], 
             				$_POST['pr_weight'], 
             				$_POST['pr_stock'], 
             				isset($_POST['pr_isDownload']), 
             				$_POST['pr_dimensions_width'].'x'.$_POST['pr_dimensions_height'].'x'.$_POST['pr_dimensions_depth'],
             				$_POST['pr_stock_nr'])){

             			$this->_msg($this->_('product update success'), Messages::INFO);
             			header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['pr_link']);
             			exit(0);
             		} else {
             			$this->_msg($this->_('product update error'), Messages::ERROR);
             			return false;
             		}
            }
            
            // new product
        	if(isset($_POST['pr_link']) &&
        		isset($_POST['pr_status']) && 
        		isset($_POST['pr_name']) && 
                isset($_POST['pr_desc']) && 
        		isset($_POST['pr_price']) && 
                isset($_POST['pr_weight']) && 
                isset($_POST['pr_stock']) && 
                isset($_POST['pr_dimensions_width']) && 
                isset($_POST['pr_dimensions_height']) && 
                isset($_POST['pr_dimensions_depth']) &&
                !isset($_POST['pr_id']) 
                ){
                	$n_id = $this->dataHelper->newProduct($_POST['pr_status'], 
             				$_POST['pr_name'], 
             				$_POST['pr_desc'], 
             				$_POST['pr_price'], 
             				$_POST['pr_weight'], 
             				$_POST['pr_stock'], 
             				isset($_POST['pr_isDownload']), 
             				$_POST['pr_dimensions_width'].'x'.$_POST['pr_dimensions_height'].'x'.$_POST['pr_dimensions_depth']);
             		if($n_id !== false){

             			$this->_msg($this->_('product add success'), Messages::INFO);
             			
             			header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['pr_link'].'action/edit/id/'.$n_id.'/');
             			exit(0);
             		} else {
             			$this->_msg($this->_('product add error'), Messages::ERROR);
             			return false;
             		}
            }
            
            // upload images to Product
            if(isset($_POST['action']) && $_POST['action'] == 'upload' &&
            	isset($_POST['album']) && 
            	isset($_POST['folder']) &&
            	isset($_POST['MAX_FILE_SIZE']) && 
            	isset($_POST['selected_type']) &&
            	isset($_POST['link'])){

            	// handle new Upload 
            	//if($_POST['album']
            		
            	if($this->dataHelper->uploadImages($_POST['folder'])){
            		$this->_msg($this->_('product update success'), Messages::INFO);
            		header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['link']);
            		exit(0);
            	} else {
            		$this->_msg($this->_('product update error'), Messages::ERROR);
             		return false;
            	}
            }
            
         	// upload images to Product
            if(isset($_POST['action']) && $_POST['action'] == 'upload_wysiwyg' &&
	            isset($_POST['album']) && 
	            isset($_POST['folder']) &&
	            isset($_POST['MAX_FILE_SIZE']) && 
	            isset($_POST['selected_type']) &&
	            isset($_POST['link'])){
	
	            // handle new Upload 
	            //if($_POST['album']
	            	
	            if($this->dataHelper->uploadImages($_POST['folder'])){
	            	$this->_msg($this->_('product update success'), Messages::INFO);
	            	header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['link']);
	            	exit(0);
	            } else {
	            	$this->_msg($this->_('product update error'), Messages::ERROR);
	             	return false;
	            }
            }
        }
        
        private function saveCartToSession() {
        	$_SESSION['shop']['cart'] = serialize($this->cart);
        }
    }
?>