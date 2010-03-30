<?php
/**
 * Plugin Info
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
 * Class PluginInfo
 * Gets information about a plugin
 *
 * @category Framework
 * @package  Plugin
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class PluginInfo {

    /**
     * The name of the plugin
     *
     * @var string
     */
    private $_pluginName;

    /**
     * The name of the class of the plugin
     *
     * @var string
     */
    private $_pluginClass;

    /**
     * Array of files of the plugin
     *
     * @var array
     */
    private $_pluginFiles;

    /**
     * Description of the plugin
     *
     * @var string
     */
    private $_pluginDescription;

    /**
     * Array of javascript files of the plugin
     *
     * @var array
     */
    private $_pluginJS;

    /**
     * The status of the plugin
     *
     * @var string
     */
    private $_pluginStatus;

    /**
     * Arry of options of the plugin
     *
     * @var array
     */
    private $_pluginOptions;


    /**
     * Constructor of PluginInfo
     *
     * @param $infoFile string The XML filename of the plugin
     * @return void
     */
    public function __construct($infoFile) {

        $this->_pluginFiles = array();
        $this->_pluginJS = array();
        $this->_pluginOptions = array();

        $this->_processXML($infoFile);

    }

    /**
     * Process the XML to extract information about the plugin
     * @param $filename The XML filename of the plugin
     * @return void
     */
    private function _processXML($filename) {
        try {
            $xmlf = simplexml_load_file($filename);
            $this->_pluginName = (string) $xmlf->name;
            $this->_pluginClass = (string) $xmlf->class;
            $this->_pluginDescription = (string) $xmlf->description;
            $this->_pluginStatus = (string) $xmlf->status;

            if (isset($xmlf->options)) {
                foreach ($xmlf->options->option as $option) {
                    $name = (string) $option["name"];
                    $value = (string) $option->value;
                    $this->_pluginOptions[$name] = $value;
                }
            }

            if (isset($xmlf->files->file)) {
                foreach ($xmlf->files->file as $file) {
                    $this->_pluginFiles[] = (string) $file["name"];
                }
            }

            if (isset($xmlf->jsFiles->file)) {
                foreach ($xmlf->jsFiles->file as $file) {
                    $this->_pluginJS[] = (string) $file["name"];
                }
            }
        }
        catch (Exception $ex) {
            trigger_error("PLUGIN | Cannot process XML plugin info file {$filename} ",E_USER_NOTICE);
        }
    }

    /**
     * Get the options of the plugin
     *
     * @return aray
     */
    public function getOptions() {
        return $this->_pluginOptions;
    }

    /**
     * Gets a property of PluginInfo
     * @param $property string The name of the property
     * @return mixed
     */
    public function __get($property) {
        $pname = "_".$property;
        if (isset($this->$pname)) {
            return ($this->$pname);
        }
    }


};
?>