<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import;

/**
 * Implementations are capable of resolving relative path to a path
 * that can be used by file_get_contents function (See {@class ImportProcessor::readRequiredImportFile}).
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
interface PathResolver
{
    const CLAZZ = 'Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\PathResolver';

    /**
     * @param string $resourcePath
     * @return string
     */
    public function resolve($resourcePath);
}
