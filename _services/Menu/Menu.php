<?php
    class Menu extends Service implements IService {
        private $menues;
        private $count;
        
        const TYPE_NORMAL = 0;
        const TYPE_SMALLER_THAN = 1;
        
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
         
        function __construct(){
            parent::__construct();
            $this->menues = array();
            $this->count = 0;
        }
        /**
         *  render menu directly with parameters or per id
         *  ----------------------
         *  file ... template file 
         *  replace_count ... count of menuitems (default: 0)
         *  replace_active ... active menuitem (default: 0)
         *  replace_class ... class for active menuitem (default: active)
         *  replace_string ... replace string for menuitems (default: menu_)
         *  replace_type ... replace type of the menuitems [smaller_than, normal] (default: normal)
         */
        public function view($args) {
        	$type = self::TYPE_NORMAL;
        	if(isset($args['replace_type']) && $args['replace_type'] == self::TYPE_SMALLER_THAN) $type = self::TYPE_SMALLER_THAN;
        	 
            if(isset($args['file'])) {  
                return $this->view(array('id'=>$this->data($args)));
            } else if(isset($args['id'])){
                $id = $args['id'];
                for($i=0;$i<$this->menues[$id]['replace_count'];$i++){
                    //check type
                	if($this->menues[$id]['replace_type'] == self::TYPE_NORMAL) {
                    	$replace = ($i == $this->menues[$id]['replace_active']) ? ($this->menues[$id]['replace_class']) ? 'class="'.$this->menues[$id]['replace_replace'].'"' : $this->menues[$id]['replace_replace'] : '';
                    } else if($this->menues[$id]['replace_type'] == self::TYPE_SMALLER_THAN){	
                    	$replace = ($i <= $this->menues[$id]['replace_active']) ? ($this->menues[$id]['replace_class']) ? 'class="'.$this->menues[$id]['replace_replace'].'"' : $this->menues[$id]['replace_replace'] : '';
                    }
                    
                    $this->menues[$id]['template']->addValue($this->menues[$id]['replace_string'].$i, $replace);
                }
                return $this->menues[$id]['template']->render();
            } else return '';
        }
        public function admin($args){
            return '';
        }

        public function run($args){
            return false;
        }
        /**
         *  add menu to menues array
         *  ----------------------
         *  @return id of added menu
         *  ----------------------
         *  @param file ... template file 
         *  @param replace_count ... count of menuitems (default: 0)
         *  @param replace_active ... active menuitem (default: 0)
         *  @param replace_replace ... class for active menuitem (default: current)
         *  @param replace_class ... user class (class="[%replace%]") or just replace the classname (default: true)
         *  @param replace_string ... replace string for menuitems (default: menu_)
         */
         public function data($args){
            if(isset($args['file'])) {
                $file = $args['file'];
                $replace_count = isset($args['replace_count']) ? $args['replace_count'] : 0;
                $replace_active = isset($args['replace_active']) ? $args['replace_active'] : 0;
                $replace_replace = isset($args['replace_replace']) ? $args['replace_replace'] : 'current';
                $replace_class = isset($args['replace_class']) ? $args['replace_class'] : true;
                $replace_string = isset($args['replace_string']) ? $args['replace_string'] : 'menu_';
                $replace_type = isset($args['replace_type']) ? $args['replace_type'] : self::TYPE_NORMAL;
                
                $this->menues[$this->count]['template'] = new ViewDescriptor($file);
                $this->menues[$this->count]['replace_count'] = $replace_count;
                $this->menues[$this->count]['replace_active'] = $replace_active;
                $this->menues[$this->count]['replace_replace'] = $replace_replace;
                $this->menues[$this->count]['replace_class'] = $replace_class;
                $this->menues[$this->count]['replace_string'] = $replace_string;
                $this->menues[$this->count]['replace_type'] = $replace_type;
                
                $this->count++;
                
                return $this->count-1;
            } else return '';
        }
        
    	public function setup(){
        	
        }
    }
?>