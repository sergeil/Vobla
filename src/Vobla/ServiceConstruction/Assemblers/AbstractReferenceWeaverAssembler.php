<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\Exception,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
abstract class AbstractReferenceWeaverAssembler implements Assembler
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    public function getContainer()
    {
        return $this->container;
    }

    public function init(Container $container)
    {
        $this->container = $container;
    }

    protected function derefenceParameter($param)
    {
        /* @var \Vobla\Container $c */
        $c = $this->getContainer();

        if ($param instanceof ServiceReference) {
            return $c->getServiceById($param->getServiceId());
        } else if ($param instanceof QualifiedReference) {
            return $c->getServiceByQualifier($param->getQualifier());
        }
    }
}
