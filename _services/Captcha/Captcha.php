<?php
    class Captcha extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
         
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Captcha/config.Captcha.php');
        }
        /**
         *  args['file'] .... file
         */
        public function view($args) {
            // display Captcha mit reload, speak etc und in session setzten
            // image get per (http)
            $action = isset($args['action']) ? $args['action'] : 'display';
            if($action == 'display') {
                return loadFile($GLOBALS['config']['root'].'_template/_services/Captcha/captcha.html');
            } 
            return '';
        }
        public function admin($args){
            return '';
        }
        public function run($args){
            // ob es richtig ist
            $action = isset($args['action']) ? $args['action'] : 'check';
            $code = isset($args['code']) ? $args['code'] : '';
            if($action == 'check') {
                return $this->checkCaptcha($code);
            }
            return false;
        }
        public function data($args){
        	//render Captcha Image
			$text = $this->randomString(4); 
			$_SESSION['Captcha']['captcha'] = $text;
			header('Content-type: image/png');
			$img = ImageCreateFromPNG($this->config['background']); //Backgroundimage
			$color = ImageColorAllocate($img,  $this->config['textcolor']['R'],  $this->config['textcolor']['G'],  $this->config['textcolor']['B']); //Farbe
			$ttf = $this->config['font']; //font
			$ttfsize = $this->config['fontsize']; //font size
			$angle = rand(-3,3);
			$t_x = rand(10,70);
			$t_y = 35;
			imagettftext($img, $ttfsize, $angle, $t_x, $t_y, $color, $ttf, $text);
			echo imagepng($img);
			imagedestroy($img);
            return '';
        }
    	public function setup(){
        	
        }
        private function randomString($len) {
              //Der String $possible enthält alle Zeichen, die verwendet werden sollen
              $str='';
              while(strlen($str)<$len) {
                $str.=substr($this->config['possible'],(rand()%(strlen($this->config['possible']))),1);
              }
            return($str);
        }
        
        public function checkCaptcha($code = ''){
        	if($code != '' && isset($_POST['captcha'])){
        		$code = $code == '' ? $_POST['captcha'] : $code;
        		return isset($_SESSION['Captcha']['captcha']) && $_SESSION['Captcha']['captcha'] == $code;
        	} else return false;
        }
    }
?>