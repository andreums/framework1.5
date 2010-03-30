<?php
/**
 * Login functionalities for SOAP
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  SOAP
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 * @todo     Check for alternative type of SOAP authentication (no session implied)
 *
 */

/**
 * Login funcitionalities for SOAP
 *
 * @package  SOAP
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @package SOAP
 *
 */
class FWSoapLogin {



	/**
	 * Logs in to the system
	 *
	 * @param $username string The username
	 * @param $password strint The password
	 * @return string
	 */
	public function login($username,$password) {


	    @session_destroy();
	    @session_start();

	    $password = md5($password);
	    $msg = "";
	    $pru = PluginRegistry::getInstance();
	    $login = $pru->getPlugin("Login Plugin");
	    $result = $login->SOAPLogin($username,$password);
	    return session_id();

	    switch ($result) {
	        case 200:
	            $msg = "Logged in successfully";
	        break;

	        case 402:
	            $msg = "There was an error while trying to login; your username is blocked";
	        break;

	        case 403:
	            $msg = "Incorrect username/password combination";
	        break;

	        case 500:
	            $msg = "There was an error on the server, please try later";
	        break;

	        default:
	            $msg = "Incorrect username/password combination";
	        break;
	    };
	    return $msg;
	}


	/**
	 * Closes the session
	 *
	 * @return string
	 */
	public function logout() {
	    try {
	        @session_destroy();
	        @session_start();
	        return "Session closed successfully";
	    }
	    catch (Exception $ex) {
	        trigger_error("SOAP | Cannot destroy the session",E_USER_ERROR);
	        return "There has been an error while closing the session";
	    }
	}

};
?>