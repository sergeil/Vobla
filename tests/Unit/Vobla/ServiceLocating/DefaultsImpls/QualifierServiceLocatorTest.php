<?php

namespace Vobla\ServiceLocating\DefaultImpls;

require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\ServiceLocating\DefaultImpls\QualifierServiceLocator as QSL,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class QualifierServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Vobla\ServiceLocating\DefaultImpls\QualifierServiceLocator
     */
    protected $locator;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->locator = new QualifierServiceLocator();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->locator = null;
    }

    public function testCreateCriteria()
    {
        $this->assertEquals('byQualifier:foo', QSL::createCriteria('foo'));
    }

    public function testAnalyzeAndLocate()
    {
        $id = 'fooId';
        $qlr = 'fooQlr';

        $this->assertNull($this->locator->locate(QSL::createCriteria($qlr)));

        $def = $this->mf->createTestCaseAware(ServiceDefinition::clazz())->addMethod('getQualifier', function() use ($qlr) {
            return $qlr;
        }, 1)->createMock();

        $this->locator->analyze($id, $def);
        $this->locator->locate(
            'fooId',
            QSL::createCriteria($qlr),
            sprintf('For some reason "%s" was unable to register and locate a service.', QSL::clazz())
        );
    }
}
