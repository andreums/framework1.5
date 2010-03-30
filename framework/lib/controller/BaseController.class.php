<?php
/**
 * The basic controller structure and methods
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class BaseController
 *
 * A basic controller with all the functionality for controllers
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class BaseController  {


	/**
    * The Model that is linked to the controller.
    *
    * @var model
    * @access private
    */
    protected $_model;


    /**
    * A stack with the variables to pass to a view
    *
    * @var array
    * @access private
    */
    protected $_viewVariables;

    /**
    * A variable to hold the filter
    *
    * @var Filter
    * @access private
    */
    protected $filter;

    /**
     * The constructor of BaseController
     *
     * @desc   The constructor of BaseController
     * @access public
     * @return void
     */
    public function __construct() {
        $this->_model = null;
		$this->_loadModel();
		$this->_viewVariables = array();
		$this->filter = Filter::getInstance();
	}



	/**
     * A method to get the name of the controller
     *
     * @access protected
     * @return string
     */
	protected function _getControllerName() {
	    $reflect = new ReflectionClass($this);
        $cname = substr($reflect->name,0,strpos ($reflect->name,"Controller"));
        return $cname;
	}

	/**
     * A method to load the model of a controller
     *
     * @access public
     * @return void
     */
    protected function _loadModel() {
        $model = $this->_getControllerName()."Model";
        try {
        	$route = "";
        	$config = Config::getInstance();
        	$route .= $config->getParam("modulePath");
        	$route .= "/";
        	$route .= $this->_getControllerName();
            $route .= "/";
            $route .= $config->getParam("modelPath");
            $route .= "/";
            $route .= $model.".php";
        	require_once $route;
        	$this->_model = new $model();
        }
        catch (Exception $ex) {
        	print $ex->getMessage();
        }
    }


    /**
     * Render a view
     *
     * @param  string $view The name of the view
     * @access public
     * @return void
     */
    public function renderView($view) {

         try {
            $route = "";
            $config = Config::getInstance();
            $route .= $config->getmodulePath();
            $route .= "/";
            $route .= $this->_getControllerName();
            $route .= "/";
            $route .= $config->getviewPath();
            $route .= "/";
            $route .= $view.".php";

            if ( file_exists($route) ) {
                include $route;
            }
            else {
                ErrorHandler::displayNotFoundError();
            }
        }
        catch (Exception $ex) {
            print $ex->getMessage();
        }
	}


	/**
	 * Adds a variable to the stack of variables of the views
	 *
	 * @param  string $name The name of the variable to add to the stack
	 * @param  mixed  $param The variable to push to the stack
	 * @access protected
	 * @return void
	 */
	protected function addVariable($name,$param) {
	    $this->_viewVariables[$name] = $param;
	}

	/**
	 * Gets a variable from the stack of variables
	 *
	 * @param string $name The name of the variable to get
	 * @return mixed The variable to get
	 */
	protected function getVariable($name) {
	    if ( isset($this->_viewVariables[$name]) ) {
	        return $this->_viewVariables[$name];
	    }
	}
}

?>