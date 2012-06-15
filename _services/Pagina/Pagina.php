<?php
    class Pagina extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
         
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Pagina/config.Pagina.php');
        }
        /**
         *  args['file'] .... file
         *  args['count']* ... pagina count
         *  args['active']* ... active Page
         *  args['url']* ... link  ({pp:page}] ... page)
         *  args['sort'] .... direction (default: desc)
         */
        public function view($args) {
            $GLOBALS['extra_css'][] = $this->config['css_file'];
            $template = (isset($args['template'])) ? $args['template'] : '';
            
            if(isset($args['count']) && isset($args['active']) && isset($args['url'])){
                $count = $args['count']==0 ? 1: $args['count'];
                $active = ($args['active'] > $count) ? 1: $args['active'];
                $url = $args['url'];

                $sort = isset($args['sort']) ? $args['sort'] : 'desc';
                                
                $main = ($template != '') ? new ViewDescriptor('_services/Pagina/'.$template) : new ViewDescriptor($this->config['tpl_main']);
                
                $main->addValue('css_url', $this->sp->tpl->getCssPath($this->config['css_file']));
                if($sort == 'desc'){
                	
                    for($i=$count;$i>=1;$i--){
                        $item = new SubViewDescriptor('items');
                        $activated = ($i==$active) ? ' class=\'active\'' : '';
                        $item->addValue('active', $activated);
                        $item->addValue('id', $i);
                        $item->addValue('page_nr', $i);
                        $item->addValue('link', str_replace(array('{page}', '{abs_root}'), array($i, $GLOBALS['abs_root'].$GLOBALS['working_dir']), $url));
                        $main->addSubView($item);
                        unset($item);
                    }
                } else {
                    for($i=1;$i<=$count;$i++){
                        $item = new SubViewDescriptor('items');
                        $activated = ($i==$active) ? ' class=\'active\'' : '';
                        $item->addValue('active', $activated);
                        $item->addValue('id', $i);
                        $item->addValue('page_nr', $i);
                        $item->addValue('link', str_replace(array('{page}', '{abs_root}'), array($i, $GLOBALS['abs_root'].$GLOBALS['working_dir']), $url));
                        $main->addSubView($item);
                        unset($item);
                    }
                }
                return $main->render();
            }
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
    }
?>