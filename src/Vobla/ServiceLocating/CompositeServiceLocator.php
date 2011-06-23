<?php

namespace Vobla\ServiceLocating;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class CompositeServiceLocator extends AbstractServiceLocator
{
    /**
     * @var array
     */
    protected $cachedLocators = array();

    /**
     * {@inheritdoc}
     */
    public function init(Container $container)
    {
        parent::init($container);

        $this->cachedLocators = $this->container->getConfiguration()->getServiceLocatorsProvider()->getServiceLocators();
    }

    /**
     * @param mixed $criteria
     * @return mixed|false
     */
    public function locate($criteria)
    {
        foreach ($this->cachedLocators as $locator) {
            $result = $locator->locate($criteria);
            if ($result !== false) {
                return $result;
            }
        }

        return false;
    }

    public function analyze($id, ServiceDefinition $serviceDefinition)
    {
        foreach ($this->cachedLocators as $locator) {
            $locator->analyze($id, $serviceDefinition);
        }
    }

}
