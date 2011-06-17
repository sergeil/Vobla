<?php

namespace Vobla;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Configuration 
{
    public function getAssemblersProvider()
    {

    }

    public function getDefinitionProcessorsProvider()
    {
        
    }

    public function getContextScopeHandlers()
    {
        return array();
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
