<?php

namespace Bundler\Component\Config;

use Bundler\Component\Repo\RepositoryClientInterface;

class RepositoryClientStub implements RepositoryClientInterface
{
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
