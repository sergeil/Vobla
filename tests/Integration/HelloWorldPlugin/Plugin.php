<?php

namespace Vobla\HelloWorldPlugin;

use Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class Plugin implements \Vobla\Extensibility\Plugin
{
    public function getName()
    {
        return 'org.vobla-project.plugins.helloworld';
    }

    public function apply(PluginManager $pluginManager)
    {
        $pluginManager->getContainer()
        ->getConfiguration()
        ->getAssemblersProvider()
        ->putLast(new InjectingProcessor());
    }
}
