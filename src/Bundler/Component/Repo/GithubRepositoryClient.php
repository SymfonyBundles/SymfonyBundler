<?php

namespace Bundler\Component\Repo;

use Bundler\Component\Exception\UnknownBundleException;

class GithubRepositoryClient implements RepositoryClientInterface
{
    protected $configFileFormatString = "https://github.com/%s/%s/raw/%s"; // namespace, bundle, version
    protected $publicRepoUrlFormatString = "git://github.com/%s/%s.git"; // namespace, bundle
    protected $cacheDir;
    protected $createSubmodules = true;
    protected $gitRoot;
    
    /**
     * @param string $cacheDir The cache directory
     * @param bool $createSubmodules Whether to create Git submodules or not
     * @param string $gitRoot The root of the git repository to include the submodules in
     */
    public function __construct($cacheDir=null, $createSubmodules=true, $gitRoot=null)
    {
        if ($cacheDir == null) {
            $cacheDir = __DIR__."/../../../../cache";
        }
        if (!file_exists($cacheDir)) {
            if (!mkdir($cacheDir)) {
                return \InvalidArgumentException("Cache directory doesn't exist and can't be created");
            }
        }
        if (!is_dir($cacheDir)) {
            return \InvalidArgumentException("The Cache path exists but is not a directory.");
        }
        $this->cacheDir = $cacheDir;
        $this->createSubmodules = $createSubmodules;
        $this->gitRoot = realpath($gitRoot);
    }
    
    /**
     * The client will use git to create submodules for each bundle
     * @param bool $createSubmodules
     */
    public function setCreateSubmodules($createSubmodules)
    {
        $this->createSubmodules = $createSubmodules;
    }
    
    /**
     * Set the format string for the base bundle path
     * The format string can include the following placeholders:
     *      %1$s --> The namespace
     *      %2$s --> The bundle name
     *      %3$s --> The bundle version
     * @param $formatString
     */
    public function setConfigFileFormatString($formatString)
    {
        $this->configFileFormatString = $formatString;
    }
    
    /**
     * {@inheritdoc}
     */
    public function bundleExists($namespace, $bundle, $version=null)
    {
        $exists = false;
        
        if ($version === null) {
            $version = "master";
        }
        
        // Get the bundle.xml file from cache if possible
        if ($this->isCached($namespace, $bundle, $version)) {
            return true;
        } else {
            $url = sprintf($this->configFileFormatString, $namespace, $bundle, $version)."/bundle.xml";
            $opts = array(
              'http'=>array(
                'method' => "GET",
                'user_agent' => "PHP/SymfonyBundler",
                'ignore_errors' => true,
              )
            );
            $context = stream_context_create($opts);
            $data = file_get_contents($url, false, $context); 
            $exists = $data !== false;
            
            if (strpos($url, "http") === 0) {
                // "HTTP/1.1 200 OK"
                $status = $http_response_header[0];
                $status = explode(" ", $status);
                $exists = ($status[1] == "200");
            }
            
            if ($exists) {
                $this->writeCache($namespace, $bundle, $version, $data);
            }
        }
        return $exists;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConfigXml($namespace, $bundle, $version)
    {
        // The config file gets cached in bundleExists
        if (!$this->bundleExists($namespace, $bundle, $version)) {
            throw new UnknownBundleException($bundle, $version, $namespace);
        }
        
        return $this->readCache($namespace, $bundle, $version);
    }
    
    /**
     * {@inheritdoc}
     */
    public function downloadBundle($target, $namespace, $bundle, $version)
    {
        if (!$this->bundleExists($namespace, $bundle, $version)) {
            throw new UnknownBundleException($bundle, $version, $namespace);
        }
        
        $target = realpath($target);
        
        $repoUrl = sprintf($this->publicRepoUrlFormatString, $namespace, $bundle);
        if ($this->createSubmodules) {
            // Add the bundle as a git submodule
            $rel_path = str_replace($this->gitRoot, "", $target);
            if ($rel_path[0] == "/") $rel_path = substr($rel_path, 1);
            $data = "\n[submodule \"$rel_path\"]\n"
                  . "\t\tpath = $rel_path\n"
                  . "\t\turl = $repoUrl\n";
            file_put_contents($this->gitRoot."/.gitmodules", $data, FILE_APPEND);
            $oldCwd = getcwd();
            chdir($this->gitRoot);
            $output = shell_exec("git submodule update --init");
            chdir($oldCwd);
        } else {
            // Clone the bundle
            $output = shell_exec("git clone \"$repoUrl\" \"$target\"");
            $oldCwd = getcwd();
            chdir($target);
            $output = shell_exec("git checkout $version");
            chdir($oldCwd);
        }
        
        return true;
    }
    
    private function isCached($namespace, $bundle, $version)
    {
        return file_exists($this->getCachedFile($namespace, $bundle, $version));
    }
    
    private function getCachedFile($namespace, $bundle, $version)
    {
        return $this->cacheDir.sprintf("/%s-%s-%s-bundle.xml",
                        strtolower($namespace),
                        strtolower($bundle),
                        strtolower($version));
    }
    
    private function readCache($namespace, $bundle, $version)
    {
        return file_get_contents($this->getCachedFile($namespace, $bundle, $version));
    }
    
    private function writeCache($namespace, $bundle, $version, $data)
    {
        return false !== file_put_contents($this->getCachedFile($namespace, $bundle, $version), $data);
    }
}
