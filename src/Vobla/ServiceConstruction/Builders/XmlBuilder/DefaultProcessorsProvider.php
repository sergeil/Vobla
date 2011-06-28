<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder;

use Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ConfigProcessor;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefaultProcessorsProvider implements ProcessorsProvider
{
    /**
     * @param array
     */
    protected $processors = array();

    public function __construct()
    {
        $this->processors = array(
            new ServiceProcessor(),
            new ConfigProcessor()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
