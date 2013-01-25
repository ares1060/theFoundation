<?php	
	require_once 'model/ContactFormForm.php';
	require_once 'model/ContactFormElement.php';
	require_once 'model/ContactFormDataHelper.php';
	
	require_once 'view/ContactFormUserView.php';
	require_once 'view/ContactFormMailView.php';
	require_once 'view/ContactFormAdminView.php';
	
	/**
     * ContactForm service
     * 
     * @author Matthias (scrapy1060@gmail.com)
     * @version: version 0.1
     * @name: Shop
     * 
     * @requires: Pagina
     */
    class ContactForm extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
    	
    	const STATUS_ONLINE = 0;
    	const STATUS_OFFLINE = 1;
    	
    	const TYPE_STRING = 0;
    	const TYPE_TEXT = 1;
    	const TYPE_MAIL = 2;
    	const TYPE_TEL = 3;
    	const TYPE_DATE = 4;
    	const TYPE_CITY = 5;
    	const TYPE_COUNTRY = 6;
    	const TYPE_CHECKBOX = 7;
    	const TYPE_COMBOBOX = 8;
    	
    	private $viewAdmin;
    	private $viewUser;
    	private $viewMail;
    	
    	private $dataHelper;
         
        function __construct(){
        	$this->name = 'ContactForm';
        	$this->ini_file = $GLOBALS['to_root'].'_services/ContactForm/ContactForm.ini';
            parent::__construct();
print_r($this->settings === null);
            $this->dataHelper = new ContactFormDataHelper($this->settings);
            $this->viewAdmin = new ContactFormAdminView($this->settings, $this->dataHelper);
            $this->viewUser = new ContactFormUserView($this->settings,  $this->dataHelper);
            $this->viewMail = new ContactFormMailView($this->settings,  $this->dataHelper);
            
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$action = isset($args['action']) ? $args['action'] : '';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	switch($action){
        		case 'contactForm':
        			return $this->viewUser->tplContactFormById($id);
        			break;
        		default:
        			echo 'asdf';
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
        	$action = isset($args['action']) ? $args['action'] : '';
        	
        	switch($action) {
        		case 'handlePost':
        			$this->viewMail->sendForm();
        			break;
        		default:
        			break;
        	}
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
        	
        }
        
    }
?>