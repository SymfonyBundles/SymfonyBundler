<?php

namespace Bundler\Component\Config;

use Bundler\Component\Exception\BundlerException;
use Bundler\Component\Exception\UnknownBundleException;
use Bundler\Component\Exception\VersionConflictException;
use Bundler\Component\Repo\RepositoryClientInterface;

/**
 * Container that holds a Bundler configuration
 */
class ConfigurationContainer
{
    const BUNDLER_REPO = "SymfonyBundles";
    const BUNDLER_NAMESPACE = "Bundles";
    
    protected $defaultRepo;
    protected $defaultNamespace;
    /**
     * A client object to the 
     * @var Bundler\Component\Repo\RepositoryClientInterface
     */
    protected $repositoryClient;
    protected $bundleQueue;
    
    /**
     * Initializes a Container holding a Bundler configuration
     * @param $defaultRepo string The default repository to look for bundles
     */
    public function __construct(RepositoryClientInterface $repositoryClient, $defaultRepo=null, $defaultNamespace=null)
    {
        $this->defaultRepo = $defaultRepo === null ? self::BUNDLER_REPO : $defaultRepo;
        $this->defaultNamespace = $defaultNamespace === null ? self::BUNDLER_NAMESPACE : $defaultNamespace;
        $this->repositoryClient = $repositoryClient;
        $this->bundleQueue = new BundleQueue();
    }
    
    /**
     * Set the repository client implementation
     * @param Bundler\Component\Repo\RepositoryClientInterface $repositoryClient 
     */
    public function setRepositoryClient(RepositoryClientInterface $repositoryClient)
    {
        $this->repositoryClient = $repositoryClient;
    }
    
    /**
     * Set the default repository to look into for bundles
     * @param string $repo 
     */
    public function setDefaultRepository($repo)
    {
        $this->defaultRepo = $repo;
    }
    
    /**
     * Set the default namespace to put bundles into
     * @param string $namespace
     */
    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;
    }
    
    /**
     * Add a bundle to the configuration
     * @param $name The bundle name
     * @param $version The bundle version (Git tag or 'master')
     * @param $repo The repository to get the bundle from (a github username/organization)
     * 
     * @throws UnknownBundleException When the bundle can't be found
     * @throws VersionConflictException When the bundle or a dependency is already included with another version
     */
    public function addBundle($name, $version="master", $repo=null)
    {
        if ($repo === null) {
            $repo = $this->defaultRepo;
        }
        
        if (false === $this->repositoryClient->bundleExists($repo, $name, $version)) {
            throw new UnknownBundleException($name, $version, $repo);
        }
        
        try {
            $bundle = $this->readBundleConfiguration($name, $version, $repo);
            // Add bundle to the queue
            $this->bundleQueue->insert($bundle);
        } catch (VersionConflictException $e) {
            // Handle Version conflict
            throw $e;
        }
    }
    
    /**
     * Get all bundles defined in the configuration including their dependencies.
     * The bundles are returned in the order of dependency (i.e. dependencies first)
     * @return array Bundles in order of dependency
     */
    public function getBundles()
    {
        $bundles = array();
        $queue = $this->bundleQueue->build();
        foreach ($queue as $bundle) {
            $bundles[] = $bundle;
        }
        return $bundles;
    }
    
    /**
     * Reads a bundle.xml configuration file into a BundleDescriptor
     * Example: bundle.xml
     * <bundle name="TwitterBundle" version="pr6" namespace="Bundles">
     *   <description>TwitterBundle description</description>
     *   <dependencies>
     *     <dependency name="UserBundle" version="pr6" repo="SymfonyBundles" />
     *     <dependency name="OAuthBundle" version="pr6" />
     *   </dependencies>
     * </bundle>
     * @return BundleDescriptor
     */
    protected function readBundleConfiguration($name, $version, $repo=null)
    {
        if ($repo === null) $repo = $this->defaultRepo;
        //$url = 'https://github.com/'.$repo.'/'.$name.'/raw/'.$version.'/bundle.xml';
        $xml = $this->repositoryClient->getConfigXml($repo, $name, $version);
        $xml = XmlConfigReader::readXmlFromString($xml);
        $bundle = new BundleDescriptor();
        $bundle->name = (string)$xml['name'];
        $bundle->version = (string)$xml['version'];
        $bundle->namespace = (string)$xml['namespace'] ? (string)$xml['namespace'] : $this->defaultNamespace;
        
        foreach ($xml->dependencies->dependency as $d) {
            $name = (string)$d['name'][0];
            $version = (string)$d['version'][0];
            $repo = (string)$d['repo'] ? (string)$d['repo'] : null;
            $bundle->addDependency(
                $this->readBundleConfiguration($name, $version, $repo));
        }
        return $bundle;
    }
}
