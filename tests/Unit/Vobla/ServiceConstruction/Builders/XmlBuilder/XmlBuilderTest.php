<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder;

require_once __DIR__.'/../../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Processor,
    Vobla\ServiceConstruction\Builders\XmlBuilder\ProcessorsProvider,
    Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class XmlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->mf = null;
    }

    public function testProcessXml()
    {
        $tc = $this;
        $cb = function($self, $xml) use ($tc) {
            $tc->assertEquals(
                'fooXml',
                $xml,
                'All aggregated xml-processors must receive the same piece of XML as XmlBuilder did.'
            );
        };

        $p1 = $this->mf->createTestCaseAware(Processor::CLAZZ)
        ->addMethod('processXml', $cb, 1)
        ->createMock();
        
        $p2 = $this->mf->createTestCaseAware(Processor::CLAZZ)
        ->addMethod('processXml', $cb, 1)
        ->createMock();

        $processors = array($p1, $p2);

        $processorsProvider = $this->mf->createTestCaseAware(ProcessorsProvider::CLAZZ)
        ->addMethod('getProcessors', function($self) use($processors) {
            return $processors;
        }, 1)
        ->createMock();

        $container = $this->mf->create(Container::clazz())->createMock();

        $xmlBuilder = new XmlBuilder($processorsProvider);
        $xmlBuilder->processXml('fooXml', $container);

    }
}
