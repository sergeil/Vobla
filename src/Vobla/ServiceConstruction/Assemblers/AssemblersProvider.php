<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface AssemblersProvider
{
    const CLAZZ = 'Vobla\ServiceConstruction\Assemblers\AssemblersProvider';

    public function init(Container $container);

    public function getAssemblers();
}
