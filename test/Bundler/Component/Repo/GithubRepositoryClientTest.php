<?php

namespace Bundler\Component\Repo;

class GithubRepositoryClientTest extends \PHPUnit_Framework_TestCase
{
    public function testBundleExists()
    {
        $client = $this->createClient();
        // Not cached
        $this->assertTrue($client->bundleExists("Namespace", "Bundle"));
        // Cached
        $this->assertTrue($client->bundleExists("Namespace", "Bundle"));
    }
    
    public function testGetConfigXml()
    {
        $client = $this->createClient();
        $client->getConfigXml("Namespace", "Bundle", "master");
        $this->assertTrue(true);
    }
    
    /**
     * @expectedException Bundler\Component\Exception\UnknownBundleException
     */
    public function testGetConfigXmlUnknownBundle()
    {
        $this->createClientWithoutKnowledge()->getConfigXml("", "", "");
    }
    
    public function testDownloadBundleAsModule()
    {
        $testTmp = __DIR__."/../../../tmp";
        if (file_exists($testTmp)) {
            shell_exec("rm -rf \"$testTmp\"");
        }
        
        mkdir($testTmp);
        $testTmp = realpath($testTmp);
        $gitRoot = $testTmp."/root_repo";
        $gitBundle = $testTmp."/bundle_repo";
        // init git repos
        shell_exec("git init \"$gitRoot\"");
        shell_exec("git init \"$gitBundle\"");
        // add content to the repos
        $oldCwd = getcwd();
        chdir($gitRoot);
        shell_exec("touch README && git add README && git commit -m \"Initial commit\"");
        chdir($oldCwd);
        
        $oldCwd = getcwd();
        chdir($gitBundle);
        shell_exec("touch README && git add README && git commit -m \"Initial commit\"");
        // Create a version tag
        shell_exec("git tag -a -m \"Test version tag\" test_version");
        chdir($oldCwd);
        
        $client = $this->createClient();
        $client->setCreateSubmodules(true);
        $client->setGitRoot($gitRoot);
        $client->setPublicRepoFormatString("file://".$gitBundle);
        $client->downloadBundle($gitRoot."/Bundles/Bundle", "Namespace", "Bundle", "test_version");
        
        $this->assertTrue(file_exists($gitRoot."/Bundles/Bundle/README"));
        
        // cleanup
        shell_exec("rm -rf \"$testTmp\"");
    }

    public function testDownloadBundleAsStandalone()
    {
        $testTmp = __DIR__."/../../../tmp";
        if (file_exists($testTmp)) {
            shell_exec("rm -rf \"$testTmp\"");
        }
        
        mkdir($testTmp);
        $testTmp = realpath($testTmp);
        $gitBundle = $testTmp."/bundle_repo";
        // init git repo
        shell_exec("git init \"$gitBundle\"");
        // add content to the repos
        $oldCwd = getcwd();
        chdir($gitBundle);
        shell_exec("touch README && git add README && git commit -m \"Initial commit\"");
        // Create a version tag
        shell_exec("git tag -a -m \"Test version tag\" test_version");
        chdir($oldCwd);
        
        $cloneDir = $testTmp."/project";
        mkdir($cloneDir);
        $cloneDir = $cloneDir."/Bundles/Bundle";
        
        $client = $this->createClient();
        $client->setCreateSubmodules(false);
        $client->setPublicRepoFormatString("file://".$gitBundle);
        $client->downloadBundle($cloneDir, "Namespace", "Bundle", "test_version");
        
        $this->assertTrue(file_exists($cloneDir."/README"));
        
        // cleanup
        shell_exec("rm -rf \"$testTmp\"");
    }

    /**
     * @expectedException Bundler\Component\Exception\UnknownBundleException
     */
    public function testDownloadUnknownBundle()
    {
        $this->createClientWithoutKnowledge()->downloadBundle("", "", "", "");
    }
    
    private function createClient($local=true)
    {
        // Clear cache
        $cache = __DIR__."/../../../cache";
        $out = array();
        $result = 1;
        exec("rm -rf ".$cache, $out, $result);
        if ($result > 0) throw new RuntimeException("Cache dir could not be deleted!");
        
        $client = new GithubRepositoryClient($cache);
        if ($local) $client->setConfigFileFormatString(__DIR__."/../../../fixtures");
        return $client;
    }
    
    private function createClientWithoutKnowledge()
    {
        $this->assertFalse(file_exists(__DIR__."/file_does_not_exist"));
        $client = new GithubRepositoryClient();
        $client->setConfigFileFormatString(__DIR__."/file_does_not_exist");
        return $client;
    }
}
