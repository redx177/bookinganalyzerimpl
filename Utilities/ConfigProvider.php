<?php

/**
 * Provides application configuration.
 * Loads data from $GLOBALS['configContent'].
 */
class ConfigProvider {
    private $configs;

    /**
     * ConfigProvider constructor.
     * @param $configs Array Configuration data.
     */
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Gets a config value for a given key.
     * @param $key Key of the configuration value.
     * @return mixed String or integer value of the config.
     */
    public function get($key) {
        return $this->configs[$key];
    }
}