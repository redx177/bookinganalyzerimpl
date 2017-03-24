<?php

class Autoloader
{
    static private $folders = ['Business', 'Controllers', 'Utilities', 'Interfaces', 'Models'];
    static public function load($classname) {
        $rootDir = dirname(__DIR__) . '/';
        foreach (self::$folders as $folder) {
            $file = $rootDir . $folder . '/' . $classname . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}