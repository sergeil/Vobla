<?php

if (!defined('VOBLA_TESTING')) {
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(__DIR__.'/../src/'),
        realpath(__DIR__.'/../src/vendor/Doctrine/lib/'),
       realpath(__DIR__.'/../src/vendor/Moko/src/'),
        get_include_path()
    )));

    require_once 'Vobla/Tools/ClassLoader.php';

    \Vobla\Tools\ClassLoader::register();

    define('VOBLA_TESTING', true);
}

