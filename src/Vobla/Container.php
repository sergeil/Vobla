<?php

namespace Vobla;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Container 
{
    protected $context;
    
    protected $definitionsHolder;

    public function getService($id)
    {
        $cx = $this->getContext();
        if (!$cx->has($id)) {
            $definition = $this->getDefinitionsHolder()->get($id);
            if (!$definition) {
                throw new ServiceNotFoundException("Unable to find a service '$id'.");
            }

            $obj = $this->getServiceBuilder()->process($definition);

            $cx->register($definition, $obj);
        } else {
            return $this->getContext()->get($id);
        }
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
