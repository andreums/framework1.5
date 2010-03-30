<?php
/**
 * Plugin Registry
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  Plugin
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class PluginRegistry
 *
 * @category Framework
 * @package  Plugin
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class PluginRegistry extends Singleton {

	/**
	 * Array of plugins
	 *
	 * @var array
	 */
	private $_plugins;

	/**
	 * The where all the plugins reside
	 *
	 * @vars string
	 */
	protected $_pluginsDir;

	private $_dir_files = array(".","..");

    /**
     * The constructor of PluginRegistry
     *
     * @return void
     */
    public function __construct() {
    	$this->_plugins = array();
    	$this->_pluginsDir = $this->_getPluginsPath();
    	$this->_registerPlugins();
    }

    /**
     * Gets the path of the plugins
     *
     * @return string
     */
    private final function _getPluginsPath() {
        $path = getcwd().DS."framework".DS."plugins";
        return $path;
    }

    /**
     * The destructor of PluginRegistry
     *
     * @return void
     */
    public function __destruct() {
        if (count($this->_plugins)>0) {
            foreach ($this->_plugins as $plugin) {
                unset($plugin);
            }
        }
    }


    /**
     * Tries to load a plugin
     *
     * @param $file string The filename of the plugin
     * @return bool
     */
    private function _loadPlugin($file) {
        try {
            if (file_exists($file) ) {
                require_once $file;
                return true;
            }
            else {
                throw new Exception("Can't load plugin {$file}");
                return false;
           }
        }
        catch (Exception $ex) {
            trigger_error("PLUGIN | PluginRegistry {$ex->getMessage()}",E_USER_ERROR);
            return false;
        }
    }

    /**
     * Register all the plugins into the pluginDir
     *
     * @return bool
     */
    private function _registerPlugins() {
        try {
            $files = scandir($this->_pluginsDir,1);
            if ( count($files)>0 ) {
                foreach ($files as $file) {
                    if ( (!in_array($file,$this->_dir_files)) && ($file[0]!='.') && (!strpos($file,".php")) ) {
                        $infoFilename = $this->_pluginsDir.DS.$file.DS."plugin.xml";
                        $plugin = new PluginInfo($infoFilename);
                        $this->_plugins[] = $plugin;
                    }
                }
                return true;
            }
        }
        catch (Exception $ex) {
            trigger_error("PLUGIN | PluginRegistry has failed to load some plugins and thrown an exception {$ex->getMessage()}",E_USER_ERROR);
            return false;
        }
    }

    /**
     * Try to unregister a plugin
     *
     * @param $pluginName string The name of the plugin to unregister
     * @return bool
     */
    public function unregisterPlugin($pluginName) {
        if (count($this->_plugins)>0) {
            foreach ($this->_plugins as $plugin) {
                if ($plugin->getName()==$pluginName) {
                    unset($plugin);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Gets all the JavaScript files associated to a plugin
     *
     * @return array
     */
    public function getPluginsJavaScript() {
        $list = array();
        foreach ($this->_plugins as $plugin) {
            $plInfo = $plugin->pluginClass;
            $jsFiles = $plugin->pluginJS;
            if (isset($jsFiles)) {
                foreach ($jsFiles as $jsFile) {
                    $list[] = "<script type=\"text/javascript\" src=\"".BASE_URL."{$jsFile}\"></script>\n";
                }
            }
        }
        return $list;
    }

    /**
     * Gets the names of the registered plugins
     *
     * @return array
     */
    public function getPluginNames() {
        $list = array();
        if (count($this->_plugins)>0) {
            foreach ($this->_plugins as $plugin) {
                $list[] = $plugin->pluginName;
            }
        }
        return $list;
    }

    /**
     * Gets a plugin
     *
     * @param $pluginName string The name of the plugin
     * @return Plugin
     */
    public function getPlugin($pluginName) {
        foreach ($this->_plugins as $plugin) {
            $name = $plugin->pluginName;
            if ($name==$pluginName) {
                if ($plugin->pluginStatus == "disabled") {
                    return false;
                }
                else {
                    $class = $plugin->pluginClass;
                    $options = $plugin->getOptions();
                    $pluginFile = $this->_pluginsDir.DS.$class.DS."plugin.php";
                    $this->_loadPlugin($pluginFile);
                    $plug = new $class($options);
                    return $plug;
                }
            }
        }
        return null;
    }
}
?>