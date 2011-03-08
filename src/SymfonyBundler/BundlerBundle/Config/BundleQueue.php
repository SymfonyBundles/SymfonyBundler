<?php

namespace SymfonyBundler\BundlerBundle\Config;

use SymfonyBundler\BundlerBundle\Exception\VersionConflictException;

/**
 * BundleQueue is a datastructure that tracks bundle dependencies.
 * Add bundles to the queue using the insert method.
 * Build an SplPriorityQueue containing all the bundles you added and their
 * dependencies in the right order using the build method.
 */
class BundleQueue
{
    /**
     * Format:
     * [
     *   name => [bundle => BundleDescriptor, priority => int],
     *   ...
     * ]
     */
    protected $bundles = array();
    
    public function __construct()
    {
    }
    
    /**
     * Insert a bundle in the queue. All dependencies are added automatically.
     * @param $bundle The bundle to be added
     * @param $priority Initial priority the bundle is given if it doesn't already exist
     * 
     * @throws VersionConflictException When a bundle is present in different versions
     */
    public function insert(BundleDescriptor $bundle, $priority=0)
    {
        if (array_key_exists($bundle->name, $this->bundles)) {
            $this->incrementPriority($bundle);
        } else {
            $this->bundles[$bundle->name] = array(
                'bundle' => $bundle,
                'priority' => $priority,
            );
        }
        
        foreach ($bundle->getDependencies() as $d) {
            $this->insert($d, $priority + 1);
        }
    }
    
    /**
     * Build and return a priority queue of bundles
     * @return SplPriorityQueue
     */
    public function build()
    {
        $queue = new \SplPriorityQueue();
        foreach ($this->bundles as $bundle) {
            $queue->insert($bundle['bundle'], $bundle['priority']);
        }
        return $queue;
    }
    
    /**
     * Increments the priority of a bundle, when it is already in the queue
     * @param $bundle The bundle that triggers the increment
     * 
     * @throws VersionConflictException When the bundle in the queue has a different version
     */
    protected function incrementPriority(BundleDescriptor $bundle)
    {
        // Bundle should be present in the queue
        assert(array_key_exists($bundle->name, $this->bundles));
        
        // Check version
        // TODO: Check version within boundaries (minVersion, maxVersion)
        if ($this->bundles[$bundle->name]['bundle']->version != $bundle->version) {
            throw new VersionConflictException($bundle->name,
                array($bundle->version, $this->bundles[$bundle->name]['bundle']->version));
        }
        $this->bundles[$bundle->name]['priority']++;
    }
}
