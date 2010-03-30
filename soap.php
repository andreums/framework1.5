<?php
/**
 * This is the soap entry point of the framework
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  SOAP
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
session_start();
require_once "framework/core.php";
Core::initSOAP();
$soap = new FWSoapServer();
$soap->serve();

?>