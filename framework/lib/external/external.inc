<?php
// External libraries will be here
$config = Config::getInstance();
$dir = $config->getParam("externalPath");

require_once "{$dir}/simple_html_dom.php";
require_once "{$dir}/soap/Base.php";
require_once "{$dir}/soap/Client.php";
require_once "{$dir}/soap/Disco.php";
require_once "{$dir}/soap/Fault.php";
require_once "{$dir}/soap/Parser.php";
require_once "{$dir}/soap/Server.php";
require_once "{$dir}/soap/Transport.php";
require_once "{$dir}/soap/Value.php";
require_once "{$dir}/soap/WSDL.php";
?>
