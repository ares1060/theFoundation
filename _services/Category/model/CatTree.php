<?php
	class CatTree extends TFCoreFunctions {
		private $root;
		private $service_trees;
		private $tree_id_links;
		public $name;
		
		function __construct(){
			//$this->root = 1;
            parent::__construct();
        	if(!isset($GLOBALS['installation']) || !$GLOBALS['installation']) $root = $this->loadSubTree($this->getNodeById(1)); // get root node
        	$this->name = 'Category';   
		}
		
		/** ==========  load functions =========*/
		/**
		 * returnes Category object by given id
		 * @param unknown_type $id
		 */
		public function getCategory($id){
			$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category` WHERE c_id="'.mysql_real_escape_string($id).'"');
			if($q != ''){
				return new CatCategory($q['c_id'], $q['name'], $q['webname'], $q['status'], $q['desc'], $q['img'], $q['service_root']);
			} else return null;
		}
		
		/**
		 * loads Service Categories into bugffer
		 * @param unknown_type $service
		 */
		public function getServiceCategories($service){
			return $this->loadSubTree($this->getNodeForService($service));
		}
		/**
		 * reloads Service Tree
		 * @param unknown_type $service
		 */
		private function reloadServiceTree($service){
			unset($this->service_trees[$service]);
			$this->getServiceCategories($service);
		}
		/**
		 * loads SubTree from Database and creates Object Tree out of nodes
		 * @param CatTreeNode $node
		 */
		private function loadSubTree(CatTreeNode $node){
			$q = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` ct
											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'category` c ON ct.c_id = c.c_id
											WHERE ct.left > '.mysql_real_escape_string($node->getLeft()).' AND ct.right < '.mysql_real_escape_string($node->getRight()).'
											ORDER BY ct.left ASC');
			
			if($q != array()){
				$active_node = $node;
				foreach($q as $n){
					// check active node
					if($active_node != null){
						while($active_node->getRight() < $n['right']){
							$active_node = $active_node->getParent();
						}
					}
					// set parent and children connection and set new active node
					// addChildren returnes the child
					$active_node = $active_node->addChildren(new CatTreeNode($n['c_id'], new CatCategory($n['c_id'], $n['name'], $n['webname'], $n['status'], $n['desc'], $n['img'], $n['service_root']), $n['left'], $n['right'], $n['parent']));
				}
			}
			return $node;
		}
		
		/**
		 * returnes buffered Service Node with whole subtree
		 * @param unknown_type $service
		 */
		public function &getNodeForService($service){
			if(!isset($this->service_trees[$service])){
				$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` ct
												LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'category` c ON ct.c_id = c.c_id
												WHERE c.name = "'.mysql_real_escape_string($service).'" AND
													  c.service_root="1"');
				if($q != '') {
					$this->service_trees[$service] = new CatTreeNode($q['c_id'], new CatCategory($q['c_id'], $q['name'], $q['webname'], $q['status'], $q['desc'], $q['img'], $q['service_root']), $q['left'], $q['right'], $q['parent']);
				} else $this->service_trees[$service] = null;
			} 
			
			return $this->service_trees[$service];
		}
		
		/**
		 * returnes Node by given Id
		 * @param unknown_type $node_id
		 */
		public function getNodeById($node_id){
			$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` ct
											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'category` c ON ct.c_id = c.c_id
											WHERE c.c_id = "'.mysql_real_escape_string($node_id).'"');
			if($q != '') {
				return new CatTreeNode($q['c_id'], new CatCategory($q['c_id'], $q['name'], $q['webname'], $q['status'], $q['desc'], $q['img'], $q['service_root']), $q['left'], $q['right'], $q['parent']);
			} else return null;
		}
		
		/**
		 * returnes Node by given webname
		 * @param unknown_type $node_name
		 */
		public function getNodeByName($node_name){
			$q = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` ct
											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'category` c ON ct.c_id = c.c_id
											WHERE c.webname = "'.mysql_real_escape_string($node_name).'"');
			if($q != '') {
				return new CatTreeNode($q['c_id'], new CatCategory($q['c_id'], $q['name'], $q['webname'], $q['status'], $q['desc'], $q['img'], $q['service_root']), $q['left'], $q['right'], $q['parent']);
			} else return null;
		}
		
		public function getChildrenForNodeId($node_id, $status=-1) {
			$r = array();
			$status = ($status != -1) ? ' AND `status`="'.mysql_real_escape_string($status).'"' : '';

			$a = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` ct
											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'category` c ON ct.c_id = c.c_id
											WHERE ct.parent = "'.mysql_real_escape_string($node_id).'"'.$status.' ORDER BY ct.left ASC');
			if($a != array()){
				foreach($a as $node){
					$r[] = new CatTreeNode($node['c_id'], new CatCategory($node['c_id'], $node['name'], $node['webname'], $node['status'], $node['desc'], $node['img'], $node['service_root']), $node['left'], $node['right'], $node['parent']);
				}
			}
			return $r;
		}
		
		private function getChildrenIdsForNodeId($node_id) {
			$r = array();
			$a = $this->mysqlArray('SELECT c_id FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` 
											WHERE `parent`="'.$node_id.'" ORDER BY `left` ASC');
			if($a != array()){
				foreach($a as $node){
					$r[] = (int)$node['c_id'];
				}
			}
			return $r;
		}
		
		/**
		 * returnes Service for given Node id
		 * @param unknown_type $id
		 */
		public function getServiceForNodeId($id){
			return $this->getServiceForNode($this->getNodeById($id));
		}
		
		/**
		 * returnes Service identifier for given Node
		 * @param CatTreeNode $node
		 */
		private function getServiceForNode(CatTreeNode $node){
			$ar = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` ct
											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'category` c ON ct.c_id = c.c_id
											WHERE ct.left < "'.mysql_real_escape_string($node->getLeft()).'" 
											AND c.service_root = "1" ORDER BY ct.left DESC LIMIT 0, 1');
			if($ar != ''){
				return $ar['name'];
			} else return '';
		}
		
		/**
		 * checks if Service Tree exists
		 * @param unknown_type $service
		 */
		public function ServiceHasTree($service) {
			$tree = $this->getNodeForService($service);
			
			return ($tree == null);
		}
		
		/**
		 * returnes path to service root
		 * @param unknown_type $id
		 */
		public function getCategoryPath($id){
			$cat = $this->getNodeById($id);	
			$return = array();
			if($cat != null){
				while(!$cat->getCategory()->isServiceRoot()){
					$return[] = $cat->getCategory();
					$cat = $this->getNodeById($cat->getParentId());
				}
			}
			return $return;
		}
		
		/**
		 * inserts Node into Database
		 * @param unknown_type $node
		 * @param unknown_type $parent_id
		 * @param unknown_type $service
		 */
		public function insertNode($node, $parent_id, $service) {
			if($this->checkRight('administer_category', $service) || ($parent_id == -1 && $service == '') || ($parent_id == -1 && $service != '')){
				
				if(($parent_id == -1 && $service == '')) $parent = $this->root;
				else if($parent_id == -1 && $service != '') $parent = $this->getNodeForService($service);
				else $parent = $this->getNodeById($parent_id);
				
				
				if($parent != null){
					// set service root - will be 1 if $node is a service root node
					$service_root = ($parent_id == -1 && $service == '') ? '1' : '0';
					
					// lock category_tree and category tables
					$this->mysqlLockTable($GLOBALS['db']['db_prefix'].'category_tree, ',$GLOBALS['db']['db_prefix'].'category');
					
					// make space in the tree for new node
					$q = ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` SET `right`=`right`+2 WHERE `right`>="'.$parent->getRight().'"') !== false);
					$q = $q && ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` SET `left`=`left`+2 WHERE `left`>"'.$parent->getRight().'"') !== false);
					
					// insert new Category
					$new_id = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category` (`name`, `webname`, `img`, `service_root`) 
												VALUES ("'.mysql_real_escape_string($node->getCategory()->getName()).'",
														"'.mysql_real_escape_string($node->getCategory()->getWebName()).'",
														"'.$node->getCategory()->getImg().'",
														"'.mysql_real_escape_string($service_root).'")');
					
					// finally insert new node into tree
					if($q && ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'category_tree` (c_id, `left`, `right`, `parent`)
															VALUES ("'.mysql_real_escape_string($new_id).'", "'.mysql_real_escape_string($parent->getRight()).'",
																	"'.mysql_real_escape_string($parent->getRight()+1).'", "'.$parent->getId().'")') !== false)){
					
						$this->mysqlUnlock();
						$this->reloadServiceTree($service);
						return true;
					} else {
						$this->mysqlUnlock();
						return false;
					}
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
		
		public function editCategory($id, $name, $webname, $status, $desc, $service, $img=0){
			if($this->checkRight('administer_category', $service)) {
				$img = ($img > -1) ?  ',`img`="'.mysql_real_escape_string($img).'"': '';
				
				return $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category` 
													SET `name`="'.mysql_real_escape_string($name).'",
														`webname`="'.mysql_real_escape_string($webname).'",
														`status`="'.mysql_real_escape_string($status).'",
														`desc`="'.mysql_real_escape_string($desc).'"
														'.$img.'
														WHERE c_id = "'.mysql_real_escape_string($id).'"');
			}else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
		
		/**
		 * triggers recursive SubTree deletion
		 * @param unknown_type $node_id
		 * @param unknown_type $service
		 */
		public function deleteSubTree($node_id, $service){
			if($this->checkRight('administer_category', $service)){
				$node = $this->getNodeById($node_id);
				if($node != null) {
					$tree = $this->loadSubTree($node);
				
					return $this->deleteSubTreeRec($tree, $service);
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
		
		/**
		 * recursively deletes a subtree  
		 * @param CatTreeNode $tree
		 * @param unknown_type $service
		 */
		private function deleteSubTreeRec(CatTreeNode $tree, $service){
			if($tree->getChildren() != array()) {
				foreach($tree->getChildren() as $child){
					// you have to reload $child node because Left and Right values could be changed
					$this->deleteSubTreeRec($this->getNodeById($child->getId()), $service);
				}
			}					
			// you have to reload $tree node because Left and Right values could be changed
			return $this->deleteNode($this->getNodeById($tree->getId()), $service);
		}
		
		/**
		 * deletes Node from Database
		 * @param unknown_type $node
		 * @param unknown_type $service
		 */
		private function deleteNode($node, $service) {
			//$node = $this->getNodeById($node_id);
			
			if($node != null){
				$this->mysqlLockTable($GLOBALS['db']['db_prefix'].'category_tree, ',$GLOBALS['db']['db_prefix'].'category');
				
				$q = ($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'category_tree` WHERE c_id="'.$node->getId().'"') !== false);
				$q = $q && ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` SET `left`=`left`-2 WHERE `left`>"'.$node->getRight().'"') !== false);
				$q = $q && ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` SET `right`=`right`-2 WHERE `right`>"'.$node->getRight().'"') !== false);
				if($q && ($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'category` WHERE c_id="'.$node->getId().'"') !== false)){
					$this->mysqlUnlock();
					$this->reloadServiceTree($service);
					return true;
				} else {
					$this->mysqlUnlock();
					return false;
				}
			}
		}
		
		public function changeCategoryOrder($service, $id, $parent=-1, $after=-1, $before=-1){
			if($id > 0 && ($parent > 0 || $after > 0 || $before > 0) && $id != $parent && $id != $after && $id != $before) {
        		if($this->checkRight('administer_category', $service)){
        			$node = $this->getNodeById($id);
        			if($node != null){
	        			$tree = $this->loadSubTree($node);
	        			if($parent == -1){
	        				if($after == -1 && $before > -1){
		        				// insert before node
	        					$before = $this->getNodeById($before);
		        				if($before != null){
		        					$parent = $this->getNodeById($before->getParentId());
		        					$children = $this->getChildrenIdsForNodeId($parent->getId());
		        					$position = array_search($before->getId(), $children);
		        					return $this->moveSubTree($tree, $parent->getId(), $position);
		        				} else return false;
	        				} else {
	        					// insert after node
	        					$after = $this->getNodeById($after);
		        				if($after != null){
		        					$parent = $this->getNodeById($after->getParentId());
		        					$children = $this->getChildrenIdsForNodeId($parent->getId());
		        					$self_pos = array_search($id, $children);
		        					$position = array_search($after->getId(), $children)+1;
		        					return $this->moveSubTree($tree, $parent->getId(), $position);
		        				} else return false;
	        				}
	        				
	        				
	        			} else {
	        				return $this->moveSubTree($tree, $parent);
	        			}
        			}
        		} else {
        			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		return false;
        	}
		}
		
		/**
		 * moves Sub Tree under Parent at given position
		 * It is a modified version of Zebra_Mptt 2.2 by Stefan Gabos <contact@stefangabos.ro>
		 * 
		 * @param CatTreeNode $tree
		 * @param unknown_type $parent
		 * @param unknown_type $position
		 */
		private function moveSubTree(CatTreeNode $tree, $parent, $position=false){
			$p_node = $this->getNodeById($parent);
			if($p_node != null){
				
				// get children nodes of target node (first level only)
				// load now because database will get locked
				$target_children = $this->getChildrenForNodeId($p_node->getId());
				
				$this->mysqlLockTable($GLOBALS['db']['db_prefix'].'category_tree');
				
            	// the value with which nodes outside the boundary set below, are to be updated with
				$source_rl_difference = ((int)$tree->getRight() - (int)$tree->getLeft() + 1);

	            // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
	            // the insert, and will need to be updated				
	            $source_boundary = (int)$tree->getLeft();
				
           	 	// we'll multiply the "left" and "right" values of the nodes we're about to move with "-1", in order to
            	// prevent the values being changed further in the script
            	$this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `left`=`left`*-1 , `right`=`right`*-1 
										WHERE `left` >= "'.mysql_real_escape_string($tree->getLeft()).'" 
										AND `right` <= "'.mysql_real_escape_string($tree->getRight()).'"' );
				
				// update the nodes in the database having their "left"/"right" values outside the boundary
				// subtree of $tree will not be changed because left and right values are now < 0
				$this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `left`=`left`-'.$source_rl_difference.'  
										WHERE `left` > "'.$source_boundary.'" ');
				$this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `right`=`right`-'.$source_rl_difference.'  
										WHERE `right` > "'.$source_boundary.'" ');
				
				// if left and right values would have changed in database -> change as well
				if($p_node->getLeft() > $source_boundary) $p_node->setLeft($p_node->getLeft()-$source_rl_difference);
				if($p_node->getRight() > $source_boundary) $p_node->setRight($p_node->getRight()-$source_rl_difference);
				
				
				// if node is to be inserted in the default position (as the last of target node's children nodes)
				if ($position === false){
					$position = count($target_children);
				} else {
					 // make sure given position is an integer value
                	$position = (int)$position;

                	// if position is a bogus number
                	if ($position > count($target_children) || $position < 0)

                    	// use the default position (as the last of the target node's children)
                    	$position = count($target_children);
                    	
				}
				//$this->debugVar($position);
				
				// if target node has no children nodes OR the node is to be inserted as the target node's first child node
	            if (empty($target_children) || $position == 0)
	
	                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
	                // the insert, and will need to be updated
	                // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
	                $target_boundary = (int)$p_node->getLeft();
	
	            // if target has any children nodes and/or the node needs to be inserted at a specific position
	            else {
	            
	                // find the target's child node that currently exists at the position where the new node needs to be inserted to
	                // since PHP 5.3 this needs to be done in two steps rather than
	                // $target_children = array_shift(array_slice($target_children, $position - 1, 1));
	                // or PHP will trigger a warning "Strict standards: Only variables should be passed by reference"
	                $tmp = $target_children[$position-1];
	                
					// if left and right values would have changed in database -> change as well
	                //if($tmp->getLeft() > $source_boundary) $tmp->setLeft($tmp->getLeft()-$source_rl_difference);
					//if($tmp->getRight() > $source_boundary) $tmp->setRight($tmp->getRight()-$source_rl_difference);
	
	                // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
	                // the insert, and will need to be updated
	               // $target_boundary = $tmp->getRight();
	                $target_boundary = ($tmp->getRight() > $source_boundary) ?  $tmp->getRight()-$source_rl_difference : $tmp->getRight();
	            }
	           // $this->debugVar($tmp);
	            
            	// update the nodes in the database having their "left"/"right" values outside the boundary
	            $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `left`=`left`+'.$source_rl_difference.'  
										WHERE `left` > "'.$target_boundary.'" ');
				$this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `right`=`right`+'.$source_rl_difference.'  
										WHERE `right` > "'.$target_boundary.'" ');
	            
				// finally, the nodes that are to be inserted need to have their "left" and "right" values updated
            	$shift = $target_boundary - $source_boundary + 1;

            	// (notice that we're subtracting rather than adding and that finally we multiply by -1 so that the values
            	// turn positive again)
            	$this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `left`=(`left`-'.$shift.')*-1,
											`right`=(`right`-'.$shift.')*-1    
										WHERE `left` < 0 ');
            	 // finally, update the parent of the source node
            	 $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category_tree` 
										SET `parent`="'.$p_node->getId().'" WHERE 
            							`c_id`="'.$tree->getId().'"');
            	
				$this->mysqlUnlock();
				
				return true;
				
			} else return false;
		}
		
		/**
		 * sets Image for given CategoryId
		 * @param unknown_type $cat_id
		 * @param unknown_type $img_id
		 */
		public function setCategoryImage($cat_id, $img_id){
        	if($this->checkRight('administer_category', $this->getServiceForNodeId($cat_id))){
        		return $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'category` SET `img`="'.mysql_real_escape_string($img_id).'" WHERE c_id="'.mysql_real_escape_string($cat_id).'"');
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
		}
	}
?>