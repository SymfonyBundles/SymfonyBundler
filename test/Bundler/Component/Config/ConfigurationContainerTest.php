<?php

namespace Bundler\Component\Config;

use Bundler\Component\Exception\UnknownBundleException;

class ConfigurationContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $client = $this->getMock("Bundler\\Component\\Repo\\RepositoryClientInterface",
                                 array(), array(), "RepositoryClient_Null");
        $container = new ConfigurationContainer($client);
        $this->assertEmpty($container->getBundles());
        
        return $container;
    }
    
    /**
     * @expectedException Bundler\Component\Exception\UnknownBundleException
     */
    public function testSetDefaultRepository()
    {
        $client = $this->getMock("Bundler\\Component\\Repo\\RepositoryClientInterface",
                                 array(), array(), "RepositoryClientStub_DefaultRepoTest");
        $client->expects($this->once())
               ->method("bundleExists")
               ->with($this->equalTo("DEFAULT_REPO_TEST"),
                      $this->anything(),
                      $this->anything())
               ->will($this->returnValue(false));
        $container = new ConfigurationContainer($client);
        $container->setDefaultRepository("DEFAULT_REPO_TEST");
        $container->addBundle("SomeBundle");
    }
    
    /**
     * 
     */
    public function testSetDefaultNamespace()
    {
        $client = $this->getMock("Bundler\\Component\\Repo\\RepositoryClientInterface",
                                 array(), array(), "RepositoryClientStub_DefaultNamespaceTest");
        $client->expects($this->any())
               ->method("bundleExists")
               ->will($this->returnValue(true));
        $client->expects($this->any())
               ->method('getConfigXml')
               ->will($this->returnCallback(function($namespace, $bundle, $version) {
                   $xml  = "<bundle name=\"$bundle\" version=\"$version\">";
                   $xml .=   "<dependencies />";
                   $xml .= "</bundle>";
                   return $xml;
               }));
        $container = new ConfigurationContainer($client);
        $container->setDefaultNamespace("DEFAULT_NAMESPACE_TEST");
        $container->addBundle("SomeBundle");
        $bundles = $container->getBundles();
        $this->assertEquals("DEFAULT_NAMESPACE_TEST", $bundles[0]->namespace);
    }
    
    /**
     * @expectedException Bundler\Component\Exception\UnknownBundleException
     */
    public function testInsertUnknownBundle()
    {
        $client = $this->getMock("Bundler\\Component\\Repo\\RepositoryClientInterface",
                                 array(), array(), "RepositoryClientStub_Empty");
        $client->expects($this->any())
               ->method('bundleExists')
               ->will($this->returnValue(false));
        $container = new ConfigurationContainer($client);
        $container->addBundle("UNKNOWN");
    }
    
    /**
     * 
     */
    public function testInsertBundle()
    {
        $client = $this->getMock("Bundler\\Component\\Repo\\RepositoryClientInterface",
                                 array(), array(), "RepositoryClientStub_Full");
        $client->expects($this->any())
               ->method('bundleExists')
               ->will($this->returnValue(true));
        $client->expects($this->any())
               ->method('getConfigXml')
               ->will($this->returnCallback(function($namespace, $bundle, $version) {
                   $xml = "<bundle name=\"$bundle\" version=\"$version\" namespace=\"Bundles\">";
                   if (strpos($bundle, "_depend") === false) {
                       $xml .= "<dependencies>
                                   <dependency name=\"".$bundle."_depend\" version=\"master\" repo=\"$namespace\" />
                                 </dependencies>";
                   } else {
                       $xml .= "<dependencies />";
                   }
                   $xml .= "</bundle>";
                   return $xml;
               }));
        $container = new ConfigurationContainer($client, "DEFAULT_REPO", "DEFAULT_NAMESPACE");
        $container->addBundle("bundle", "version");
        $bundles = $container->getBundles(true);
        $this->assertEquals(2, count($bundles));
        $this->assertEquals("bundle_depend", $bundles[0]->name);
        $this->assertEquals("bundle", $bundles[1]->name);
        return $container;
    }

    /**
     * @depends testInsertBundle
     * @expectedException Bundler\Component\Exception\VersionConflictException
     */
    public function testInsertBundleWithVersionConflict(ConfigurationContainer $container)
    {
        $client = $this->getMock("Bundler\\Component\\Repo\\RepositoryClientInterface",
                                 array(), array(), "RepositoryClientStub_Conflict");
        $client->expects($this->any())
               ->method('bundleExists')
               ->will($this->returnValue(true));
        $client->expects($this->any())
               ->method('getConfigXml')
               ->will($this->returnCallback(function($namespace, $bundle, $version) {
                   $xml = "<bundle name=\"$bundle\" version=\"$version\" namespace=\"Bundles\">
                   <dependencies /></bundle>";
                   return $xml;
               }));
        $container->setRepositoryClient($client);
        $container->addBundle("bundle_depend", "CONFLICT");
    }
}
