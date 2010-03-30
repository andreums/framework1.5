<?php
/**
 * Adapter for REST webservices
 * PHP Version 5.2
 *
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * A class to adapt a controller action
 * to a REST service
 *
 * @author andreu
 * @package REST
 *
 */
class FWRestAdapter {

    /**
 	* The API of the adapted object
 	*
 	* @var array
 	*/
    private $_objectApi = array();


	/**
	 * The adapted object
	 *
	 * @var object
	 */
	private $_object = null;

	/**
	 * The HTTP method for this REST Service
	 *
	 * @var string
	 */
	private $_method = "";


	/**
	 * The name of this REST Service
	 *
	 * @var string
	 */
	private $_serviceName = "";

	/**
	 * The description of this REST service
	 *
	 * @var string
	 */
	private $_description = "";


	/**
	 * An array of actions of the adapted object
	 * to be served with this REST service
	 *
	 * @var array
	 */
	private $_actions = array();

	/**
	 * An array with the roles of authentication to use this REST service
	 *
	 * @var array
	 */
	private $_authentication = false;


	/**
	 * The constructor of FWRestAdapter
	 *
	 * @param $object string The object to be adapted
	 * @param $actions array An array of actions to be adapted
	 * @param $method string The HTTP method for this REST service
	 * @param $serviceName string The name of this REST service
	 * @param $description string The description of this REST service
	 * @param $authentication array An array with the roles of authentication to use this REST service
	 * @return void
	 */
	public function __construct($object=null,$actions=array(),$method="",$serviceName="",$description="",$authentication=null) {

	    if ($object!=null) {
	        if(!$this->_setObject($object)) {
	            return;
	        }
	        if (!empty($actions)) {
	            foreach ($actions as $action) {
	                $this->_addAction($action);
	            }
	        }

	        if (empty($method)) {
	            trigger_error("REST | Error, method is not defined while creating a FWRestAdapter. Using GET as default");
	            $this->_method = "GET";
	        }
	        else {
	            $this->_method = $method;
	        }

	        if (empty($serviceName)) {
	            trigger_error("REST | Error, serviceName is not defined while creating a FWRestAdapter. Cannot create this REST Service without a name to call it");
	            return;
	        }
	        else {
	            $this->_serviceName = $serviceName;
	        }
	        if (!empty($description)) {
	            $this->_description = $description;
	        }

	        if ($authentication!=null) {
	            $this->_authentication = $authentication;
	        }

	        $this->_generateObjectApi();
	    }
	}

	/**
	 * Calls a method on the adapted object
	 *
	 * @param $method string The name of the method
	 * @param $arguments array The arguments to call the method
	 * @return mixed
	 */
	public function __call($method,$arguments) {
	    $arguments = array_shift($arguments);
	    return call_user_func_array(array($this->_object, $method),$arguments);
	}

	/**
	 * Tries to set an object creating an instance of
	 * the object to be set
	 * @access private
	 * @param $object string The name of the object to be set
	 * @return bool
	 */
	private function _setObject($object) {
	    try {
	        includeController($object);
            $controllerName = "{$object}Controller";
            $controllerObj = new $controllerName();
	        $this->_object = $controllerObj;
	        return true;
	    }
	    catch (Exception $ex) {
	        trigger_error("REST | Error while setting the object: {$ex->getMessage()} ",E_USER_ERROR);
	        return false;
	    }
	}

	/**
	 * Adds an action to the RestAdapter
	 *
	 * @access private
	 * @param $actionName string The name of the action
	 * @return bool
	 */
	private function _addAction($actionName) {
	    try {
	        if ( ! (isset($this->_object) ) ) {
	            trigger_error("REST | The object to be adapted is not set",E_USER_NOTICE);
	            return false;
	        }
	        if ( !(method_exists($this->_object,$actionName)) ) {
	            trigger_error("REST | The method {$actionName} of the object to be adapted doesn't exists/is not accesible",E_USER_NOTICE);
	            return false;
	        }
	        else {
	            $this->_actions[] = $actionName;
	        }
	    }
	    catch (Exception $ex) {
	        trigger_error("REST | REST Exception {$ex->getMessage()}",E_USER_NOTICE);
	        return false;
	    }
	}

	/**
	 * Generates the API of the Adapted object
	 *
	 * @access private
	 * @return bool
	 */
	private function _generateObjectApi() {

	    if ( (!isset($this->_object) ) || (count($this->_actions)==0) ) {
	        return false;
	    }

	    $validMethods = array("GET","POST","PUT","DELETE");
	    if (! in_array($this->_method,$validMethods)) {
	        trigger_error("Error: Method {$this->_method} is not a valid REST method",E_USER_WARNING);
	        return false;
        }

	    $reflect = new ReflectionClass($this->_object);
	    $reflectMethods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
	    foreach ($reflectMethods as $reflectAction) {

	        $actionName = $reflectAction->name;
	        if ( in_array($actionName,$this->_actions) ) {

                // Input parameters
                $inParams = array();
                // Output parameters
                $outParams = array();

                // Method
                $method = "";

                // Get the docstring of the Action
                $docString = $reflectAction->getDocComment();

                // Parse the docstring of the Action
                $docLines = explode("\n",$docString);
                foreach ($docLines as $line) {
                    $info = $this->_parseTagLine($line);
                    if ($info!=null) {

                        if ($info["tag"]=="@param") {
                            $type = $info["type"];
                            $value = $info["value"];
                            $value = substr($value,1);
            	            $inParams[] = array($value=>$type);
            	        }

       	                if ($info["tag"]=="@return") {
       	                    $type = $info["type"];
                            $value = $info["value"];
                            $value = substr($value,1);
            	            $outParams[] = array($value=>$type);
            	        }
        	       }
                }

                $in = array();
            	foreach ($inParams as $inParam) {
            	    $in = array_merge($in,$inParam);
            	}
                $out = array();
            	foreach ($outParams as $outParam) {
            	    $out = array_merge($out,$outParam);
            	}

     	        $this->_objectApi[] = array (
     	            "serviceName"        => $this->_serviceName,
     	            "description" => $this->_description,
     	            "method"      => $this->_method,
     	            "auth" 	      => $this->_authentication,
     	            "action"      => $actionName,
     	        	"in"          => $in,
        	        "out"         => $out
            	);
        	}
	    }
	    return true;
	}

	/**
	 * Parses a tag line and extracts the tagname and values
	 *
	 * @access private
	 * @param string The tagline
	 * @return void
	 */
	private function _parseTagLine($line) {

	    if (empty($line)) {
	        return null;
	    }

		$lineExplode = explode("*", $line);
		$tagArr = explode(" ",$lineExplode[1]);

		if (count($tagArr)>2) {
		    if ($tagArr[0]=="") {
		        $tag = $tagArr[1];
		        $info = array_slice($tagArr,2);
		        if (count($info)>1) {
		            $value = $info[0];
		            $type = $info[1];
		        }
		        else {
		            return null;
		        }
		    }
		}
		else {
		    return null;
		}

		if ( !isset($tag) ) {
		    return;
		}

		switch(strtolower($tag)){

		    case "@param":
		        return array (
		            "tag"=>$tag,
		            "value"=>$value,
		            "type"=>$type
		        );
		    break;

		    case "@return":
		        return array (
		            "tag"=>$tag,
		            "value"=>$value,
		        	"type"=>$type
		        );
		    break;
		}
		unset($tag);
	}

	/**
	 * Gets the objectApi of this adapter
	 *
	 * @return array
	 */
	public function getObjectApi() {
	    return $this->_objectApi;
	}

};
?>