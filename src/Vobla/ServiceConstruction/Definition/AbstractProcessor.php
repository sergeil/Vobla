<?php

namespace Vobla\ServiceConstruction\Definition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
abstract class AbstractProcessor  implements DefinitionProcessor
{
    protected $serviceBuilder;

    public function init(ServiceBuilder $serviceBuilder)
    {
        $this->serviceBuilder = $serviceBuilder;
    }

    public function isResponsible($serviceDefinitionObject)
    {
    }

    public function process($serviceDefinitionObject)
    {
    }

}
