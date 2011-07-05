<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class StaticPathResolver implements PathResolver
{
    /**
     * @var string
     */
    protected $prefix;

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function __construct($prefix)
    {
        $this->setPrefix($prefix);
    }

    /**
     * @param string $resourcePath
     * @return string
     */
    public function resolve($resourcePath)
    {
        return implode(DIRECTORY_SEPARATOR, array($this->getPrefix(), $resourcePath));
    }
}
