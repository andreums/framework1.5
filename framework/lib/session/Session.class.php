<?php
/**
 * Session handler
 * PHP Version 5.2
 *
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Session
 *
 * A basic session handler
 *
 * @category Framework
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

class Session extends Singleton {
	/**
	 * Sets or specifies the value for an index of the session
	 *
	 * @param string $index The index to set
	 * @param mixed  $value The value for the index
	 * @param string $namespace The namespace of the index
	 */
	public static function set($index, $value, $namespace="default")	{
	  	$_SESSION["FRAMEWORK_SESSION"][$namespace][$index] = $value;
	}
	/**
	 * Gets the value for an index of the session
	 *
	 * @param string $index The index to get
	 * @param string $namespace The namespace of the index
	 * @return mixed
	 */
	public static function get($index, $namespace="default"){
		if (isset($_SESSION["FRAMEWORK_SESSION"][$namespace][$index])) {
		    return $_SESSION["FRAMEWORK_SESSION"][$namespace][$index];
		}
		else {
			return null;
		}
	}
	/**
	 * Unsets an index on the session
	 *
	 * @param string $index The index to unset
	 * @param string $namespace The namespace of the index
	 */
	public static function unsetData($index, $namespace="default") {
	  	unset($_SESSION["FRAMEWORK_SESSION"][$namespace][$index]);
	}
	/**
	 * Check if an index is set in the session
	 *
	 * @param string $index The index to get
	 * @param string $namespace The namespace of the index
	 * @return bool
	 */
	public static function issetData($index, $namespace="default") {
	    return (isset($_SESSION["FRAMEWORK_SESSION"][$namespace][$index]));
	}


	/**
	 * Gets the id of the session
	 *
	 * @return mixed
	 */
	public static function getId() {
	    return (session_id());
	}
}
?>