<?php
/**
 * ActiveRecord design pattern implementation
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
 * Class ActiveRecord
 *
 * @package  ActiveRecord
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 * @abstract
 *
 */
abstract class ActiveRecord {

	protected static $_dataBase = null;
	protected $_modelName = null;

    public function __construct() {

	}

	/**
	 * Sets up the model to be used with ActiveRecord
	 *
	 * @return bool
	 */
	protected function _setUp() {

	    // Get the name of the model
	    $reflect = new ReflectionClass($this);
	    $this->_modelName = $reflect->name;

	    // Connect to the DataBase
	    $this->_connect();

	    // Check if the table exists
	    if (!$this->_existsTable() ) {
	        return false;
	    }

	    // Obtain the columns of the database table
	    $this->_dbColumns = $this->_getTableColumns();

	    // Obtain the columns of the model
	    $this->_columns = $this->_getModelColumns();

	    // Obtain the primary keys
	    $this->_primaryKey = $this->_getPrimaryKey();

	    // Check all the columns in the model are in the columns in the database table
	    foreach ($this->_dbColumns as $column) {
	        $name = $column["name"];
            if ( !in_array($name,$this->_columns) ) {
                trigger_error ("Column {$name} is not in the columns on the database for  {$this->_modelName}",E_USER_ERROR);
                return false;
            }
       }
       return true;
	}

	/**
	 * Connects to the database
	 *
	 * @access private
	 * @return bool
	 */
	private function _connect() {
	    try {
	        self::$_dataBase = DataBase::getInstance();
	        return true;
	    }
	    catch (Exception $ex) {
	        trigger_error("Error: ActiveRecord can't get a DataBase instance, please check database configuration in config.xml",E_USER_ERROR);
	        return false;
	    }
	}

	/**
	 * Checks if the table of the model exists in the
	 * DataBase
	 *
	 * @return bool
	 */
	private function _existsTable() {
	    $table = $this->_modelName;
	    if (self::$_dataBase==null) {
	        $this->_connect();
	    }
	    $result = self::$_dataBase->existsTable($table);
	    if (!$result) {
	        trigger_error("Error: Model {$table} does not exist on the database. Please create a table to fit the model",E_USER_ERROR);
	    }
	    return $result;
	}

	/**
	 * Gets the columns of the database table
	 * of the model
	 *
	 * @return unknown_type
	 */
	protected function _getTableColumns() {
	    $table = $this->_modelName;
	    $columns = self::$_dataBase->getTableFields($table);
	    return $columns;
	}


	/**
     * Method to get the columns of the model
     *
     * @access protected
     * @return array
     */
	protected function _getModelColumns() {
	    $columns = array();
	    $reserved_names = array("has_one","belongs_to","has_many","has_and_belongs_to_many");

	    $class = new ReflectionClass($this);
        $props = $class->getProperties();
        if ($props==null) {
            return false;
        }
        foreach ($props as $column) {
            $name = $column->name;
            if ( ($name[0]!="_") && (!in_array($name,$reserved_names) ) ) {
                $columns[] = $name;
            }
        }
        return $columns;
	}


	/**
     * Method to get the primary key of the model
     *
     * @access protected
     * @return boolean
     */
	protected function _getPrimaryKey() {
	    $primaryKey = array();
	    if ( count($this->_dbColumns)==0 ) {
	        return false;
	    }
	    foreach ($this->_dbColumns as $field) {
	        if ( in_array("primary_key",$field["flags"]) ) {
	            $primaryKey[] = array("name"=>$field["name"],"type"=>$field["type"],"value"=>null);
	        }
        }
        return $primaryKey;
	}


	/**
	 * Builds a SQL string with the columns of the model
	 * @return string
	 */
	private function _buildColumnList() {

	    $query = "";
	    $numColumns = count($this->_columns);

	    if ($numColumns==0) {
	        return $query;
	    }
	    $i=0;
	    foreach ($this->_columns as $column) {
	        if ($i<$numColumns-1) {
				$query .= " {$column}, ";
			}
			else {
				$query .= " {$column} ";
			}
			$i++;
		}
		return $query;
	}

	/**
	 * Builds a SQL string with the values
	 * of the columns of the model
	 *
	 * @return string
	 */
	private function _buildColumnValueList() {

	    $query = "";
	    $numColumns = count($this->_columns);

	    if ($numColumns==0) {
	        return $query;
	    }
	    $i=0;
	    foreach ($this->_columns as $column) {
	        if ($this->$column=="") {
	            $this->$column=" ";
	        }
	        if ($i<$numColumns-1) {
				$query .= " '{$this->$column}', ";
			}
			else {
				$query .= " '{$this->$column}' ";
			}
			$i++;
		}
		return $query;
	}

	/**
	 * Builds a SQL string with the columns and values
	 * special for update sentences
	 *
	 * @return string
	 */
	private function _buildUpdateColumns() {

	    $query = "";
	    $autoNumeric = false;
	    $numColumns = count($this->_dbColumns);

	    if ($numColumns==0) {
	        return $query;
	    }
	    $i=0;
	    if (count($this->_oldPrimaryKey)>0) {
	        foreach ($this->_dbColumns as $dbColumn) {
	            $cname = $dbColumn["name"];
	            if ($this->$cname=="") {
	                $this->$cname=" ";
	            }
	            $query .= " {$cname}='{$this->$cname}' ";
	            if ($i<$numColumns-1) {
	                $query .=" , ";
	            }
	            $i++;
	        }
	        return $query;
	    }
	    else {
	        foreach ($this->_dbColumns as $dbColumn) {
	            $cname = $dbColumn["name"];
	            $flags = $dbColumn["flags"];
	            if (!in_array("auto_increment",$flags)) {
	                if ($this->$cname=="") {
	                    $this->$cname=" ";
	                }
	                $query .= " {$cname}='{$this->$cname}' ";
	                if ($i<$numColumns-1) {
	                    $query .=" , ";
	                }
	            }
	            $i++;
	        }
	        return $query;
	    }
	    return $query;
	}

	/**
	 * Builds a SQL string with the values
	 * of the columns of the model
	 *
	 * @return string
	 */
	private function _buildUpdateValueList() {

	    $query = "";
	    $numColumns = count($this->_primaryKey);

	    if ($numColumns==0) {
	        return $query;
	    }
	    $i=0;
	    foreach ($this->_primaryKey as $dbColumn) {
	        $cname = $dbColumn["name"];
	        if ($this->$cname=="") {
	            $this->$cname=" ";
	        }
	        $query .= " {$cname}='{$this->$cname}' ";
	        if ($i<$numColumns-1) {
	            $query .=" AND ";
	        }
	        $i++;
	    }
	    return $query;
	}


	/**
	 * Builds a SQL string with the values
	 * of the columns of the model
	 *
	 * @return string
	 */
	private function _buildUpdateOldValueList() {

	    $query = "";
	    if (count($this->_oldPrimaryKey)>0) {
	    	    $numColumns = count($this->_oldPrimaryKey);

	    	    if ($numColumns==0) {
	    	        return $query;
	    	    }
	    	    $i=0;
	    	    foreach ($this->_oldPrimaryKey as $oldPkColumn) {
	    	        $cname = key($this->_oldPrimaryKey);
	    	        if ($oldPkColumn=="") {
	    	            $oldPkColumn=" ";
	    	        }
	    	        $query .= " {$cname}='{$oldPkColumn}' ";
	    	        if ($i<$numColumns-1) {
	    	            $query .=" AND ";
	    	        }
	    	        $i++;
	    	    }
	    }
	    else {
	        return $this->_buildUpdateValueList();
	    }
	    return $query;
	}




	private function _getColumnsWithValues() {
	    // TODO: REWRITE!;
	    $query = "";
	    $numColumns = count($this->_dbColumns);

	    if (empty($this->_dbColumns)) {
	        return $query;
	    }

	    $i=0;
	    foreach ($this->_dbColumns as $column) {
	        $name = $column["name"];
	        $value = $this->$name;
	        if (!in_array("auto_increment",$column["flags"])) {
	            if ($value!=null) {
	                $query .= " {$name}='{$value}' ";
	            }
	            else {
	                $query .= " {$name} IS NULL ";
	            }

	            if ($i<$numColumns-1) {
	                $query .= " AND ";
	            }
	        }
	        $i++;
		}
		return $query;
	}


	/**
	 * Checks if this model has data on
	 * the database table
	 *
	 * @return bool
	 */
	protected function _existsData() {
		$i=0;
		$max = count($this->_primaryKey);
		$table = $this->_modelName;
		$query = "SELECT * FROM {$table} WHERE";
		if (!$this->_isNewRecord) {
		    $query .= $this->_buildUpdateOldValueList();
		}
		else {
		    $query .= $this->_buildUpdateValueList();
		}
		self::$_dataBase->query($query);
		$rows = self::$_dataBase->numRows();
		if ($rows>0) {
			return true;
		}
		else {
			return false;
		}
	}


	/**
	 * Saves the object in the database
	 *
	 * @return bool
	 */
	protected function _save() {
	    $result = -1;
		if ($this->_existsData()) {
		    $result = $this->_update();
		}
		else {
		    $result =  $this->_insert();
		}
		return $result;
	}

	/**
	 * Updates a record on the database
	 *
	 * @return bool
	 */
	public function _update() {
	    if ( method_exists($this,"_beforeUpdate") ) {
	        if(!$this->_beforeUpdate()) {
	            return false;
		    }
		}
		$query = "UPDATE {$this->_modelName} SET ";
		$query .=$this->_buildUpdateColumns();
		$query .= " WHERE (";
		$query .= $this->_buildUpdateOldValueList();
		$query .= ")";
		self::$_dataBase->query($query);
		if (self::$_dataBase->affectedRows()>0) {
		    if ( method_exists($this,"_afterUpdate") ) {
		        if(!$this->_afterUpdate()) {
		            return false;
		        }
		        return true;
		    }
		}
		else {
			return false;
		}
	}

	private function _updateHABTM($srcTable,$srcColumn,$dstTable,$dstColumn,$throughTable,$throughTableSrcColumn,$throughTableDstColumn,$dstColumnValue,$srcColumnValue,$oldSrcValue) {
	    $dbd = new DataBase();
	    // check the existence of more objects relationated with the dstTable than this object
	    $query = "SELECT * FROM {$throughTable} t WHERE (t.{$throughTableSrcColumn}='{$srcColumnValue}'";
	    $dbd->query($query);
	    if ($dbd->numRows()) {
	        $updateQuery = "UPDATE {$throughTable} SET {$throughTableSrcColumn}='{$srcColumnValue}' WHERE ( {$throughTableSrcColumn}='{$oldSrcValue}' )";
	        $dbd->query($updateQuery);
	        if ($dbd->affectedRows()>0) {
	            return true;
	        }
	        else {
	            return false;
	        }
	    }
	}

	private function _deleteHABTM($srcTable,$srcColumn,$dstTable,$dstColumn,$throughTable,$throughTableSrcColumn,$throughTableDstColumn,$dstColumnValue,$srcColumnValue) {

	    $dbd = new DataBase();
	    // check the existence of more objects relationated with the dstTable than this object
	    $existsQuery = "SELECT * FROM  {$throughTable} t JOIN {$dstTable} b ON ( t.{$throughTableDstColumn}=b.{$dstColumn} ) WHERE ( (t.{$throughTableDstColumn}='{$dstColumnValue}') AND (t.{$throughTableSrcColumn}<>{$srcColumnValue}) ) ";
	    $dbd->query($existsQuery);
	    if ($dbd->numRows()>0) {
	        // There are more relationated objects in the dstTable
	        $deleteQuery = "DELETE FROM {$throughTable} WHERE ( ({$throughTableDstColumn}='{$dstColumnValue}') AND ({$throughTableSrcColumn}='{$srcColumnValue}') )";
	        $dbd->query($deleteQuery);
	    }
	    else {
	        $deleteQuery = "DELETE FROM {$throughTable} WHERE ( ({$throughTableDstColumn}='{$dstColumnValue}') AND ({$throughTableSrcColumn}='{$srcColumnValue}') )";
	        $dbd->query($deleteQuery);
	        $deleteQuery1 = "DELETE FROM {$dstTable} WHERE ({$dstColumn}='{$dstColumnValue}')";
	        $dbd->query($deleteQuery1);
	    }

	}




	/**
	 * Deletes a object from the database
	 *
	 * @access private
	 * @return bool
	 */
	protected function _delete() {

	    if ( method_exists($this,"_beforeDelete") ) {
	        if(!$this->_beforeDelete()) {
	            return false;
		    }
		}

		$query = "DELETE FROM {$this->_modelName} WHERE (";
        $query .= $this->_buildUpdateValueList();
        $query .= ")";
        self::$_dataBase ->query($query);
        $rows = self::$_dataBase->affectedRows();

        if ( method_exists($this,"_afterDelete") ) {
	        if(!$this->_afterDelete()) {
	            return false;
		    }
		}

        if ($rows>0) {
            return true;
        }
        return false;
    }



	/**
	 * Inserts data on the database
	 *
	 * @return bool
	 */
	public function _insert() {

	    if ( method_exists($this,"_beforeInsert") ) {
			if (!$this->_beforeInsert()) {
			    return false;
			};
		}

		$i=0;
		$max = count($this->_columns);

		$query = "INSERT INTO {$this->_modelName} (";
		$query .= $this->_buildColumnList();
		$query .= ") VALUES (";
		$query .= $this->_buildColumnValueList();
		$query .= ")";
		self::$_dataBase->query($query);

		if (self::$_dataBase->affectedRows()>0) {
		    if ( method_exists($this,"_afterInsert") ) {
		        if (!$this->_afterInsert()) {
		            return false;
		        }
		        return true;
		    }
		}
		else {
			return false;
		}
	}

	/**
	 * After deleting a model processes
	 * all the relations of the model
	 * and updates its foreign keys
	 * with the new foreign key value
	 * or may be deleted
	 *
	 * @return void
	 */
	private function _afterDelete() {
	    if (count($this->_relations)>0) {
	        foreach ($this->_relations as $relation) {
	            $type = $relation["type"];
	            $deleteMethod = $relation["delete"];

	            if ($deleteMethod=="restrict") {
	                continue;
    		    }

    		    if ($deleteMethod=="cascade") {

    		        if ($type=="has_one" || $type=="has_many") {
    		            $propName = $relation["table"];
    		            if (is_array($this->$propName)) {
    		                if (count($this->$propName)>0) {
    		                    foreach ($this->$propName as $object) {
    		                        $object->delete();
    		                     }
    		                }
    		            }
    		        }

    		        if ($type=="has_many_and_belongs_to") {

    		            $propName = $relation["dstTable"];
    		            $srcTable = $relation["srcTable"];
    		            $srcColumn = $relation["srcColumn"];
    		            $dstTable = $relation["dstTable"];
    		            $dstColumn = $relation["dstColumn"];
    		            $throughTable = $relation["throughTable"];
    		            $throughTableSrcColumn = $relation["throughTableSrcColumn"];
    		            $throughTableDstColumn = $relation["throughTableDstColumn"];
    		            $dstColumnValue = $object->$dstColumn;
    		            $srcColumnValue = $this->$srcColumn;
    		            $this->_deleteHABTM($srcTable,$srcColumn,$dstTable,$dstColumn,$throughTable,$throughTableSrcColumn,$throughTableDstColumn,$dstColumnValue,$srcColumnValue);

    		        }

    		    }

    		    if ($deleteMethod=="set null") {

    		        if ($type=="has_one" || $type=="has_many") {
    		            $column = $relation["dstColumn"];
    		            if (is_array($this->$propName)) {
    		                if (count($this->$propName)>0) {
    		                    foreach ($this->$propName as $object) {
    		                            $object->$column = "NULL";
    		                            $object->save();
    		                        }
    		                    }
    		                }
    		          }
    		          if ($type=="has_many_and_belongs_to") {
    		              $propName = $relation["dstTable"];
    		              $srcTable = $relation["srcTable"];
    		              $srcColumn = $relation["srcColumn"];
    		              $dstTable = $relation["dstTable"];
    		              $dstColumn = $relation["dstColumn"];
    		              $throughTable = $relation["throughTable"];
    		              $throughTableSrcColumn = $relation["throughTableSrcColumn"];
    		              $throughTableDstColumn = $relation["throughTableDstColumn"];
    		              $dstColumnValue = $object->$dstColumn;
    		              $srcColumnValue = $this->$srcColumn;
    		              $this->_updateHABTM($srcTable,$srcColumn,$dstTable,$dstColumn,$throughTable,$throughTableSrcColumn,$throughTableDstColumn,$dstColumnValue,$srcColumnValue,"NULL");
    		          }
    		    }
	        }
	    }
	 }


	/**
	 * After updating a model processes
	 * all the relations of the model
	 * and updates its foreign keys
	 * with the new foreign key value
	 * (if changed)
	 *
	 * @return void
	 */
	private function _afterUpdate() {

	    if (count($this->_relations)>0) {

	        foreach ($this->_relations as $relation) {

	            $type = $relation["type"];
	            $propName = $relation["table"];
    		    $updateMethod = $relation["update"];

    		    if ($updateMethod=="restrict") {
    		        continue;
    		    }

    		    if ($updateMethod=="cascade") {
    		        if (is_array($this->$propName)) {
    		            if (count($this->$propName)>0) {
    		                foreach ($this->$propName as $object) {
    		                    if ( ($type=="has_one") || ($type=="has_many") ) {
    		                            $column = $relation["dstColumn"];
    		                            $srcColumn = $relation["srcColumn"];
    		                            $object->$column = $this->$srcColumn;
    		                            $object->save();
    		                        }
    		                        if ($type=="has_many_and_belongs_to") {
    		                            $oldSrcValue = $object->$srcColumn;
    		                            $srcTable = $relation["srcTable"];
    		                            $srcColumn = $relation["srcColumn"];
    		                            $dstTable = $relation["dstTable"];
    		                            $dstColumn = $relation["dstColumn"];
    		                            $throughTable = $relation["throughTable"];
    		                            $throughTableSrcColumn = $relation["throughTableSrcColumn"];
    		                            $throughTableDstColumn = $relation["throughTableDstColumn"];
    		                            $dstColumnValue = $object->$dstColumn;
    		                            $srcColumnValue = $this->$srcColumn;
    		                            $this->_updateHABTM($srcTable,$srcColumn,$dstTable,$dstColumn,$throughTable,$throughTableSrcColumn,$throughTableDstColumn,$dstColumnValue,$srcColumnValue,$oldSrcValue);
    		                        }
    		                    }
    		                }
    		            }
    		        }

    		        if ($updateMethod=="set null") {

    		            if (is_array($this->$propName)) {
    		                if (count($this->$propName)>0) {
    		                    foreach ($this->$propName as $object) {
    		                        if ( ($type=="has_one") || ($type=="has_many") ) {
    		                            $column = $relation["dstColumn"];
    		                            $object->$column = "NULL";
    		                            $object->save();
    		                        }
    		                        if ($type=="has_many_and_belongs_to") {
    		                            $srcTable = $relation["srcTable"];
    		                            $srcColumn = $relation["srcColumn"];
    		                            $dstTable = $relation["dstTable"];
    		                            $dstColumn = $relation["dstColumn"];
    		                            $throughTable = $relation["throughTable"];
    		                            $throughTableSrcColumn = $relation["throughTableSrcColumn"];
    		                            $throughTableDstColumn = $relation["throughTableDstColumn"];
    		                            $dstColumnValue = $object->$dstColumn;
    		                            $srcColumnValue = $this->$srcColumn;
    		                            $this->_updateHABTM($srcTable,$srcColumn,$dstTable,$dstColumn,$throughTable,$throughTableSrcColumn,$throughTableDstColumn,$dstColumnValue,$srcColumnValue,"NULL");
    		                        }
    		                    }
    		                }
    		            }
    		        }
    		    }
		    }
	}

	/**
	 * After the insert of a model
	 *
	 * @return bool
	 */
	private function _afterInsert() {
	    return true;
	}



	/**
	 * beforeUpdaete checks all the properties
	 * of the model are of the type that
	 * is defined on the database
	 *
	 * @return bool
	 */
	private function _beforeUpdate() {
	    return $this->_validateData();
	}

	/**
	 * beforeInsert checks all the properties
	 * of the model are of the type that
	 * is defined on the database
	 *
	 * @return bool
	 */
	private function _beforeInsert() {

	    if ($this->_existsData()) {
	        return false;
	    }

	    if (!$this->_validateData()) {
	        return false;
	    }
	    return true;
	}

	/**
	 * Before delete,
	 * check if the model exists
	 *
	 * @return bool
	 */
	private function _beforeDelete() {
	    return $this->_existsData();
	}



	/**
	 * Validates the data type and length of a column
	 *
	 * @param $column The column name
	 * @param $value  The value of the column
	 * @return bool
	 */
	private function _validateColumn($column,$value) {

	    $type = "";
	    $notNull = false;
	    $length = 0;

	    foreach ($this->_dbColumns as $dbColumn) {
	        if ($dbColumn["name"]==$column) {
	            // Ignore all the auto_increment columns
	            if (in_array("auto_increment",$dbColumn["flags"])) {
	                return true;
	            }
	            if (in_array("not_null",$dbColumn["flags"])) {
	                $notNull = true;
	            }
	            $type   = $dbColumn["type"];
	            $length = $dbColumn["length"];
	            break;
	        }
	    }

	    if ($notNull) {
	        if ( ($value==null) || ($value=="") ) {
	            return false;
	        }
	    }

	    $valueLength = strlen($value);
	    if ($valueLength>$length) {
	        return false;
	    }

	    switch($type) {
	        case "string":
	            return Filter::isString($value);
	        break;

	        case "int":
	            $res = Filter::isInteger($value);
	            if (!$res && ($value==null || $value=="NULL") ) {
	                return true;
	            }
	            if ($res) {
	                return true;
	            }
	        break;

	        case "float":
	            return Filter::isFloat($value);
	        break;

	        case "bool":
	            return Filter::isBoolean($value);
	        break;

	        case "blob":
	            return true;
	        break;
	    };

	}


	/**
	 * Gets the number of model elements
	 * in the DataBase table
	 *
	 * @param $conditions
	 *
	 * @return int
	 */
	public static function count($conditions="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if (strlen($conditions)>0) {
            $query = "SELECT COUNT(*) FROM {$name} WHERE {$conditions}";
        }
        else {
            $query = "SELECT COUNT(*) FROM {$name}";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows) {
            $res = $db->fetchRow();
            return $res[0];
        }
        return null;
    }

	/**
	 * Count by column
	 *
	 * @param $column The column to count in
	 * @param $value  The column=value
	 *
	 * @return int
	 */

	public static function countByColumn($column,$value="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if ( empty($value) ) {
            $query = "SELECT COUNT({$column}) FROM {$name} ";
        }
        else {
            $query = "SELECT COUNT({$column}) FROM {$name} WHERE {$column}='{$value}' ";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows) {
            $res = $db->fetchRow();
            return intval($res[0]);
        }
        return null;
    }


	/**
	 * Returns the SUM() aggregate function
	 * value for the column passed as argument.
	 *
	 * @param $column     The column to make SUM()
	 * @param $conditions Conditions to make SUM()
	 * @return double
	 */
	public static function sum($column,$conditions="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if ( empty($conditions) ) {
            $query = "SELECT SUM({$column}) FROM {$name} ";
        }
        else {
            $query = "SELECT SUM({$column}) FROM {$name} WHERE {$conditions}";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows) {
            $res = $db->fetchRow();
            return $res[0];
        }
        return null;
    }

	/**
	 * Returns the MIN() aggregate function
	 * value for the column passed as argument.
	 *
	 * @param $column     The column to make MIN()
	 * @param $conditions Conditions to make MIN()
	 * @return double
	 */
	public static function min($column,$conditions="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if ( empty($conditions) ) {
            $query = "SELECT MIN({$column}) FROM {$name} ";
        }
        else {
            $query = "SELECT MIN({$column}) FROM {$name} WHERE {$conditions}";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows) {
            $res = $db->fetchRow();
            return $res[0];
        }
        return null;
   }


	/**
	 * Returns the MAX() aggregate function
	 * value for the column passed as argument.
	 *
	 * @param $column     The column to make MAX()
	 * @param $conditions Conditions to make MAX()
	 * @return double
	 */
	public static function max($column,$conditions="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if ( empty($conditions) ) {
            $query = "SELECT MAX({$column}) FROM {$name} ";
        }
        else {
            $query = "SELECT MAX({$column}) FROM {$name} WHERE {$conditions}";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows) {
            $res = $db->fetchRow();
            return $res[0];
        }
        return null;
   }

	/**
	 * Returns the AVG() aggregate function
	 * value for the column passed as argument.
	 *
	 * @param $column     The column to make AVG()
	 * @param $conditions Conditions to make AVG()
	 * @return double
	 */
	public static function avg($column,$conditions="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if ( empty($conditions) ) {
            $query = "SELECT AVG({$column}) FROM {$name} ";
        }
        else {
            $query = "SELECT AVG({$column}) FROM {$name} WHERE {$conditions}";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows) {
            $res = $db->fetchRow();
            return $res[0];
        }
        return null;
   }

    /**
	 * Gets the number of model elements
	 * in the DataBase table
	 *
	 * @param $sql The SQL...
	 * @return int
	 */
	public static function countBySQL($sql="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if (strlen($sql)>0) {
            $query = $sql;
        }
        else {
            $query = "SELECT COUNT(*) FROM {$name}";
        }
        $db->query($query);
        if ($rows) {
            $res = $db->fetchRow();
            return intval($res[0]);
        }
        return null;
    }


    /**
     * Gets the distinct values of a column
     *
     * @param $column The column
     * @return array
     */
    public static function distinct($column) {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        $query = "SELECT DISTINCT({$column}) FROM {$name} ";
        $db->query($query);
        $rows = $db->numRows();

        if ($rows>0) {
            $results = array();
            while ($result = $db->fetchRow()) {
                $results[] = $result[0];
            }
            return $results;
        }
        return null;
    }


    /**
	 * Gets all the model of the table on the
	 * DataBase
	 *
	 * @return array
	 */
	public static function find($param="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }
        if (!empty($param)) {

            if ($param=="all") {
                $query = "SELECT * FROM {$name}";
            }

            if ($param=="first") {
                $query = "SELECT * FROM {$name} LIMIT 1";
            }

            if ($param=="last") {
                $query = "SELECT * FROM {$name} LIMIT 1";
            }
        }
        else {
             $query = "SELECT * FROM {$name}";
        }
        $db->query($query);
        $rows = $db->numRows();
        if ($rows>0) {
            $results = array();

            while ($result = $db->fetchObject()) {
                $object = new $name($result);
                $results[] = $object;
            }
            if ($param=="first") {
                return $results[0];
            }
            if ($param=="last") {
                return $results[count($results)-1];
            }
            return $results;
        }
        else {
            return null;
        }
	}



	/**
	 * Gets all the model of the table on the
	 * DataBase
	 *
	 * @return array
	 */
	public static function findAll() {
	    return self::find("all");
    }


	/**
	 * Gets the first record of the table in the database
	 *
	 * @return array
	 */
	public static function findFirst() {
	    return self::find("first");
    }


    /**
	 * Gets the last record of the table in the database
	 *
	 * @return array
	 */
	public static function findLast() {
	    return self::find("last");
    }

    /**
	 * Gets all the model of the table on the
	 * DataBase which the column has the specified
	 * value
	 *
	 * @return array
	 */
	public static function findByColumn($column,$value) {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }

        $query = "SELECT * FROM {$name} WHERE ($column='$value')";
        $db->query($query);
        $rows = $db->numRows();
        $results = array();
        while ($result = $db->fetchObject()) {
            $object = new $name($result);
            $results[] = $object;
        }
        return $results;
    }


    /**
	 * Gets all the model of the table on the
	 * DataBase from an SQL sentence
	 *
	 * @param $sql string The SQL Query
	 * @param $groups string If needs a GROUP BY function
	 * @param $orders string If need ordering of the results
	 * @return array
	 */
	public static function findBySQL($sql,$groups="",$orders="") {

        $name = get_called_class();
        $db = new DataBase();
        $res = $db->existsTable($name);
        if ($res===false) {
            trigger_error ("Error: Table {$name} doesn't exists in the selected database",E_USER_ERROR);
        }

        $query = "SELECT * FROM {$name} WHERE ( ";
        $query .= $sql;
        $query .= ")";
        if (!empty($groups)) {
            $query .= " {$groups} ";
        }
        if (!empty($orders)) {
            $query .= " {$orders} ";
        }
        $db->query($query);
        $rows = $db->numRows();
        $results = array();
        while ($result = $db->fetchObject()) {
            $object = new $name($result);
            $results[] = $object;
        }
        return $results;
    }



    /**
     * Method to get the relations of the model
     *
     * @return boolean
     */

    protected function _getRelations() {

        if ( count($this->_columns)==0 ) {
            return false;
        }

        if ( $this->_relations==null ) {
            $reserved_names = array("has_one","belongs_to","has_many","has_and_belongs_to_many");

            $class = new ReflectionClass($this);
            $columns = $class->getProperties();
            foreach ($columns as $column) {
                $name = $column->name;
                if ( in_array($name,$reserved_names) ) {

                    if ( $name=="has_one")  {
                        $prop = $class->getProperty($name);
                        $value = $prop->getValue();
                        foreach ($value as $val) {
                            $this->_relations[] = array(
                                "type"     =>"has_one",
                                "table"    =>$val["table"],
                                "srcColumn"=>$val["srcColumn"],
                                "dstColumn"=>$val["dstColumn"],
                                "update"   =>$val["update"],
                                "delete"   =>$val["delete"]
                            );
                        }
                    }

                    if ( $name=="has_many" ) {
                        $prop = $class->getProperty($name);
                        $value = $prop->getValue();
                        foreach ($value as $val) {
                            $this->_relations[] = array(
                                "type"     =>"has_many",
                                "table"    =>$val["table"],
                                "srcColumn"=>$val["srcColumn"],
                                "dstColumn"=>$val["dstColumn"],
                            	"update"   =>$val["update"],
                                "delete"   =>$val["delete"]
                            );
                        }
                    }

                    if ( $name=="has_and_belongs_to_many" ) {
                        $prop = $class->getProperty($name);
                        $value = $prop->getValue();
                        foreach ($value as $val) {
                            $this->_relations[] = array(
                                "type"             =>"has_many_and_belongs_to",
                                "srcTable"         =>$val["srcTable"],
                                "srcColumn"        =>$val["srcColumn"],
                                "dstTable"         =>$val["dstTable"],
                                "dstColumn"        =>$val["dstColumn"],
                                "throughTable"    =>$val["throughTable"],
                                "throughTableSrcColumn"=>$val["throughTableSrcColumn"],
                                "throughTableDstColumn"=>$val["throughTableDstColumn"],
                            	"update"           =>$val["update"],
                                "delete"           =>$val["delete"]
                            );
                        }
                   }
                }
            }
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Loads the relations of a model
     *
     * @return void
     */
    protected function _loadRelations() {

        if ( count($this->_relations)>0 ) {
            foreach ($this->_relations as $relation) {

                if ( $relation["type"] == "has_one" ) {
                    $table = $relation["table"];
                    $src = $relation["srcColumn"];
                    $dst = $relation["dstColumn"];
                    $condition = "{$dst}='{$this->$src}' ";
                    if (! isset($this->$table) ) {
                        $this->_setRelationProperty($table,$this->_findRelations($table,$condition));

                    }
                }

                if ( $relation["type"] == "has_many" ) {
                    $table = $relation["table"];
                    $src = $relation["srcColumn"];
                    $dst = $relation["dstColumn"];
                    $condition = "{$dst}='{$this->$src}' ";
                    if (! isset($this->$table) ) {
                        $this->_setRelationProperty($table,$this->_findRelations($table,$condition));
                    }
                }

                if ( $relation["type"] == "has_many_and_belongs_to" )  {
                    $srcTable         = $relation["srcTable"];
                    $srcColumn        = $relation["srcColumn"];
                    $dstTable         = $relation["dstTable"];
                    $dstColumn        = $relation["dstColumn"];
                    $throughTable    = $relation["throughTable"];
                    $throughSrcColumn= $relation["throughTableSrcColumn"];
                    $throughDstColumn= $relation["throughTableDstColumn"];
                    $update           = $relation["update"];
                    $delete           = $relation["delete"];
                    $condition = " {$srcTable} a JOIN {$throughTable} t JOIN {$dstTable} b ON ( (a.{$srcColumn}=t.{$throughSrcColumn}) ";
                    $condition .= " AND (t.{$throughDstColumn}=b.{$dstColumn}) ) WHERE (a.{$srcColumn}='{$this->$srcColumn}')";
                    if (! isset($this->$dstTable) ) {
                        $this->_setRelationProperty($dstTable,$this->_findHABTMRelations($dstTable,$condition));
                    }
                }
            }
       }
    }

    /**
     * Find the related objects of the model
     * on an HABTM relationship
     * (Has And Belongs To)
     *
     * @param $table The name of the table of the objects
     * @param $conditions The relation conditions
     * @return array Array of objects
     */
    public function _findHABTMRelations($table,$conditions="") {
        if ( strlen($conditions)>0 ) {
            $query = "SELECT * FROM {$conditions} ";
        }
        $dbhabtm = new DataBase();
        $dbhabtm->query($query);
        $numResults = $dbhabtm->numRows();
        $results = array();
        for ($i=0;$i<$numResults;$i++) {
            $result = $dbhabtm->fetchObject();
            $results[] = new $table($result);
        }
        return $results;

    }


    /**
     * Find the related objects of the model
     * @param $table The name of the table of the objects
     * @param $conditions The relation conditions
     * @return array Array of objects
     */
    public function _findRelations($table,$conditions) {
        if ( count($conditions)==0 ) {
            $query = "SELECT * FROM {$table}";
        }
        else {
            $query = "SELECT * FROM {$table} WHERE ( {$conditions} )";
        }
        $db2 = new DataBase();
        $db2->query($query);
        $numResults = $db2->numRows();
        $results = array();
        for ($i=0;$i<$numResults;$i++) {
            $result = $db2->fetchObject();
            $results[] = new $table($result);
        }
        return $results;
    }


    /**
	 * Inits a transaction on the database (where available)
	 *
	 */
	public function begin(){
		return self::$_dataBase->begin();
	}


	/**
	 * Cancels a running transaction (where available)
	 *
	 */
	public function rollback(){
		return self::$_dataBase->rollback();
	}

	/**
	 * Makes commit of a running transaction (where available)
	 *
	 */
	public function commit(){
		return self::$_dataBase->commit();
	}

	/**
	 * Validate the data types and custom
	 * validations within the columns of
	 * the model
	 *
	 * @return bool
	 */
	protected function _validateData() {

	    // Obtain all the methods in the model
	    $reflect = new ReflectionClass($this);
	    $methods = $reflect->getMethods(ReflectionMethod::IS_PROTECTED);

	    // Obtain and execute custom validators
	    foreach ($methods as $method) {
	        if (strpos((string) $method->name,"validate")!==false) {
	            if ( ($method->name!="_validateData") && ($method->name!="validateData") ) {
	                $result = call_user_func(array($this,$method->name));
	                if (!$result) {
	                    throw new Exception("Error using {$method->name}");
	                    return false;
	                }

	            }
	        }
	    }

	    // Validate the columns of the model
	    $reserved_names = array("has_one","belongs_to","has_many","has_and_belongs_to_many");
	    foreach ($this->_columns as $column) {
	        if (!in_array($column,$reserved_names)) {
	            $result = $this->_validateColumn($column,$this->$column);
	            if (!$result) {
	                throw new Exception("Error: {$this->$column} is not a valid value for {$column} column");
	                return false;
	            }
	        }
	    }
	    return true;
	}





}
