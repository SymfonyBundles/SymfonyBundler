<?php

namespace Bundler\Component\Config;

class BundleDescriptorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $descriptor = new BundleDescriptor();
        $this->assertNull($descriptor->name);
        $this->assertNull($descriptor->version);
        $this->assertNull($descriptor->namespace);
        $this->assertEmpty($descriptor->getDependencies());
        return $descriptor;
    }
    
    /**
     * @dataProvider constructorProvider
     */
    public function testConstruct($name, $version, $namespace)
    {
        $descriptor = new BundleDescriptor($name, $version, $namespace);
        $this->assertEquals($name, $descriptor->name);
        $this->assertEquals($version, $descriptor->version);
        $this->assertEquals($namespace, $descriptor->namespace);
    }
    
    /**
     * @depends testEmpty
     * @dataProvider constructorProvider
     */
    public function testAddDependency($name, $version, $namespace, BundleDescriptor $descriptor)
    {
        $descriptor->name = $name."_parent";
        $descriptor->version = $version."_parent";
        $descriptor->namespace = $namespace."_parent";
        
        $dependency = new BundleDescriptor($name, $version, $namespace);
        $descriptor->addDependency($dependency);
        
        $dependencies = $descriptor->getDependencies();
        $this->assertEquals(1, count($dependencies));
        
        $this->assertEquals($name, $dependencies[0]->name);
        $this->assertEquals($version, $dependencies[0]->version);
        $this->assertEquals($namespace, $dependencies[0]->namespace);
    }
    
    public function constructorProvider()
    {
        return array(
            array("name", "version", "namespace"),
        );
    }
}
