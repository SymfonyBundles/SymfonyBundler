<?php

namespace Bundler\Component\Repo;

interface RepositoryClientInterface
{
    /**
     * Checks if a bundle exists in the repository
     * @param $namespace The namespace to search for the bundle
     * @param $bundle The bundle name
     * @param $version If specified, look for a specific version
     * @return bool Whether or not the bundle exists
     */
    public function bundleExists($namespace, $bundle, $version=null);
    
    /**
     * Gets a bundle configuration from the repository
     * @param $namespace The bundle namespace
     * @param $bundle The bundle name
     * @param $version The bundle version
     * @return SimpleXmlElement The configuration
     * @throws Bundler\Component\Exception\UnknownBundleException When the bundle can't be found
     */
    public function getConfigXml($namespace, $bundle, $version);
    
}