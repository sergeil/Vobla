<?php

use Doctrine\Common\Annotations\Annotation;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Autowired extends Annotation
{
    public $id;

    public $qualifier;

    static public function clazz()
    {
        return get_called_class();
    }
}
