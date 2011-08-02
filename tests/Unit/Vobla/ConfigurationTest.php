<?php

namespace Vobla;

require_once __DIR__.'/../../bootstrap.php';

use Vobla\Configuration,
    Vobla\ServiceConstruction\Assemblers\AssemblersProvider,
    Vobla\Context\ContextScopeHandlersProvider,
    Vobla\ServiceLocating\ServiceLocatorsProvider,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider as AnnotationProcessorsProvider,
    Vobla\ServiceConstruction\Builders\XmlBuilder\ProcessorsProvider as XmlProcessorsProvider;
/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\Configuration
     */
    protected $cfg;

    public function setUp()
    {
        $this->cfg = new Configuration();
    }

    public function tearDown()
    {
        $this->cfg = null;
    }

    public function testGetAssemblersProvider()
    {
        $this->assertType(
            AssemblersProvider::CLAZZ,
            $this->cfg->getAssemblersProvider(),
            sprintf(
                'If no instance of %s is explicitly provided then %s::getAssemblersProvider() method must create one automatically',
                AssemblersProvider::CLAZZ, Configuration::clazz()
            )
        );
    }

    public function testGetContextScopeHandlersProvider()
    {
        $this->assertType(
            ContextScopeHandlersProvider::CLAZZ,
            $this->cfg->getContextScopeHandlersProvider(),
            sprintf(
                'If no instance of %s is explicitly provided then %s::getContextScopeHandlersProvider() method must create one automatically',
                ContextScopeHandlersProvider::CLAZZ, Configuration::clazz()
            )
        );
    }

    public function testGetServiceLocatorsProvider()
    {
        $this->assertType(
            ServiceLocatorsProvider::CLAZZ,
            $this->cfg->getServiceLocatorsProvider(),
            sprintf(
                'If no instance of %s is explicitly provided then %s::getServiceLocatorsProvider() method must create one automatically',
                ServiceLocatorsProvider::CLAZZ, Configuration::clazz()
            )
        );
    }

    public function testGetAnnotationsBuilderProcessorsProvider()
    {
        $this->assertType(
            AnnotationProcessorsProvider::CLAZZ,
            $this->cfg->getAnnotationsBuilderProcessorsProvider(),
            sprintf(
                'If no instance of %s is explicitly provided then %s::getAnnotationsBuilderProcessorsProvider() method must create one automatically',
                AnnotationProcessorsProvider::CLAZZ, Configuration::clazz()
            )
        );
    }

    public function testGetXmlBuilderProcessorsProvider()
    {
        $this->assertType(
            XmlProcessorsProvider::CLAZZ,
            $this->cfg->getXmlBuilderProcessorsProvider(),
            sprintf(
                'If no instance of %s is explicitly provided then %s::getXmlBuilderProcessorsProvider() method must create one automatically',
                XmlProcessorsProvider::CLAZZ, Configuration::clazz()
            )
        );
    }
}
