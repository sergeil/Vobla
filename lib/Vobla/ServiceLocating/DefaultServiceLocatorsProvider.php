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
    Vobla\ServiceLocating\DefaultImpls\QualifierServiceLocator,
    Vobla\ServiceLocating\DefaultImpls\TagServiceLocator,
    Vobla\ServiceLocating\DefaultImpls\TypeServiceLocator;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class DefaultServiceLocatorsProvider implements ServiceLocatorsProvider
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $locators;

    /**
     * @return \Vobla\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function __construct()
    {
        $this->locators = array(
            new QualifierServiceLocator(),
            new TypeServiceLocator(),
            new TagServiceLocator()
        );
    }

    public function init(Container $container)
    {
        $this->container = $container;

        foreach ($this->locators as $locator) {
            $locator->init($container); // TODO ughm, what is a good place to initialize ?
        }
    }

    /**
     * @return void
     */
    public function getServiceLocators()
    {
        return $this->locators;
    }
}
