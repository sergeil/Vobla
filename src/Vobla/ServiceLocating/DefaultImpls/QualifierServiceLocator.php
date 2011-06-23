<?php

namespace Vobla\ServiceLocating\DefaultImpls;

use Vobla\ServiceLocating\AbstractServiceLocator,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class QualifierServiceLocator extends AbstractServiceLocator
{
    static public function createCriteria($qualifier)
    {
        return "byQualifier:$qualifier";
    }

    /**
     * @var array
     */
    protected $lookup = array();

    /**
     * {@inheritdoc}
     */
    public function analyze($id, ServiceDefinition $serviceDefinition)
    {
        $clr = $serviceDefinition->getQualifier();
        if ($clr != '') {
            $this->lookup[$clr] = $id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function locate($criteria)
    {
        $matches = array();
        if (preg_match('/^byQualifier:(.*)$/', $criteria, $matches)) {
            $qlr = $matches[1];
            return isset($this->lookup[$qlr]) ? $this->lookup[$qlr] : null;
        }

        return null;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
