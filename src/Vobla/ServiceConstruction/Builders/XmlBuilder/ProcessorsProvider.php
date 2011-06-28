<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ProcessorsProvider
{
    const CLAZZ = 'Vobla\ServiceConstruction\Builders\XmlBuilder\ProcessorsProvider';

    /**
     * @return array
     */
    public function getProcessors();
}
