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
     * @return string The configuration data
     * @throws Bundler\Component\Exception\UnknownBundleException When the bundle can't be found
     */
    public function getConfigXml($namespace, $bundle, $version);
    
    /**
     * Download a bundle and save it locally
     * @param string $target The target directory
     * @param string $namespace The bundle namespace
     * @param $bundle The bundle name
     * @param $version The bundle version
     * @return bool Whether the download was successful or not
     * @throws Bundler\Component\Exception\UnknownBundleException When the bundle can't be found
     */
    public function downloadBundle($target, $namespace, $bundle, $version);
    
}
