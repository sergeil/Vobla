<?php

namespace Vobla\HelloWorldPlugin;

use Vobla\ServiceConstruction\Assemblers\AbstractAssembler,
    Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class InjectingProcessor extends AbstractAssembler
{
    public function execute(AssemblersManager $assemblersManager, ServiceDefinition $definition, $obj = null)
    {
        if (get_class($obj) == 'RootService') {
            /* @var \RootService $obj */
            $obj->helloProperty = 'Hello World!';
        }

        return $assemblersManager->proceed($definition, $obj);
    }

}
