<?php
spl_autoload_register(function ($classname) {
    Autoloader::load($classname);
});

class Autoloader
{
    static private $folders = ['Business', 'Controllers', 'Utilities', 'Interfaces', 'Models'];

    static public function load($classname)
    {
        $rootDir = dirname(__DIR__) . '/';
        foreach (self::$folders as $folder) {
            if (self::includeIfExists($rootDir . $folder . '/' . $classname . '.php')) {
                return;
            }
            if (self::includeIfExists($rootDir . $folder . '/Algorithms/' . $classname . '.php')) {
                return;
            }
            if (self::includeIfExists($rootDir . $folder . '/Progress/' . $classname . '.php')) {
                return;
            }
            if (self::includeIfExists($rootDir . $folder . '/Iterators/' . $classname . '.php')) {
                return;
            }
        }
    }

    static private function includeIfExists($file) : bool
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}