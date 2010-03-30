<?php
/**
 * The Server for REST webservices
 * PHP Version 5.2
 *
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * A REST webservices server implemented
 * in PHP
 *
 * @author andreu
 * @package REST
 *
 */
class FWRestServer extends Singleton {

    /**
     * The method of the HTTP request
     *
     * @var string
     */
    private $_method;


    /**
     * The data of the HTTP request for
     * a REST service
     *
     * @var mixed
     */
    private $_requestData = null;


    /**
     * A property to access to the
     * configuration of the framework
     *
     * @var Config
     */
    private $_config;


    /**
     * The base URL of the framework
     *
     * @var string
     */
    private $_baseURL;

    /**
     * The URL of the REST service
     *
     * @var string
     */
    private $_url;


    /**
     * The parts of the REST service URL
     * @var array
     */
    private $_urlParts;


    /**
     * The dispatch map of the REST server
     *
     * @var array
     */
    private $_dispatchMap = array();

    /**
     * The constructor of FWRestServer
     *
     * @return void
     */
    public function  __construct() {

        $this->_config = Config::getInstance();
        $this->_baseURL = $this->_config->getParam("baseurl");
        $router = Router::getInstance();
        $routes = $router->getRestRoutes();
        if (count($routes)>0) {
            foreach ($routes as $route) {
                $this->_addService($route);
            }
        }
        $this->serve();
        $this->_process();
    }

    /**
     * Starts the REST webservices server
     *
     * @return void
     */
    public function serve() {
        if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD'])) {
            $this->_url = $_SERVER['REQUEST_URI'];

            preg_match_all('/(([^\/]){1}(\/\/)?){1,}/',$this->_url,$regexUrlParts);

            $i=0;
            $found = false;
            for($i=0;$i<count($regexUrlParts[0]);$i++) {
                if ($regexUrlParts[0][$i]=="rest.php") {
                    $found=true;
                    break;
                }
            }
            if ($found===true) {
                $this->_urlParts = array_splice($regexUrlParts[0],$i+1);
            }
            $this->_method = $_SERVER['REQUEST_METHOD'];
       }
  }

  /**
   * Checks the parameter to be of a type
   *
   * @param $type string The type of the parameter
   * @param $value mixed The value of the parameter
   * @return bool
   */
  private function _checkParam($type,$value) {

      $filter = Filter::getInstance();
      if (!empty($value)) {
          try {
              $value = trim($value);

              if ($type=="string") {
                  $res = $filter->isString($value);
                  if (!$res) {
                      return false;
                  }
                  else {
                      return true;
                  }
              }

              if ($type=="integer") {
                  $res = $filter->isInteger($value);
                  if($res) {
                      return true;
                  }
                  else {
                      return false;
                  }
              }

              if ($type=="float") {
                  $res = $filter->isFloat($value);
                  if ($res) {
                      return true;
                  }
                  else {
                      return false;
                  }
              }

              if ($type=="boolean") {
                  $res = $filter->isBoolean($value);
                  if ($res) {
                      return true;
                  }
                  else {
                      return false;
                  }
              }
          }
          catch (Exception $ex) {
              trigger_error("REST | Error while checking some parameter value of type={$type} and value={$value}: {$ex->getMessage()} ",E_USER_ERROR);
          }
      }
  }

  /**
   * Adds a REST service to this server
   *
   * @access private
   * @param $restAdapter FWRestAdapter
   * @return void
   */
  private function _addService($restAdapter) {
      $this->_dispatchMap[] = $restAdapter;
  }

  /**
   * Processes the request of this REST server
   *
   * @access private
   * @return void
   */
  private function _process() {

      $action = "";
      $method = "";
      $object = null;
      $objectMap = null;
      $callParams = array();

      $serviceName = $this->_urlParts[0];
      if (empty($serviceName)) {
          $this->serviceNameEmpty();
          return;
      }
      $parameters = array();
      $params = array_splice($this->_urlParts,1);
      if ( (count($params)%2)!=0 ) {
          $params[] = "null";
      }
      for ($i=0;$i<count($params);$i+=2) {
          $parameters[$params[$i]] = array(
              "value" =>$params[$i+1]
          );
      }
      if ( count($this->_dispatchMap)>0 ) {

          if ($serviceName=="system") {
              $this->system($params);
              return true;
          }

          if ($serviceName=="login") {
              if ( (count($params)%4)!=0) {
                  return $this->unauthorized();
              }
              return $this->_restLogin($params);
          }

          if ($serviceName=="logout") {
              return $this->_restLogout();
          }

          foreach ($this->_dispatchMap as $dispatchObject) {
              foreach ($dispatchObject->getObjectApi() as $map) {
                  if ( ($map["serviceName"]==$serviceName) && ($map["method"]==$this->_method) ) {
                      $object = $dispatchObject;
                      $objectMap = $map;
                      break;
                  }
              }
          }
          if ($object!=null) {
              // Process the FWRestAdapter
              if ($objectMap!=null) {
                  if (isset($objectMap["in"])) {
                      $pars = array_keys($objectMap["in"]);
                      foreach ($pars as $par) {
                          if ( isset($parameters[$par])) {
                              $value = $parameters[$par]["value"];
                              $type = $objectMap["in"][$par];
                              if ( $this->_checkParam($type,$value) ) {
                                  $callParams[$par] = $value;
                              }
                          }
                      }
                  }
                  $method = $objectMap["action"];
                  $auth = $objectMap["auth"];

                  if ($auth!=false) {
                      $authenticationMethod = (string) $this->_config->getParam("restAuthMethod");
                      $isAuthenticated = Session::get("authentication","REST");
                      if ($isAuthenticated) {
                          if ($authenticationMethod=="login") {
                              $role = false;
                              $role = Session::get("role","REST");
                              if ($role!=$auth) {
                                  trigger_error("Error: Acceding without a valid role to the ... ",E_USER_ERROR);
                                  return $this->unauthorized(0);
                              }
                          }
                          else {
                              $key = Session::get("key","REST");
                          }
                      }
                      else {
                          return $this->unauthorized();
                      }
                  }
              }

              if ( ( ($_SERVER["REQUEST_METHOD"]=="POST") || ($_SERVER["REQUEST_METHOD"]=="PUT") ) && (isset($_SERVER['CONTENT_LENGTH'])) && ($_SERVER['CONTENT_LENGTH']>0) ) {
                  $this->_requestData = '';
                  $httpContent = fopen('php://input', 'r');
                  while ($data = fread($httpContent, 1024)) {
                      $this->_requestData .= $data;
                  }
                  fclose($httpContent);
                  Session::set("data",$this->_requestData,"REST");
                  $result = $object->$method($callParams,$this->_requestData);
                  header('Content-type: text/xml');
                  print $result;
                  exit(0);
              }

              $result = $object->$method($callParams);
              header('Content-type: text/xml');
              print $result;
              exit(0);
          }
          else {
              return $this->notFound();
          }
      }
      else {
          return $this->notFound();
      }
  }


  /**
   * System REST service
   *
   * @param $parameters array An array of parameters to this service
   * @return bool
   */
  public function system($parameters) {

      if (count($parameters)==0) {
          $parameters = array("listServices");
      }
      if ($parameters[0]=="info") {
          if ($parameters[1]=="serviceName") {
              $serviceName = $parameters[2];
              $this->infoService($serviceName);
              return true;
          }
          else {
              $this->notFound();
              return false;
          }
      }
      if ($parameters[0]=="listServices") {
          $this->listServices();
          return true;
      }

      else {
          $this->notFound();
          return false;
      }

  }

  /**
   * Lists the REST services that can be served within this server
   *
   * @return void
   */
  public function listServices() {
      header("Content-type: text/xml");
      print '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
      print "<services>";
      if (count($this->_dispatchMap)>0) {
          foreach ($this->_dispatchMap as $map) {
                  $service = $map->getObjectApi();
                  $service = array_shift($service);
                  $sName = $service["serviceName"];
                  $sDescription = $service["description"];
                  print "\n\t<service>";
                  print "\t\t<name>{$sName}</name>\n";
                  print "\t\t<description>{$sDescription}</description>\n";
                  print "\t</service>\n\n";
          }
      }
      print "</services>";
  }

  /**
   * Gets info about a REST service
   *
   * @param $serviceName The name of the REST service
   * @return void
   */
  public function infoService($serviceName) {
      $sservice = null;
      header("Content-type: text/xml");
      print '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
      if (count($this->_dispatchMap)>0) {
          foreach ($this->_dispatchMap as $map) {
              $service = $map->_objectApi;
              $service = array_shift($service);
              if ($service["serviceName"]==$serviceName) {
                  $sservice = $service;
                  break;
              }
          }
      }
      if ($sservice!=null) {
          $sname = $sservice["name"];
          print "\n\t<service>\n";
          print "\t\t<name>{$sservice["serviceName"]}</name>\n";
          print "\t\t<method>{$sservice["method"]}</method>\n";
              print "\t\t<description>{$sservice["description"]}</description>\n";
              print "\t\t<parameters>\n";
                  print "\t\t\t<in>\n";
                  if (count($sservice["in"])>0) {
                      foreach ($sservice["in"] as $param) {
                          $key = key($service["in"]);
                          print "\t\t\t\t<parameter name=\"{$key}\">\n";
                          print "\t\t\t\t\t<type>{$param}</type>\n";
                          print "\t\t\t\t</parameter>\n";
                      }
                  }
                  print "\t\t\t</in>\n";

                  print "\t\t\t<out>\n";
                  if (count($sservice["out"])>0) {
                      foreach ($sservice["out"] as $param) {
                          $key = key($sservice["out"]);
                          print "\t\t\t\t<parameter name=\"{$key}\">\n";
                          print "\t\t\t\t\t<type>{$param}</type>\n";
                          print "\t\t\t\t</parameter>\n";
                      }
                  }
                  print "\t\t\t</out>\n";
             print "\t\t</parameters>\n";
             print "\n\t</service>\n";
          }
          else {
              print "<error>The selected service {$serviceName} either is not enabled is not implemented</error>";
          }
  }

  /**
   * REST service to login into the system
   * to use a REST service that needs authentication
   *
   * @param $params array Array of params
   * @return void
   */
  private function _restLogin($params) {

      if (count($params)==4) {
          $username = $params[1];
          $password = $params[3];
      }
      else {
          $this->unauthorized();
      }

      $password = md5($password);
      $pru = PluginRegistry::getInstance();
      $login = $pru->getPlugin("Login Plugin");
      $result = $login->restLogin($username,$password);
      header("Content-type: text/xml");
      print '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
      if ($result==200) {
          print "<result>Logged in succesfully</result>";
      }
      else {
          print "<error>Incorrect user or password combination</error>";
      }
  }

  /**
   * REST service to close the session on the system
   *
   * @return void
   */
  private function _restLogout() {
      header("Content-type: text/xml");
      print '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
      try {
          @session_destroy();
          @session_start();
          print "<result>Session closed succesfully</result>";
      }
      catch (Exception $ex) {
          print "<error>There was an error closing the session</error>";
      }
  }


    /**
     * Send a HTTP 404 response header.
     */
    public function notFound() {
        header('HTTP/1.0 404 Not Found');
        header("Content-type: text/xml");
        print '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        print "<error>The selected service either is not enabled is not implemented</error>";
        exit(0);
    }

	/**
     * Send a HTTP 404 response header.
     */
    public function serviceNameEmpty() {
        header('HTTP/1.0 404 Not Found');
        header("Content-type: text/xml");
        print '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        print "<error>You haven't specified a service</error>";
        exit(0);
    }

    /**
     * Send a HTTP 500 response header.
     */
    public function internalServerError() {
        header('HTTP/1.0 500 Internal Server Error');
    }

}
?>