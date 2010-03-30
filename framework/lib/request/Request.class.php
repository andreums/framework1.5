<?php
/**
 * Basic Request Object
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  Request
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Request
 *
 * @category Framework
 * @package  Request
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class Request extends Singleton {


    /**
     * Array of GET parameters
     *
     * @var array
     */
    private static $getParams;


    /**
     * Array of POST parameters
     *
     * @var array
     */
    private static $postParams;


    /**
     * Array of FILE parameters
     *
     * @var array
     */
    private static $filesParams;


    /**
     * The url of the REQUEST
     *
     * @var string
     */
    private static $requestURL;


    /**
     * Array of parameters
     *
     * @var array
     */
    private static $parameters;

    /**
     * The constructor of Request
     *
     * @return void
     */
    public function __construct() {

        self::$getParams = $_GET;
        self::$postParams = $_POST;
        self::$filesParams = $_FILES;
        self::$requestURL = $_SERVER["REQUEST_URI"];
        self::$parameters = array();

    }

    /**
     * Gets the REQUEST URL
     *
     * @return string
     */
    public static function getServerURL() {
        return self::$requestURL;
    }

    /**
     * Get the "clean" server URL
     *
     * @return string
     */
    public static function getServerURLClean() {

    	$url = self::getServerURL();

    	$pos = strpos($url,"index.php");
    	if ($pos!==false) {
    	    $cleanURL = substr($url,$pos);
    	    return $cleanURL;
    	}

        return self::$requestURL;
    }


    /**
     * Gets all the GET parameters
     *
     * @return array
     */
    public static function getGetParams() {
        return self::$getParams;
    }

    /**
     * Gets all POST parameters
     * @return array
     */
    public static function getPostParams() {
        return self::$postParams;
    }


    /**
     * Gets all FILE parameters
     * @return array
     */
    public static function getFilesParams() {

        return self::$filesParams;
    }

    /**
     * Gets a GET parameter
     *
     * @param $name strint The name of the parameter
     * @return mixed
     */
    public static function getGetParam($name) {

        if ( in_array($name,array_keys(self::$getParams)) ) {
             return self::$getParams[$name];
        }
        else {
              return null;
        }

    }

    /**
     * Gets a POST parameter
     * @param $name string The name of the parameter
     * @return mixed
     */
    public static function getPostParam($name) {

        if ( in_array($name,array_keys(self::$postParams)) ) {
             return self::$postParams[$name];
        }
        else {
        	  throw new Exception("Error: Error getting post param. The post param $name is not set");
              return null;
        }

    }

    /**
     * Gets a file parameter
     *
     * @param $name string The name of the parameter
     * @return mixed
     */
    public static function getFilesParam($name) {

        if ( in_array($name,array_keys(self::$filesParams)) ) {
             return self::$filesParams[$name];
        }
        else {
              return null;
        }


    }

    /**
     * Gets the value of a parameter
     *
     * @param $name string The name of the parameter
     * @param $type string The method of the parameter
     * @return mixed
     */
    public static function getValue($name,$type="") {

        switch ($type) {

            case "GET":
                if ( in_array($name,array_keys(self::$getParams)) ) {
                    return self::$getParams[$name];
                }
                else {
                    return null;
                }
            break;

            case "POST":
                if ( in_array($name,array_keys(self::$postParams) ) ) {
                    return self::$postParams[$name];
                }
                else {
                    return null;
                }
            break;


            case "FILE":
                if ( in_array($name,array_keys(self::$filesParams) ) ) {
                    return self::$fileParams[$name];
                }
                else {
                    return null;
                }
            break;

        };
    }

    /**
     * Registers a parameter on the request object
     *
     * @param $name string The name of the parameter
     * @param $value mixed The value of the parameter
     * @return void
     */
    public static function registerParameter($name,$value) {
    	self::$parameters[$name] = array("name"=>$name,"value"=>$value);
    }

    /**
     * Gets all the parameters
     *
     * @return array
     */
    public static function getParameters() {
    	return self::$parameters;
    }

	/**
     * Gets all the parameters
     *
     * @return array
     */
    public static function getParams() {
    	return self::getParameters();
    }

    /**
     * Gets a parameter
     *
     * @param $name string The name of the parameter
     * @return mixed
     */
    public static function getParam($name) {
    	foreach (self::$parameters as $param) {
    		if ($param["name"]==$name) {
    			$param["value"] = urldecode($param["value"]);
    			return $param["value"];
    		}
    	}
    	return null;
    }



}
?>
