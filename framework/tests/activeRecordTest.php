<?php
require_once 'PHPUnit2/Framework/TestCase.php';
chdir("../../");
require_once("framework/bootstrap.php");

class activeRecordTest extends PHPUnit2_Framework_TestCase {

    public function testNewModel() {

    	$per = new User();
        $per->name = "Andrés";
        $per->username = "andreums9186";
        $per->save();

        $this->assertEquals("Andrés",$per->name);

    }

    public function testDiscoverColumns() {

    	$table = new ActiveTable("Activity");
    	$table->idActivity=223;
    	$table->name = "ActiveRecording";

    }
}
?>
