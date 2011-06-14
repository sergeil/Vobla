<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder;

require_once __DIR__.'/../../../../bootstrap.php';
require_once __DIR__.'/fixtures/classes.php';

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\Container,
    Moko\MockFactory,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class AnnotationsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder
     */
    protected $ab;

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new MockFactory($this);

        $ar = new AnnotationReader();
        $ar->setAutoloadAnnotations(false);
        $this->ab = new AnnotationsBuilder($ar);
    }

    public function testProcessClass()
    {
        $c = $this->mf->createTestCaseAware(Container::clazz())->createMock();

        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $definition */
        $definition = $this->ab->processClass($c, SomeDumbService::clazz());
        $this->assertTrue($definition instanceof ServiceDefinition);

        $args = $definition->getArguments();
        $this->assertTrue(is_array($args));

        if (sizeof($args) == 3) {
            $this->fail('It seems that annotations from parent class were not taken into account.');
        } else if (sizeof($args) == 5) {
            $this->fail(
                'It looks that annotations from a parent class were taken into account nevertheless the fact it doesn\'t have Service annotation.'
            );
        } else {
            $this->assertEquals(4, sizeof($args), 'Declared service references were not collected properly.');
        }

        $this->assertTrue(isset($args['ref1x']));
        $this->assertType(ServiceReference::clazz(), $args['ref1x']);
        $this->assertEquals('ref1x', $args['ref1x']->getServiceId());

        $this->assertTrue(isset($args['ref2x']));
        $this->assertType(ServiceReference::clazz(), $args['ref2x']);
        $this->assertEquals('barbaz', $args['ref2x']->getServiceId());

        $this->assertTrue(isset($args['ref3x']));
        $this->assertType(
            QualifiedReference::clazz(),
            $args['ref3x'],
            sprintf(
                'When parameter "qualifier" of annotation "%s" is provided an instance of "%s" must be injected in "%s".',
                Autowired::clazz(), QualifiedReference::clazz(), ServiceDefinition::clazz()
            )
        );
        $this->assertEquals('booz', $args['ref3x']->getQualifier());


    }
}
