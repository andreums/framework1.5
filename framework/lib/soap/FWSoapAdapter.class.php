<?php
/**
 * Adapter for SOAP webservices
 * PHP Version 5.2
 *
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * A class to adapt a controller action
 * to a SOAP webservice
 *
 * @author andreu
 * @package SOAP
 *
 */
class FWSoapAdapter {

    /**
     * The dispatch map of the adapter
     *
     * @var array
     */
    var $__dispatch_map = array();


	/**
	 * The type map of the adapter
	 *
	 * @var array
	 */
	var $__typedef = array();


	/**
	 * The object that is adapted
	 *
	 * @var object
	 */
	private $_object = null;


	/**
	 * The methods of the adapted object
	 * to be exposed to SOAP
	 *
	 * @var array
	 */
	private $_methods = array();


	/**
	 * The constructor of FWSoapAdapter
	 *
	 * @param $object object The object to be adapted
	 * @param $methods array The methods to be exposed to SOAP
	 * @return void
	 */
	public function __construct($object=null,$methods=array()) {
	    $this->_setObject($object);
	    foreach ($methods as $method) {
	        $this->_addMethod($method);
	    }
	    $this->_generateDispatchMap();
	}

	/**
	 * Call to a method of the adapted object
	 *
	 * @param $method string The method of the adapted object
	 * @param $arguments array An array of arguments
	 * @return mixed
	 */
	public function __call($method,$arguments) {
	    return call_user_func_array(array(&$this->_object, $method), $arguments);
	}

	/**
	 * Sets the adapted object
	 *
	 * @param $object object The instance of the object
	 * to be adapted
	 *
	 * @return bool
	 */
	public function _setObject($object) {
	    try {
	        $this->_object = $object;
	        return true;
	    }
	    catch (Exception $ex) {
	        trigger_error("SOAP | Can't adapt the object",E_USER_ERROR);
	    }
	}

	/**
	 * Exposes a method to SOAP
	 *
	 * @param $methodName string The name of the method to be exposed
	 * @return bool
	 */
	public function _addMethod($methodName) {
	    try {
	        if ( !(isset($this->_object) ) ) {
	            trigger_error("SOAP | The adapted object is not set",E_USER_ERROR);
	            return false;
	        }
	        if ( !(method_exists($this->_object,$methodName)) ) {
	            trigger_error("SOAP | Method {$methodName} doesn't exists in the adapted object",E_USER_ERROR);
	            return false;
	        }
	        $this->_methods[] = $methodName;
	    }
	    catch (Exception $ex) {
	        trigger_error("SOAP | Exception {$ex->getMessage()} ",E_USER_ERROR);
	    }
	}

	/**
	 * Generates the dispatch map of an adapted object
	 *
	 * @return bool
	 */
	public function _generateDispatchMap() {

	    if ( (!isset($this->_object) ) || (count($this->_methods)==0) ) {
	        return false;
	    }

	    $reflect = new ReflectionClass($this->_object);
	    $reflectMethods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
	    foreach ($reflectMethods as $reflectMethod) {
	        $name = $reflectMethod->name;
	        if ( in_array($name,$this->_methods) ) {
                $rmethod = $reflect->getMethod($name);

                // Input parameters
                $inParams = array();
                // Output parameters
                $outParams = array();

                // Get the docstring of the method
                $docString = $rmethod->getDocComment();

                // Parse the docstring of the method
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

     	        $this->__dispatch_map[$name] = array (
     	        	"in"  => $in,
        	         "out" => $out
            	);
        	}
	    }
	    return true;
	}

	/**
	 * Parses a tag line and extracts the tagname and values
	 *
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
	 * Dispatches a SOAP call
	 *
	 * @param $methodname The method to call
	 * @return mixed
	 */
	public function __dispatch($methodname)  {
	    if (isset($this->__dispatch_map[$methodname])) {
	        return $this->__dispatch_map[$methodname];
        }
        return null;
    }


};
?>