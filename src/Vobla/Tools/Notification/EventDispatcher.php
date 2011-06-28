<?php

namespace Vobla\Tools\Notification;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class EventDispatcher 
{
    /**
     * @var array
     */
    protected $listeners = array();

    public function subscribe($eventName, Listener $listener)
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = array();
        }

        $this->listeners[$eventName][spl_object_hash($listener)] = $listener;
    }

    public function notify(Event $event)
    {
        if (isset($this->listeners[$event->getName()])) {
            foreach ($this->listeners[$event->getName()] as $listener) {
                /* @var Listener $listener*/
                $listener->execute($event);
            }
        }
    }
}
