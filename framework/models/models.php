<?php

/**
 * Loads the user models
 *
 * @return void
 */
function load_models() {
    $config = Config::getInstance();
    $path = $config->getParam("modelsPath");
    $files = @scandir($path,1);
    if (count($files)>2) {
        foreach ($files as $file) {
            if ( ($file!=".") || ($file!="..") || ($file!="models.php") ) {
                $pathInfo = pathinfo("{$path}".DS."{$file}");
                if ($pathInfo["extension"]=="php") {
                    require_once "{$path}".DS."{$file}";
                }
            }
        }
    }
}

?>