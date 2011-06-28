<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import;

use Vobla\Container,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Processor,
    Vobla\Exception,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\XsdNamespaces;

/**
 * Processes <import resource=".*" /> tags.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ImportProcessor implements Processor
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\PathResolver
     */
    protected $pathResolver;

    /**
     * @var array
     */
    protected $resolvedResources = array();

    /**
     * @param \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\PathResolver $pathResolver
     */
    public function setPathResolver(PathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\PathResolver
     */
    public function getPathResolver()
    {
        if (null === $this->pathResolver) {
            $this->pathResolver = new CwdPathResolver();
        }

        return $this->pathResolver;
    }

    public function getResolvedResources()
    {
        return $this->resolvedResources;
    }

    public function resetIncludedResources()
    {
        $this->resolvedResources = array();
    }

    public function processXml($xmlBody, Container $container, XmlBuilder $xmlBuilder)
    {
        $xmlEl = new \SimpleXMLElement($xmlBody, 0, false, XsdNamespaces::CONTEXT);
        foreach ($xmlEl->children() as $childXml) {
            if ('import' == $childXml->getName()) {
                $resourcePath = $this->getPathResolver()->resolve(
                    $this->parseImportTag($childXml)
                );

                // handling circular importing
                if (!in_array($resourcePath, $this->resolvedResources)) {
                    $this->resolvedResources[] = $resourcePath;
                } else {
                    return;
                }

                $resourceBody = $this->loadResource($resourcePath);
                $xmlBuilder->processXml($resourceBody, $container);
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    protected function loadResource($path)
    {
        return file_get_contents($path);
    }

    public function parseImportTag(\SimpleXMLElement $importXml)
    {
        $importAttrsXml = $importXml->attributes();
        if (!isset($importAttrsXml['resource'])) {
            throw new Exception('"resource" attribute is mandatory!');
        }

        return (string)$importAttrsXml['resource'];
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
