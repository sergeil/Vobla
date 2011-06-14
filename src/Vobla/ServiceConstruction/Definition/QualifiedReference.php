<?php

namespace Vobla\ServiceConstruction\Definition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class QualifiedReference 
{
    /**
     * @var string
     */
    protected $qualifier;

    /**
     * @param string $qualifier
     */
    public function setQualifier($qualifier)
    {
        $this->qualifier = $qualifier;
    }

    /**
     * @return string
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * @param string $qualifier
     */
    public function __construct($qualifier)
    {
        $this->setQualifier($qualifier);
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
