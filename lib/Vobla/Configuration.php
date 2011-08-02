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

namespace Vobla;

use Vobla\ServiceConstruction\Assemblers\AssemblersProvider,
    Vobla\ServiceConstruction\Assemblers\DefaultAssemblersProvider,
    Vobla\Context\DefaultContextScopeHandlersProvider,
    Vobla\ServiceLocating\DefaultServiceLocatorsProvider,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\DefaultProcessorsProvider as AnnotationsDefaultProcessorsProvider,
    Vobla\ServiceConstruction\Builders\XmlBuilder\DefaultProcessorsProvider as XmlDefaultProcessorsProvider;

/**
 * The most important extension point of the container, you are
 * able to pass your manually configured instance of configuration
 * when an instance of {@class Container} is created.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class Configuration
{
    /**
     * @var \Vobla\ServiceConstruction\Assemblers\AssemblersProvider
     */
    protected $assemblersProvider;

    /**
     * @var \Vobla\ServiceConstruction\Definition\DefinitionProcessorsProvider 
     */
    protected $definitionProcessorsProvider;

    /**
     * @var \Vobla\Context\ContextScopeHandlersProvider
     */
    protected $contextScopeHandlersProvider;

    /**
     * @var \Vobla\ServiceLocating\ServiceLocatorsProvider
     */
    protected $serviceLocatorsProvider;

    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider
     */
    protected $xmlBuilderProcessorsProvider;

    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider
     */
    protected $annotationsBuilderProcessorsProvider;

    /**
     * @param \Vobla\ServiceConstruction\Assemblers\AssemblersProvider $assemblersProvider
     */
    public function setAssemblersProvider(AssemblersProvider $assemblersProvider)
    {
        $this->assemblersProvider = $assemblersProvider;
    }

    /**
     * @return \Vobla\ServiceConstruction\Assemblers\AssemblersProvider
     */
    public function getAssemblersProvider()
    {
        if (null === $this->assemblersProvider) {
            $this->assemblersProvider = new DefaultAssemblersProvider();
        }

        return $this->assemblersProvider;
    }

    /**
     * @param \Vobla\Context\ContextScopeHandlersProvider $contextScopeHandlersProvider
     */
    public function setContextScopeHandlersProvider($contextScopeHandlersProvider)
    {
        $this->contextScopeHandlersProvider = $contextScopeHandlersProvider;
    }

    /**
     * @return \Vobla\Context\ContextScopeHandlersProvider
     */
    public function getContextScopeHandlersProvider()
    {
        if (null === $this->contextScopeHandlersProvider) {
            $this->contextScopeHandlersProvider = new DefaultContextScopeHandlersProvider();
        }

        return $this->contextScopeHandlersProvider;
    }

    /**
     * @param \Vobla\ServiceConstruction\Definition\DefinitionProcessorsProvider $definitionProcessorsProvider
     */
    public function setDefinitionProcessorsProvider($definitionProcessorsProvider)
    {
        $this->definitionProcessorsProvider = $definitionProcessorsProvider;
    }

    /**
     * @return \Vobla\ServiceConstruction\Definition\DefinitionProcessorsProvider
     */
    public function getDefinitionProcessorsProvider()
    {
        return $this->definitionProcessorsProvider;
    }

    public function setServiceLocatorsProvider($serviceLocatorsProvider)
    {
        $this->serviceLocatorsProvider = $serviceLocatorsProvider;
    }

    /**
     * @return ServiceLocating\ServiceLocatorsProvider
     */
    public function getServiceLocatorsProvider()
    {
        if (null === $this->serviceLocatorsProvider) {
            $this->serviceLocatorsProvider = new DefaultServiceLocatorsProvider();
        }

        return $this->serviceLocatorsProvider;
    }

    /**
     * @param \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider $annotationsBuilderProcessorsProvider
     */
    public function setAnnotationsBuilderProcessorsProvider($annotationsBuilderProcessorsProvider)
    {
        $this->annotationsBuilderProcessorsProvider = $annotationsBuilderProcessorsProvider;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider
     */
    public function getAnnotationsBuilderProcessorsProvider()
    {
        if (null === $this->annotationsBuilderProcessorsProvider) {
            $this->annotationsBuilderProcessorsProvider = new AnnotationsDefaultProcessorsProvider();
        }

        return $this->annotationsBuilderProcessorsProvider;
    }

    /**
     * @param \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider $xmlBuilderProcessorsProvider
     */
    public function setXmlBuilderProcessorsProvider($xmlBuilderProcessorsProvider)
    {
        $this->xmlBuilderProcessorsProvider = $xmlBuilderProcessorsProvider;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider
     */
    public function getXmlBuilderProcessorsProvider()
    {
        if (null === $this->xmlBuilderProcessorsProvider) {
            $this->xmlBuilderProcessorsProvider = new XmlDefaultProcessorsProvider();
        }

        return $this->xmlBuilderProcessorsProvider;
    }


    public function validate()
    {
        
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
