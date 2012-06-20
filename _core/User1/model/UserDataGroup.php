<?php
        class UserDataGroup {
                private $id;
                private $name;
                private $desc;
                private $data;
                
                function __construct($id, $name, $desc){
                        $this->id = $id;
                        $this->name = $name;
                        $this->desc = $desc;
                        $this->data = array();
                }
                
                // setter
                public function addData($data){
                        if(get_class($data) == 'UserData'){
                                $this->data[] = $data;
                                return true;
                        } else return false;
                }
                
                // getter
                public function getId() { return $this->id; }
                public function getName() { return $this->name; }
                public function getDesc() { return $this->desc; }
                public function getData() { return $this->data; }
        }
?>