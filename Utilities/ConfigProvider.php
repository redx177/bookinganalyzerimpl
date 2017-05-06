<?php

/**
 * Provides application configuration.
 * Loads data from $GLOBALS['configContent'].
 */
class ConfigProvider
{
    private $configs;

    /**
     * ConfigProvider constructor.
     * @param array $configs Configuration data.
     * @param string $rootDir Root directory.
     */
    public function __construct($configs, $rootDir)
    {
        $this->configs = $configs;
        $this->set('rootDir', $rootDir);

        $editableConfigFile = $this->get('rootDir') . '/' . $this->get('editableConfigFile');
        if (file_exists($editableConfigFile)) {
            $editableConfigRaw = file_get_contents($editableConfigFile);
            $editableConfig = json_decode($editableConfigRaw);
            if (isset($editableConfig->pageSize)) {
                $this->set('pageSize', (int)$editableConfig->pageSize);
            }
            if (isset($editableConfig->gamma)) {
                $this->set('gamma', (float)$editableConfig->gamma);
            }
            if (isset($editableConfig->bookingsCountCap)) {
                $this->set('bookingsCountCap', (int)$editableConfig->bookingsCountCap);
            }
            if (isset($editableConfig->minSup)) {
                $configs['apriori']['minSup'] = (float)$editableConfig->minSup;
            }
            if (isset($editableConfig->radius)) {
                $configs['dbscan']['radius'] = (float)$editableConfig->radius;
            }
            if (isset($editableConfig->minPoints)) {
                $configs['dbscan']['minPoints'] = (float)$editableConfig->minPoints;
            }
        }
    }

    /**
     * Gets a config value for a given key.
     * @param string $key Key of the configuration value.
     * @return mixed String or integer value of the config.
     */
    public function get($key)
    {
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