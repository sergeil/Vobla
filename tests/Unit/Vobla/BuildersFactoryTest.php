<?php

namespace Vobla;

require_once __DIR__.'/../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class BuildersFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\BuildersFactory
     */
    protected $bf;

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->bf = new BuildersFactory();
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->bf = null;
        $this->mf = null;
    }

    /**
     * @expectedException Vobla\InitializationException
     */
    public function testGetContainer_nonInitialized()
    {
        $this->bf->getContainer();
    }

    public function testCreateXmlBuilder()
    {
        $c = $this->mf->createTestCaseAware(Container::clazz())->createMock();
        $this->bf->init($c);

        $pp = new \stdClass();

        $xmlBuilder = $this->bf->createXmlBuilder($pp);
        $this->assertType(XmlBuilder::clazz(), $xmlBuilder);
        $this->assertSame($c, $xmlBuilder->getContainer());
        $this->assertSame($pp, $xmlBuilder->getProcessorsProvider());
    }

    public function testCreateAnnotationsBuilder()
    {
        $c = $this->mf->createTestCaseAware(Container::clazz())->createMock();
        $this->bf->init($c);

        $pp = new \stdClass();

        $annBuilder = $this->bf->createAnnotationsBuilder($pp);
        $this->assertType(AnnotationsBuilder::clazz(), $annBuilder);
        $this->assertSame($c, $annBuilder->getContainer());
        $this->assertSame($pp, $annBuilder->getProcessorsProvider());
    }
}
