<?php

namespace Vobla;

use Vobla\ServiceConstruction\Assemblers\AssemblersProvider;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Configuration
{
    /**
     * @var \Vobla\ServiceConstruction\Assemblers\AssemblersProvider
     */
    protected $assemblersProvider;

    /**
     * @var \Vobla\ServiceConstruction\Definition\DefinitionProcessorsProvider 
     */
    protected $definitionProcessorsProvider;

    /**
     * @var \Vobla\Context\ContextScopeHandlersProvider
     */
    protected $contextScopeHandlersProvider;

    /**
     * @var \Vobla\ServiceLocating\ServiceLocatorsProvider
     */
    protected $serviceLocatorsProvider;

    /**
     * @param \Vobla\ServiceConstruction\Assemblers\AssemblersProvider $assemblersProvider
     */
    public function setAssemblersProvider(AssemblersProvider $assemblersProvider)
    {
        $this->assemblersProvider = $assemblersProvider;
    }

    /**
     * @return \Vobla\ServiceConstruction\Assemblers\AssemblersProvider
     */
    public function getAssemblersProvider()
    {
        return $this->assemblersProvider;
    }

    /**
     * @param \Vobla\Context\ContextScopeHandlersProvider $contextScopeHandlersProvider
     */
    public function setContextScopeHandlersProvider($contextScopeHandlersProvider)
    {
        $this->contextScopeHandlersProvider = $contextScopeHandlersProvider;
    }

    /**
     * @return \Vobla\Context\ContextScopeHandlersProvider
     */
    public function getContextScopeHandlersProvider()
    {
        return $this->contextScopeHandlersProvider;
    }

    /**
     * @param \Vobla\ServiceConstruction\Definition\DefinitionProcessorsProvider $definitionProcessorsProvider
     */
    public function setDefinitionProcessorsProvider($definitionProcessorsProvider)
    {
        $this->definitionProcessorsProvider = $definitionProcessorsProvider;
    }

    /**
     * @return \Vobla\ServiceConstruction\Definition\DefinitionProcessorsProvider
     */
    public function getDefinitionProcessorsProvider()
    {
        return $this->definitionProcessorsProvider;
    }

    public function setServiceLocatorsProvider($serviceLocatorsProvider)
    {
        $this->serviceLocatorsProvider = $serviceLocatorsProvider;
    }

    /**
     * @return ServiceLocating\ServiceLocatorsProvider
     */
    public function getServiceLocatorsProvider()
    {
        return $this->serviceLocatorsProvider;
    }

    public function validate()
    {
        
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
