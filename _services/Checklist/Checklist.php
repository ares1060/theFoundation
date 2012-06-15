<?php
	/**
	 * 
	 * Enter description here ...
	 * @author author
	 *
	 */
    class Checklist extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
 		const OFFLINE = 0;
        const ONLINE = 1; 
         
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Checklist/config.Checklist.php');
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
        	$GLOBALS['extra_css'][] = 'Checklist/checklist.css';
        	
        	$mode = isset($args['mode'])&&$args['mode'] != '' ? mysql_real_escape_string($args['mode']) : '';
        	
        	if($mode == 'list'){
        		return $this->listChecklists($args);
        	} else if($mode == 'view'){
        		return $this->viewChecklist($args);
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

        	if($mode=='checkItem'){
        		return $this->checkItem($args);
        	} else if($mode=='uncheckItem'){
        		return $this->uncheckItem($args);
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
        
        public function listChecklists($args){
        	$array = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'checklist WHERE status="'.self::ONLINE.'"', 'type'=>'array'));
        	
            $main = new ViewDescriptor($this->config['tpl']['list_checklists']);
            if($array != array()){
            	foreach($array as &$checklist){
            		$checklist_d = new SubViewDescriptor('checklist');
            		
            		$checklist_d->addValue('id', $poll['cl_id']);
            		$checklist_d->addValue('name', $poll['title_'.$GLOBALS['Localization']['language']]);
            		
            		$main->addSubView($checklist_d);
            		
            		unset($checklist_d);
            	}
            	
            	unset($checklist);
            } else $main->removeSubView('checklist');
            
            
            return $main->render();
        }
        
     	public function viewChecklist($args){
     		if(isset($args['id'])){
     			$id = mysql_real_escape_string($args['id']);
     			
            	$main = new ViewDescriptor($this->config['tpl']['view_checklist']);
     			$array = $this->sp->db->data(array('query'=>'SELECT *, 
     													cli.cli_id AS item_id,
     													cl.title_'.$GLOBALS['Localization']['language'].' AS checklist_titel, 
     													cli.title_'.$GLOBALS['Localization']['language'].' AS item_titel,
     													cl.desc_'.$GLOBALS['Localization']['language'].' AS checklist_beschreibung, 
     													cli.desc_'.$GLOBALS['Localization']['language'].' AS item_beschreibung, 
     													cliu.u_id AS selected_by
     													FROM '.$GLOBALS['db']['db_prefix'].'checklist_item cli 
     														LEFT JOIN '.$GLOBALS['db']['db_prefix'].'checklist cl ON cli.cl_id = cl.cl_id 
     														LEFT JOIN '.$GLOBALS['db']['db_prefix'].'checklist_item_user cliu ON cli.cli_id = cliu.cli_id
     													WHERE cl.status ="'.self::ONLINE.'" AND cl.cl_id="'.$id.'"', 'type'=>'array'));
     			
     			if($array != ''){
     				if(is_array($array) && isset($array[0])){
	     				$main->addValue('id', $array[0]['cl_id']);
	     				$main->addValue('title', $array[0]['checklist_titel']);
	     				$main->addValue('beschreibung', $array[0]['checklist_beschreibung']);
	     				
	     				foreach($array as &$option){

	     					$selected = false;
	     					$selected_me = false;
	     					
	     					$option_d = new SubViewDescriptor('items');
	     					
	     					if($option['selected_by'] != ''){
	     						$selected = true;
	     						$selected_me = ($option['selected_by'] == $_SESSION['User']['id']) ? true : false;
	     					} 
	     					$selected_d =  ($selected) ? 'checked="checked"' : '';
	     					$disabled_d =  ($selected_me) ? '' : ($selected) ? 'disabled="true"' : '';
	     					$disabled_text =  ($selected_me) ? '' : ($selected) ? $this->sp->loc->data(array('str'=>'DISABLED', 'service'=>'Checklist')) : '';
	     					
	     					$option_d->addValue('id', $option['cl_id']);
	     					$option_d->addValue('i_id', $option['item_id']);
	     					$option_d->addValue('name', $option['item_titel']);
	     					$option_d->addValue('beschreibung', $option['item_beschreibung']);
	     					$option_d->addValue('selected', $selected_d);
	     					$option_d->addValue('disabled', $disabled_d);
	     					$option_d->addValue('disabled_text', $disabled_text);
	     					
	       					$main->addSubView($option_d);
	     					unset($option_d);
	     				}
	     				unset($option);
						
     				}  else $main->removeSubView('items');//else not_found or not exists
     			} else $main->removeSubView('items');//else not_found or not exists
     			return $main->render();
     		}
        }
    
        public function checkItem($args){
        	if(isset($args['cli_id']) && isset($args['u_id']) && isset($_SESSION['User'])){
        		$cli_id = mysql_real_escape_string($args['cli_id']);
        		$u_id = $_SESSION['User']['id'];
           		$exists = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'checklist_item_user WHERE cli_id="'.$cli_id.'" AND u_id="'.$u_id.'";'));
           		if(is_array($exists) && isset($exists[0]['u_id'])){
        			return true;
           		} else return $this->sp->db->bool('INSERT INTO '.$GLOBALS['db']['db_prefix'].'checklist_item_user (cli_id, u_id, time) VALUES ("'.$cli_id.'", "'.$u_id.'", NOW());'); 
        	}
        	return false;
        }
        
        public function uncheckItem($args){
        	if(isset($args['cli_id']) && isset($args['u_id']) && isset($_SESSION['User'])){
        		$cli_id = mysql_real_escape_string($args['cli_id']);
        		$u_id = $_SESSION['User']['id'];
        	
        		return $this->sp->db->bool('DELETE FROM '.$GLOBALS['db']['db_prefix'].'checklist_item_user WHERE cli_id="'.$cli_id.'" AND u_id="'.$u_id.'";');
        	}
        	return false;
        }
    }
?>