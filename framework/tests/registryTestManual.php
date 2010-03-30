<?php
chdir("../../");
require_once("framework/bootstrap.php");

function testNewRegistry() {
        $reg = new Registry();
        $count = $reg->elementCount();
        print "Test new Registry\n";
        var_dump($count);
        var_dump($reg);
 }

function testAddElement() {
        $reg = new Registry();
        $element = array("numbers"=>array(1,2,3,4,5),"letters"=>array("a","b","c","..."));
        $reg->add($element);
        $count = $reg->elementCount();
        print "Test add element\n";
        var_dump($count);
        var_dump($reg);
}

function testDelElement() {
        $reg = new Registry();
        $element = array("numbers"=>array(1,2,3,4,5),"letters"=>array("a","b","c","..."));
        $element2 = array("foo"=>"bar");
        $reg->add($element);
        $reg->add($element2);
        $count = $reg->elementCount();

        $reg->remove($element);
        $count2 = $reg->elementCount();

        print "Test delete Element\n";
        var_dump($count);
        var_dump($count2);
        var_dump($reg);
}


testNewRegistry();
testAddElement();
testDelElement();
?>
