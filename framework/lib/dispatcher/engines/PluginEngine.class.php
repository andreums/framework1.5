<?php
/**
 * PluginEngine to dispatch Plugin actions
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class PluginEngine
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class PluginEngine implements  IEngine {

    protected $_name;
    protected $_action;
    private $_pluginPath;
    private $_pluginFile;

    private $_authentication;

    /**
     * Constructor of the Engine
     *
     * @param $name string The name of the plugin
     * @param $action string The action of the plugin
     * @return void
     */
    public function __construct($name,$action) {

        $this->_name = $name;
        $this->_action = $action;
    }

    /**
     * Sets the parameters for the Engine
     *
     * @param $name string The name of the plugin
     * @param $action string The name of the action of the plugin
     * @param $authentication Array authentication roles
     * @return void
     */
    public function setParams($name,$action,$authentication=false) {
        if (isset($this->_authentication)) {
            unset($this->_authentication);
        }
    	$this->_name = $name;
        $this->_action = $action;
        $this->_authentication = $authentication;
    }

/**
     * Before render an action ...
     * 1. Check that the controller exists
     * 2. Check that the action exists and is callable (_process() method)
     * 3. Check the authentication
     * 4. Set Locales
     *
     * @return int
     */
    private function _beforeRender() {

    	$config = Config::getInstance();
    	$pluginPath = (string) $config->getParam("pluginPath");
    	$this->_pluginFile = $pluginPath."/".$this->_name."/plugin.php";

        if (!$this->_process()) {
            return 0;
        }

        if ($this->_authentication) {
            if (!Session::issetData("status","login") || Session::get("status","login")===false) {
                return -1;
            }

            else {
                if (!in_array(Session::get("role","login"),$this->_authentication)) {
                    return -1;
                }
            }
        }
        $this->_setLocales();
        return 1;
    }



	/**
     * Unset all the private variables
     *
     * @return void
     */
    private function _afterRender() {
        unset($this->_pluginFile);
        unset($this->_pluginPath);
        unset($this->_action);
    }

    /*
     * Checks if a plugin exists
     *
     * @return bool
     */
    public function _process() {
        if ( file_exists($this->_pluginFile) ) {
            return true;
        }
        return false;
    }

/*
     * Renders an action
     *
     * @return bool
     */
    public function render() {

        $result = false;

        if (method_exists($this,"_beforeRender")) {
            $result = $this->_beforeRender();

            if ($result==-1)  {
                // We are not authenticated to use this action
                ErrorHandler::displayForbiddenError();
                return false;
            }

            if ($result===0) {
                // Some error has ocurred
                ErrorHandler::displayNotFoundError();
                return false;
            }

            if ($result===1) {
                // Ok, dispatch the action
                try {
                    include "$this->_pluginFile";
                    $plugin = new $this->_name();
                    if ( method_exists($plugin,$this->_action) ) {
                        $action = $this->_action;
                        $result = $plugin->$action();
                    }
                    else {
                        ErrorHandler::displayNotFoundError();
                        trigger_error("Dispatcher | Error: Action {$action} does not exists in plugin {$this->_name}",E_USER_WARNING);
                    }
                }
                catch (Exception $ex) {
                    trigger_error("Dispatcher | An unexpected exception has been produced while trying to dispatch {$action} in controller {$this->_controllerName}: {$ex->getMessage()} ",E_USER_WARNING);
                }
            }

            else {
                ErrorHandler::displayNotFoundError();
                return false;
            }

        }

        if (method_exists($this,"_afterRender")) {
            $result = $this->_afterRender();
            return $result;
        }
    }


	/**
     * Establishes the Locale
     * for the application
     *
     * @return void
     */
    private function _setLocales() {
        i18n::setLocale();
    }
};
?>
