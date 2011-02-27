<?php

namespace Bundler\Component\Config;


/**
 * Bundler configuration reader for the xml format
 * Example bundler.xml:
 * <bundler defaultRepo="SymfonyBundles">
 *   <bundle name="CryptoBundle" version="master" repo="lbotsch" />
 *   <bundle name="SpecBundle" version="pr6" />
 * </bundler>
 */
class XmlConfigReader extends ConfigReader
{
    /**
     * {@inheritdoc}
     */
    public function read($file)
    {
        $xml = $this->readXml($file);
        $config = new ConfigurationContainer($xml['defaultRepo']);
        foreach ($xml->bundle as $bundle) {
            $config->addBundle($bundle['name'], $bundle['version'], $bundle['repo']);
        }
        return $config;
    }
    
    /**
     * Read a file in xml format into a php object
     * @param $file Path or URL to a XML file
     * @return SimpleXMLElement The root element
     */
    public function readXml($file)
    {
        return $this->parseFile($file);
    }
    
    /**
     * Parse the xml config file
     * @param $file string Path/URL to the config file
     * @return SimpleXMLElement the parsed configuration
     * @throws InvalidArgumentException When unable to load config file
     */
    protected function parseFile($file)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $opts = array(
            'http' => array(
                'user_agent' => 'PHP/SymfonyBundler',
            )
        );
        $context = stream_context_create($opts);
        libxml_set_streams_context($context);
        
        if (!$dom->load($file, LIBXML_COMPACT)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        $dom->validateOnParse = true;
        $dom->normalizeDocument();
        libxml_use_internal_errors(false);
        
        return simplexml_import_dom($dom, 'Symfony\\Component\\DependencyInjection\\SimpleXMLElement');
    }
    
    /**
     * Returns an array of XML errors.
     *
     * @return array
     */
    protected function getXmlErrors()
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();

        return $errors;
    }
}
