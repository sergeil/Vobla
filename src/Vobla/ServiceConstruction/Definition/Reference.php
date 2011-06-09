<?php
 
/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Reference 
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
}
