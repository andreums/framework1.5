<?php
class BaseWidget implements IWidget {

        protected $name;
        protected $style;
        protected $html;
        protected $data;
        protected $dataSource;
        protected $keys;
        protected $js;

        public function __construct() {
            $this->data = array();
            $this->style = "";

        }

        private function _getData() {
            if ( is_string($this->dataSource) ) {
                $db = new DataBase();
                $db->Query($this->dataSource);
                if ($db->numRows()>0) {
                 while ( $data = $db->fetchObject() ) {
                        array_push($this->data,$data);
                 }
                }
                else {
                    $this->data = NULL;
                }
            }
            if (is_object ($this->dataSource) && (!is_string($this->dataSource) )  ) {
                $this->data = $this->dataSource;
            }

            if (is_array($this->dataSource)) {
                $this->data = $this->dataSource;
            }
            $this->getDataKeys();
        }

        public function setDataSource($object) {
            $this->dataSource = $object;
            $this->_getData();
        }

        public function getDataSource() {
            return $this->dataSource;
        }

        public function render() {
            $this->process();
            print $this->html;
        }

        public function setHTML($html) {
            $this->html = $html;
        }
        public function getHTML() {
            return $this->html;
        }

        public function setStyle($cssStyle) {
            $this->style = $cssStyle;
        }
        public function getStyle() {
            return $this->style;
        }

        public function getDataKeys() {
            if ( ($this->data!=NULL) && (count($this->data)>0) ) {
                if ( is_object($this->data[0]) ) {
                    $this->keys = array();
                    $reflect = new ReflectionObject($this->data[0]);
                    foreach ($reflect->getProperties() as $prop) {
                        array_push($this->keys,$prop->getName());
                    }
                }
                if ( is_array($this->data[0]) ) {
                    $this->keys = array_keys($this->data[0]);
                }
            }
            else {
                return NULL;
            }
        }

        public function process() {
        }

        public function setJS($js) {
        	$this->js = $js;
        }
        public function getJS() {
        	return $this->js;
        }

    }
?>