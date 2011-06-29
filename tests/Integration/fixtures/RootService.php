<?php
 
/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(
 *     id="rootService",
 *     scope="prototype"
 * )
 */ 
class RootService 
{
    public $loggerFactory;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(id="cacheMap")
     */
    public $cacheMap;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor(
     *     params={
     *         @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter(name="loggerFactory", qualifier="loggingFacility")
     *     }
     * )
     */
    public function __construct($loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }
}
