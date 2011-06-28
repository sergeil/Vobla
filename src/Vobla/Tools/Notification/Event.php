<?php

namespace Vobla\Tools\Notification;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Event
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $arguments = array();

    public function getName()
    {
        return $this->name;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function __construct($name, array $arguments = array())
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
}
