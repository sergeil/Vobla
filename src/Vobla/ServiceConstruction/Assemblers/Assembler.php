<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface Assembler
{
    public function init(Container $container);

    public function execute(AssemblersManager $assemblersManager, ServiceDefinition $definition, $obj = null);
}
