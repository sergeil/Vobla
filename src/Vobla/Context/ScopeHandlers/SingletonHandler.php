<?php

namespace Vobla\Context\ScopeHandlers;

use Vobla\Context\ContextScopeHandler,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class SingletonHandler implements ContextScopeHandler
{
    /**
     * @var array
     */
    protected $objects = array();

    public function dispense($id)
    {
        return $this->contains($id) ? $this->objects[$id] : null;
    }

    public function register($id, $obj)
    {
        $this->objects[$id] = $obj;
    }

    public function contains($id)
    {
        return isset($this->objects[$id]);
    }

    public function isDispenseResponsible($id)
    {
        return $this->contains($id);
    }

    public function isRegisterResponsible($id, ServiceDefinition $serviceDefinition, $obj)
    {
        return $serviceDefinition->getScope() == 'singleton';
    }

}
