<?php
    abstract class Service extends TFCoreFunctions{
        /**
         * name of the service
         * @var string
         */
    	protected $name;
        
        /**
         * Array for configuration data
         * @var string[]
         */
        protected $config;

        /**
         * configuration file path
         * @var string
         */
    	protected $config_file;  
        
        public function __construct() {
        	parent::__construct();
            $this->config = array();
            
            // load old config file
            if(isset($this->config_file) && $this->config_file != '') $this->loadConfig($this->config_file);
            
            // load new ini config 
            if(isset($this->ini_file) && $this->ini_file != '') $this->loadConfigIni($this->ini_file, $this->name);
            
            // load localization
            if(isset($this->config['loc_folder']) && isset($this->sp->loc)) $this->sp->loc->loadLocalizationFile($this->config['loc_folder'], $this->name);
            
            if($this->_setting('loc.loc_folder') != null){
            	$setting = (strpos($this->_setting('loc.loc_folder'), $GLOBALS['config']['root']) === false) ?  $GLOBALS['config']['root'].$this->_setting('loc.loc_folder') : $this->_setting('loc.loc_folder');
				
            	//exception for localization (it loads systems core localization file
            	$name = ($this->name == 'Localization') ? 'core' : $this->name;

            	if(isset($this->sp->loc)) $this->sp->loc->loadLocalizationFile($setting, $name);
            	
            	// preload Localization File -> fil will be loaded after initialization
            	else Localization::preloadLocalizationFolder($setting, $name);
            }
        }
        
        /**
         * 
         * Loads specified config file
         * @param string $file
         */
        public function loadConfig($file){
            if(is_file($file)){
                require_once($file);
            } else {
            	$this->sp->msg->run(array('message'=>str_replace(array('{@pp:datei}'), array($file), $this->_('FILE_NOT_FOUND')), 'type'=>Messages::INFO));
            }
        }
        
        
        /**
         * 
         * Loads File with Htmlwrapper service
         * @param string $file
         */
        protected function loadFile($file){ 
        	return $this->sp->view('Filehandler', array('action'=>'load', 'file'=>$file));
       	}       	
        
        /**
         * Generates POT file for this Service by searching through the sourcecode
         * Feature for developers only
         * 
         * @see Localization generatePOTFile();
         */
        protected function generatePOT() {
        	$this->sp->ref('Localization')->generatePOTFile($this->name);
        }
        
    }
?>
