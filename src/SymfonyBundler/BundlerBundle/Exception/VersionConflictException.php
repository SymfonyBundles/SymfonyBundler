<?php

namespace SymfonyBundler\BundlerBundle\Exception;

class VersionConflictException extends BundlerException
{
    public $name;
    public $versions;
    
    public function __construct($name, $versions)
    {
        $this->name = $name;
        $this->versions = $versions;
    }
}
