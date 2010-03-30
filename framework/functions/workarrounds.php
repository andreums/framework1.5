<?php
/**
 *
 * This file includes hacks and workarrounds to provide compatibility with further versions of PHP.
 * @author Andrés Ignacio Martínez Soto <andresmartinezsoto@gmail.com>
 * @version 1.5
 * @package Framework
 *
 */

if( !function_exists('get_called_class') ) {

	/**
	 * Workarround for get_called_class that is not implemented in PHP 5.2.*
	 *
	 * @param $backtraking mixed External debug_backtrace()
	 * @param $limit int Depth of the backtrack
	 * @return string
	 */
	function get_called_class($backtraking=false,$limit=1) {
		if (!$backtraking) {
			$backtraking = debug_backtrace();
		}
        if ( !isset($backtraking[$limit]) ) {
        	throw new Exception("Cannot find called class -> stack level too deep.");
        }
        if (!isset($backtraking[$limit]['type'])) {
        throw new Exception ('type not set');
    }
    else switch ($backtraking[$limit]['type']) {
        case '::':
            $lines = file($backtraking[$limit]['file']);
            $i = 0;
            $callerLine = '';
            do {
                $i++;
                $callerLine = $lines[$backtraking[$limit]['line']-$i] . $callerLine;
            } while (stripos($callerLine,$backtraking[$limit]['function']) === false);
            preg_match('/([a-zA-Z0-9\_]+)::'.$backtraking[$limit]['function'].'/',
                        $callerLine,
                        $matches);
            if (!isset($matches[1])) {
                // must be an edge case.
                return get_class($this);
            }
            switch ($matches[1]) {
                case 'self':
                case 'parent':
                    return get_called_class($backtraking,$limit+1);
                default:
                    return $matches[1];
            }
            // won't get here.
        case '->': switch ($backtraking[$limit]['function']) {
                case '__get':
                    // edge case -> get class of calling object
                    if (!is_object($backtraking[$limit]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                    return get_class($backtraking[$limit]['object']);
                default: return $backtraking[$limit]['class'];
            }

        default: throw new Exception ("Unknown backtrace method type");
    }
  }
}

/**
 * Check if a class is a instace of a superclass
 *
 * @param $sub
 * @param $super
 * @return bool
 */
function is_instance_of($sub, $super) {
    $sub = (string)$sub;
    $super = is_object($super) ? get_class($super) : (string)$super;

    switch(true) {
        case $sub === $super; // well ... conformity
        case is_subclass_of($sub, $super):
        case in_array($super, class_implements($sub)):
            return true;
        default:
            return false;
    }
}

/**
 * Shorthand function to include a controller
 *
 * @param $controllerName string The name of the controller to be included
 * @return bool
 */
function includeController($controllerName) {
    $dir = "modules";
    $files = scandir($dir,1);
    if ( in_array($controllerName,$files) ) {
        $controllerFile = "modules".DS."{$controllerName}".DS."controller".DS."{$controllerName}Controller.php";
        require_once $controllerFile;
        return true;
    }
    return false;
}


/**
 * Function to download an URL
 *
 * @param $Url string URL to download
 * @return string The contents of the URL
 */
function DownloadUrl($Url){

    // is curl installed?
    if (!function_exists('curl_init')){
        trigger_error("DownloadURL | CURL is not installed, please install it");
        return false;
    }

    // create a new curl resource
    $ch = curl_init();

    /*
    Here you find more options for curl:
    http://www.php.net/curl_setopt
    */

    // set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);

    // set referer:
    curl_setopt($ch, CURLOPT_REFERER, "http://www.google.com/");

    // user agent:
    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

    // remove header? 0 = yes, 1 = no
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // should curl return or print the data? true = return, false = print
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // download the given URL, and return output
    $output = curl_exec($ch);

    // close the curl resource, and free system resources
    curl_close($ch);

    // print output
    return $output;
}
?>