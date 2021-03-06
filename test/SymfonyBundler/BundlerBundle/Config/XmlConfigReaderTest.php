<?php

namespace SymfonyBundler\BundlerBundle\Config;

class XmlConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConfigFileDoesNotExist()
    {
        $container = $this->getMock('SymfonyBundler\BundlerBundle\Config\ConfigurationContainer',
                                    null, array(), 'ConfigurationContainerStub_Null', false);
        $reader = new XmlConfigReader($container);
        $reader->read(__DIR__."/file-does-not-exist.xml");
    }
    
    public function testReadConfigFile()
    {
        $container = $this->getMock('SymfonyBundler\BundlerBundle\Config\ConfigurationContainer',
                                    array("setDefaultRepository", "setDefaultNamespace", "addBundle"),
                                    array(), 'ConfigurationContainerStub_ReadConfigFile', false);
        $container->expects($this->once())
                  ->method("setDefaultRepository")
                  ->with($this->equalTo("SymfonyBundles"));
        $container->expects($this->once())
                  ->method("setDefaultNamespace")
                  ->with($this->equalTo("Bundles"));
        $container->expects($this->exactly(2))
                  ->method("addBundle")
                  ->with($this->stringStartsWith("bundle_"),
                         $this->logicalOr($this->equalTo("master"), $this->equalTo("pr6")),
                         $this->logicalOr($this->equalTo(null), $this->equalTo("lbotsch")));
        $reader = new XmlConfigReader($container);
        $reader->read(__DIR__."/../../../fixtures/bundler.xml");
        $conf = $reader->getConfiguration();
        return $reader;
    }

    /**
     * @depends testReadConfigFile
     * @expectedException \InvalidArgumentException
     */
    public function testReadInvalidConfigFile(XmlConfigReader $reader)
    {
        $reader->read(__DIR__."/../../../fixtures/bundler.invalid.xml");
    }
    
    public function testReadXmlString()
    {
        $xml = "<container><option>VALUE</option></container>";
        $xml = XmlConfigReader::readXmlFromString($xml);
        $this->assertEquals("VALUE", (string)$xml->option[0]);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadInvalidXmlString()
    {
        $xml = "<container><option>VALUE</container>";
        $xml = XmlConfigReader::readXmlFromString($xml);
    }
}