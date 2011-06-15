<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder;

require_once __DIR__.'/../../../../bootstrap.php';
require_once __DIR__.'/fixtures/classes.php';

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\Container,
    Moko\MockFactory,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter,
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
        $ar->setAutoloadAnnotations(true);
        $this->ab = new AnnotationsBuilder($ar);
    }

    public function testProcessClass()
    {
        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $definition */
        $definition = $this->ab->processClass(SomeDumbService::clazz());
        $this->assertTrue($definition instanceof ServiceDefinition);
        $this->assertEquals('fooScope', $definition->getScope());

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

        $this->assertEquals(
            'fooFactory',
            $definition->getFactoryMethod(),
            sprintf(
                "For some reason '%s' annotation was ignored on %s::%s",
                Constructor::clazz(), SomeDumbService::clazz(), 'factoryMethod'
            )
        );

        $cArgs = $definition->getConstructorArguments();
        $this->assertEquals(3, sizeof($cArgs), "Constructor parameters count is wrong.");

        $this->assertTrue($cArgs[0] instanceof QualifiedReference);
        $this->assertEquals('fooQfr', $cArgs[0]->getQualifier(), "Qualifier parameter for constructor's method wasn't take into account.");

        $this->assertTrue($cArgs[1] instanceof ServiceReference);
        $this->assertEquals('bService', $cArgs[1]->getServiceId(), "Parameter without explicitely specified 'name' wasn't registered properly.");

        $this->assertTrue($cArgs[2] instanceof ServiceReference);
        $this->assertEquals(
            'megaCService',
            $cArgs[2]->getServiceId(),
            sprintf(
                "'id' parameter for third parameter of %s::%s factory-method hasn't been properly treated.",
                SomeDumbService::clazz(), 'fooFactory'
            )
        );
    }

    /**
     * @expectedException \Vobla\Exception
     */
    public function testProcessClassWithTwoConstructors()
    {
        $this->ab->processClass(ClassWithTwoConstructors::clazz());
    }
}
