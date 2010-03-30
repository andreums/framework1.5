<?php
require_once 'PHPUnit2/Framework/TestCase.php';
chdir("../../");
require_once("framework/bootstrap.php");

class registryTest extends PHPUnit2_Framework_TestCase {

    public function testNewRegistry() {
        $reg = new Registry();
        $count = $reg->elementCount();
        $this->assertEqual(0,$count);
    }

    public function testAddElement() {
        $reg = new Registry();
        $element = array("numbers"=>array(1,2,3,4,5),"letters"=>array("a","b","c","..."));
        $reg->add($element);
        $count = $reg->elementCount();
        $this->assertEqual(1,$count);
    }

    public function testDelElement() {
        $reg = new Registry();
        $element = array("numbers"=>array(1,2,3,4,5),"letters"=>array("a","b","c","..."));
        $reg->add($element);
        $count = $reg->elementCount();
        $this->assertEqual(1,$count);

        $reg->delete($element);
        $count = $reg->elementCount();
        $this->assertEqual(0,$count);


    }


}
?>
