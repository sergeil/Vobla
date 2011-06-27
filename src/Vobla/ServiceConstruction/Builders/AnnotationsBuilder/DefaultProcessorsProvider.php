<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\GeneralAttributesProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConstructorProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\PropertiesProcessor;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefaultProcessorsProvider implements ProcessorsProvider
{
    /**
     * @var array
     */
    protected $processors = array();

    public function __construct()
    {
        $this->processors = array(
            new GeneralAttributesProcessor(),
            new ConstructorProcessor(),
            new PropertiesProcessor()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessors()
    {
        return $this->processors;
    }

}
