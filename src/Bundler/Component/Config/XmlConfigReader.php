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
    protected function parseFile($file)
    {
        $xml = $this->readXml($file);
        
        if ((string)$xml['defaultRepo']) {
            $this->configuration->setDefaultRepository((string)$xml['defaultRepo']);
        }
        if ((string)$xml['defaultNamespace']) {
            $this->configuration->setDefaultNamespace((string)$xml['defaultNamespace']);
        }
        
        foreach ($xml->bundle as $bundle) {
            $this->configuration->addBundle((string)$bundle['name'], (string)$bundle['version'], (string)$bundle['repo']);
        }
    }
    
    /**
     * Parse the xml config file
     * @param $file string Path/URL to the config file
     * @return SimpleXMLElement the parsed configuration
     * @throws InvalidArgumentException When unable to load config file
     */
    protected function readXml($file)
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
        
        return simplexml_import_dom($dom);
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
