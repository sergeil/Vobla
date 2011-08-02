<?php

namespace Vobla\Extensibility;

use Vobla\Container,
    Vobla\Configuration,
    Vobla\Extensibility\ProvidersDecoration\DecoratedAssemblersProvider,
    Moko\Integrated\TestCaseAwareMockDefinition,
    Vobla\Extensibility\ProvidersDecoration\DecoratedDefinitionProcessorsProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedContextScopeHandlersProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedServiceLocatorsProvider,
    Vobla\Extensibility\Plugin,
    Vobla\Extensibility\ProvidersDecoration\Builders\DecoratedAnnotationsBuilderProcessorsProvider,
    Vobla\Extensibility\ProvidersDecoration\Builders\DecoratedXmlBuilderProcessorsProvider;

require_once __DIR__.'/../../../bootstrap.php';

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class PluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Vobla\Extensibility\PluginManager
     */
    protected $pm;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->pm = new PluginManager();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->pm = null;
    }

    protected function complementMock(TestCaseAwareMockDefinition $config, $providerName, $providerType)
    {
        $tc = $this;

        $config->addMethod('get'.$providerName, function() use($tc, $providerName) {
            return 'original-'.$providerName;
        })
        ->addMethod('set'.$providerName, function($self, $decorated) use ($tc, $providerName, $providerType) {
            $tc->assertType($providerType, $decorated);
            $tc->assertEquals('original-'.$providerName, $decorated->getOriginalProvider());
        }, 1);
    }

    /**
     * @expectedException Vobla\InitializationException
     */
    public function testActivate_withoutContainer()
    {
        $this->pm->activate();
    }

    public function testInstallGetPluginsFindUninstallFind()
    {
        $pluginName = 'fooPlugin';

        $plugin = $this->mf->create(Plugin::CLAZZ)
        ->addMethod('getName', $pluginName)
        ->createMock();
        
        $this->assertTrue(
            $this->pm->install($plugin),
            sprintf('%s::%s should return TRUE when plugin is successfully installed!', PluginManager::clazz(), 'install')
        );
        $this->assertSame($plugin, $this->pm->find($pluginName), 'Unable to find an installed plugin!');
        $this->assertFalse(
            $this->pm->install($plugin),
            sprintf(
                '%s::%s should return FALSE when plugin it is tried to install is already installed',
                PluginManager::clazz(), 'install'
            )
        );

        $installedPlugins = $this->pm->getPlugins();
        $this->assertTrue(is_array($installedPlugins));
        $this->assertSame($plugin, $installedPlugins[0]);

        $this->pm->uninstall($pluginName);
        $this->assertNull($this->pm->find($pluginName));
    }

    public function testInstallActivate()
    {
        $tc = $this;

        $config = $this->mf->createTestCaseAware(Configuration::clazz());
        $this->complementMock($config, 'ContextScopeHandlersProvider', DecoratedContextScopeHandlersProvider::clazz());
        $this->complementMock($config, 'AssemblersProvider', DecoratedAssemblersProvider::clazz());
        $this->complementMock($config, 'ServiceLocatorsProvider', DecoratedServiceLocatorsProvider::clazz());
        $this->complementMock($config, 'XmlBuilderProcessorsProvider', DecoratedXmlBuilderProcessorsProvider::clazz());
        $this->complementMock($config, 'AnnotationsBuilderProcessorsProvider', DecoratedAnnotationsBuilderProcessorsProvider::clazz());
//        $this->completeTest($config, 'DefinitionProcessorsProvider', DecoratedDefinitionProcessorsProvider::clazz());
        $config = $config->createMock(array(), null, true);

        $container = $this->mf->create(Container::clazz())
        ->addMethod('getConfiguration', $config)
        ->createMock();

        $pm = $this->pm;
        $applyMethodCallback = function($self, $argPm) use($tc, $pm) {
            $tc->assertSame($pm, $argPm);
        };

        $fooPlugin = $this->mf->createTestCaseAware(Plugin::CLAZZ)
        ->addMethod('getName', 'fooPlugin')
        ->addMethod('apply', $applyMethodCallback, 1)
        ->createMock(array(), 'fooPlugin', true);

        $barPlugin = $this->mf->createTestCaseAware(Plugin::CLAZZ)
        ->addMethod('getName', 'barPlugin')
        ->addMethod('apply', $applyMethodCallback, 1)
        ->createMock(array(), 'barPlugin', true);

        $this->pm->init($container);
        $this->pm->install($fooPlugin);
        $this->pm->install($barPlugin);

        $this->pm->activate();
    }
}
