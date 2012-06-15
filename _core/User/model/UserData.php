<?php
        class UserData {
                private $id;
                private $name;
                private $desc;
                private $help;
                private $value;
                private $type;
                private $user_groups;

                
                function __construct($id, $name, $desc, $help, $value, $type){
                        $this->id = $id;
                        $this->name = $name;
                        $this->desc = $desc;
                        $this->help = $help;
                        $this->value = $value;
                        $this->type = $type;
                        $this->user_groups = array();
                }
                
                public function isUsedByGroup($id){
                        $this->user_groups[$id] = true;
                }
                
                public function getId() { return $this->id; }
                public function getName() { return $this->name; }
                public function getDesc() { return $this->desc; }
                public function getHelp() { return $this->help; }
                public function getValue() { return $this->value; }
                public function getType() { return $this->type; }
                
                public function isUsedByUserGroup($id) { return isset($this->user_groups[$id]) && ($this->user_groups[$id]==true); }
        }
?>