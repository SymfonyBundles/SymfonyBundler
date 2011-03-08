<?php

namespace SymfonyBundler\BundlerBundle\Exception;

class UnknownBundleException extends BundlerException
{
    public $name;
    public $version;
    public $repo;
    
    public function __construct($name, $version, $repo)
    {
        $this->name = $name;
        $this->version = $version;
        $this->repo = $repo;
    }
}
