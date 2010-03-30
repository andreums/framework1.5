<?php
/**
 * A DataBase abstraction
 * PHP Version 5.2
 *
 * @category Framework
 * @package  DataBase
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
require_once("IDatabase.php");

/**
 * Class DataBase
 *
 * A DataBase abstraction
 *
 * @category Framework
 * @package  DataBase
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class DataBase extends Singleton {

	/**
    * The name of the DataBaseDriver
    *
    * @var string
    * @access protected
    */
	protected $_driver;


	/**
    * The username of the database
    *
    * @var string
    * @access protected
    */
	protected $_username;

	/**
    * The password of the database
    *
    * @var string
    * @access protected
    */
	protected $_password;

	/**
    * The name of the DataBase
    *
    * @var string
    * @access protected
    */
	protected $_database;

	/**
    * The prefix of the tables of the database
    *
    * @var string
    * @access protected
    */
	protected $_prefix;

	/**
    * The file of the DataBase (for SQLite3)
    *
    * @var string
    * @access protected
    */
	protected $_dbfile;

	/**
    * The driver Object
    * @var DataBaseDriver
    * @access protected
    */
	protected $_driverObject;

	/**
    * A variable to access to the config
    * @var Config
    * @access protected
    */
	protected $_config;


	/**
	 * The constructor of DataBase
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->_config = Config::getInstance();
		$this->_setUpConnection();
		$this->_loadDriver();
	}


	/**
     * Sets up the connection with the parameters
     * of the configuration file
     *
     * @access private
     * @return void
     */
	private function _setUpConnection() {

		$dbConfig = $this->_config->getDatabaseConfig();
		$this->_driver = (string) $dbConfig->type;
		$this->_host = (string) $dbConfig->host;
		$this->_database = (string) $dbConfig->database;
		$this->_username = (string) $dbConfig->username;
		$this->_password = (string) $dbConfig->password;
		$this->_prefix = (string) $dbConfig->prefix;
		$this->_dbfile = (string) $dbConfig->file;
	}

	/**
     * Loads the Driver of the DataBase
     *
     * @access private
     * @return void
     */
	private function _loadDriver() {

	    $driverName = "{$this->_driver}Driver";
	    $driverFile = "drivers/".$driverName.".php";
	    try {
	        require_once $driverFile;
	        $this->_driverObject = new $driverName(
	            array(
	            	"host"=>$this->_host,
	            	"database"=>$this->_database,
	            	"username"=>$this->_username,
	            	"password"=>$this->_password,
	            	"dbprefix"=>$this->_prefix,
	            	"dbfile"=>$this->_dbfile
	            )
	          );
	    }
	    catch (Exception $ex) {
	        trigger_error("Error while loading the database driver {$driverName} please check if the driver exists",E_USER_ERROR);
	    }
	}


	/**
     * Handles the calls to special methods of the
     * driver
     *
     * @param string $method The name of the method
	 * @param Array $arguments An array of arguments
     * @access public
     * @return mixed
     */
	public function __call($method,$arguments) {
		if ( method_exists($this->driverObject,$method) ) {
		    return call_user_func(array($this->_driverObject,$method),$arguments);
		}
		else {
			trigger_error("Error: Method {$method} is not implemented in DataBase",E_USER_ERROR);
		}
	}


    /**
     * Gets information about the current driver
     *
     * @access public
     * @return string
     */
    public function getInfo()   {
        return $this->_driverObject->getInfo();
    }

    /**
     * Connects to the database
     *
     * @access public
     * @return bool
     */
    public function connect()    {
        return $this->_driverObject->connect();
    }

    /**
     * Disconnects from the database
     *
     * @access public
     * @return bool
     */
    public function disconnect()    {
        return $this->_driverObject->disconnect();
    }

    /**
     * Queries the database
     *
     * @param  string The SQL query
     * @access public
     * @return mixed The result
     */
    public function query($query)    {
        return $this->_driverObject->query($query);
    }

    /**
     * Gets the affected rows for a query
     *
     * @access public
     * @return int Number of affected rows
     */
    public function affectedRows()   {
        return $this->_driverObject->affectedRows();
    }

    /**
     * Gets the number of rows from a result
     *
     * @access public
     * @return int Number of rows of the result
     */
    public function numRows()    {
        return $this->_driverObject->numRows();
    }

    /**
     * Fetches the result of a query as array
     *
     * @access public
     * @return Array
     */
    public function fetchArray() {
        return $this->_driverObject->fetchArray();
    }

    /**
     * Fetches the result of a query as Row
     *
     * @access public
     * @return Array
     */
    public function fetchRow() {
        return $this->_driverObject->fetchRow();
    }

    /**
     * Fetches the result of a query as an associative array
     *
     * @access public
     * @return Array
     */
    public function fetchAssoc() {
        return $this->_driverObject->fetchAssoc();
    }

    /**
     * Fetches the result of a query as an object
     *
     * @access public
     * @return object
     */
    public function fetchObject() {
        return $this->_driverObject->fetchObject();
    }

    /**
     * Fetches the result as an XML string
     *
     * @access public
     * @return string
     */
    public function fetchXML() {
        return $this->_driverObject->fetchXML();
    }


    /**
     * Fetches the result as a JSON array
     *
     * @access public
     * @return mixed
     */
    public function fetchJSON() {
        return $this->_driverObject->fetchJSON();
    }

	/**
     * Checks if a table exists
     *
     * @param string $tableName The name of the table
     * @access public
     * @return bool
     */
    public function existsTable($tableName) {
        return $this->_driverObject->existsTable($tableName);
    }

    /**
     * Gets info about a table of the database
     *
     * @param string $tableName The name of the table
     * @access public
     * @return Array
     */
    public function getTableFields($tableName) {
        return $this->_driverObject->getTableFields($tableName);
    }

	/**
	 * Inits a transaction on the database (where available)
	 *
	 */
	public function begin(){
		return $this->_driverObject->query("BEGIN");
	}


	/**
	 * Cancels a running transaction (where available)
	 *
	 */
	public function rollback(){
		return $this->_driverObject->query("ROLLBACK");
	}

	/**
	 * Makes commit of a running transaction (where available)
	 *
	 */
	public function commit(){
		return $this->_driverObject->query("COMMIT");
	}


	/**
	 * Gets an XML representation of the object
	 *
	 * @param $object object The object to transform
	 * @return string
	 */
	public static function toXML($object) {

	    if ( ( $object===null ) || ( !is_object($object) ) ) {
	        return false;
	    }

	    $reflect = new ReflectionClass($object);
	    $xml = "<result>\n";
	    foreach ($reflect->getProperties() as $property) {
	        $column = $property->getName();
	        if ($object->$column!=null) {
	            $xml .= "\t<{$column}>{$object->$column}</{$column}>\n";
	        }
	        else {
	            $xml .= "\t<{$column}>null</{$column}>\n";
	        }
	    }
        $xml .= "</result>\n";
        return $xml;
	}

	/**
	 * Gets a JSON representation of the object
	 *
	 * @param $object object The object to transform
	 * @return string
	 */
	public static function toJSON($object) {

	    if ( ( $object===null ) || ( !is_object($object) ) ) {
	        return false;
	    }

	    $reflect = new ReflectionClass($object);
	    $json = array();
	    foreach ($reflect->getProperties() as $property) {
	        $column = $property->getName();
	        if ($object->$column!=null) {
	            $json[$column] = $object->$column;
	        }
	        else {
	            $json[$column] = null;
	        }
	    }
	    $jsonEncoded = json_encode($json);
	    return $jsonEncoded;
	}

}
?>