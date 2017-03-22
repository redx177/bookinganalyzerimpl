<?php

$configContent = array(
    'dataSource' => './rapidminerdata.csv',
    'pageSize' => 5,
);

/**
 * Provides application configuration.
 */
class ConfigProvider {
    /**
     * Gets a config value for a given key.
     * @param $key Key of the configuration value.
     * @return mixed String or integer value of the config.
     */
    public function get($key) {
        return $GLOBALS['configContent'][$key];
    }
}