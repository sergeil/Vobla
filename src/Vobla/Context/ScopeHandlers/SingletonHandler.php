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

namespace Vobla\Context\ScopeHandlers;

use Vobla\Context\ContextScopeHandler,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class SingletonHandler extends AbstractHandler
{
    /**
     * @var array
     */
    protected $services = array();

    public function dispense($id)
    {
        return $this->contains($id) ? $this->services[$id] : null;
    }

    public function register($id, $obj)
    {
        $this->services[$id] = $obj;
    }

    public function contains($id)
    {
        return isset($this->services[$id]);
    }

    public function isDispenseResponsible($id)
    {
        return $this->contains($id);
    }

    public function isRegisterResponsible($id, ServiceDefinition $serviceDefinition, $obj)
    {
        return in_array($serviceDefinition->getScope(), array('singleton', '')); // default scope
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
