<?php
	/**
	 * 
	 * Implementation of a Poll Service
	 * @author Matthias Eigner
	 *
	 */
    class Poll extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
 		const OFFLINE = 0;
        const ONLINE = 1; 
        
        const OFFEN = 10;
        const GESCHLOSSEN = 11;
               
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Poll/config.Poll.php');
            $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$GLOBALS['extra_css'][] = 'Poll/poll.css';
        	
        	$mode = isset($args['mode'])&&$args['mode'] != '' ? mysql_real_escape_string($args['mode']) : '';
        	
        	if($mode == 'list'){
        		return $this->listPolls($args);
        	} else if($mode == 'view'){
        		return $this->viewPoll($args);
        	} else {
        		return '';
        	}
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
        	$mode = isset($args['mode'])&&$args['mode'] != '' ? mysql_real_escape_string($args['mode']) : '';

        	if($mode=='insert_reply'){
        		return $this->reply($args);
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
        
    	public function setup(){
        	
        }
        
        public function listPolls($args){
        	$array = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'poll_poll WHERE status="'.self::ONLINE.'"', 'type'=>'array'));
        	
            $main = new ViewDescriptor($this->config['tpl']['list_polls']);
            if($array != array()){
            	foreach($array as &$poll){
            		$poll_d = new SubViewDescriptor('poll');
            		
            		$now = time();
            		$begin = strtotime($poll['begin_d']);
            		$end = strtotime($poll['end_d']);
            		
					$status = ($now >= $begin && $now <= $end) ? self::OFFEN: self::GESCHLOSSEN;
            		$poll_d->addValue('status', $status);
            		$poll_d->addValue('status_msg', $this->_('STATUS_MSG_'.$status, 'Poll'));
            		
            		$poll_d->addValue('id', $poll['p_id']);
            		$poll_d->addValue('name', $poll['title_'.$GLOBALS['Localization']['language']]);
            		
            		$main->addSubView($poll_d);
            		
            		unset($poll_d);
            	}
            	
            	unset($poll);
            } else $main->removeSubView('poll');
            
            
            return $main->render();
        }
        
     	public function viewPoll($args){
     		if(isset($args['id'])){
     			$id = mysql_real_escape_string($args['id']);
     			
     			$selected = isset($args['selected']) ? $args['selected'] : -1;
            	$main = new ViewDescriptor($this->config['tpl']['view_poll']);
     			$array = $this->sp->db->data(array('query'=>'SELECT *, 
     													pp.title_'.$GLOBALS['Localization']['language'].' AS p_titel, 
     													po.title_'.$GLOBALS['Localization']['language'].' AS o_titel,
     													pp.desc_'.$GLOBALS['Localization']['language'].' AS p_beschreibung, 
     													po.desc_'.$GLOBALS['Localization']['language'].' AS o_beschreibung 
     													FROM '.$GLOBALS['db']['db_prefix'].'poll_option po 
     														LEFT JOIN '.$GLOBALS['db']['db_prefix'].'poll_poll pp ON po.p_id = pp.p_id 
     													WHERE pp.status ="'.self::ONLINE.'" AND pp.p_id="'.$id.'"', 'type'=>'array'));
     			
     			if($array != ''){
     				if(is_array($array) && isset($array[0])){
     					$now = time();
	            		$begin = strtotime($array[0]['begin_d']);
	            		$end = strtotime($array[0]['end_d']);
	            		
						if($now >= $begin && $now <= $end){
		     				$main->addValue('id', $array[0]['p_id']);
		     				$main->addValue('title', $array[0]['p_titel']);
		     				$main->addValue('beschreibung', $array[0]['p_beschreibung']);
		     				
		     				foreach($array as &$option){
		     					$selected_d =  ($option['o_id'] == $selected) ? 'checked="checked"' : '';
		     					$option_d = new SubViewDescriptor('options');
		     					$option_d->addValue('id', $option['p_id']);
		     					$option_d->addValue('o_id', $option['o_id']);
		     					$option_d->addValue('name', $option['o_titel']);
		     					$option_d->addValue('beschreibung', $option['o_beschreibung']);
		     					$option_d->addValue('selected', $selected_d);
		     					
		       					$main->addSubView($option_d);
		     					unset($option_d);
		     				}
		     				unset($option);
						} else {
							$this->sp->msg->run(array( 'message'=>$this->_('MESSAGE_NOT_OPEN', 'Poll'), 'type'=>Messages::ERROR));
							return '';
						}
     				}  else $main->removeSubView('options');//else not_found or not exists
     			} else $main->removeSubView('options');//else not_found or not exists
     			return $main->render();
     		}
        }
        
        public function reply($args){
        	if(isset($args['o_id']) && isset($args['u_id']) && isset($_SESSION['User'])){
        		$o_id = mysql_real_escape_string($args['o_id']);
        		$u_id = $_SESSION['User']['id'];
				if($this->sp->db->bool('DELETE FROM '.$GLOBALS['db']['db_prefix'].'poll_selects WHERE u_id="'.$u_id.'";')){
           			return $this->sp->db->bool('INSERT INTO '.$GLOBALS['db']['db_prefix'].'poll_selects (o_id, u_id, time) VALUES ("'.$o_id.'", "'.$u_id.'", NOW());');
				} else return false;
        	}
        	return false;
        }
    }
?>