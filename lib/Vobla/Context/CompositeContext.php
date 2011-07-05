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

namespace Vobla\Context;

use Vobla\Container;

/**
 * @todo throw an exception if no context-scope-handlers were found
 *
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class CompositeContext implements Context
{
    /**
     * @var array
     */
    protected $cachedHandlers = null;

    /**
     * @var \Vobla\Container
     */
    protected $container;

    /**
     * @return \Vobla\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param \Vobla\Container $container
     */
    public function init(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getScopeHandlers()
    {
        if (null === $this->cachedHandlers) {
            $this->cachedHandlers = $this->getContainer()
                                         ->getConfiguration()
                                         ->getContextScopeHandlersProvider()
                                         ->getContextScopeHandlers();

            foreach ($this->cachedHandlers as $scopeHandler) {
                $scopeHandler->init($this->getContainer());
            }
        }

        return $this->cachedHandlers;
    }

    public function register($id, $obj)
    {
        foreach ($this->getScopeHandlers() as $handler) {
            $definition = $this->getContainer()->getDefinitionsHolder()->get($id);

            if ($handler->isRegisterResponsible($id, $definition, $obj)) {
                $handler->register($id, $obj);

                return;
            }
        }
    }

    public function dispense($id)
    {
        foreach ($this->getScopeHandlers() as $handler) {
            if ($handler->isDispenseResponsible($id)) {
                return $handler->dispense($id);
            }
        }
    }

    public function contains($id)
    {
        foreach ($this->getScopeHandlers() as $cachedHandler) {
            if ($cachedHandler->contains($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
