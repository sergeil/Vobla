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

namespace Vobla\Extensibility;

use Vobla\Container,
    Vobla\Exception,
    Vobla\Extensibility\ProvidersDecoration\DecoratedContextScopeHandlersProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedAssemblersProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedDefinitionProcessorsProvider,
    Vobla\Extensibility\ProvidersDecoration\DecorationAwareProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedServiceLocatorsProvider,
    Vobla\InitializationException,
    Vobla\Configuration,
    Vobla\Extensibility\ProvidersDecoration\Builders\DecoratedXmlBuilderProcessorsProvider,
    Vobla\Extensibility\ProvidersDecoration\Builders\DecoratedAnnotationsBuilderProcessorsProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class PluginManager
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    /**
     * @var boolean
     */
    protected $isActivated = false;

    /**
     * @var array
     */
    protected $plugins = array();

    /**
     * @return \Vobla\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return array_values($this->plugins);
    }

    public function init(Container $container)
    {
        $this->container = $container;
    }

    public function install(Plugin $plugin)
    {
        if (isset($this->plugins[$plugin->getName()])) {
            return false;
        }

        $this->plugins[$plugin->getName()] = $plugin;

        return true;
    }

    /**
     * @param string|Plugin $plugin
     * @return boolean
     */
    public function uninstall($pluginName)
    {
        $return = isset($this->plugins[$pluginName]);
        unset($this->plugins[$pluginName]);
        return $return;
    }

    public function find($pluginName)
    {
        return isset($this->plugins[$pluginName]) ? $this->plugins[$pluginName] : null;
    }

    protected function handleContextScopeHandlersProvider(Configuration $config)
    {
        $contextScopeHandlersProvider = $config->getContextScopeHandlersProvider();
        if (!($contextScopeHandlersProvider instanceof DecorationAwareProvider)) {
            $config->setContextScopeHandlersProvider(
                new DecoratedContextScopeHandlersProvider($contextScopeHandlersProvider, $this)
            );
        }
    }

    protected function handleAssemblersProvider(Configuration $config)
    {
        $assemblersProvider = $config->getAssemblersProvider();
        if (!($assemblersProvider instanceof DecorationAwareProvider)) {
            $config->setAssemblersProvider(
                new DecoratedAssemblersProvider($assemblersProvider, $this)
            );
        }
    }

    protected function handleDefinitionProcessorsProvider(Configuration $config)
    {
        $definitionProcessorsProvider = $config->getDefinitionProcessorsProvider();
        if (!($definitionProcessorsProvider instanceof DecorationAwareProvider)) {
            $config->setDefinitionProcessorsProvider(
                new DecoratedDefinitionProcessorsProvider($definitionProcessorsProvider, $this)
            );
        }
    }

    protected function handleServiceLocatorsProvider(Configuration $config)
    {
        $serviceLocatorsProvider = $config->getServiceLocatorsProvider();
        if (!($serviceLocatorsProvider instanceof DecorationAwareProvider)) {
            $config->setServiceLocatorsProvider(
                new DecoratedServiceLocatorsProvider($serviceLocatorsProvider, $this)
            );
        }
    }

    protected function handleXmlBuilderProcessorsProvider(Configuration $config)
    {
        $xmlBuilderProcessorsProvider = $config->getXmlBuilderProcessorsProvider();
        if (!($xmlBuilderProcessorsProvider instanceof DecorationAwareProvider)) {
            $config->setXmlBuilderProcessorsProvider(
                new DecoratedXmlBuilderProcessorsProvider($xmlBuilderProcessorsProvider, $this)
            );
        }
    }

    protected function handleAnnotationsBuilderProcessorsProvider(Configuration $config)
    {
        $annotationsBuilderProcessorsProvider = $config->getAnnotationsBuilderProcessorsProvider();
        if (!($annotationsBuilderProcessorsProvider instanceof DecorationAwareProvider)) {
            $config->setAnnotationsBuilderProcessorsProvider(
                new DecoratedAnnotationsBuilderProcessorsProvider($annotationsBuilderProcessorsProvider, $this)
            );
        }
    }

    protected function prepareActivation()
    {
        $config = $this->getContainer()->getConfiguration();
        $this->handleAssemblersProvider($config);
        $this->handleContextScopeHandlersProvider($config);
        $this->handleServiceLocatorsProvider($config);
//        $this->handleDefinitionProcessorsProvider($config);
        $this->handleXmlBuilderProcessorsProvider($config);
        $this->handleAnnotationsBuilderProcessorsProvider($config);
    }
    public function activate()
    {
        if (null === $this->container) {
            throw InitializationException::create($this);
        }

        if ($this->isActivated) {
            return false;
        }

        $this->prepareActivation();

        foreach ($this->getPlugins() as $plugin) {
            try {
                $plugin->apply($this);
            } catch (\Exception $e) {
                throw new Exception('Unable to apply plugin '.$plugin->getName());
            }
        }
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}