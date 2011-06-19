<?php

namespace Vobla\ServiceConstruction\Assemblers;

class MockWithDefaultConstructor
{
    public $foo;

    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

class MockWithLocalFactory
{
    public $foo;

    static public function fooFactory($foo)
    {
        $obj = new self();
        $obj->foo = $foo;

        return $obj;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

class MockFactoryOfOtherClass
{
    public function barFactory($foo, $bar)
    {
        return new MockWithDefaultConstructor($foo, $bar);
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}