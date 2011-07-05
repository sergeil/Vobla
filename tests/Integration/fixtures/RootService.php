<?php

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations as Vobla;

/**
 * @Vobla\Service(
 *     id="rootService",
 *     scope="prototype"
 * )
 */ 
class RootService 
{
    public $loggerFactory;

    /**
     * @Vobla\Autowired(id="cacheMap")
     */
    public $cacheMap;

    /**
     * @Vobla\AutowiredMap(
    *      type="Controller"
     * )
     */
    public $controllers;

    /**
     * @Vobla\Constructor(
     *     params={
     *         @Vobla\Parameter(name="loggerFactory", qualifier="loggingFacility")
     *     }
     * )
     */
    public function __construct($loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }
}
