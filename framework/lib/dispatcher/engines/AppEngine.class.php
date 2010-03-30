<?php
/**
 * AppEngine to dispatch PHP
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class AppEngine
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class AppEngine implements  IEngine {

    /**
     * The module
     *
     * @var string
     */
    protected $_module;


    /**
     * The controller
     *
     * @var string
     */
    protected $_controller;


    /**
     * The action
     *
     * @var string
     */
    protected $_action;


    /**
     * Array of authentication roles
     *
     * @var array
     */
    protected $_authentication;


    /**
     * The path of the modules
     *
     * @var string
     */
    private $_modulePath;


    /**
     * The path of the controller
     *
     * @var string
     */
    private $_controllerPath;


    /**
     * The path of the model
     *
     * @var string
     */
    private $_modelPath;


    /**
     * The file of the controller
     *
     * @var string
     */
    private $_controllerFile;

    /**
     * The name of the controller
     *
     * @var string
     */
    private $_controllerName;


	/**
     * Constructs the Engine
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Sets the parameters for the Engine
     *
     * @param $module string The name of the module
     * @param $controller string The name of the controller
     * @param $action string The name of the action
     * @param $authentication Array authentication roles
     * @return void
     */
    public function setParams($module,$controller,$action,$authentication=false) {
        $this->_afterRender();
        if (isset($this->_authentication)) {
            unset($this->_authentication);
        }
    	$this->_module = $module;
        $this->_controller = $controller;
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

    	$modulePath = (string) $config->getParam("modulePath");
    	$controllerPath = (string) $config->getParam("controllerPath");
    	$modelPath = (string) $config->getParam("modelPath");


    	$this->_controllerName = $this->_controller."Controller";
        $this->_modulePath = $modulePath.DS.$this->_module;

        $this->_controllerPath = $this->_modulePath.DS.$controllerPath;
        $this->_controllerFile = $this->_modulePath.DS.$controllerPath.DS.$this->_controllerName.".php";
        $this->_modelPath = $this->_modulePath.DS.$modelPath.DS.$this->_controller."Model";

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
        unset($this->_action);
        unset($this->_controller);
        unset($this->_module);
        unset($this->_controllerName);
    }

    /*
     * Check if the module and the controller exists
     *
     * @return bool
     */
    public function _process() {
        if ( is_dir($this->_modulePath) ) {
            if (  is_dir($this->_controllerPath) ) {
                if ( file_exists($this->_controllerFile) ) {
                    return true;
                }
            }
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
                    require_once $this->_controllerFile;
                    $controllerObj = new $this->_controllerName();
                    if ( method_exists($controllerObj,$this->_action) ) {
                        $action = $this->_action;
                        $controllerObj->$action();
                    }
                    else {
                        ErrorHandler::displayNotFoundError();
                        trigger_error("Dispatcher | Error: Action {$action} does not exists in controller {$this->_controllerName}",E_USER_WARNING);
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
