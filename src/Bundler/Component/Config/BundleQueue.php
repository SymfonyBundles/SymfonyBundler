<?php

namespace Bundler\Component\Config;

use Bundler\Component\Exception\BundlerException;
use Bundler\Component\Exception\VersionConflictException;

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
        $this->queue = new \SplPriorityQueue();
    }
    
    /**
     * Insert a bundle in the queue. All dependencies are added automatically.
     * @param $bundle The bundle to be added
     * @param $priority Initial priority the bundle is given if it doesn't already exist
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
    
    protected function incrementPriority(BundleDescriptor $bundle)
    {
        // Check presence of bundle
        if (!array_key_exists($bundle->name, $this->bundles)) {
            throw new BundlerException("Bundle $bundle->name not present in the queue!");
        }
        // Check version
        // TODO: Check version within boundaries (minVersion, maxVersion)
        if ($this->bundles[$bundle->name]->version != $bundle->version) {
            throw new VersionConflictException($bundle->name,
                array($bundle->version, $this->bundles[$bundle->name]->version));
        }
        $this->bundles[$bundle->name]['priority']++;
    }
}
