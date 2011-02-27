<?php

namespace Bundler\Component\Config;

/**
 * Base class for all Bundler configuration readers
 */
abstract class ConfigReader
{
    protected $configuration;
    
    /**
     * @param $path The path to the configuration file
     */
    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Config file $path does not exist.");
        }
        $this->configuration = $this->read($path);
    }
    
    /**
     * Gets the read configuration
     * @return ConfigurationContainer
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
    
    /**
     * Reads a configuration from a file
     * @param $file Path to the configuration file
     * @return ConfigurationContainer
     */
    public abstract function read($file);
    
}
