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

namespace Vobla\Extensibility\ProvidersDecoration;

use Vobla\Container,
    Vobla\ServiceConstruction\Assemblers\AssemblersProvider,
    Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
abstract class AbstractDecorationAwareProvider implements DecorationAwareProvider
{
    /**
     * @var mixed
     */
    protected $originalProvider;

    /**
     * @var \Vobla\Extensibility\PluginManager
     */
    protected $pluginManager;

    /**
     * @var array
     */
    private $sortedProviders = null;

    /**
     * @return mixed
     */
    public function getOriginalProvider()
    {
        return $this->originalProvider;
    }

    public function __construct($originalProvider, PluginManager $pluginManager)
    {
        $this->originalProvider = $originalProvider;
        $this->pluginManager = $pluginManager;
    }

    protected function put($targetClassName, $newProvider, $position)
    {
        $result = array();
        foreach ($this->getSortedProviders() as $currentProviderCn => $provider) {
            $newProviderCn = get_class($newProvider);

            if ($currentProviderCn == $targetClassName) {
                if ('before' == $position) {
                    $result[$newProviderCn] = $newProvider;
                    $result[$currentProviderCn] = $provider;
                } else {
                    $result[$currentProviderCn] = $provider;
                    $result[$newProviderCn] = $newProvider;
                }
            }
            
            $result[$currentProviderCn] = $provider;
        }
        $this->sortedProviders = $result;
    }

    public function putFirst($newProvider)
    {
        $className = get_class($newProvider);
        $providers = $this->getSortedProviders();
        if (sizeof($providers) > 1) {
            $this->sortedProviders = array_merge(
                array($className => $newProvider),
                array_slice($providers, 1)
            );
        } else {
            $this->sortedProviders = array_merge(
                array($className => $newProvider),
                $this->sortedProviders
            );
        }
    }

    public function putLast($newProvider)
    {
        $providers = $this->getSortedProviders();
        $providers[get_class($newProvider)] = $newProvider;
        $this->sortedProviders = $providers;
    }
    
    public function putBefore($targetClassName, $newProvider)
    {
        $this->put($targetClassName, $newProvider, 'before');
    }

    public function putAfter($targetClassName, $newProvider)
    {
        $this->put($targetClassName, $newProvider, 'after');
    }

    public function exists($className)
    {
        $providers = $this->getSortedProviders();
        return isset($providers[$className]);
    }

    public function getSortedProviders()
    {
        if (null !== $this->sortedProviders) {
            return $this->sortedProviders;
        }

        foreach ($this->getProviders() as $provider) {
            $this->sortedProviders[get_class($provider)] = $provider;
        }

        return $this->sortedProviders;
    }

    abstract protected function getProviders();

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
