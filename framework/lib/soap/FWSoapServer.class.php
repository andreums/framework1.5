<?php
/**
 * Basic server for the SOAP protocol
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  SOAP
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

class FWSoapServer extends Singleton {

    private $_server;
    private $_disco;
    private $_namespace = "http://schemas.xmlsoap.org/soap/envelope/";
    private $_soapRoutes = array();

    public function __construct() {
        $this->_server = new SOAP_Server();
        $this->_server->setDefaultNamespace($this->_namespace);
        $router = Router::getInstance();
        $this->_soapRoutes = $router->getSoapRoutes();

        if (count($this->_soapRoutes)>0) {
            foreach ($this->_soapRoutes as $route) {
                $this->_addService($route[0],$route[1],$route[2],$route[3]);
            }
        }

        $soapLogin = new FWSoapLogin();
        $loginAdapter = new FWSoapAdapter($soapLogin,array("login"));
        $logoutAdapter = new FWSoapAdapter($soapLogin,array("logout"));
        $this->_addService($loginAdapter,"http://schemas.xmlsoap.org/soap/envelope/","login","Login functionalities");
        $this->_addService($logoutAdapter,"http://schemas.xmlsoap.org/soap/envelope/","logout","Logout functionalities");


    }

    private function _addService($soapAdapter,$namespace,$serviceName=null,$description=null) {
        $this->_server->addObjectMap($soapAdapter,$namespace,$serviceName,$description);
    }

    public function serve() {

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
            if (!isset($HTTP_RAW_POST_DATA)){
                $HTTP_RAW_POST_DATA = file_get_contents('php://input');
            }
            $this->_server->service($HTTP_RAW_POST_DATA);
        }
        else {
            $disco = new SOAP_DISCO_Server($this->_server,'Servidor SOAP de Framework',"FWSoapService");
            header("Content-type: text/xml");
            //var_dump($this->_server);
            if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
                // show only the WSDL/XML output if ?wsdl is set in the address bar
                echo $disco->getWSDL();
            }
            else {
                echo $disco->getDISCO();
            }
       }
       exit;
    }
};
?>