<?php

namespace SymfonyBundler\BundlerBundle\Config;

class BundleQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $queue = new BundleQueue();
        $this->assertEquals(0, count($queue->build()));
        return $queue;
    }
    
    /**
     * @depends testEmpty
     */
    public function testInsertBundle(BundleQueue $queue)
    {
        $dependency = $this->getMock('SymfonyBundler\\BundlerBundle\\Config\\BundleDescriptor');
        $dependency->expects($this->any())
                   ->method('getDependencies')
                   ->will($this->returnValue(array()));
        $dependency->name = "dependencyName";
        $dependency->version = "dependencyVersion";
        $dependency->namespace = "dependencyNamespace";
        
        $bundle = $this->getMock('SymfonyBundler\\BundlerBundle\\Config\\BundleDescriptor');
        $bundle->expects($this->any())
               ->method('getDependencies')
               ->will($this->returnValue(array($dependency)));
        $bundle->name = "bundle1Name";
        $bundle->version = "bundle1Version";
        $bundle->namespace = "bundle1Namespace";
        $queue->insert($bundle);
        
        $bundle2 = $this->getMock('SymfonyBundler\\BundlerBundle\\Config\\BundleDescriptor');
        $bundle2->expects($this->any())
               ->method('getDependencies')
               ->will($this->returnValue(array($bundle)));
        $bundle2->name = "bundle2Name";
        $bundle2->version = "bundle2Version";
        $bundle2->namespace = "bundle2Namespace";
        $queue->insert($bundle2);
        
        $bundles = $queue->build();
        $bundles->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
        
        $bundle = $bundles->extract();
        $this->assertEquals("dependencyName", $bundle->name);
        
        $bundle = $bundles->extract();
        $this->assertEquals("bundle1Name", $bundle->name);
        
        $bundle = $bundles->extract();
        $this->assertEquals("bundle2Name", $bundle->name);
        
        return $queue;
    }

    /**
     * @depends testInsertBundle
     * @expectedException SymfonyBundler\BundlerBundle\Exception\VersionConflictException
     */
    public function testVersionConflict(BundleQueue $queue)
    {
        $dependency = $this->getMock('SymfonyBundler\\BundlerBundle\\Config\\BundleDescriptor');
        $dependency->expects($this->any())
                   ->method('getDependencies')
                   ->will($this->returnValue(array()));
        $dependency->name = "dependencyName";
        $dependency->version = "MISMATCH";
        $dependency->namespace = "dependencyNamespace";
        
        $bundle = $this->getMock('SymfonyBundler\\BundlerBundle\\Config\\BundleDescriptor');
        $bundle->expects($this->any())
               ->method('getDependencies')
               ->will($this->returnValue(array($dependency)));
        $bundle->name = "bundle3Name";
        $bundle->version = "bundle3Version";
        $bundle->namespace = "bundle3Namespace";
        
        // Should throw VersionConflictException
        $queue->insert($bundle);
    }
}
    