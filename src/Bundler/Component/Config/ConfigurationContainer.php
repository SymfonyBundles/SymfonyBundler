<?php

namespace Bundler\Component\Config;

use Bundler\Component\Exception\BundlerException;
use Bundler\Component\Exception\UnknownBundleException;

/**
 * Container that holds a Bundler configuration
 */
class ConfigurationContainer
{
    const BUNDLER_REPO = "";
    
    protected $defaultRepo;
    protected $github;
    protected $bundleQueue;
    
    /**
     * Initializes a Container holding a Bundler configuration
     * @param $defaultRepo string The default repository to look for bundles
     */
    public function __construct(string $defaultRepo=null)
    {
        $this->defaultRepo = $defaultRepo === null ? self::BUNDLER_REPO : $defaultRepo;
        // Setup github api
        //$this->github = new Github($user, $key);
        $this->bundleQueue = new BundleQueue();
    }
    
    /**
     * Add a bundle to the configuration
     * @param $name The bundle name
     * @param $version The bundle version (Git tag or 'master')
     * @param $repo The repository to get the bundle from (a github username/organization)
     * @throws UnknownBundleException When the bundle can't be found
     * @throws VersionConflictException When the bundle or a dependency is already included with another version
     */
    public function addBundle(string $name, string $version="master", string $repo=null)
    {
        if ($repo === null) {
            $repo = $this->defaultRepo;
        }
        try {
            $bundle = $this->readBundleConfiguration($name, $version, $repo);
            // Add bundle to the queue
            $this->bundleQueue->insert($bundle);
        } catch (UnknownBundleException $e) {
            throw $e;
        } catch (VersionConflictException $e) {
            // Handle Version conflict
        } catch (BundlerException $e) {
            throw new UnknownBundleException($name, $version, $repo);
        }
    }
    
    /**
     * Get all bundles defined in the configuration including their dependencies.
     * The bundles are returned in the order of dependency (i.e. dependencies first)
     * @param $secure If set to false, this function cannot be called anymore, but it is faster
     * @return array Bundles in order of dependency
     */
    public function getBundles($secure = false)
    {
        $bundles = array();
        $queue = $secure ? clone $this->bundleQueue : $this->bundleQueue;
        foreach ($queue as $bundle) {
            $bundles[] = $bundle;
        }
        return $bundles;
    }
    
    /**
     * Reads a bundle.xml configuration file into a BundleDescriptor
     * Example: bundle.xml
     * <bundle name="TwitterBundle" version="pr6" namespace="SymfonyBundles">
     *   <description>TwitterBundle description</description>
     *   <dependencies>
     *     <dependency name="UserBundle" version="pr6" repo="SymfonyBundles" />
     *     <dependency name="OAuthBundle" version="pr6" />
     *   </dependencies>
     * </bundle>
     * @return BundleDescriptor
     * @throws BundlerException
     */
    protected function readBundleConfiguration(string $name, string $version, string $repo=null)
    {
        if ($repo === null) $repo = $this->defaultRepo;
        $url = 'https://github.com/'.$repo.'/'.$name.'/raw/'.$version.'/bundle.xml';
        $xmlReader = new XmlConfigReader();
        $xml = $xmlReader->readXml($url);
        $bundle = new BundleDescriptor();
        $bundle->name = $xml['name'];
        $bundle->version = $xml['version'];
        $bundle->namespace = $xml['namespace'];
        foreach ($xml->dependencies->dependency as $d) {
            $bundle->addDependency(
                $this->readBundleConfiguration($d['name'], $d['version'], $d['repo']));
        }
        return $bundle;
    }
}
