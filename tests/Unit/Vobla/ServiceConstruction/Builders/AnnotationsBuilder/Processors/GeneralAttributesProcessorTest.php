<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

require_once __DIR__.'/../../../../../../bootstrap.php';
require_once __DIR__.'/fixtures/classes.php';

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\GeneralAttributesProcessor,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class GeneralAttributesProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\GeneralAttributesProcessor
     */
    protected $gap;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $ar;

    public function setUp()
    {
        $this->gap = new GeneralAttributesProcessor();
        $this->ar = new AnnotationReader();
    }

    public function tearDown()
    {
        $this->gap = null;
        $this->ar = null;
    }

    public function testHandle()
    {
        $def = new ServiceDefinition();
        $this->gap->handle($this->ar, new \ReflectionClass(ClassWithAllGeneralProperties::clazz()), $def);

        $this->assertEquals(
            ClassWithAllGeneralProperties::clazz(),
            $def->getClassName(),
            "Class name doesn't match."
        );
        $this->assertEquals(
            'someQualifier',
            $def->getQualifier(),
            "Qualifier value doesn't match."
        );
        $this->assertFalse(
            $def->isAbstract(),
            sprintf("Even if %s::isScope is string it should be properly casted to bool value!", Service::clazz())
        );
        $this->assertEquals(
            'fooScope',
            $def->getScope(),
            "Scope doesn't match."
        );
    }
}
