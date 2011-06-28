<?php

namespace Vobla\Tools\Notification;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface Listener
{
    public function execute(Event $event);
}
