<?php
class helloWidget extends BaseWidget {

	public function process() {
	    $html = "<div id=\"Hello World\" style=\"background-color: #ECCCEC;\">\n";
	    $html .= "<p>¡Hola mundo de los Widgets!</p>";
	    $html .= "</div>";
        $this->setHTML($html);
     }

     public function getStyle() {
     }
}

?>