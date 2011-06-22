<?php

namespace Vobla\Context\ScopeHandlers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class TransientHandler extends AbstractHandler
{
    public function dispense($id)
    {
        return $this->getContainer()->getServiceById($id);
    }

    public function register($id, $obj)
    {
    }

    public function contains($id)
    {
    }

    public function isDispenseResponsible($id)
    {
    }

    public function isRegisterResponsible($id, ServiceDefinition $serviceDefinition, $obj)
    {
        return $serviceDefinition->getScope() == 'transient';
    }

}
