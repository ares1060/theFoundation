<?php
	class UserObject{
        private $nick;
        private $email;
        private $group;
        private $id;
        private $status;
        
        private $userData;
       // private $fields;
		
        public function __construct($nick, $id, $email, $group, $status) {
            $this->nick = $nick;
            $this->email = $email;
            $this->id = $id;
            $this->group = $group;
            $this->status = $status;
            $this->userData = null;
        }
       
        public function loadData($sp) {
                if($this->userData == null) $this->userData = $sp->ref('User')->getUserData($this->id);
                return true;
        }
       
        //setter
       
        // getter
        public function getNick(){ return $this->nick; }
        public function getEmail(){ return $this->email; }
        public function getId(){ return $this->id; }
        public function getGroup() { return $this->group; }
        public function getStatus() { return $this->status; }
        public function getUserData() { return $this->userData; }
        //public function getField($name) {if(isset($this->fields[$name])) return $this->fields[$name]; else return false;}
    }
?>