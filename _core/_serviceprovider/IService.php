<?php
    interface IService {
        /**
         *  args: array('_action'=>$action, //page, list, etc
         *              'argumente...  //GET, POST
         *              );
         */
    	
    	/**
    	 * Renders a view of the Service results
    	 * @param array $args An array containing arguments
    	 * @return string
    	 */
        public function view($args);
        
        /**
         * Returns an adminview of the Service 
         * @param array $args An array containing arguments
         * @return string
         */
        public function admin($args); 

        /**
         * Returns the data fetched by the Service
         * @param array $args An array of arguments
         * @return array
         */
        public function data($args);
        
        /**
         * Executes a Service an returns true if successful. Otherwise false is returned.
         * @param array $args An array of arguments
         * @return boolean
         */
        public function run($args);
        
        /**
         * Runs the setup steps implemented by the service. e.g. creation of database entries, folders, ...
         */
        public function setup();
    }
?>