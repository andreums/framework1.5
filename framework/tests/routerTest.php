<?php
require_once 'PHPUnit2/Framework/TestCase.php';
require_once("../bootstrap.php");
class routerTest extends PHPUnit2_Framework_TestCase {

	public function testRouterInstance() {
		$router = Router::getInstance();
		$this->assertNotNull($router);
	}

	public function testToURLValid() {
        $router = Router::getInstance();
        $url = $router->toURL("frontend","frontend","websuggerence");
        $this->assertEquals("http://localhost/framework/index.php/sugerencias",$url);
    }

    public function testToURLInvalid() {
        $router = Router::getInstance();
        $url = $router->toURL("frontend","noExistant","noExiste");
        $this->assertNull($url);
    }

}
?>
