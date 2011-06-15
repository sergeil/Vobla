<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ReferencesWeaverAssembler extends AbstractReferenceWeaverAssembler
{
    /**
     * @var \Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector
     */
    protected $referenceInjector;

   /**
     * @param \Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector $referenceInjector
     */
    public function setReferenceInjector($referenceInjector)
    {
        $this->referenceInjector = $referenceInjector;
    }

    /**
     * @return \Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector
     */
    public function getReferenceInjector()
    {
        return $this->referenceInjector;
    }
    
    public function __construct(ReferenceInjector $referenceInjector)
    {
        $this->referenceInjector = $referenceInjector;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AssemblersManager $assemblersManager, ServiceDefinition $definition, $obj = null)
    {
        foreach ($definition->getArguments() as $paramName=>$paramValue) {
            $this->getReferenceInjector()->inject($obj, $paramName, $paramValue, $definition);
        }

        return $assemblersManager->proceed($definition, $obj);
    }
}
