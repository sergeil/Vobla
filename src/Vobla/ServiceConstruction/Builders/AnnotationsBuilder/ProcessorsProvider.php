<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ProcessorsProvider
{
    const CLAZZ = 'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider';

    /**
     * @return array
     */
    public function getProcessors();
}
