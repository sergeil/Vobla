<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder;

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service;

class SomeBarService
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired
     */
    public $ref5x;
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(id="someFooServiceId")
 */
class SomeFooService extends SomeBarService
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired
     */
    public $ref4x;
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(id="someDumbServiceId")
 */
class SomeDumbService extends SomeFooService
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired
     */
    protected $ref1x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(id="barbaz")
     */
    protected $ref2x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(qualifier="booz", id="bazbar")
     */
    protected $ref3x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations
     */
    public function fooFactory()
    {
        
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
