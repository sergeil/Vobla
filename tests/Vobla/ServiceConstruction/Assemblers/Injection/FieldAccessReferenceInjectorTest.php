<?php

namespace Vobla\ServiceConstruction\Assemblers\Injection;

require_once __DIR__.'/../../../../bootstrap.php';
require_once __DIR__.'/fixtures/classes.php';

use Vobla\ServiceConstruction\Assemblers\Injection\FieldAccessReferenceInjector,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class FieldAccessReferenceInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Assemblers\Injection\FieldAccessReferenceInjector
     */
    protected $farj;

    public function setUp()
    {
        $this->farj = new FieldAccessReferenceInjector();
    }

    public function tearDown()
    {
        $this->farj = null;
    }

    public function testInject()
    {
        $target = new MockInjectionTarget();

        $def = new ServiceDefinition();

        $this->farj->inject($target, 'publicProperty', 'value1', $def);
        $this->assertEquals('value1', $target->getPublicProperty(), 'Injection to public field failed.');

        $this->farj->inject($target, 'protectedProperty', 'value2', $def);
        $this->assertEquals('value2', $target->getProtectedProperty(), 'Injection to protected field failed.');

        $this->farj->inject($target, 'privateProperty', 'value3', $def);
        $this->assertEquals('value3', $target->getPrivateProperty(), 'Injection to private field failed.');
    }
}
