<?php

namespace SymfonyBundler\BundlerBundle\Config;

/**
 * Base class for all Bundler configuration readers
 */
abstract class ConfigReader
{
    protected $configuration;
    
    /**
     * @param $path The path to the configuration file
     */
    public function __construct(ConfigurationContainer $configurationContainer)
    {
        $this->configuration = $configurationContainer;
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
     */
    public function read($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("Config file $file does not exist.");
        }
        
        $this->parseFile($file);
    }
    
    /**
     * Parse a configuration file and populate the ConfigurationContainer
     * @param string $file The path to the config file
     */
    protected abstract function parseFile($file);
}
