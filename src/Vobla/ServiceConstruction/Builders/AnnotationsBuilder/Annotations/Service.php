<?php

use Doctrine\Common\Annotations\Annotation;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Service extends Annotation
{
    public $id;

    public $isAbstract = false;

    static public function clazz()
    {
        return get_called_class();
    }
}
