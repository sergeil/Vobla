<?php

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations as Vobla;

/**
 * @Vobla\Service(
 *     id="cacheMap",
 *     scope="prototype"
 * )
 */ 
class CacheMap 
{
    /**
     * @Vobla\AutowiredMap(
     *     tags={"cacheDriver"}
     * )
     */
    public $cacheDrivers;
}
