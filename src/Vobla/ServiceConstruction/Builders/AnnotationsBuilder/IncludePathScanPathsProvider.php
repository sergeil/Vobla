<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder;

use Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class IncludePathScanPathsProvider implements ScanPathsProvider
{
    /**
     * @return array
     */
    public function getScanPaths(Container $container)
    {
        return explode(PATH_SEPARATOR, get_include_path());
    }

}
