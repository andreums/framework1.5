<?php
/**
 * ActiveRecord model abstraction
 *
 * PHP Version 5.2
 *
 * @package  ActiveRecord
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class Model
 *
 *  ActiveRecord model abstraction
 *
 * @package  ActiveRecord
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

class Model extends ActiveRecord {


    /**
     * @var array
     */
    protected $_columns = null;

    /**
     * @var array
     */
    protected $_dbColumns = null;

    /**
     * @var array
     */
    protected $_primaryKey = null;

    /**
     * @var array
     */
    protected $_relations = null;

    /**
     * @var string The name of the model
     */
    protected $_modelName;


    /**
     * @var array old Primary Key Values
     */
    protected $_oldPrimaryKey;

    /**
     * @var bool is a new record
     */
    protected $_isNewRecord;

    /**
	 * The constructor of Model class
	 *
	 * @param mixed $data Data for filling the model
	 *
	 * @return void
	 */
	public function __construct($data=null) {
		$this->_setUp();
		$this->setUpData($data);
		parent::__construct();
		$this->_loadRelations();
		$this->_oldPrimaryKey = array();
		$this->_savePrimaryKey();
	}


	/**
	 * Sets up and configures the model for its use
	 *
	 * @return void
	 */
	protected function _setUp() {
	    parent::_setUp();
	    $this->_getRelations();
	    $this->_isNewRecord = true;
    }

    /**
     * Set up the data of an existing object of the database
     * @param mixed $data
     *
     * @return void
     */
    public function setUpData($data=null)  {
        if ($data!=null) {
           $attributes = get_object_vars($data);
           foreach (array_keys($attributes) as $name) {
               $this->$name = $attributes[$name];
           }
           $this->_isNewRecord = false;
        }
    }




   /* Gets a property of the model
    *
    * @param $property
    * @see framework/lib/dataLayer/ActiveRecord#__get($property)
    */
   public function __get($property) {
       if ($property[0]=="_") {
           return null;
       }
       if (isset($this->$property)) {
           return $this->$property;
       }
   }

   /**
    * @param $property
    * @param $value
    * @return unknown_type
    */
   public function __set($property,$value) {
       if ($property[0]=="_") {
           return null;
       }
       $this->$property = $value;
   }

     /**
     * Check if this object exists in the database
     *
     * @return boolean
     */
    public function exists()   {
        return $this->_existsData();
    }

    /**
	 * Deletes a object from the database
	 *
	 * @access public
	 * @return bool
	 */
	public function delete() {

	        if ( method_exists($this,"beforeDelete") ) {
	            if (!$this->beforeDelete()) {
	                return false;
	            }
	        }

	        if (!$this->_delete()) {
	            return false;
	        }

	        if ( method_exists($this,"afterDelete") ) {
	            if (!$this->afterDelete()) {
	                return false;
	            }
	        }

	}


	/**
	 * Saves the object in the database
	 *
	 * @return bool
	 */
	public function save() {


	    if ($this->exists()) {

	        if ( method_exists($this,"beforeUpdate") ) {
	            if (!$this->beforeUpdate()) {
	                return false;
	            }
	        }

	        if (!$this->_save()) {
	            return false;
	        }

	        if ( method_exists($this,"afterUpdate") ) {
	            if (!$this->afterUpdate()) {
	                return false;
	            }
	        }
	    }

	    else {

	        if ( method_exists($this,"beforeInsert") ) {
	            if (!$this->beforeInsert()) {
	                return false;
	            }
	        }

	        if (!$this->_save()) {
	            return false;
	        }

	        if ( method_exists($this,"afterInsert") ) {
	            if (!$this->afterInsert()) {
	                return false;
	            }
	        }

	    }

	}


	/**
	 * Sets a relation property of the model
	 *
	 * @param $propertyName The name of the property
	 * @param $propertyValue The value of the property
	 * @return void
	 */
	protected function _setRelationProperty($propertyName,$propertyValue) {
	    $this->$propertyName = $propertyValue;
	}


	/**
	 * Saves the primary key (util for updates)
	 *
	 * @return void
	 */
	protected function _savePrimaryKey() {
	    if (!empty($this->_primaryKey)) {
	        foreach ($this->_primaryKey as $pkey) {
	            $name = $pkey["name"];
	            $this->_oldPrimaryKey[$name] = $this->$name;
	        }
	    }
	}

	/**
	 * Validate the data types and custom
	 * validations within the columns of
	 * the model
	 *
	 * @return bool
	 */
	public function validateData() {
	    return $this->_validateData();
	}



    /**
     * Generates a XML with the model data
     *
     * @return string
     */
    public function toXML() {
        $modelName = $this->_modelName;
        $xml = "<{$modelName}>\n";
        foreach ($this->_columns as $column) {
            if ($this->$column!=null) {
                $xml .= "\t<{$column}>{$this->$column}</{$column}>\n";
            }
            else {
                $xml .= "\t<{$column}>null</{$column}>\n";
            }
        }
        $xml .= "</{$modelName}>\n";

        return $xml;
    }

	/**
	 * Gets a JSON of the model
	 *
	 * @return string
	 */
	public static function toJSON() {

	    $json = $this->toArray();
	    $jsonEncoded = json_encode($json);
	    return $jsonEncoded;
	}

	/**
     * Generates an array with the model data
     *
     * @return array
     */
    public function toArray() {
        $reserved_names = array("has_one","belongs_to","has_many","has_and_belongs_to_many");
        $modelName = $this->_modelName;
        $array = array();
        foreach ($this->_columns as $column) {
            if ( !in_array($column,$reserved_names) ) {
                $array[$column] = $this->$column;
            }
        }
        return $array;
    }
};
?>