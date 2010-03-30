<?php
/**
 *
 * This file includes all the necessary to load all the helpers of the framework
 * @author Andrés Ignacio Martínez Soto <andresmartinezsoto@gmail.com>
 * @version 1.5
 * @package Helpers
 *
 */


/**
 * Loads the helpers
 *
 * @return void
 */
function load_helpers() {
    $config = Config::getInstance();
    $path = $config->getParam("helpersPath");
    $files = @scandir($path,1);
    if (count($files)>2) {
        foreach ($files as $file) {
            if ( ($file!=".") || ($file!="..") || ($file!="helpers.php") ) {
                $pathInfo = pathinfo("{$path}".DS."{$file}");
                if ($pathInfo["extension"]=="php") {
                    require_once "{$path}".DS."{$file}";
                }
            }
        }
    }
}

?>
