<?php

namespace Vobla\ServiceLocating;

use Vobla\Container,
    Vobla\ServiceLocating\DefaultImpls\QualifierServiceLocator;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefaultServiceLocatorsProvider implements ServiceLocatorsProvider
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $locators;

    /**
     * @return \Vobla\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function __construct()
    {
        $this->locators = array(
            new QualifierServiceLocator()
        );
    }

    public function init(Container $container)
    {
        $this->container = $container;

        foreach ($this->locators as $locator) {
            $locator->init($container); // TODO ughm, what is a good place to initialize ?
        }
    }

    /**
     * @return void
     */
    public function getServiceLocators()
    {
        return $this->locators;
    }
}
