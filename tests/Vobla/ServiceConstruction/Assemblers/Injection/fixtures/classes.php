<?php

namespace Vobla\ServiceConstruction\Assemblers\Injection;

class MockInjectionTarget
{
    public $publicProperty;

    protected $protectedProperty;

    private $privateProperty;

    public function getPublicProperty()
    {
        return $this->publicProperty;
    }

    public function getPrivateProperty()
    {
        return $this->privateProperty;
    }

    public function getProtectedProperty()
    {
        return $this->protectedProperty;
    }
}