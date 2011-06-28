<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder;

use Vobla\Tools\Notification\EventDispatcher,
    Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class XmlBuilder
{
    /**
     * @var \Vobla\Tools\Notification\EventDispatcher
     */
    protected $eventDispatcher;
    
    /**
     * @param \Vobla\Tools\Notification\EventDispatcher $eventDispatcher
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return \Vobla\Tools\Notification\EventDispatcher
     */
    public function getEventDispatcher()
    {
        if (null === $this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }

    public function processXml($xmlBody, Container $container)
    {
        
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
