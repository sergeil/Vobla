<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

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
    protected $ref4x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(id="someRef3xService")
     */
    protected $ref3x;
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(id="someDumbServiceId", scope="fooScope", qualifier="fooQualifier")
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
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor(
     *     params={
     *         @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter(name="aService", qualifier="fooQfr"),
     *         @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter(name="cService", id="megaCService")
     *     }
     * )
     */
    public function fooFactory($aService, $bService, $cService)
    {
        
    }

    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service
 */
class ClassWithTwoConstructors
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor
     */
    public function factory1()
    {

    }

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor
     */
    public function factory2()
    {
        
    }

    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service
 */
class ClassWithNoId
{
    static public function clazz()
    {
        return get_called_class();
    }
}