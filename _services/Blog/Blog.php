<?php
    /**
     * Service for displaying and editing Data for a Blog.
     * @author Matthias Eigner
     * @version: 0.1r2
     * @name: Blog
     * 
     * @requires: Pagina
     * @requires: Comment (if Comments are enabled)
     */

	class Blog extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
    	
        const OFFLINE = 0;
        const ONLINE = 1;
        const BLOCKED = 2;
        const REVIEW = 3;
        const NEU = 4;
        const STICKY = 5;
        const UNBEKANNT = 6;
        const TRASH = 7;
        
        function __construct(){
        	$this->name = 'Blog';
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Blog/config.Blog.php');
            //$this->sp->run('Localization', array('load'=>$this->config['loc_file']));
            $this->date_name = array('01' => 'Jan',
                                '02' => 'Feb',
                                '03' => 'Mrz',
                                '04' => 'Apr',
                                '05' => 'Mai',
                                '06' => 'Jun',
                                '07' => 'Jul',
                                '08' => 'Aug',
                                '09' => 'Sep',
                                '10' => 'Okt',
                                '11' => 'Nov',
                                '12' => 'Dez');
        }
        
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['mode'] String | Mode-Parameter of Blog (list, view, cat, tag, list_categories, list_tags)
         *  @param $args['cat'] String | Category to display
         *  @param $args['tag'] String | Tag to display
         *  @param $args['id'] String | Id of the Blog entry (for mode=view only)
         *  @param $args['page'] String | current page
         */
        public function view($args) {
            $GLOBALS['extra_css'][] = $this->config['css_file'];
            $modes = array('list', 
                            'view', 
                            'cat', 
                            'tag', 
                            'list_categories', 
                            'list_tags',
            				'list_tag_cloud');
            
            $mode = isset($args['mode'])&&$args['mode'] != '' ? mysql_real_escape_string($args['mode']) : '';
            $mode = ($mode == '') ? $modes[0]: $mode;
            $mode = (in_array($mode, $modes)) ? $mode : 'break';
            
            $page = isset($args['page'])&&$args['page'] != '' ? mysql_real_escape_string($args['page']) : 1;
            $cat = isset($args['cat'])&&$args['cat'] != '' ? mysql_real_escape_string($args['cat']) : '';
            $id = isset($args['id'])&&$args['id'] != '' ? mysql_real_escape_string($args['id']) : '';
            $tags = isset($args['tag'])&&$args['tag'] != '' ? mysql_real_escape_string($args['tag']) : '';
			
            
            if($mode == 'list' || $mode == 'cat' || $mode == 'tag')   
            	return $this->renderList($mode, $cat, $tags, $page);
            else if($mode == 'view' && $id != '') 
            	return $this->renderSingle($id, $page);
            else if($mode == 'list_categories') 
            	return $this->renderSideMenuCategories();
            else if($mode == 'list_tags') 
            	return $this->renderSideMenuTags();
            else if($mode == 'list_tag_cloud') 
            	return $this->renderSideMenuTagCloud();
            else if($mode == 'break')   
            	return '';
            
            return '';
                
        }
        public function admin($args){
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	$page = isset($args['page']) ? $args['page'] : 1;
        	$type = isset($args['type']) ? $args['type'] : '';
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$action= isset($args['action']) ? $args['action']: '';
        	$parent= isset($args['parent']) ? $args['parent']: -1;
        	$after= isset($args['after']) ? $args['after']: -1;
        	$before= isset($args['before']) ? $args['before']: -1;
        	
        	switch($chapter) {
        		case 'list':
        			return $this->getAdminCenterList($type, $page);
        			break;
        		case 'new':
        			return $this->getAdminCenterNew();
        			break;
        		case 'view':
        			return $this->getAdminCenterEdit($id);
        			break;
        		case 'category':
        			return $this->tplAdminGetCategories();
        			break;
        		default;
        			switch($action){
        				case 'changeCategoryOrder':
        					return $this->sp->ref('Category')->changeCategoryOrder($this->name, $id, $parent, $after, $before);
        					break;
        				case 'deleteCategory':
        					return $this->sp->ref('Category')->deleteCategory($id, $this->name);
        					break;
        				case 'showCategoryEdit':
        					return $this->sp->ref('Category')->tplEditCategory($id, $this->name);
        					break;
        			}
        			return $this->getAdminCenterMain($chapter);
        			break;
        	}
        	
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
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
        	if(isset($_POST['cat_action']) && $_POST['cat_action'] == 'new'){
        		$this->sp->ref('Category')->newCategory($_POST['cat_name'], $this->sp->ref('TextFunctions')->string2Web($_POST['cat_name']), $this->name);
        	} else if(isset($_POST['cat_action']) && $_POST['cat_action'] == 'edit'){
        		$this->sp->ref('Category')->editCategory($_POST['cat_id'], $_POST['cat_name'], $_POST['cat_webname'], $this->name);
        	}
        }
        
        /**
         * Displays a sidebox with Category-Links for the Blog
         */
        public function renderSideMenuCategories() {
            //--------   Sidebars
            $array = $this->sp->db->data(array('query'=>'SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'blog_category` ;', 'type'=>'array'));
            $main = new ViewDescriptor($this->config['tpl']['stuff/list_categories']);
            if(is_array($array)){
            foreach($array as &$row){
	            	
	                $cat = new SubViewDescriptor('categories');
            	
	                $count = $this->mysqlRow('SELECT COUNT(*) as count FROM `'.$GLOBALS['db']['db_prefix'].'blog_entry_category` WHERE `k_id` = "'.$row['k_id'].'"');
            		if(is_array($count) && isset($count['count'])) {
            			if($count['count'] > 0) {
	            			$a = new SubViewDescriptor('categories_count');
	            			$cat->addSubView($a);
	            			$a->addValue('count', $count['count']);
	            			unset($a);
            			}
            		}
	            	
	                $cat->addValue('id', $row['k_id']);
	                $cat->addValue('name', $row['name_'.$GLOBALS['Localization']['language']]);
	                $cat->addValue('webname', $row['name_'.$GLOBALS['Localization']['language']]); //TODO: createWebname function
	                $main->addSubView($cat);
	                unset($cat);
	            }
	            
	            unset($row);
            }
            
            return $main->render();
        }
        
        /**
         * 
         * Displays a sidebox with Tag-Cloud for the Blog
         * @return string
         */
        public function renderSideMenuTagCloud() {
            $array = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'blog_tags ;');
            $main = new ViewDescriptor($this->config['tpl']['stuff/list_tag_cloud']);
            
            $countar = array();

            foreach($array as &$row){

            	$count = $this->mysqlRow('SELECT COUNT(*) as count FROM `'.$GLOBALS['db']['db_prefix'].'blog_entry_tags` WHERE `t_id` = "'.$row['t_id'].'"');
            	
            	if(is_array($count) && isset($count['count'])) $countar[$row['t_id']] = $count['count'];
            	else $countar[$row['t_id']] = 0;
            } 
            
            $max = max($countar);
            
            shuffle($array);
            
            foreach($array as &$row){
                $tag = new SubViewDescriptor('tags');
                $main->addSubView($tag);
                
                $percent = floor(($countar[$row['t_id']] / $max) * 100);
                
                if ($percent < 20) $class = 'smallest';
				elseif ($percent >= 20 and $percent < 40) $class = 'small';
				elseif ($percent >= 40 and $percent < 60) $class = 'medium';
				elseif ($percent >= 60 and $percent < 80) $class = 'large';
				else $class = 'largest';

				$tag->addValue('id', $row['t_id']);
				$tag->addValue('class', $class);
				$tag->addValue('name', $row['name_'.$GLOBALS['Localization']['language']]);
                $tag->addValue('webname', $row['name_'.$GLOBALS['Localization']['language']]); //TODO: createWebname function
                
                unset($tag);
            }
            
            unset($row);
            
            return $main->render();
        }
        
		/**
         * 
         * Displays a sidebox with Tag-Links for the Blog
         * @return string
         */
        public function renderSideMenuTags() {
            $array = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'blog_tags ;');
            $main = new ViewDescriptor($this->config['tpl']['stuff/list_tags']);
			
            if(is_array($array) && count($array) > 0) {
	            foreach($array as &$row){
	                $tag = new SubViewDescriptor('tags');
	                $tag->addValue('id', $row['t_id']);
	                $tag->addValue('name', $row['name_'.$GLOBALS['Localization']['language']]);
	                $tag->addValue('webname', $row['name_'.$GLOBALS['Localization']['language']]); //TODO: createWebname function
	                $main->addSubView($tag);
	                unset($tag);
	            }
	            unset($row);
           	}
            
            return $main->render();
        }
        
        /**
         * 
         * Displays single Blog entry with the id $id.
         * @param $id integer | id of the blog-entry
         */
        public function renderSingle($id){
            $main = new ViewDescriptor($this->config['tpl']['view/main']);
            $array = $this->sp->db->data(array('query'=>'SELECT *, 
                                                                    DATE_FORMAT(be.creation_date, \'%m\') datum_month,
                                                                    DATE_FORMAT(be.creation_date, \'%d\') datum_day,
                                                                    DATE_FORMAT(be.creation_date, \'%Y\') datum_year,
                                                                    DATE_FORMAT(be.creation_date, \'%Y:%m:%d\') datum_full
                                                             FROM '.$GLOBALS['db']['db_prefix'].'blog_entry be
                                                             LEFT JOIN '.$GLOBALS['db']['db_prefix'].'user u ON be.author = u.id
                                                             WHERE be.status=\''.Blog::ONLINE.'\' AND be.e_id=\''.$id.'\'  
                                                             ORDER BY be.e_id DESC', 'type'=>'array'));
            if($array != array()){
                $row=$array[0];
                $main->addValue('author', $row['nick']);
                $main->addValue('author_id', $row['author']);
                $main->addValue('date_month', $row['datum_month']);
                $main->addValue('date_month_name', $this->date_name[$row['datum_month']]);
                $main->addValue('date_year', $row['datum_year']);
                $main->addValue('date_day', $row['datum_day']);
                $main->addValue('date_full', $row['datum_full']);
                $main->addValue('titel', $row['title_'.$GLOBALS['Localization']['language']]);
                $main->addValue('description', $this->sp->ref('TextFunctions')->renderBBCode($row['desc_'.$GLOBALS['Localization']['language']]));
                $main->addValue('content', $this->sp->ref('TextFunctions')->renderBBCode($row['content_'.$GLOBALS['Localization']['language']]));
                $main->addValue('datum', $row['creation_date']);
                $main->addValue('id', $row['e_id']);
                
                if($row['comments'] == 1){
                	$main->showSubView('comments_enabled');
                	$a = new SubViewDescriptor('comments_enabled_script');
                	$main->addSubView($a);
                	$a->addValue('id', $row['e_id']);
                	unset($a);
                } else {
                	$main->showSUbView('comments_disabled');
                }
                 
                $cat_array = $this->sp->db->data(array('query'=>'SELECT * 
                                                                    FROM '.$GLOBALS['db']['db_prefix'].'blog_entry_category ek 
                                                                    LEFT JOIN '.$GLOBALS['db']['db_prefix'].'blog_category k ON ek.k_id = k.k_id
                                                                    WHERE ek.e_id=\''.$row['e_id'].'\';', 'type'=>'array'));
                if($cat_array != array()) {
                	$cat_l = new SubViewDescriptor('category_list');
                    foreach($cat_array as &$cat_row) {  
                        $cat = new SubViewDescriptor('category');
                        $cat_l->addSubView($cat);
                        
                        $cat->addValue('cat_name', $cat_row['name_'.$GLOBALS['Localization']['language']]);
                        $cat->addValue('cat_webname', $cat_row['name_'.$GLOBALS['Localization']['language']]); //TODO: create webname
                        unset($cat);
                    }
                    $main->addSubView($cat_l);
                    unset($cat_l);
                }
                $tag_array = $this->sp->db->data(array('query'=>'SELECT * 
                                                                FROM '.$GLOBALS['db']['db_prefix'].'blog_entry_tags et
                                                                LEFT JOIN '.$GLOBALS['db']['db_prefix'].'blog_tags t ON et.t_id = t.t_id
                                                                WHERE et.e_id=\''.$row['e_id'].'\';', 'type'=>'array'));
                if($tag_array != array()) {
                	$tag_l = new SubViewDescriptor('tags_list');
                	foreach($tag_array as &$tag_row) {  
                        $tag = new SubViewDescriptor('tags');
                        $tag_l->addSubView($tag);
                        
                        $tag->addValue('tag_name', $tag_row['name_'.$GLOBALS['Localization']['language']]);
                        $tag->addValue('tag_webname', $tag_row['name_'.$GLOBALS['Localization']['language']]); //TODO: create webname
                        unset($tag);
                    }
                    $main->addSubView($tag_l);
                    unset($tag_l);
                }
            } else header('Location: '.$GLOBALS['tpl']['root'].'blog.php');
            return $main->render();
        }
        
        /**
         * 
         * Displays a list of Blog-entrys defined by categories, tags and/or pages.
         * @param $mode String | Mode(list, cat, tag)
         * @param $cat String | Categories to display
         * @param $tags String | Tags to display
         * @param $page integer | Page number
         */
        public function renderList($mode, $cat='', $tags='', $page=1){
            //List style (all, category, tags)
            $array = array();
            $main = new ViewDescriptor($this->config['tpl']['list/main']);
            if($mode == 'cat' && $cat != '') {
            	$cat_array = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'blog_category WHERE name_'.$GLOBALS['Localization']['language'].'=\''.$cat.'\'', 'type'=>'array'));

                if($cat_array != array()) {
                	
	            	$nav = new SubViewDescriptor('nav_cat');
	            	$nav->addValue('name', $cat_array[0]['name_'.$GLOBALS['Localization']['language']]);
	            	$nav->addValue('webname', $cat_array[0]['webname_'.$GLOBALS['Localization']['language']]);
	            	$main->addSubView($nav);
	            	unset($nav);
	            		            	
                    $count = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count 
                                                                FROM ".$GLOBALS['db']['db_prefix']."blog_entry be 
                                                                LEFT JOIN ".$GLOBALS['db']['db_prefix']."blog_entry_category ek ON be.e_id = ek.e_id 
                                                                WHERE status='".Blog::ONLINE."' 
                                                                    AND ek.k_id = '".$cat_array[0]['k_id']."';"));
                    $count = $count['count'];
                    if($count == 0) $count = 1;
                    $pages = ceil($count/$this->config['per_page_list']);
                    $page = ($page <= $pages) ? $page : $pages;
                    $page = ($page >= 1) ? $page : 1;
             

                    $array = $this->sp->db->data(array('query'=>'SELECT *, 
                                                                        DATE_FORMAT(be.creation_date, \'%m\') datum_month,
                                                                        DATE_FORMAT(be.creation_date, \'%d\') datum_day,
                                                                        DATE_FORMAT(be.creation_date, \'%Y\') datum_year,
                                                                        DATE_FORMAT(be.creation_date, \'%Y:%m:%d\') datum_full
                                                                 FROM '.$GLOBALS['db']['db_prefix'].'blog_entry be
                                                                 LEFT JOIN '.$GLOBALS['db']['db_prefix'].'user u ON be.author = u.id
                                                                 LEFT JOIN '.$GLOBALS['db']['db_prefix'].'blog_entry_category ek ON be.e_id = ek.e_id
                                                                 WHERE be.status=\''.Blog::ONLINE.'\'
                                                                        AND ek.k_id = \''.$cat_array[0]['k_id'].'\'
                                                                 ORDER BY be.e_id DESC 
                                                                 LIMIT '.($page-1)*$this->config['per_page_list'].', '.$this->config['per_page_list'].';', 'type'=>'array'));
                } else header('Location: '.$GLOBALS['tpl']['root'].'blog.php');
                $main->addValue('headline', 'Alle Eintr&auml;ge der Kategorie: '.$cat_array[0]['name_'.$GLOBALS['Localization']['language']]);
                
                $pagina = new SubViewDescriptor('show_pagina');
                $pagina->addValue('pagina_count', $pages);
                $pagina->addValue('pagina_active', $page);
            } else if($mode == 'tag' && $tags != '') {
                $tag_array = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'blog_tags WHERE name_'.$GLOBALS['Localization']['language'].'=\''.$tags.'\'', 'type'=>'array'));

                if($tag_array != array()) {
                	
                	$nav = new SubViewDescriptor('nav_tag');
	            	$nav->addValue('name', $tag_array[0]['name_'.$GLOBALS['Localization']['language']]);
	            	$nav->addValue('webname', $tag_array[0]['webname_'.$GLOBALS['Localization']['language']]);
	            	$main->addSubView($nav);
	            	unset($nav);
	            	
                    $count = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count 
                                                                FROM ".$GLOBALS['db']['db_prefix']."blog_entry be 
                                                                LEFT JOIN ".$GLOBALS['db']['db_prefix']."blog_entry_tags et ON be.e_id = et.e_id 
                                                                WHERE status='".Blog::ONLINE."' 
                                                                    AND et.t_id = '".$tag_array[0]['t_id']."';"));
                    $count = $count['count'];
                    if($count == 0) $count = 1;
                    $pages = ceil($count/$this->config['per_page_list']);
                    $page = ($page <= $pages) ? $page : $pages;
                    $page = ($page >= 1) ? $page : 1;
                    
                    $array = $this->sp->db->data(array('query'=>'SELECT *, 
                                                                        DATE_FORMAT(be.creation_date, \'%m\') datum_month,
                                                                        DATE_FORMAT(be.creation_date, \'%d\') datum_day,
                                                                        DATE_FORMAT(be.creation_date, \'%Y\') datum_year,
                                                                        DATE_FORMAT(be.creation_date, \'%Y:%m:%d\') datum_full
                                                                 FROM '.$GLOBALS['db']['db_prefix'].'blog_entry be
                                                                 LEFT JOIN '.$GLOBALS['db']['db_prefix'].'user u ON be.author = u.id
                                                                 LEFT JOIN '.$GLOBALS['db']['db_prefix'].'blog_entry_tags et ON be.e_id = et.e_id
                                                                 WHERE be.status=\''.Blog::ONLINE.'\'
                                                                        AND et.t_id = \''.$tag_array[0]['t_id'].'\'
                                                                 ORDER BY be.e_id DESC 
                                                                 LIMIT '.($page-1)*$this->config['per_page_list'].', '.$this->config['per_page_list'].';', 'type'=>'array'));
                } else header('Location: '.$GLOBALS['tpl']['root'].'blog.php');
                $main->addValue('headline', 'Alle Eintr&auml;ge des Tags: '.$tag_array[0]['name_'.$GLOBALS['Localization']['language']]);
                
                $pagina = new SubViewDescriptor('show_pagina');
                $pagina->addValue('pagina_count', $pages);
                $pagina->addValue('pagina_active', $page);
            } else {
                $count = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."blog_entry WHERE status='".Blog::ONLINE."'"));
                if(isset($count['count']))$count = $count['count'];
                else $count = 1;
                if($count == 0) $count = 1;
                $pages = ceil($count/$this->config['per_page_list']);
                $page = ($page <= $pages) ? $page : $pages;
                $page = ($page >= 1) ? $page : 1;
                
                $array = $this->sp->db->data(array('query'=>'SELECT *, 
                                                                    DATE_FORMAT(be.creation_date, \'%m\') datum_month,
                                                                    DATE_FORMAT(be.creation_date, \'%d\') datum_day,
                                                                    DATE_FORMAT(be.creation_date, \'%Y\') datum_year,
                                                                    DATE_FORMAT(be.creation_date, \'%Y:%m:%d\') datum_full
                                                             FROM '.$GLOBALS['db']['db_prefix'].'blog_entry be
                                                             LEFT JOIN '.$GLOBALS['db']['db_prefix'].'user u ON be.author = u.id
                                                             WHERE be.status=\''.Blog::ONLINE.'\'  
                                                             ORDER BY be.e_id DESC 
                                                             LIMIT '.($page-1)*$this->config['per_page_list'].', '.$this->config['per_page_list'].';', 'type'=>'array'));
                $pagina = new SubViewDescriptor('show_pagina');
                $pagina->addValue('pagina_count', $pages);
                $pagina->addValue('pagina_active', $page);
                
                $main->addValue('headline', 'Blog');
            }
            
            
            if(!is_array($array) || count($array) == 0) {
            	unset($pagina);
            	$no_results = new SubViewDescriptor('no_results');
            	$no_results->addValue('noResults', $this->_('Keine Eintr&auml;ge vorhanden.'));
                $main->addSubView($no_results);
            } else {
            	$main->addSubView($pagina);
                foreach($array as &$row){
                    $entry = new SubViewDescriptor('entry');
                    $main->addSubView($entry);
                    
                    $comment_count = $this->sp->ref('Comment')->getCommentCount($row['e_id'], 'blog');

                    $comment_count = ($comment_count == '0') ? 'Noch keine Kommentare' :
                    						(($comment_count == 1) ? '1 Kommentar' : $comment_count.' Kommentare');
                    
                    if($row['comments'] == 0) $comment_count = 'Kommentare deaktiviert.';						
                    
                    $entry->addValue('author', $row['nick']);
                    $entry->addValue('author_id', $row['author']);
                    $entry->addValue('date_month', $row['datum_month']);
                    $entry->addValue('date_month_name', $this->date_name[$row['datum_month']]);
                    $entry->addValue('date_year', $row['datum_year']);
                    $entry->addValue('date_day', $row['datum_day']);
                    $entry->addValue('date_full', $row['datum_full']);
                    $entry->addValue('comment_count', $comment_count);
                    $entry->addValue('titel', $row['title_'.$GLOBALS['Localization']['language']]);
                    $entry->addValue('description', $this->sp->ref('TextFunctions')->renderBBCode($row['desc_'.$GLOBALS['Localization']['language']]));
                    $entry->addValue('datum', $row['creation_date']);
                    $entry->addValue('id', $row['e_id']);

                    $cat_array = $this->sp->db->data(array('query'=>'SELECT * 
                                                                    FROM '.$GLOBALS['db']['db_prefix'].'blog_entry_category ek 
                                                                    LEFT JOIN '.$GLOBALS['db']['db_prefix'].'blog_category k ON ek.k_id = k.k_id
                                                                    WHERE ek.e_id=\''.$row['e_id'].'\';', 'type'=>'array'));
                    if($cat_array != array()) {
                        $cat_l = new SubViewDescriptor('category_list');
                    	foreach($cat_array as &$cat_row) {  
                            $cat = new SubViewDescriptor('category');
                            $cat_l->addSubView($cat);
                            
                            $cat->addValue('cat_name', $cat_row['name_'.$GLOBALS['Localization']['language']]);
                            $cat->addValue('cat_webname', $cat_row['name_'.$GLOBALS['Localization']['language']]); //TODO: create webname
                            unset($cat);
                        }
                        $entry->addSubView($cat_l);
                        unset($cat_l);
                    }
                    $tag_array = $this->sp->db->data(array('query'=>'SELECT * 
                                                                    FROM '.$GLOBALS['db']['db_prefix'].'blog_entry_tags et
                                                                    LEFT JOIN '.$GLOBALS['db']['db_prefix'].'blog_tags t ON et.t_id = t.t_id
                                                                    WHERE et.e_id=\''.$row['e_id'].'\';', 'type'=>'array'));
                    if($tag_array != array()) {
                        $tag_l = new SubViewDescriptor('tags_list');
                    	foreach($tag_array as &$tag_row) {  
                            $tag = new SubViewDescriptor('tags');
                            $tag_l->addSubView($tag);
                            
                            $tag->addValue('tag_name', $tag_row['name_'.$GLOBALS['Localization']['language']]);
                            $tag->addValue('tag_webname', $tag_row['name_'.$GLOBALS['Localization']['language']]); //TODO: create webname
                            unset($tag);
                        }
                        $entry->addSubView($tag_l);
                      	unset($tag_l);
                    }
                    unset($entry);
                }
                unset($row);
            }
            return $main->render();
        }
        
        /* -------------------------   ADMIN CENTER -----------------------*/
        
        public function getAdminCenterMain(){
        	$GLOBALS['extra_css'][] = 'services/blog_admin.css';
        	$GLOBALS['extra_js'][] = 'blog_admin.js';
        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
        	        	
        	$content = new ViewDescriptor($this->config['tpl']['admin']);
        	
        	return $content->render();
        }
        
        public function getAdminCenterList($type='', $page=1){
        	$GLOBALS['extra_css'][] = 'services/blog_admin.css';
        	$GLOBALS['extra_js'][] = 'blog_admin.js';
        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
        	        	
        	
        	$count = $this->sp->db->data(array('query'=>"SELECT COUNT(*) count FROM ".$GLOBALS['db']['db_prefix']."blog_entry"));
            $count = $count['count'];
            if($count == 0) $count = 1;
            $pages = ceil($count/$this->config['per_page_list_admin']);
            $page = ($page <= $pages) ? $page : $pages;
            $page = ($page >= 1) ? $page : 1;
                
        	$content = new ViewDescriptor($this->config['tpl']['admin/list']);
        	$content->addValue('pagina_count', $pages);
            $content->addValue('pagina_active', $page);
            
            if($type=='detail') $content->showSubView('detail_sel');
            else $content->showSubView('simple_sel');
            
            $sv1 = ($type=='detail') ? new SubViewDescriptor('detail') : new SubViewDescriptor('simple');
                
        	$entries = $this->mysqlArray('SELECT *, 
                                                                    DATE_FORMAT(be.creation_date, \'%m\') datum_month,
                                                                    DATE_FORMAT(be.creation_date, \'%d\') datum_day,
                                                                    DATE_FORMAT(be.creation_date, \'%Y\') datum_year,
                                                                    DATE_FORMAT(be.creation_date, \'%H:%i:%S\') datum_time,
                                                                    DATE_FORMAT(be.creation_date, \'%Y.%m.%d\') datum_full
                                                             FROM '.$GLOBALS['db']['db_prefix'].'blog_entry be
                                                             LEFT JOIN '.$GLOBALS['db']['db_prefix'].'user u ON be.author = u.id
                                                             ORDER BY be.e_id DESC 
                                                             LIMIT '.($page-1)*$this->config['per_page_list_admin'].', '.$this->config['per_page_list_admin'].';');
        	//print_r($entries);
        	if(is_array($entries)){
        		foreach($entries as $e){
        			$sv = ($type=='detail') ?  new SubViewDescriptor('entry') : new SubViewDescriptor('entry_simple');
        			$sv->addValue('author', $e['nick']);
                    $sv->addValue('author_id', $e['author']);
                    $sv->addValue('date_month', $e['datum_month']);
                    $sv->addValue('date_month_name', $this->date_name[$e['datum_month']]);
                    $sv->addValue('date_year', $e['datum_year']);
                    $sv->addValue('date_day', $e['datum_day']);
                    $sv->addValue('date_time', $e['datum_time']);
                    $sv->addValue('status', $e['status']);
                    $sv->addValue('date_full', $e['datum_full']);
                    $sv->addValue('titel', $e['title_'.$GLOBALS['Localization']['language']]);
                   // $sv->addValue('description', $this->sp->ref('TextFunctions')->renderBBCode($e['desc_'.$GLOBALS['Localization']['language']]));
                    $sv->addValue('description', $this->sp->ref('TextFunctions')->cropText($e['desc_'.$GLOBALS['Localization']['language']], 200));
                    $sv->addValue('datum', $e['creation_date']);
                    $sv->addValue('id', $e['e_id']);
        			
                    $sv1->addSubView($sv);
                    unset($sv);
        		}
        	}
        	
        	$content->addSubView($sv1);
        	
        	return $content->render();
        }
        
        public function getAdminCenterNew() {
        	$tpl = new ViewDescriptor($this->config['tpl']['admin/new']);
        	return $tpl->render();	
        }
        
        public function getAdminCenterEdit($id){
        	$tpl = new ViewDescriptor($this->config['tpl']['admin/edit']);
        	return $tpl->render();	
        }
        
        public function tplAdminGetCategories(){
        	return $this->sp->ref('Category')->tplGetCategoryAdmincenter($this->name);
        }
        
    }
?>