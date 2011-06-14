<?php
 
/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceBuilder
{
    protected $container;

    public function init(Container $container)
    {
        $this->container = $container;
    }

    public function process(ServiceDefition $serviceDefinition)
    {
        
    }
}
