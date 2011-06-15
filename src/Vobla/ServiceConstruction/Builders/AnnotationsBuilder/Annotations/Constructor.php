<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Constructor extends Annotation
{
    public $params = array();

    static public function clazz()
    {
        return get_called_class();
    }
}
