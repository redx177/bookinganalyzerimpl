<?php

/**
 * Provides application configuration.
 * Loads data from $GLOBALS['configContent'].
 */
class ConfigProvider {
    private $configs;

    /**
     * ConfigProvider constructor.
     * @param Array $configs Configuration data.
     */
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Gets a config value for a given key.
     * @param string $key Key of the configuration value.
     * @return mixed String or integer value of the config.
     */
    public function get($key) {
        return $this->configs[$key];
    }

    /**
     * Sets a configuration value.
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public function set(string $key, $value)
    {
        $this->configs[$key] = $value;
    }
}