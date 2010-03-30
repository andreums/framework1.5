<?php
/**
 * A DataBase Driver interface
 * PHP Version 5.2
 *
 * @category Framework
 * @package  DataBase
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/* Database interface definition for database drivers */
interface IDatabase {
	public function getInfo();
	public function connect();
	public function disconnect();
	public function select();
	public function query($query);
	public function affectedRows();
	public function numRows();
	public function fetchArray();
	public function fetchRow();
	public function fetchAssoc();
	public function fetchObject();
	public function fetchXML();
	public function fetchJSON();
	public function existsTable($tableName);
	public function getTableFields($tableName);
}
?>