<?php
 
/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class CompositeContext 
{
    /**
     * @var array
     */
    protected $scopeHandlers = array();

    public function register($id, $obj)
    {
        foreach ($this->scopeHandlers as $handler) {
            if ($handler->isRegisterResponsible($id, $obj)) {
                $handler->register($id, $obj);

                return;
            }
        }
    }

    public function dispense($id)
    {
        foreach ($this->scopeHandlers as $handler) {
            if ($handler->isDispenseResponsible($id)) {
                return $handler->dispense($id);
            }
        }
    }
}
