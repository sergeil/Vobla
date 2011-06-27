<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Parameter extends Annotation
{
    public $name;

    public $qualifier;

    public $id;

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
