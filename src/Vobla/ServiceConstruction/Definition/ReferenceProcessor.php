<?php

namespace Vobla\ServiceConstruction\Definition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ReferenceProcessor extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    public function isResponsible($serviceDefinitionObject)
    {
        return is_object($serviceDefinitionObject) && $serviceDefinitionObject instanceof Reference;
    }

    /**
     * {@inheritdoc}
     */
    public function process($sdo)
    {
        return $this->serviceBuilder->getContainer()->getService($sdo->getServiceId());
    }
}
