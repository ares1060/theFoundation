<?php
    class Rating extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
         
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Rating/config.Rating.php');
            $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        /**
         *  args['file'] .... file
         */
        public function view($args) {
            $GLOBALS['extra_css'][] = 'Rating/rating.css';        
            $group = mysql_real_escape_string($args['group']);
            $id = mysql_real_escape_string($args['id']);
            
            if(isset($args['id']) && isset($args['group'])) {
                $ar = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'rating WHERE id=\''.$id.'\' AND `group`=\''.$group.'\''));
                $rating = round($ar['rating']);
                $return = new ViewDescriptor($this->config['tpl_main']);
                
                //dynamics
                for($i=0;$i<$this->config['rating_count'];$i++){
                    $rated = new SubViewDescriptor('rating');
                    if($i<$rating){
                        $rated->addValue('img', $this->config['rated_pic']);
                    } else {
                        $rated->addValue('img', $this->config['unrated_pic']);
                    }
                    $rated->addValue('dynid', $i);
                    $rated->addValue('id', $id);
                    $rated->addValue('group', $group);         
                    $return->addSubView($rated);
                    unset($rated);            
                }
                
                $return->addValue('rating_count', $this->config['rating_count']);
                $return->addValue('rating', $rating);
                $return->addValue('id', $id);
                $return->addValue('group', $group);
                
                return $return->render();
            } else return '';
        }
        public function admin($args){
            return '';
        }
        public function run($args){
            if(isset($args['id']) && isset($args['group']) && isset($args['rate'])) {
                
            } 
            return false;
        }
        public function data($args){
            if(isset($args['id']) && isset($args['group']) && isset($args['rating'])) {
                $id = mysql_real_escape_string($args['id']);
                $group = mysql_real_escape_string($args['group']);
                $rating = mysql_real_escape_string($args['rating']);
                $ip = $this->getRealIpAddr();
                
                if(!isset($_SESSION['Rating']['Rated_'.$group.'_'.$id])) {
                    $query = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'rating_ips WHERE `group`=\''.$group.'\' AND id=\''.$id.'\' AND ip=\''.$ip.'\''));
                    
                    if(!isset($query['id'])){
                        $query = $this->sp->db->data(array('query'=>'SELECT COUNT(*) count FROM '.$GLOBALS['db']['db_prefix'].'rating WHERE `group`=\''.$group.'\' AND id=\''.$id.'\''));
                        $count = $query['count'];
                       
                        if($count == 0){
                            $result = $this->sp->db->bool('INSERT INTO '.$GLOBALS['db']['db_prefix'].'rating (`group`, id, rating, `rating_count`) VALUES (\''.$group.'\', \''.$id.'\', \''.$rating.'\', \'1\')');
                            
                            //save rating ip in db and session
                            $_SESSION['Rating']['Rated_'.$group.'_'.$id] = true;
                            $query = $this->sp->db->bool('INSERT INTO '.$GLOBALS['db']['db_prefix'].'rating_ips (`id`, `group`, `ip`, `date`) VALUES (\''.$id.'\', \''.$group.'\', \''.$ip.'\', NOW())');
                            
                            return ($result) ? 
                                        $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('RATE_SUCCESS', 'Rating')))) : 
                                        $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('RATE_ERROR', 'Rating'))));
                        } else {
                            $query = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'rating WHERE `group`=\''.$group.'\' AND id=\''.$id.'\''));
                            $rating_count = $query['rating_count'];
                            $rating_old = $query['rating'];
                            $new_rating = (($rating_old*$rating_count)+$rating)/($rating_count+1);
                            $result = $this->sp->db->bool('UPDATE '.$GLOBALS['db']['db_prefix'].'rating SET rating =\''.$new_rating.'\', `rating_count`=\''.($rating_count+1).'\' WHERE id=\''.$id.'\' AND `group`=\''.$group.'\'');
                            
                            //save rating ip in db and session
                            $_SESSION['Rating']['Rated_'.$group.'_'.$id] = true;
                            $query = $this->sp->db->bool('INSERT INTO '.$GLOBALS['db']['db_prefix'].'rating_ips (`id`, `group`, `ip`, `date`) VALUES (\''.$id.'\', \''.$group.'\', \''.$ip.'\', NOW())');
                            
                            return ($result) ? 
                                        $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('RATE_SUCCESS', 'Rating')))) : 
                                        $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::INFO, 'msg'=>$this->_('RATE_ERROR', 'Rating'))));
                        }
                    } else  return $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::ERROR, 'msg'=>$this->_('ALLREADY_RATED_IP', 'Rating'))));
                } else  return $this->sp->msg->view(array('action'=>'div', 'msg'=>array('type'=>Messages::ERROR, 'msg'=>$this->_('ALLREADY_RATED_SESSION', 'Rating'))));
            }
        }
    	
        public function setup(){
   	     	
        }
        
        private function getRealIpAddr(){
            //check ip from share internet
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
            //to check ip is pass from proxy
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else $ip = $_SERVER['REMOTE_ADDR'];
            return $ip;
        }
    }
?>