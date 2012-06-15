<?php
	require_once('classes/LocalizationHelper.php');
	require_once('classes/POTCreator.php');
	
    class Localization extends Service implements IService{
        protected $config_file;
        
        private $language;
        private $translationTable;
        private $locHelper;
        private $potCreator;
    
        function __construct(){
        	/* service part */
        	$this->name = 'Localization';
        	//$this->config_file = $GLOBALS['config']['root'].'_core/Localization/Localization.config.php'; // $GLOBALS['config']['root'].'_services/Gallery/config.Gallery.php';
        	$this->ini_file = $GLOBALS['to_root'].'_core/Localization/Localization.ini'; // $GLOBALS['config']['root'].'_services/Gallery/config.Gallery.php';
        	
            parent::__construct();
            
            /* -- new localization -- */
            $this->language = (isset($_SESSION['config']['language'])) ? $_SESSION['config']['language'] : $GLOBALS['config']['default_language'];
            $GLOBALS['Localization']['language'] = $this->language;
            
            $this->translationTable = array();
            $this->locHelper = new LocalizationHelper();
            
            /* load core localization file */
          //  $this->loadLocalizationFile($this->config['loc_folder'], 'core');
        }
        public function getSettings() { return $this->settings; }
        
        public function view($args){return ''; }
        public function admin($args){ return ''; }
        
        public function run($args){
        	$folder = (isset($args['folder'])) ? $args['folder'] : '';
        	$lang = (isset($args['lang'])) ? $args['lang'] : '';
        	$domain = (isset($args['domain'])) ? $args['domain'] : '';
        	$action = (isset($args['action'])) ? $args['action'] : '';
        	
            switch($action){
            	case 'load':
            		return $this->loadLocalizationFile($folder, $domain, $lang);
            		break;
            }
            return false;
        }
        
        /**
         *  $args['str'] ... string 
         */
        public function data($args){
            $service = (isset($args['service'])) ? $args['service'] : 'core';
            $str = (isset($args['str'])) ? $args['str'] : '';
            $lang = (isset($args['language'])) ? $args['language'] : $this->language;
        	$action = (isset($args['action'])) ? $args['action'] : 'translate';
            
            switch($action){
            	case 'translate':
            		return $this->translate($str, $service, $lang);
            		break;
            }
        }
        
        public function setup(){
        	return true;
        }
        
        
	    /**
	     * Return a translated string
	     *
	     * If the translation is not found, the original passed message
	     * will be returned.
	     *
	     * @return Translated message
	     */
	    public function translate($msg, $domain='core', $lang='') {
	    	if($lang=='') $lang = $GLOBALS['Localization']['language'];
	    	
	    	if(isset($this->translationTable[$lang][$domain]) && is_array($this->translationTable[$lang][$domain]) && array_key_exists($msg, $this->translationTable[$lang][$domain])) {
	            return $this->translationTable[$lang][$domain][$msg][0];
	        }
	        return $msg;
	    }
	    
	    /**
	     * loads LocalizationForm with LocalizationHelper into translationTable
	     * if domain already exists it will be merged with the old values
	     * 
	     * @param $folder
	     * @param $domain
	     * @param $lang
	     */
	    public function loadLocalizationFile($folder, $domain='core', $lang=''){
	    	if($folder != ''){
	    		if($lang=='') $lang = $GLOBALS['Localization']['language'];
	    		if($domain=='') $domain = 'core';

	    		/* create language if neccesary */
	    		if(!isset($this->translationTable[$lang])) $this->translationTable[$lang] = array();
	    		
	    		/* if domain exists merge arrays */
	    		$ar = $this->locHelper->loadMoFile($folder, $domain, $lang);

	    		if(is_array($ar)){
			    	if(isset($this->translationTable[$lang][$domain])){
			    		array_merge($this->translationTable[$lang][$domain], $ar);
			    	} else {
				    	$this->translationTable[$lang][$domain] = $ar;
			    	}
		    		return true;
	    		} else return false;
	    	} else return false;
	    }
	    
	    /**
	     * Generates POT file for given service and returnes true if successfull
	     * The file wil be generated in a subfolder of the Service named lang
	     * 
	     * @param $service
	     * @param $regular
	     */
	    public function generatePOTFile($service, $regular='') {
			/*
    		 * it will take only the single parameter Functions --
    		 * 					$this->_('test'); // will be recognized
    		 * 					$this->_('test', 'core'); // will not be recognized 
    		 */
	    	if($regular == '') $regular = '/\$this\-\>\_\([\"|\']([^\"|\']+)[\"|\']\)/i'; // all $this->_(...)
	    	
	    	if($this->potCreator == null) $this->potCreator = new POTCreator();
	    	
	    	$searchfolder = $GLOBALS['config']['root'].'/_services/'.$service.'/';
	    	
	    	if(is_dir($searchfolder)){
	    		$this->potCreator->set_root($searchfolder);
	    		$this->potCreator->set_exts('php|tpl');
				$this->potCreator->set_regular($regular); // old -- set_regular('/_[_|e]\([\"|\']([^\"|\']+)[\"|\']\)/i');
				$this->potCreator->set_base_path('..');
				$this->potCreator->set_read_subdir(true);
				
				//if(!is_dir($searchfolder.'lang/')) mkdir($searchfolder.'lang/');
				
				$potfile = $searchfolder.'lang/'.$service.'.pot';

				$this->potCreator->write_pot($potfile);
				
				return true;
				
	    	} else return false;
	    	return $this->locHelper->generatePOT($searchfolder, $keys);
	    }
	    
	    /**
	     * returnes if POT File exists for given service
	     * @param $service
	     */
	    public function hasPOTFile($service) {
	    	$searchfolder = $GLOBALS['config']['root'].'/_services/'.$service.'/';
	    	$potfile = $searchfolder.'lang/'.$service.'.pot';
	    	return is_file($potfile);
	    }
	    /**
	     * stores LocalizationFolder in globals 
	     * used if Localization object is not loaded yet eg. localization strings
	     * @param unknown_type $folder
	     * @param unknown_type $domain
	     * @param unknown_type $lang
	     */
	    public static function preloadLocalizationFolder($folder, $domain='core', $lang=''){
	    	if(!isset($GLOBALS['Localization']['preload'])) $GLOBALS['Localization']['preload'] = array();
	    	$GLOBALS['Localization']['preload'][] = array('folder'=>$folder, 'domain'=>$domain, 'lang'=>$lang);
	    }
	    /**
	     * loads Files saved with preloadLocalizationFolder
	     */
	    public function loadPreloadedFiles() {
	    	if(isset($GLOBALS['Localization']['preload']) && is_array($GLOBALS['Localization']['preload'])){
		    	foreach($GLOBALS['Localization']['preload'] as $g){
		    		$this->loadLocalizationFile($g['folder'], $g['domain'], $g['lang']);
		    	}
	    	}
	    }
    }
?>
