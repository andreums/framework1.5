<?php
/**
 * Filter
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  Filter
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Filter
 *
 * @category Framework
 * @package  Filter
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class Filter extends BaseFilter {

    /**
     * Array of filters
     *
     * @var array
     */
    private $_filters;


    /**
     * Constructor of Filter
     *
     * @return void
     */
    public function __construct() {
        $this->_filters = array();
        $this->_loadFilters();
    }

    /**
     * Load all the filters in the filter firectory
     *
     * @return void
     */
    public function _loadFilters() {
        $reserved_files = array(".","..","BaseFilter.class.php","Filter.class.php");
        try {
            $filterFiles = @scandir("framework".DS."lib".DS."filter",1);
            if (count($filterFiles)>4) {
                foreach ($filterFiles as $filterFile) {
                    if (!in_array($filterFile,$reserved_files)) {
                        require_once $filterFile;
                        $filterName = substr($filterFile,0,-10);
                        $this->_filters[] = new $filterName();
                    }
                }
            }
        }
        catch (Exception $ex) {

        }
    }

    /**
     * Method to call to a filter method
     *
     * @param $method string The name of the method to call
     * @param $arguments array An array of arguments
     * @return mixed
     */
    public function __call($method,$arguments) {
        if (!empty($this->_filters)) {
            foreach ($this->_filters as $filter) {
                if (method_exists($filter,$method)) {
                    return call_user_func_array(array($filter,$method),$arguments);
                }
            }
        }
    }

}
?>