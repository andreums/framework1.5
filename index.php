<?php
/**
 * This is the main entry point of the framework
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
session_start();
error_reporting(E_ALL);
ob_start('ob_gzhandler');
require_once "framework/core.php";
Core::init();
ob_flush();
?>