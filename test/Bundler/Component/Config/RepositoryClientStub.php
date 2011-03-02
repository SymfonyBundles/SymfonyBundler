<?php

namespace Bundler\Component\Config;

use Bundler\Component\Repo\RepositoryClientInterface;

abstract class RepositoryClientStub implements RepositoryClientInterface
{
    public function __construct() {}
    
    /**
     * {@inheritdoc}
     */
    public function bundleExists($namespace, $bundle, $version=null)
    {
        return null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConfigXml($namespace, $bundle, $version)
    {
        return null;
    }
}
