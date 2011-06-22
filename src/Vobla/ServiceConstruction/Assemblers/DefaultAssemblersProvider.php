<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\Injection\FieldAccessReferenceInjector;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefaultAssemblersProvider implements AssemblersProvider
{
    /**
     * @var array
     */
    protected $assemblers;

    public function __construct()
    {
        $this->assemblers = array(
            new ObjectFactoryAssembler(),
            new ReferencesWeaverAssembler(new FieldAccessReferenceInjector())
        );
    }

    /**
     * @TODO throw an exception if no initialization done yet
     */
    public function getAssemblers()
    {
        return $this->assemblers;
    }

    public function init(Container $container)
    {
        foreach ($this->assemblers as $assembler) {
            $assembler->init($container);
        }
    }
}
