<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

require_once __DIR__.'/../../../../../bootstrap.php';
require_once __DIR__ . '/fixtures/classes.php';

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\Container,
    Moko\MockFactory,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ScanPathsProvider;

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
        $result = $this->ab->processClass(SomeDumbService::clazz());
        $this->assertTrue(is_array($result), 'It is expected that AnnotationBuilder::processClass returns an array as execution result.');
        $this->assertEquals('someDumbServiceId', $result[0], 'We expected a component to have a different ID.');
        $this->assertTrue($result[1] instanceof ServiceDefinition);
        $definition = $result[1];
        $this->assertEquals('fooScope', $definition->getScope());
        $this->assertEquals(SomeDumbService::clazz(), $definition->getClassName(), 'Class name does not match.');

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

    public function testConfigure()
    {
        $tc = $this;

        $scanPaths1 = array(
            'foo1',
            'foo2'
        );
        $scanPaths2 = array(
            'bar1',
            'bar2'
        );

        $container = $this->mf->create(Container::clazz())->createMock();

        $scanPathProvider1 = $this->mf->createTestCaseAware(ScanPathsProvider::CLAZZ)->addMethod('getScanPaths', function($self, $argContainer) use($tc, $container, $scanPaths1) {
            $tc->assertSame($container, $argContainer);
            return $scanPaths1;
        }, 1)->createMock();
        $scanPathProvider2 = $this->mf->createTestCaseAware(ScanPathsProvider::CLAZZ)->addMethod('getScanPaths', function($self, $argContainer) use($tc, $container, $scanPaths2) {
             $tc->assertSame($container, $argContainer);
            return $scanPaths2;
        }, 1)->createMock();

        $scanPathsProviders = array(
            $scanPathProvider1,
            $scanPathProvider2
        );

        $processedPaths = array();

        /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder $ab */
        $ab = $this->mf->createTestCaseAware(AnnotationsBuilder::clazz())->addMethod('getScanPathsProviders', function() use($scanPathsProviders) {
            return $scanPathsProviders;
        }, 1)->addMethod('processPath', function($self, $argContainer, $argPath) use ($tc, $container, &$processedPaths){
            $tc->assertSame($container, $argContainer);
            $processedPaths[] = $argPath;
            return array();
            }, 4)->addDelegateMethod('configure', 1)->createMock();
        
        $result = $ab->configure($container);
        $this->assertTrue(is_array($result));

        $this->assertEquals(
            array('foo1', 'foo2', 'bar1', 'bar2'),
            $processedPaths,
            sprintf('AnnotationBuilder::processPath was not invoked with expected parameters.')
        );
    }

    public function testProcessPath()
    {
        $tc = $this;
        $c = $this->mf->create(Container::clazz())->createMock();

        $classNames = array();

        /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder $ab */
        $ab = $this->mf->createTestCaseAware(AnnotationsBuilder::clazz())->addMethod('processClass', function($self, $argReflClass) use(&$classNames, $tc) {
            $tc->assertType('ReflectionClass', $argReflClass);

            $classNames[] = $argReflClass->getName();
        }, 2)->addDelegateMethod('processPath', 1)->createMock();

        $result = $ab->processPath($c, __DIR__.'/fixtures/DirectoryToScan');
        $this->assertTrue(is_array($result));

        $this->assertEquals(2, sizeof($classNames));

        $this->assertTrue(in_array(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\TopClass',
                $classNames
        ), 'We were not able to find and introspect requried class "Vobla\ServiceConstruction\Builders\AnnotationsBuilder\TopClass".');

        $this->assertTrue(in_array(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\SubA\SubB\BurriedClass',
            $classNames
        ), 'We were not able to find and introspect requried class "Vobla\ServiceConstruction\Builders\AnnotationsBuilder\SubA\SubB\BurriedClass".');

    }
}
