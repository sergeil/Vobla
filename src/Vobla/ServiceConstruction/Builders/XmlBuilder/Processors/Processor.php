<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors;

use Vobla\Container,
    Vobla\Tools\Notification\EventDispatcher,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface Processor
{
    public function processXml($xmlBody, Container $container, XmlBuilder $xmlBuilder);
}
