<?php
/**
 *
 * This file includes all the necessary to load all the widgets of the framework
 * @author Andrés Ignacio Martínez Soto <andresmartinezsoto@gmail.com>
 * @version 1.5
 * @package Widgets
 *
 */

/**
 * Loads the widgets
 *
 * @return void
 */
function load_widgets() {
    $config = Config::getInstance();
    $path = $config->getParam("widgetsPath");
    $files = @scandir($path,1);
    if (count($files)>2) {
        foreach ($files as $file) {
            if ( ($file!=".") || ($file!="..") || ($file!="widgets.php") ) {
                $pathInfo = pathinfo("{$path}".DS."{$file}");
                if ($pathInfo["extension"]=="php") {
                    require_once "{$path}".DS."{$file}";
                }
            }
        }
    }
}


?>