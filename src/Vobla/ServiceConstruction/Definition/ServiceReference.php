<?php

namespace Vobla\ServiceConstruction\Definition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceReference
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * @param string $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function __construct($serviceId = null)
    {
        $this->setServiceId($serviceId);
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
