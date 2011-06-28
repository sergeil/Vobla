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

namespace Vobla\ServiceConstruction\Builders;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
abstract class AbstractBuilder
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\ServiceIdGenerator
     */
    protected $serviceIdGenerator;

    /**
     * @var array
     */
    protected $cachedProcessors;

    /**
     * @return array
     */
    public function getProcessors()
    {
        if (null === $this->cachedProcessors) {
            $this->cachedProcessors = $this->processorsProvider->getProcessors();

            // TODO throw an exception if no processors provided
        }

        return $this->cachedProcessors;
    }

    public function setServiceIdGenerator($serviceIdGenerator)
    {
        $this->serviceIdGenerator = $serviceIdGenerator;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\ServiceIdGenerator
     */
    public function getServiceIdGenerator()
    {
        if (null === $this->serviceIdGenerator) {
            $this->serviceIdGenerator = new ServiceIdGenerator();
        }

        return $this->serviceIdGenerator;
    }

    /**
     * @return mixed
     */
    abstract protected function getDefaultProcessorsProvider();

    public function __construct($processorsProvider = null)
    {
        if (null === $processorsProvider) {
            $this->processorsProvider = $this->getDefaultProcessorsProvider();
        } else {
            $this->processorsProvider = $processorsProvider;
        }
    }
}
