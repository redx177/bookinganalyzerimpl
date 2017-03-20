<?php

$configContent = array(
    'dataSource' => './rapidminerdata.csv',
);

class Config {
    public function get($name) {
        return $GLOBALS['configContent'][$name];
    }
}