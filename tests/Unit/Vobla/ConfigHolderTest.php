<?php

namespace Vobla;

require_once __DIR__.'/../../bootstrap.php';

use Vobla\ConfigHolder;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ConfigHolderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ConfigHolder
     */
    protected $ch;

    public function setUp()
    {
        $this->ch = new ConfigHolder();
    }

    public function tearDown()
    {
        $this->ch = null;
    }

    public function testAll()
    {
        $this->assertNull($this->ch->get('fooProp'));
        $this->assertFalse($this->ch->has('fooProp'));

        $this->ch->set('fooProp', 'fooValue');

        $this->assertEquals('fooValue', $this->ch->get('fooProp'));
        $this->assertTrue($this->ch->has('fooProp'));
    }

    /**
     * @expectedException Vobla\Exception
     */
    public function testSet_withNonScalar()
    {
        $this->ch->set('fooProp', new \stdClass());
    }
}
