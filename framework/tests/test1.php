<?php
require_once 'PHPUnit2/Framework/TestCase.php';

class test1 extends PHPUnit2_Framework_TestCase {
    public function testNewArrayIsEmpty() {
        // Create the Array fixture.
        $fixture = Array();

        // Assert that the size of the Array fixture is 0.
        $this->assertEquals(0, sizeof($fixture));
    }

    public function testArrayContainsAnElement() {
        // Create the Array fixture.
        $fixture = Array();

        // Add an element to the Array fixture.
        $fixture[] = 'Element';

        // Assert that the size of the Array fixture is 1.
        $this->assertEquals(1, sizeof($fixture));
    }
}
?>
