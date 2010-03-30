<?php
/**
 * Base plugin
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  Plugin
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class BasePlugin
 *
 * @category Framework
 * @package  Plugin
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 * @abstract
 *
 */
abstract class BasePlugin {

        /**
         * The name of the plugin
         *
         * @var string
         */
        private $_name;

        /**
         * Stack of variables to pass to a plugin view
         *
         * @var array
         */
        private $_viewVariables;

        /**
         * Array of options
         *
         * @var array
         */
        protected $_options;


        /**
         * Filter object
         *
         * @var Filter
         */
        protected $_filter;

        /**
         * The constructor of BasePlugin
         *
         * @param $options array An array of options for the plugin
         * @return void
         */
        public function __construct($options=array()) {
            $this->_filter = Filter::getInstance();
            $this->_properties = array();
            $this->_viewVariables = array();
            $this->_options = array();
            if (count($options)>0) {
                if (method_exists($this,"_setOptions")) {
                    $this->_setOptions($options);
                }
            }
            if (method_exists($this,"_setUp")) {
                $this->_setUp();
            }

        }

        /**
         * Destructor of BasePlugin
         *
         * @return void
         */
        public function __destruct() {
            unset($this->_options);
        }


        /**
         * Calls to a method of the plugin
         *
         * @param $method string The method to call
         * @param $arguments array An array of arguments for the call
         * @return mixed
         */
        public function __call($method,$arguments) {
        	if ( method_exists($this,$method) ) {
                $this->$method($arguments);
        	}
        	else {
        		trigger_error ("PLUGIN | Error: Plugin {$this->getPluginName()} does not provide a method {$method} to call",E_USER_NOTICE);
        		throw new Exception("Error: Plugin {$this->getPluginName()} does not provide a method {$method} to call");
        	}
        }

        /**
         * Gets an option
         *
         * @param $option string The name of the option
         * @return mixed
         */
        public function __get($option) {
            if ( isset($this->_options[$option]) ) {
                return $this->_options[$option];
            }
        }

        /**
         * Sets the value of an option
         *
         * @param $option string The name of the option
         * @param $value mixed The value to set on the option
         * @return void
         */
        public function __set($option,$value) {
            $this->_options[$option] = $value;
        }

        /**
         * Gets the name of the plugin
         *
         * @return string
         */
        public function getPluginName() {
            $reflect = new ReflectionClass($this);
	        $pname = $reflect->name;
	        $this->_name = $pname;
            return $pname;
        }

        /**
         * Gets the path where the plugins resides
         *
         * @return string
         */
        public final function getPluginsPath() {
            $path = getcwd().DS."framework".DS."plugins";
            return $path;
        }

        /**
         * Gets the path where the plugin resides
         *
         * @return string
         */
        public final function getPluginPath() {
            $name = $this->getPluginName();
            $path = getcwd().DS."framework".DS."plugins".DS.$name;
            return $path;
        }

        /**
         * Tries to render a view of a plugin
         *
         * @param $view string The name of the view
         * @return void
         */
        public function renderView($view) {
           try {
              $route = "";
              $route .= $this->getPluginsPath();
              $route .= DS;
              $route .= $this->getPluginName();
              $route .= DS."view".DS;
              $route .= $view.".php";

              if ( file_exists($route) ) {
                  include $route;
              }
              else {
                  ErrorHandler::displayNotFoundError();
              }
            }
            catch (Exception $ex) {
                trigger_error("PLUGIN | Plugin {$this->getPluginName()} cannot render view {$view}",E_USER_WARNING);
            }
        }

        /**
         * Adds a variable to the stack of variables
         * for the view
         *
         * @param $name string The name of the variable
         * @param $value mixed The value of the variable
         * @return void
         */
        public function addVariable($name,$value) {
            $this->_viewVariables[$name] = $value;
        }

        /**
         * Gets a variable of the stack of variables
         *
         * @param $name string The name of the variable
         * @return mixed
         */
        public function getVariable($name) {
            if ( isset($this->_viewVariables[$name]) ) {
                return $this->_viewVariables[$name];
            }
        }

        /**
         * Set the options of the plugin
         *
         * @param $options array Array of options
         * @return void
         */
        protected function _setOptions($options) {
            $this->_options = $options;
        }

        /**
         * Gets the options of the plugin
         *
         * @return array
         */
        protected function _getOptions() {
            return $this->_options;
        }

        /**
         * Sets the value of an option
         * @param $optionName string The name of the option
         * @param $optionValue mixed The value for the option
         * @return void
         */
        protected function _setOption($optionName,$optionValue) {
            $this->_options[$optionName] = $optionValue;
        }

        /**
         * Gets the value of an option
         *
         * @param $optionName string The name of the option
         * @return mixed
         */
        protected function _getOption($optionName) {
            if ( isset($this->_options[$optionName]) ) {
                return $this->_options[$optionName];
            }
            return null;
        }

}

?>