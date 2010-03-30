<?php
/**
 * This file includes all the necessery code to load
 * all the objects needed by the framework
 *
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */


if (!defined("DS")) {
    define("DS",DIRECTORY_SEPARATOR);
}

if (!isset($dir_files)) {
    $dir_files = array(".","..");
}

try {

    include "framework".DS."core".DS."singleton.php";
    include "framework".DS."config".DS."Config.class.php";
    include "framework".DS."functions".DS."workarrounds.php";
    include "framework".DS."lib".DS."external".DS."external.inc";
    include "framework".DS."models".DS."models.php";
    include "framework".DS."helpers".DS."helpers.php";
    include "framework".DS."widgets".DS."widgets.php";


    function __autoload($class) {

        if (file_exists( "framework".DS."plugins".DS."{$class}.class.php")) {
            require_once  "framework".DS."plugins".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."config".DS."{$class}.class.php")) {
            require_once  "framework".DS."config".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."session".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."session".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."router".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."router".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."database".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."database".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."database".DS."drivers".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."database".DS."drivers".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."dispatcher".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."dispatcher".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."dispatcher".DS."engines".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."dispatcher".DS."engines".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."dataLayer".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."dataLayer".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."request".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."request".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."controller".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."controller".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."widgets".DS."{$class}.class.php")) {
            require_once  "framework".DS."widgets".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."plugins".DS."{$class}.class.php")) {
            require_once  "framework".DS."plugins".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."filter".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."filter".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."locale".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."locale".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."soap".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."soap".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."error".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."error".DS."{$class}.class.php";
        }

        if (file_exists( "framework".DS."lib".DS."rest".DS."{$class}.class.php")) {
            require_once  "framework".DS."lib".DS."rest".DS."{$class}.class.php";
        }
    }
    load_models();
    load_helpers();
    load_widgets();

}

catch (Exception $ex) {
    print $ex->getMessage();
}

?>