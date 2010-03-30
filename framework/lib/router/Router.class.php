<?php
/**
 * Routing class
 * PHP Version 5.2

 * @package Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Router
 * a class to route all the requests of the framework
 *
 * @author andreu
 * @package Framework
 *
 */
class Router extends Singleton {

    /**
     * The URL of the request
     *
     * @var string
     */
    var $_url;


    /**
     * An array of loaded routes
     *
     * @var array
     */
    private $_loadedRoutes = array();


    /**
     * An array of params of the enrouting process
     *
     * @var array
     */
    var $_params;

    /**
     * The type of the enrouted route
     *
     * @var string
     */
    var $_type;


    /**
     * File for a static route
     *
     * @var string
     */
    var $_file;

    /**
     * Array of paths (util to look for
     * duplicated routes)
     *
     * @var array
     */
    private $_paths = array();


    /**
     * The result of the enrouting process
     *
     * @var bool
     */
    private $_enrouteResult = false;


    /**
     * Array of files that are directories (in UNIX)
     *
     * @var array
     */
    private $_dir_files = array(".","..");

    /**
     * The constructor of Router
     *
     * @return void
     */
    public function __construct() {
        $this->_loadRoutes();
    	$this->_loadPluginRoutes();
    	$this->_enroute();
    }

    /**
     * Performs the enrouting of the URLs
     * of the framework
     *
     * @return bool
     */
    private function _enroute() {
        $this->_url = Request::getServerURL();
        $this->_checkSpecialRoute();
    	$url = $this->_getRoutePath($this->_url);
    	$result = $this->toParams($url);
    	$this->_enrouteResult = $result;
    	return $result;
    }

    /**
     * Checks for special routes
     * (routes like index.php?lng=en)
     *
     * @return void
     */
    private function _checkSpecialRoute() {
        $url = Request::getServerURLClean();
        $pos = strpos($url,"?");
        if ($pos!==false) {
            $params = Request::getGetParams();
            if (isset($params["lng"])) {
                $locale = $params["lng"];
                i18n::setLocale($locale);
                Dispatcher::redirectToIndex();
                exit(0);
            }
        }
    }

    /**
     * Get all the paths of the loaded routes
     *
     * @return array
     */
    private function _getPaths() {
        $paths = array();
        if (count($this->_loadedRoutes)>0) {
            foreach ($this->_loadedRoutes as $route) {
                $paths[] = $route["path"];
            }
        }
        return $paths;
    }

    /**
     * Gets SOAP routes
     *
     * @return array
     */
    public function getSoapRoutes() {
        $soapRoutes = array();
        foreach ($this->_loadedRoutes as $route) {
            if ($route["type"]=="soap") {
                $controller = $route["options"]["controller"];
                $action = $route["options"]["action"];
                includeController($controller);
                $dcontroller = "{$controller}Controller";
                $controllerObj = new $dcontroller();
                if ( method_exists($controllerObj,$action) ) {
                    $soapAdapter = new FWSoapAdapter($controllerObj,array("{$action}"));
                    $soapRoutes[] = array($soapAdapter,$route["options"]["namespace"],$route["options"]["serviceName"],$route["options"]["description"]);
                }
            }
        }
        return $soapRoutes;
    }

    /**
     * Gets REST routes
     *
     * @return array
     */
    public function getRestRoutes() {

        $restRoutes = array();
        foreach ($this->_loadedRoutes as $route) {
            if ($route["type"]=="rest") {
                $controller = $route["options"]["controller"];
                $action = $route["options"]["action"];
                $method = $route["options"]["method"];
                $serviceName = $route["options"]["serviceName"];
                $description =  $route["options"]["description"];
                $authentication = $route["options"]["authenticationRole"];
                $restAdapter = new FWRestAdapter($controller,array($action),$method,$serviceName,$description,$authentication);
                $restRoutes[] = $restAdapter;
            }
        }
        return $restRoutes;
    }


    /**
     * Loads a PHP Route
     *
     * @param $route SimpleXMLElement The route
     * @return void
     */
    private function _loadPhp($route) {
        $path = (string) $route->path;
        $type = (string) $route->type;
        $module = (string) $route->module;
        $controller = (string) $route->controller;
        $action = (string) $route->action;
        $authenticationRoles = array();
        if (isset($route->authentication)) {
            foreach ($route->authentication->role as $auth) {
                $authenticationRoles[] = (string) $auth;
            }
        }

        $parameters = array();

         if (isset($route->parameters)) {
             foreach ($route->parameters->parameter as $param) {
                 $name = (string) $param["name"];
                 $ptype = (string) $param->type;
                 $required = (string) $param->required;
                 $parameters[] = array("name"=>$name,"type"=>$ptype,"required"=>$required);
              }
         }
         $this->connect($path,$type,array("module"=>$module,"controller"=>$controller,"action"=>$action,"auth","authentication"=>$authenticationRoles),$parameters);
    }

    /**
     * Loads a SOAP route
     *
     * @param $route SimpleXMLElement The route
     * @return bool
     */
    private function _loadSoap($route) {

        $path = (string) $route->path;
        $type = (string) $route->type;
        $module = (string) $route->module;
        $controller = (string) $route->controller;
        $action = (string) $route->action;
        $namespace = (string) $route->namespace;
        $description = (string) $route->description;
        $serviceName = (string) $route->serviceName;

        $parameters = array();

         if (isset($route->parameters)) {
             foreach ($route->parameters->parameter as $param) {
                 $name = (string) $param["name"];
                 $ptype = (string) $param->type;
                 $required = (string) $param->required;
                 $parameters[] = array("name"=>$name,"type"=>$ptype,"required"=>$required);
              }
         }

         // include the controller and check that the action exists
         includeController($controller);
         $dcontroller = "{$controller}Controller";
         $controllerObj = new $dcontroller();
         if ( method_exists($controllerObj,$action) ) {
             $this->connect($path,$type,array("module"=>$module,"controller"=>$controller,"action"=>$action,"namespace"=>$namespace,"description"=>$description,"serviceName"=>$serviceName),$parameters);
             return true;
         }
         return false;
    }

    /**
     * Loads a REST route
     *
     * @param $route SimpleXMLElement The route
     * @return bool
     */
    private function _loadRest($route) {

        $path = (string) $route->path;
        $type = (string) $route->type;
        $module = (string) $route->module;
        $controller = (string) $route->controller;
        $action = (string) $route->action;
        $method = (string) $route->method;
        $description = (string) $route->description;
        $serviceName = (string) $route->serviceName;
        $authenticationRoles = array();
        if (isset($route->authentication)) {
            foreach ($route->authentication->role as $auth) {
                $authenticationRoles[] = (string) $auth;
            }
        }

        $parameters = array();

         if (isset($route->parameters)) {
             foreach ($route->parameters->parameter as $param) {
                 $name = (string) $param["name"];
                 $ptype = (string) $param->type;
                 $required = (string) $param->required;
                 $parameters[] = array("name"=>$name,"type"=>$ptype,"required"=>$required);
              }
         }

         // include the controller and check that the action exists
         includeController($controller);
         $dcontroller = "{$controller}Controller";
         $controllerObj = new $dcontroller();
         if ( method_exists($controllerObj,$action) ) {
             $this->connect($path,$type,array("module"=>$module,"controller"=>$controller,"action"=>$action,"method"=>$method,"description"=>$description,"serviceName"=>$serviceName,"authentication"=>$authenticationRoles),$parameters);
             return true;
         }
         return false;
    }

    /**
     * Loads an AJAX route
     *
     * @param $route SimpleXMLElement The route
     * @return void
     */
    private function _loadAjax($route) {
        $path = (string) $route->path;
        $type = (string) $route->type;
        $module = (string) $route->module;
        $controller = (string) $route->controller;
        $action = (string) $route->action;
        $parameters = array();
        if (isset($route->parameters)) {
            foreach ($route->parameters->parameter as $param) {
                $name = (string) $param["name"];
                $ptype = (string) $param->paramType;
                $required = (string) $param->required;
                $parameters[] = array("name"=>$name,"type"=>$ptype,"required"=>$required);
             }
        }
        $this->connect($path,$type,array("module"=>$module,"controller"=>$controller,"action"=>$action),$parameters);
    }

    /**
     * Loads a JSON route
     *
     * @param $route SimpleXMLElement The route
     * @return void
     */
    private function _loadJson($route) {
        $path = (string) $route->path;
        $type = (string) $route->type;
        $module = (string) $route->module;
        $controller = (string) $route->controller;
        $action = (string) $route->action;
        $parameters = array();
        if (isset($route->parameters)) {
            foreach ($route->parameters->parameter as $param) {
                $name = (string) $param["name"];
                $ptype = (string) $param->type;
                $required = (string) $param->required;
                $parameters[] = array("name"=>$name,"type"=>$ptype,"required"=>$required);
            }
         }
        $this->connect($path,$type,array("module"=>$module,"controller"=>$controller,"action"=>$action),$parameters);
    }

    /**
     * Loads a static route
     * (eg: a static html file)
     *
     * @param $route SimpleXMLElement The route
     * @return void
     */
    private function _loadStatic($route) {
        $path = (string) $route->path;
        $type = (string) $route->type;
        $file = (string) $route->file;
        $this->connect($path,$type,$file);
    }

    /**
     * Loads a plugin route
     *
     * @param $route SimpleXMLElement The route
     * @return void
     */
    private function _loadPlugin($route) {
        $path = (string) $route->path;
        $type = (string) $route->type;
        $plugin = (string) $route->plugin;
        $action = (string) $route->action;
        $parameters = array();
        if (isset($route->parameters)) {
            foreach ($route->parameters->parameter as $param) {
                $name = (string) $param["name"];
                $ptype = (string) $param->type;
                $required = (string) $param->required;
                $parameters[] = array("name"=>$name,"type"=>$ptype,"required"=>$required);
            }
         }
         $this->connect($path,$type,array("plugin"=>$plugin,"action"=>$action),$parameters);
    }


    /**
     * Loads a redirect route
     *
     * @param $route SimpleXMLElement The route
     * @return void
     */
    private function _loadRedirect($route) {
        $path = (string) $route->path;
        $type = (string) $route->type;
        $redirectURL = (string) $route->url;
        $this->connect($path,$type,array("url"=>$redirectURL),false,false);
        return true;
    }


    /**
     * Gets the path of the route
     *
     * @param $route string The route
     * @return string
     */
    private function _getRoutePath($route) {

        $indexPos = strpos($route,"index.php");
        if ($indexPos!==false) {
        	$config = Config::getInstance();
        	$modRW = $config->getParam("modRewrite");
        	$str = substr($route,$indexPos+9);

        	if ($modRW=="yes") {
        		$url = substr($route,$indexPos+10);
        	}
        	else {
                $url = substr($route,$indexPos);
        	}

        }
        else {
            $urlexp = explode("/",$route);
            if ( $urlexp[count($urlexp)-1]=="" ) {
                $url = "/";
            }
        }

        return $url;
    }


    /**
     * Load all the routes of the framework
     * in all routes.xml files
     *
     * @return bool
     */
    private function _loadRoutes() {
        if (count($this->_loadedRoutes)==0) {
            try {
                $files = scandir("modules".DS,1);
                $files[] = "framework".DS."config";
                if ( count($files)>0 ) {
                    foreach ($files as $file) {
                        if ( !in_array($file,$this->_dir_files) && (!strpos($file,".php"))  ) {
                            if ($file=="framework".DS."config") {
                                $routesFilename = "{$file}".DS."routes.xml";
                            }
                            else {
                                $routesFilename = "modules".DS."{$file}".DS."routes.xml";
                            }
                            $xmlf = simplexml_load_file($routesFilename);
                            foreach ($xmlf as $route) {
                                $type = (string) $route->type;
                                $path = (string) $route->path;
                                $type = ucfirst($type);
                                $method = "_load{$type}";
                                $this->$method($route);
                            }
                        }
                    }
                }
            }
            catch (Exception $ex) {
                trigger_error("ROUTER | Router can't load some files and produces execption {$ex->getMessage()}",E_USER_WARNING);
                return false;
            }
        }
       return true;
    }

    /**
     * Load all the plugin routes
     *
     * @return bool
     */
    protected function _loadPluginRoutes() {

        try {
            $files = scandir("framework".DS."plugins",1);
            if ( count($files)>0 ) {
                foreach ($files as $file) {
                    if ( !in_array($file,$this->_dir_files)  && (!strpos($file,".php")) ) {
                        $routesFilename = "framework".DS."plugins".DS."{$file}".DS."routes.xml";
                        $xmlf = simplexml_load_file($routesFilename);
                        if (count($xmlf)>0) {
                            foreach ($xmlf as $route) {
                                $type = (string) $route->type;
                                $path = (string) $route->path;
                                $type = ucfirst($type);
                                $method = "_load{$type}";
                                $this->$method($route);
                            }
                        }
                    }
                }
            }
        }

        catch (Exception $ex) {
            trigger_error("ROUTER | Router can't load some files and produces execption {$ex->getMessage()}",E_USER_WARNING);
            return false;
        }
        return true;
    }

    /**
     * Method to "connect" a route in the framework
     *
     * @param $path string The path of the route
     * @param $type string The type of the route
     * @param $options array An array with the specific information about the route
     * @param $parameters array An array of parameters
     * @return bool
     */
    public function connect($path,$type,$options,$parameters) {

        $paths = $this->_getPaths();
        $specialPaths = array("/soap.php","/rest.php");
        if ( !in_array($path,$specialPaths) ) {
            if ( in_array($path,$paths) ) {
                trigger_error("Error: Route {$path} does exists yet (its duplicated!)");
                return false;
            }
        }

    	$route = array("path"=>$path,
                     "type"=>$type,
                     "options"=>$options,
                     "parameters"=>$parameters
        );

    	$this->_loadedRoutes[] = $route;
    	return true;
    }

    /**
     * Get the value of a parameter in the URL
     *
     * @param $url string The url
     * @param $name string The name of the parameter
     * @return mixed
     */
    private function _getParamValue($url,$name) {

        $urlArr = explode("/",$url);
        $urlArr = array_slice($urlArr,2);
        for ($i=0;$i<count($urlArr);$i++) {
            if ($urlArr[$i] == $name) {
                if ( isset($urlArr[$i+1]) ) {
                    return $urlArr[$i+1];
                }
            }
        }
        return false;
    }

    /**
     * Process a static route
     *
     * @param $route SimpleXMLElement the route
     * @param $url string The URL
     * @return bool
     */
    private function _processStatic($route,$url="") {
        if (count($route["options"])>0) {
            $file = $route["file"];
            $rparams = array("type"=>"static","url"=>$file);
        	$this->_params = $rparams;
        	return true;
        }

    }

    /**
     * Process a redirection
     *
     * @param $route SimpleXMLElement the route
     * @param $url string The URL
     * @return bool
     */
    private function _processRedirect($route,$url="") {
        if ( count($route["options"])>0 ) {
            $redirectURL = $route["options"]["url"];
            $type = $route["type"];
        	$rparams = array("type"=>"redirect","url"=>$redirectURL);
        	$this->_params = $rparams;
        	return true;
        }
        return false;
    }

    /**
     * Process a PHP request
     *
     * @param $route SimpleXMLElement the route
     * @param $url string The URL
     * @return bool
     */
    private function _processPhp($route,$url="") {


        $filter = Filter::getInstance();

        if ( count($route["options"])>0 )  {

            $authentication = false;
            $options = $route["options"];
        	$module = $options["module"];
            $controller = $options["controller"];
            $action = $options["action"];
            $type = $route["type"];
            if (isset($route["options"]["authentication"])) {
                $authentication = $route["options"]["authentication"];
            }
            $rparams = array( "type"=>$type,"module"=>$module,"controller"=>$controller,"action"=>$action,"authentication"=>$authentication);
            $this->_params = $rparams;

            $parameters = array();
            if (count($route["parameters"])>0) {

                foreach ($route["parameters"] as $parameter) {
                    $name = $parameter["name"];
                    $type = $parameter["type"];
                    $required = $parameter["required"];
                    $res = $this->_getParamValue($url,$name);

                    if (!$res) {
                        if ($required=="true") {
                            trigger_error("ROUTER | Parameter {$name} is required for {$route["path"]}",E_USER_WARNING);
                            return false;
                        }
                    }
                    else {

                        $urlArr = explode("/",$url);
                        $urlArr = array_slice($urlArr,3);
	                    for ($pos=0;$pos<count($urlArr);$pos++) {
	                        if ($url[$pos]==$name) {
	                            break;
	                        }
	                     }

	                     $value = $this->_getParamValue($url,$name);
	                     if (!empty($value)) {
	                         try {
	                             $value = trim($value);
	                             if ($type=="string") {
	                                 $res = $filter->isString($value);
	                        		 if (!$res) {
	                        		     trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        		     return false;
	                        		 }
	                        		 else {
	                        		      Request::registerParameter($name,$value);
	                        		 }
	                              }

	                              if ($type=="integer") {
	                                  $res = $filter->isInteger($value);
	                                   if($res) {
	                                       Request::registerParameter($name,$value);
	                                    }
	                        			else {
	                                       trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                       return false;
	                        			}
	                              }

	                        	  if ($type=="float") {
	                        	      $res = $filter->isFloat($value);
	                        	      if ($res) {
	                        	          Request::registerParameter($name,$value);
	                                  }
	                                  else {
	                                      trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                      return false;
	                                  }
	                        		}

	                        		if ($type=="boolean") {
	                        		    $res = $filter->isBoolean($value);
	                        		    if ($res) {
	                        		        Request::registerParameter($name,$value);
	                        			}
	                        			else {
	                        			    trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        			    return false;
	                        			}
	                        		}
	                        	}
	                        	catch (Exception $ex) {
	                        	    trigger_error("ROUTER | An exception has been produced in the router {$ex->getMessage()} ",E_USER_WARNING);
	                        	    return false;
	                        	}
	                          }
	                        }
	                        $parameters[] = array("name"=>$name,"type"=>$type,"required"=>$required);
	                    }
                    }
                    return true;
        }
        return false;
    }


    /**
     * Process an AJAX request
     *
     * @param $route SimpleXMLElement the route
     * @param $url string The URL
     * @return bool
     */
    private function _processAjax($route,$url="") {
        if ( count($route["options"])>0 )  {
            $options = $route["options"];
            $module = $options["module"];
            $controller = $options["controller"];
            $action = $options["action"];
            $type = $route["type"];
            $rparams = array( "type"=>$type,"module"=>$module,"controller"=>$controller,"action"=>$action);
            $this->_params = $rparams;

                    $parameters = array();
            if (count($route["parameters"])>0) {

                foreach ($route["parameters"] as $parameter) {
                    $name = $parameter["name"];
                    $type = $parameter["type"];
                    $required = $parameter["required"];
                    $res = $this->_getParamValue($url,$name);

                    if (!$res) {
                        if ($required=="true") {
                            trigger_error("ROUTER | Parameter {$name} is required for {$route["path"]}",E_USER_WARNING);
                            return false;
                        }
                    }
                    else {

                        $urlArr = explode("/",$url);
                        $urlArr = array_slice($urlArr,3);
	                    for ($pos=0;$pos<count($urlArr);$pos++) {
	                        if ($url[$pos]==$name) {
	                            break;
	                        }
	                     }

	                     $value = $this->_getParamValue($url,$name);
	                     if (!empty($value)) {
	                         try {
	                             $value = trim($value);
	                             if ($type=="string") {
	                                 $res = $filter->isString($value);
	                        		 if (!$res) {
	                        		     trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        		     return false;
	                        		 }
	                        		 else {
	                        		      Request::registerParameter($name,$value);
	                        		 }
	                              }

	                              if ($type=="integer") {
	                                  $res = $filter->isInteger($value);
	                                   if($res) {
	                                       Request::registerParameter($name,$value);
	                                    }
	                        			else {
	                                       trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                       return false;
	                        			}
	                              }

	                        	  if ($type=="float") {
	                        	      $res = $filter->isFloat($value);
	                        	      if ($res) {
	                        	          Request::registerParameter($name,$value);
	                                  }
	                                  else {
	                                      trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                      return false;
	                                  }
	                        		}

	                        		if ($type=="boolean") {
	                        		    $res = $filter->isBoolean($value);
	                        		    if ($res) {
	                        		        Request::registerParameter($name,$value);
	                        			}
	                        			else {
	                        			    trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        			    return false;
	                        			}
	                        		}
	                        	}
	                        	catch (Exception $ex) {
	                        	    trigger_error("ROUTER | An exception has been produced in the router {$ex->getMessage()} ",E_USER_WARNING);
	                        	    return false;
	                        	}
	                          }
	                        }
	                        $parameters[] = array("name"=>$name,"type"=>$type,"required"=>$required);
	                    }
                    }
                    return true;
        }
        return false;
    }

    /**
     * Process a JSON Request
     *
     * @param $route SimpleXMLElement the route
     * @param $url string The URL
     * @return bool
     */
    private function _processJson($route,$url="") {
        if ( count($route["options"])>0 ) {
            $options = $route["options"];
            $module = $options["module"];
            $controller = $options["controller"];
            $action = $options["action"];
            $type = $route["type"];
            $rparams = array( "type"=>$type,"module"=>$module,"controller"=>$controller,"action"=>$action);
            $this->_params = $rparams;

                    $parameters = array();
            if (count($route["parameters"])>0) {

                foreach ($route["parameters"] as $parameter) {
                    $name = $parameter["name"];
                    $type = $parameter["type"];
                    $required = $parameter["required"];
                    $res = $this->_getParamValue($url,$name);

                    if (!$res) {
                        if ($required=="true") {
                            trigger_error("ROUTER | Parameter {$name} is required for {$route["path"]}",E_USER_WARNING);
                            return false;
                        }
                    }
                    else {

                        $urlArr = explode("/",$url);
                        $urlArr = array_slice($urlArr,3);
	                    for ($pos=0;$pos<count($urlArr);$pos++) {
	                        if ($url[$pos]==$name) {
	                            break;
	                        }
	                     }

	                     $value = $this->_getParamValue($url,$name);
	                     if (!empty($value)) {
	                         try {
	                             $value = trim($value);
	                             if ($type=="string") {
	                                 $res = $filter->isString($value);
	                        		 if (!$res) {
	                        		     trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        		     return false;
	                        		 }
	                        		 else {
	                        		      Request::registerParameter($name,$value);
	                        		 }
	                              }

	                              if ($type=="integer") {
	                                  $res = $filter->isInteger($value);
	                                   if($res) {
	                                       Request::registerParameter($name,$value);
	                                    }
	                        			else {
	                                       trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                       return false;
	                        			}
	                              }

	                        	  if ($type=="float") {
	                        	      $res = $filter->isFloat($value);
	                        	      if ($res) {
	                        	          Request::registerParameter($name,$value);
	                                  }
	                                  else {
	                                      trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                      return false;
	                                  }
	                        		}

	                        		if ($type=="boolean") {
	                        		    $res = $filter->isBoolean($value);
	                        		    if ($res) {
	                        		        Request::registerParameter($name,$value);
	                        			}
	                        			else {
	                        			    trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        			    return false;
	                        			}
	                        		}
	                        	}
	                        	catch (Exception $ex) {
	                        	    trigger_error("ROUTER | An exception has been produced in the router {$ex->getMessage()} ",E_USER_WARNING);
	                        	    return false;
	                        	}
	                          }
	                        }
	                        $parameters[] = array("name"=>$name,"type"=>$type,"required"=>$required);
	                    }
                    }
                    return true;
        }
        return false;
    }

    /**
     * Process a Plugin Request
     *
     * @param $route SimpleXMLElement the route
     * @param $url string The URL
     * @return bool
     */
    private function _processPlugin($route,$url="") {

        if ( count($route["options"])>0 ) {
            $filter = Filter::getInstance();

            $options = $route["options"];
            $name = $options["plugin"];
            $action = $options["action"];
            $type = $route["type"];
            $this->_type = $type;
            $rparams = array( "type"=>$type,"name"=>$name,"action"=>$action);
            $this->_params = $rparams;

                    $parameters = array();
            if (count($route["parameters"])>0) {

                foreach ($route["parameters"] as $parameter) {
                    $name = $parameter["name"];
                    $type = $parameter["type"];
                    $required = $parameter["required"];
                    $res = $this->_getParamValue($url,$name);

                    if (!$res) {
                        if ($required=="true") {
                            trigger_error("ROUTER | Parameter {$name} is required for {$route["path"]}",E_USER_WARNING);
                            return false;
                        }
                    }
                    else {

                        $urlArr = explode("/",$url);
                        $urlArr = array_slice($urlArr,3);
	                    for ($pos=0;$pos<count($urlArr);$pos++) {
	                        if ($url[$pos]==$name) {
	                            break;
	                        }
	                     }

	                     $value = $this->_getParamValue($url,$name);
	                     if (!empty($value)) {
	                         try {
	                             $value = trim($value);
	                             if ($type=="string") {
	                                 $res = $filter->isString($value);
	                        		 if (!$res) {
	                        		     trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        		     return false;
	                        		 }
	                        		 else {
	                        		      Request::registerParameter($name,$value);
	                        		 }
	                              }

	                              if ($type=="integer") {
	                                  $res = $filter->isInteger($value);
	                                   if($res) {
	                                       Request::registerParameter($name,$value);
	                                    }
	                        			else {
	                                       trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                       return false;
	                        			}
	                              }

	                        	  if ($type=="float") {
	                        	      $res = $filter->isFloat($value);
	                        	      if ($res) {
	                        	          Request::registerParameter($name,$value);
	                                  }
	                                  else {
	                                      trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                                      return false;
	                                  }
	                        		}

	                        		if ($type=="boolean") {
	                        		    $res = $filter->isBoolean($value);
	                        		    if ($res) {
	                        		        Request::registerParameter($name,$value);
	                        			}
	                        			else {
	                        			    trigger_error("ROUTER | Paramater {$name} must be type {$type} for {$route["path"]} using url {$url}",E_USER_WARNING);
	                        			    return false;
	                        			}
	                        		}
	                        	}
	                        	catch (Exception $ex) {
	                        	    trigger_error("ROUTER | An exception has been produced in the router {$ex->getMessage()} ",E_USER_WARNING);
	                        	    return false;
	                        	}
	                          }
	                        }
	                        $parameters[] = array("name"=>$name,"type"=>$type,"required"=>$required);
	                    }
                    }
                    return true;
        }
        return false;
    }


    /**
     * Prepares an URL
     *
     * @param $url string The URL
     * @return string
     */
    private function _prepareURL($url) {
        if ( ($url=="/") || ($url=="") ) {
    		$url = "/";
    	}
    	else {
    		$url = trim($url,"/");
    	}

    	$tmpURL = array();
    	// if is a valid URL!
    	preg_match_all('/(([^\/]){1}(\/\/)?){1,}/',$url,$found);

        if ( (count($found[0])>0) ) {
        	$pieces = $found[0];
        	$params = array();
            for($i=1;$i<count($pieces);$i++) {
            	$params[] = $pieces[$i];
            }
            $tmpURL = array("base"=>$pieces[0],"params"=>$params);
        }
        else {
        	$pieces = "/";
        	$tmpURL = array("base"=>$pieces);
        }
        return $tmpURL;
    }

    /**
     * Compare two URLs
     *
     * @param $url1 string The first URL
     * @param $url2 string The second URL
     * @return int
     */
    private function _compareURLs($url1,$url2) {
        $url1 = explode("/",$url1);
        $url1 = array_slice($url1,0,2,true);
        $url1 = implode("/",$url1);
        if (strlen($url1)>1) {
            $url1 = rtrim($url1,"/");
        }

      	if ($url1[0]!="/") {
      	    $url1 = "/".$url1;
        }
       	if ($url2[0]!="/") {
       	    $url2 = "/".$url2;
        }
        $compVal = strcmp($url1,$url2);
        return $compVal;
    }


    /**
     * Compare two URLs for Plugin
     *
     * @param $url1 string The first URL
     * @param $url2 string The second URL
     * @return int
     */
    private function _comparePluginURLs($url1,$url2) {
        // workarround para plugins
        // TODO: Solucionar (MVC?)
        $url1 = explode("/",$url1);
        $url1 = array_slice($url1,0,3,true);
        $url1 = implode("/",$url1);

        if (strlen($url1)>0) {
            if  ($url1[0]!="/") {
                $url1 = "/".$url1;
            }
        }
        if (strlen($url2)>0) {
            if ($url2[0]!="/") {
                $url2 = "/".$url2;
            }

        }
        $compVal = strcmp($url1,$url2);
        return $compVal;
    }


    /**
     * Get the params of an URL
     *
     * @param $url string The URL
     * @return bool
     */
    public function toParams($url) {
        $tmpURL = $this->_prepareURL($url);
       	foreach ($this->_loadedRoutes as $route) {
       	    $url2 = $route["path"];
       	    $comp = $this->_compareURLs($url,$url2);
       	    $comp2 = $this->_comparePluginURLs($url,$url2);
  	        if ($comp==0 || $comp2==0) {
  	            $type = $route["type"];
  	            $type = ucfirst($type);
       	        $method = "_process{$type}";
       	        $retval =  $this->$method($route,$url);
       	        return $retval;
  	        }
       	}
       	return false;
    }



    /**
     * Gets an URL by passing all the parameters
     *
     * @param $module string The name of the module
     * @param $controller string The name of the controller
     * @param $action string The name of the action
     * @param $params array An array of parameters
     * @return mixed
     */
    public function toURL($module,$controller,$action,$params=array()) {
        $url = "";
        $config = Config::getInstance();
        $baseURL = $config->getbaseurl();
        $url .= $baseURL;
        $url = rtrim($baseURL,"/");
        if (count($this->_loadedRoutes)>0) {
            foreach ($this->_loadedRoutes as $route) {
                if ( isset($route["options"]) ) {
                    $options = $route["options"];
                    if ( ($options["module"]==$module) && ($options["controller"]==$controller) && ($options["action"]==$action) ) {
                        $url .= $route["path"];
                        return $url;
                    }
                }
            }
        }
        return false;
    }




    /**
     * Gets the result of the enrouting process
     *
     * @return bool
     */
    public function getResult() {
        return $this->_enrouteResult;
    }

    /**
     * Gets the parameters resulting of the routing process
     *
     * @return array
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * Gets the type of the request resulting of the routing process
     * @return string
     */
    public function getType() {
        return $this->_type;
    }


    /**
     * Gets the file of the request resulting of the routing process
     * (only if has been routed an static route)
     *
     * @return string
     */
    public function getFile() {
        return $this->_file;
    }
};
?>
