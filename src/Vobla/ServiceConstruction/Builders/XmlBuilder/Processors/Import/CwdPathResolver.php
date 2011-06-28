<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import;

/**
 * Resolves a path against PHP's CurrentWorkingDirectory.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class CwdPathResolver implements PathResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve($resourcePath)
    {
        return implode(DIRECTORY_SEPARATOR, array(getcwd(), $resourcePath));
    }

}
