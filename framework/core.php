<?php
/**
 * Core class of the framework
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
 * Core class of the framework
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

class Core
{
    private static $_instance = null;

    public function __construct()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = $this;
        }
        self::init();
        return self::$_instance;
    }

    public static function &getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Core();
            return self::$_instance;
        } else {
            return self::$_instance;
        }

    }

    public static function init($debug=false)
    {
        define('FATAL', E_USER_ERROR);
        define('ERROR', E_USER_WARNING);
        define('WARNING', E_USER_NOTICE);

        try {
            include "framework".DS."bootstrap.php";
            $error = new ErrorHandler();
            $config = Config::getInstance();
            $baseURL = $config->getbaseurl();
            if (!defined("BASE_URL")) {
                define("BASE_URL",$baseURL);
            }
            $filter = BaseFilter::getInstance();
            new Request();
            $i18n = i18n::init();
            Router::getInstance();
            new Dispatcher();
        }
        catch (Exception $ex) {
            trigger_error("CORE | There has been an exception at the core of the Framework {$ex->getMessage()} ",E_USER_ERROR);
        }
    }

    public static function initSOAP($debug=false)
    {
        try {
            include "framework".DS."bootstrap.php";

            $config = Config::getInstance();
            $baseURL = $config->getbaseurl();
            if (!defined("BASE_URL")) {
                define("BASE_URL",$baseURL);
            }
            $filter = BaseFilter::getInstance();
            $router = Router::getInstance();
            $router->_loadRoutes();
        }
        catch (Exception $ex) {
            trigger_error("CORE | There has been an exception at the core of the Framework {$ex->getMessage()} ",E_USER_ERROR);
        }
    }

    public static function initREST($debug=false)
    {
        try {
            include "framework".DS."bootstrap.php";

            $config = Config::getInstance();
            $baseURL = $config->getbaseurl();
            if (!defined("BASE_URL")) {
                define("BASE_URL",$baseURL);
            }
            $filter = BaseFilter::getInstance();
            $router = Router::getInstance();
            $router->_loadRoutes();
        }
        catch (Exception $ex) {
            trigger_error("CORE | There has been an exception at the core of the Framework {$ex->getMessage()} ",E_USER_ERROR);
        }
    }
}

if (!defined("DS")) {
    define("DS",DIRECTORY_SEPARATOR);
}
?>