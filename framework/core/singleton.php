<?php
/**
 *
 * This is an implementation of the Singleton pattern.
 * @author Andrés Ignacio Martínez Soto <andresmartinezsoto@gmail.com>
 * @version 1.5
 * @package Framework
 *
 */

/**
 *
 * Class Singleton
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 * @abstract
 *
 */
abstract class Singleton {
    /**
     * Array of cached singleton objects
     *
     * @var array
     */
    private static $instances = array();

    /**
     * Static method for instantiating a singleton object.
     *
     * @return object
     */
    final public static function getInstance() {
    	$className = get_called_class();
        if ( !isset(self::$instances[$className]) ) {
        	self::$instances[$className] = new $className;
        }
        return self::$instances[$className];
    }

    /**
     * Singleton objects should not be cloned
     *
     * @return void
     */
    final private function __clone() {  }

};
?>