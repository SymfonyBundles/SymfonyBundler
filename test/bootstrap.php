<?php

require_once __DIR__."/UniversalClassLoader.php";

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'SymfonyBundler' => __DIR__.'/../src',
));
$loader->register();
