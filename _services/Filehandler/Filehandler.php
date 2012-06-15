<?php
    class Filehandler extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
         
    	private $safeMode = false;
    	
        function __construct(){
            parent::__construct();
            
            $this->safeMode = ini_get('safe_mode');
        }
        /**
         *  args['file'] .... file
         */
        public function view($args) {
        	if(isset($args['file'])){
                $filename = $args['file'];

                if(!file_exists($this->getPath($filename))) {
                	return $this->_('FILE_NOT_FOUND'); 
                }
                if(!is_file($this->getPath($filename))) {
                    return $this->_('NO_FILE'); 
                }
                
                switch($args['action']){
                	case 'write':
                		if(isset($args['data'])){
                			$file= $this->openFile($filename, 'w');
                			
                			fwrite($file, $args['data']);
                			fclose($file);
                			
                			return '';
                		} else return 'no_data';
                		break;
                	default:
                		$file= $this->openFile($filename, 'r');
		                if(filesize($this->getPath($filename)) > 0) {
		                    $text = fread($file,filesize($this->getPath($filename)));
		                    fclose($file);
		                    return $text;
		                } else {
		                	$this->__(str_replace('{@pp:file}', $filename, $this->_('FILE_EMPTY', 'core')), Messages::DEBUG_ERROR);
		                	return str_replace('{@pp:file}', $filename, $this->_('FILE_EMPTY', 'core'));
		                }
		                return (isset($args['file'])) ? loadFile($GLOBALS['config']['root'].$args['file']) : $this->sp->_('FILE_NOT_FOUND', 'core');
                		break;
                }
                
            } else return '';
        }
        public function admin($args){
            return '';
        }
        public function run($args){
            return false;
        }
        public function data($args){
            return '';
        }
    	public function setup(){

    	}
        
        /**
         * Opens a file and returns the resource reference like fopen. If files or folder don't exist, they will be created.
         * @param string $path The path to the file. The root path is added automatically.
         * @param string $mode The mode in which to open the file. See fopen()
         * @return resource or false
         */
        public function openFile($path, $mode){
        	$path = $this->getPath($path);
        	if(file_exists($path) && is_file($path)){
        		return fopen($path, $mode);
        	} else if($mode != 'r' && $mode != 'r+') {
        		$dir = substr($path, 0, strrpos($path, '/'));
        		if(!$this->safeMode && !file_exists($dir)){
        			//try to create folders
        			if(mkdir($dir, 0777, true)) return fopen($path, $mode);
        			else return false;
        		} else {
        			return fopen($path, $mode);
        		}
        	} else {
        		$this->sp->_('FILE_NOT_FOUND: '.$path, 'core');
        		return false;
        	}
        }
        
        /**
         * Deletes a file 
         * @param string $path The path to the file. The root path is added automatically.
         */
        public function deleteFile($path){
        	$path = $this->getPath($path);
        	if(file_exists($path) && is_writable($path)){
        		unlink($path);
        	} else {
        		$this->sp->_('FILE_NOT_FOUND: '.$path, 'core');
        	}
        }

       /**
         * Deletes a dirctory and its content.
         * @param string $path The path to the directory. The root path is added automatically.
         */
        public function deleteDirectory($path){
        	$path = $this->getPath($path);
        	$dir = substr($path, 0, strrpos($path, '/'));
        	$filestub = str_replace($dir, '', $path);
        	if(file_exists($path) && is_dir($path)){
				$objects = scandir($path);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (filetype($path."/".$object) == "dir") $this->deleteDirectory($path."/".$object); else unlink($path."/".$object);
					}
				}
				reset($objects);
				rmdir($path);
        	} else if(file_exists($dir) && is_dir($dir)) {
        		$objects = scandir($path);
        		foreach ($objects as $object) {
        			if ($object != "." && $object != "..") {
        				if (filetype($dir."/".$object) == "file" && strpos($object, $filestub) !== false) unlink($dir."/".$object);
        			}
        		}
        		reset($objects);
        	} else {
        		$this->sp->_('FILE_NOT_FOUND: '.$path, 'core');
        	}
        }
        
        
		/**
		 * Calculates the actual path of the file taking safe_mode into account.
         * @param string $path The path to the file. The root path is added automatically.
         * @return mixed The transformed path or false if the folder does not exist.
		 */        
        public function getPath($path){
        	
        	if(strpos($path, $GLOBALS['config']['root']) !== false) return $path;
        	
        	if(!$this->safeMode) return $GLOBALS['config']['root'].$path;
        	$parts = explode('/', $path);
        	$pc = count($parts);

        	if($pc <= 1) return $path;
        	
        	$newPath = '';
        	$lp = 0;
        	for($i = 0; $i < $pc; $i++){
        		if(!file_exists($GLOBALS['config']['root'].$newPath.$parts[$i].'/')){
        			$lp = $i;
        			break;
        		}
        		$newPath .= $parts[$i].'/';
        	}
        	
            for($i = $lp; $i < $pc-1; $i++){
        		$newPath .= $parts[$i].'_';
        	}
        	$newPath .= $parts[$pc-1];
        	return $GLOBALS['config']['root'].$newPath;
        }
    }
?>