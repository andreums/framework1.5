<?php
/**
 * This file includes all the necessery code manage
 * the configuration of the Framework
 *
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Core
 *
 * Class to hold all the configuration of the framework
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class Config extends Singleton
{
    /**
    * The filename of the XML file where the configuration resides
    *
    * @var string
    * @access private
    * @static
    */
    private static $_filename;

    /**
    * SimpleXMLElement to hold all the config
    *
    * @var SimpleXMLElement
    * @access private
    */
    private $_configData;

    /**
     * The constructor of Config
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->_readConfigFromXML();
    }

    /**
    * A method to read the configuration of the framework from an xml file.
    *
    * @access private
    * @return  void
    */
    private function _readConfigFromXML()
    {
        try {
            $configFile = "framework".DS."config".DS."config.xml";
            //chmod($configFile,0640);
            $xmlHandler = simplexml_load_file($configFile);
            //chmod($configFile,0100);
            foreach ($xmlHandler->section as $element) {
                $this->_configData[] = $element;
            }
        }
        catch (Exception $ex) {
            print $ex->getMessage();
        }
    }

    /**
    * A method to get a param of the configuration
    *
    * @param string $param The param to get
    * @access public
    * @return string
    */
    private function _getParam($param)
    {
        foreach ($this->_configData as $configElement) {
            if ( isset($configElement->$param) ) {
                $value = (string) $configElement->$param;
                return $value;
            }
        }
    }

    /**
    * Gets the database configuration for the framework
    *
    * @access public
    * @return SimpleXMLElement The database configuration section
    */
    public function getDatabaseConfig()
    {
        foreach ($this->_configData as $configElement) {
            if ($configElement["name"]=="database") {
                return $configElement;
            }
        }
    }

    /**
    * Gets the general section of the configuration
    *
    * @access public
    * @return SimpleXMLElement The database configuration section
    */
    public function getGeneralConfig()
    {
        foreach ($this->_configData as $configElement) {
            if ($configElement["name"]=="general") {
                return $configElement;
            }
        }
    }

    /**
    * Handles the calls to get and set an element of the configuration
    *
    * @param $method string Method to get or set
    * @param $args array Array of arguments
    * @access public
    * @return string The value of the config element
    */

    public function __call($method,$args)
    {
        $pos = strpos($method, "get");
        if ($pos!==false) {
            $param = substr($method, strlen("get"));
            $value = $this->_getParam($param);
            return ($value);
        }
    }

    /**
     * Gets a parameter of the configuration
     *
     * @param $paramName string The name of the parameter
     * @return mixed
     */
    public function getParam($paramName) {
        return ($this->_getParam($paramName));
    }
};
?>