<?php

namespace Bundler\Component\Repo;

class GithubRepositoryClientTest extends \PHPUnit_Framework_TestCase
{
    public function testBundleExists()
    {
        $client = $this->createClient();
        // Not cached
        $this->assertTrue($client->bundleExists("Namespace", "Bundle"));
        // Cached
        $this->assertTrue($client->bundleExists("Namespace", "Bundle"));
    }
    
    public function testGetConfigXml()
    {
        $client = $this->createClient();
        $client->getConfigXml("Namespace", "Bundle", "master");
        $this->assertTrue(true);
    }
    
    /**
     * @expectedException Bundler\Component\Exception\UnknownBundleException
     */
    public function testGetConfigXmlUnknownBundle()
    {
        $client = $this->createClient(false);
        $client->getConfigXml("SymfonyBundles", "UnknownBundle", "master");
    }
    
    private function createClient($local=true)
    {
        // Clear cache
        $cache = __DIR__."/../../../cache";
        $out = array();
        $result = 1;
        exec("rm -rf ".$cache, $out, $result);
        if ($result > 0) throw new RuntimeException("Cache dir could not be deleted!");
        
        $client = new GithubRepositoryClient($cache);
        if ($local) $client->setConfigFileFormatString(__DIR__."/../../../fixtures");
        return $client;
    }
}
