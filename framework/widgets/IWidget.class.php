<?php

    interface IWidget {

        public function setDataSource($object);
        public function getDataSource();
        public function getDataKeys();

        public function render();
        public function process();

        public function setHTML($html);
        public function getHTML();

        public function setStyle($cssStyle);
        public function getStyle();

        public function setJS($js);
        public function getJS();

    }
?>