<?php

namespace Vobla\Tools;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ClassLoader 
{
    static public function load($className)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('_', '\\', $className));

        require_once $path.'.php';
    }

    static public function register()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
    }
}
