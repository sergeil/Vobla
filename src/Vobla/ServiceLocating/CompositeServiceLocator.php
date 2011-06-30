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

namespace Vobla\ServiceLocating;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class CompositeServiceLocator extends AbstractServiceLocator
{
    /**
     * @var array
     */
    protected $cachedLocators = array();

    /**
     * {@inheritdoc}
     */
    public function init(Container $container)
    {
        parent::init($container);

        $this->cachedLocators = $this->container->getConfiguration()->getServiceLocatorsProvider()->getServiceLocators();
    }

    /**
     * @param mixed $criteria
     * @return mixed|false
     */
    public function locate($criteria)
    {
        $result = array();
        foreach ($this->cachedLocators as $locator) {
            $result = array_merge(
                $result,
                $locator->locate($criteria)
            );
        }

        return $result;
    }

    public function analyze($id, ServiceDefinition $serviceDefinition)
    {
        foreach ($this->cachedLocators as $locator) {
            $locator->analyze($id, $serviceDefinition);
        }
    }

}
