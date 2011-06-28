<?php
/*
 * Copyright (c) 2011 Sergei Lissovski, http://sergei.lissovski.org
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ScanPathsProvider,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\Processor,
    Vobla\ServiceConstruction\Builders\ServiceIdGenerator;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
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
    }

    public function tearDown()
    {
        $this->mf = null;
    }

    public function test_defaultGetters()
    {
        $ab = new AnnotationsBuilder();

        $this->assertType(
            'Doctrine\Common\Annotations\AnnotationReader',
            $ab->getAnnotationReader(),
            'Even if no AnnotationReader is explicitly provided a default one must be created on first request.'
        );

        $this->assertType(
            ServiceIdGenerator::clazz(),
            $ab->getServiceIdGenerator(),
            'Even if no default ServiceIdGenerator is provided a default one must be created upon a first request'
        );
    }

    public function testProcessClass()
    {
        $tc = $this;
        $annotationReader = new AnnotationReader();
        $reflTarget = new \ReflectionClass(SomeClassForAnnotationReader::clazz());

        $p1 = $this->mf->createTestCaseAware(Processor::CLAZZ)->addMethod('handle', function($self, $argAnnotationReader, $argReflTarget, $argDef) use($tc, $reflTarget, $annotationReader) {
            $tc->assertSame($annotationReader, $argAnnotationReader);
            $tc->assertSame($reflTarget, $argReflTarget);
            $tc->assertType(ServiceDefinition::clazz(), $argDef);
        }, 1)->createMock();
        $p2 = $this->mf->createTestCaseAware(Processor::CLAZZ)->addMethod('handle', function() {}, 1)->createMock();
        $processors = array($p1, $p2);

        $processorsProvider = $this->mf->createTestCaseAware(ProcessorsProvider::CLAZZ)->addMethod('getProcessors', function() use($processors){
            return $processors;
        }, 1)->createMock();
        
        $serviceIdGenerator = $this->mf->createTestCaseAware(ServiceIdGenerator::clazz())->addMethod('generate', function() {
            return 'some-unique-id';
        }, 1)->createMock();

        $ab = new AnnotationsBuilder($processorsProvider);
        $ab->setAnnotationReader($annotationReader);
        $ab->setServiceIdGenerator($serviceIdGenerator);

        $result = $ab->processClass($reflTarget);
        $this->assertTrue(
            is_array($result),
            sprintf(
                'Result of successful parsing of a class by %s::processClass must be an array.',
                AnnotationsBuilder::clazz(), ServiceDefinition::clazz()
            )
        );
        $this->assertEquals(
            2,
            sizeof($result),
            'Result of successful parsing of a class by %s::processClass must be an array where first index is ID of a service and second is an instance of %s '
        );
        $this->assertEquals(
            'some-unique-id',
            $result[0]
        );
        $this->assertType(
            ServiceDefinition::clazz(),
            $result[1],
            sprintf(
                'Second element of returned by %s::processClass must be an instance of %s.',
                AnnotationsBuilder::clazz(), ServiceDefinition::clazz()
            )
        );
    }
    
    public function testProcessPath()
    {
        $tc = $this;
        $c = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('addServiceDefinition', function() {}, 2)
        ->createMock();

        $classNames = array();

        /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder $ab */
        $ab = $this->mf->createTestCaseAware(AnnotationsBuilder::clazz())->addMethod('processClass', function($self, $argReflClass) use(&$classNames, $tc) {
            $tc->assertType('ReflectionClass', $argReflClass);

            $classNames[] = $argReflClass->getName();

            return array('foo', new ServiceDefinition());
        }, 2)->addDelegateMethod('processPath', 1)->createMock();

        $result = $ab->processPath($c, __DIR__.'/fixtures/DirectoryToScan');
        $this->assertTrue(is_array($result)); // skippedClasses

        $this->assertEquals(2, sizeof($classNames));

        $this->assertTrue(in_array(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\TopClass',
            $classNames
        ), 'We were not able to find and introspect required class "Vobla\ServiceConstruction\Builders\AnnotationsBuilder\TopClass".');

        $this->assertTrue(in_array(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\SubA\SubB\BurriedClass',
            $classNames
        ), 'We were not able to find and introspect required class "Vobla\ServiceConstruction\Builders\AnnotationsBuilder\SubA\SubB\BurriedClass".');
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
}
