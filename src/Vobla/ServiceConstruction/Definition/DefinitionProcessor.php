<?php
 
/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface DefinitionProcessor
{
    public function init(ServiceBuilder $serviceBuilder);

    public function isResponsible($serviceDefinitionObject);

    public function process($serviceDefinitionObject);
}
