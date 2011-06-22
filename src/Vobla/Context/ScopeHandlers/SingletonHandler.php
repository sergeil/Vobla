<?php

namespace Vobla\Context\ScopeHandlers;

use Vobla\Context\ContextScopeHandler,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class SingletonHandler extends AbstractHandler
{
    /**
     * @var array
     */
    protected $services = array();

    public function dispense($id)
    {
        return $this->contains($id) ? $this->services[$id] : null;
    }

    public function register($id, $obj)
    {
        $this->services[$id] = $obj;
    }

    public function contains($id)
    {
        return isset($this->services[$id]);
    }

    public function isDispenseResponsible($id)
    {
        return $this->contains($id);
    }

    public function isRegisterResponsible($id, ServiceDefinition $serviceDefinition, $obj)
    {
        return in_array($serviceDefinition->getScope(), array('singleton', '')); // default scope
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
