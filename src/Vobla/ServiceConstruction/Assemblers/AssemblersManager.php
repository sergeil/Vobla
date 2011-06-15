<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class AssemblersManager 
{
    public function proceed(ServiceDefinition $definition, $obj = null)
    {
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
