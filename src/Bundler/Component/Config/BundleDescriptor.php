<?php

namespace Bundler\Component\Config;

/**
 * Describes a Bundle for use with SymfonyBundler and its dependencies
 */
class BundleDescriptor
{
    /**
     * @var string The bundle name
     */
    public $name;
    /**
     * @var string The bundle version
     */
    public $version;
    /**
     * @var string The bundle namespace
     */
    public $namespace;
    /**
     * @var array[BundleDescriptor] List of bundles this bundle depends on
     */
    protected $dependencies = array();
    
    /**
     * @param $name Bundle name
     * @param $version Bundle version
     */
    public function __construct($name=null, $version=null)
    {
        $this->name = $name;
        $this->version = $version;
    }
    
    /**
     * Add a bundle this bundle depends on
     * @param BundleDescriptor $bundle
     */
    public function addDependency(BundleDescriptor $bundle)
    {
        $this->dependencies[] = $bundle;
    }
    
    /**
     * Get bundles this bundle depends on
     * @return array[BundleDescription]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
}
