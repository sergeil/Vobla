<?php

namespace Vobla\ServiceLocating;

use Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ServiceLocatorsProvider
{
    const CLAZZ = 'Vobla\ServiceLocating\ServiceLocatorsProvider';

    public function init(Container $container);

    /**
     * @return array
     */
    public function getServiceLocators();
}
