<?php
/**
 * The Dispatcher of the framework
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Dispatcher
 *
 * The Dispatcher of the Framework
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class Dispatcher extends Singleton {

    /**
     * Result of the routing process
     *
     * @var bool
     */
    private $_hasResult;

    /**
     * Variable to hold the Router instance in it
     *
     * @var Router
     */
    private $_router;

    /**
     * Array of parameters
     *
     * @var array
     */
    private $_parameters;


    /**
     * Array of authentication
     *
     * @var array
     */
    private $_authentication;

    /**
     * The constructor of Dispatcher
     *
     * @return void
     */
    public function __construct() {
        $this->_hasResult = false;
        $this->_router = Router::getInstance();
        $this->_process();
    }

    /**
     * Processes the dispatch
     *
     * @return bool
     */
    private function _process() {
        $this->_hasResult = $this->_router->getResult();
        $this->_parameters    = $this->_router->getParams();
        if ($this->_parameters!==false) {
            $type = $this->_parameters["type"];
        }
        if ($this->_hasResult===false) {
            $type = "error";
        }
        return $this->_dispatch($type);
    }


    /**
     * Selects the dispatcher method for this route
     *
     * @param $type string Type of dispatch
     * @return void
     */
    private function _dispatch($type) {
        $type = ucfirst($type);
        $method = "_dispatch{$type}";
        if (!method_exists($this,$method)) {
            $this->_dispatchError();
        }
        else {
            call_user_func(array($this,$method));
        }
    }


    /**
     * Dispatches a redirection
     *
     * @return void
     */
    private function _dispatchRedirect() {
        $url = $this->_parameters["url"];
        header("Location: {$url}");
    }

    /**
     * Redirects to the main page
     *
     * @return void
     */
    public function redirectToIndex() {
        $url = BASE_URL;
        header("Location: {$url}");
    }

    /**
     * Dispatch a PHP request
     *
     * @return void
     */
    private function _dispatchPHP() {
        $module         = $this->_parameters["module"];
        $controller     = $this->_parameters["controller"];
        $action         = $this->_parameters["action"];
        $this->_authentication = $this->_parameters["authentication"];

        if ($this->_hasResult) {
            if ($module=="index" && $controller=="index" && $action=="index") {
                // User wants to get the main page
                $engine = new AppEngine();
         	    $engine->setParams("index", "index","indexFirst");
         	    $engine->render();
         	    $engine->setParams("index", "index","index");
         	    $engine->render();
         	    $engine->setParams("index", "index" ,"indexLast");
         	    $engine->render();
         	}
      	    else {
      	        $engine = new AppEngine();
         	    $engine->setParams("index", "index","indexFirst");
         	    $engine->render();
         	    $engine->setParams($module, $controller,$action,$this->_authentication);
         	    $engine->render();
         	    $engine->setParams("index", "index" ,"indexLast");
         	    $engine->render();
         	}
        }
    }

    /**
     * Dispatch a plugin
     *
     * @return void
     */
    private function _dispatchPlugin() {
        $pluginName   = $this->_parameters["name"];
        $pluginAction = $this->_parameters["action"];
        $engine       = new PluginEngine($pluginName,$pluginAction);
        return $engine->render();
    }

    /**
     * Dispatches an static page
     *
     * @return void
     */
    private function _dispatchStaticPage() {
        $file = $this->_router->getFile();
        $file = "static".DS."{$file}";
        $handle   = fopen($file,"r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        print $contents;
    }

    /**
      * Dispatches AJAX
      *
      * @return void
      */
    private function _dispatchAjax() {
        $module     = $this->_parameters["module"];
        $controller = $this->_parameters["controller"];
        $action     = $this->_parameters["action"];
        $ajax       = new AjaxEngine($module,$controller,$action,$this->_authentication);
        $ajax->render();
     }

     /**
      * Dispatches JSON
      *
      * @return void
      */
     private function _dispatchJSON() {
        $module     = $this->_parameters["module"];
        $controller = $this->_parameters["controller"];
        $action     = $this->_parameters["action"];
        $json       = new jsonEngine($module,$controller,$action);
        $json->render();
     }


     /**
      * Dispatches an error
      *
      * @return bool
      */
     private function _dispatchError() {
        if ( $this->_hasResult===false && $this->_router->getType()!="plugin" ) {
            $engine = new AppEngine();
            $engine->setParams("index", "index","indexFirst");
         	$engine->render();
         	ErrorHandler::displayNotFoundError();
         	$engine->setParams("index", "index" ,"indexLast");
         	$engine->render();
         	return true;
        }
        return false;
     }

}

?>
