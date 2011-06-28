<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder;

require_once __DIR__.'/../../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\DefaultProcessorsProvider,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ConfigProcessor;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefaultProcessorsProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProcessors()
    {
        $dpp = new DefaultProcessorsProvider();

        $hasServiceProcessor = $hasConfigProcessor = false;
        foreach ($dpp->getProcessors() as $processor) {
            if ($processor instanceof ServiceProcessor) {
                $hasServiceProcessor = true;
            }
            if ($processor instanceof ConfigProcessor) {
                $hasConfigProcessor = true;
            }
        }

        $this->assertTrue(
            $hasServiceProcessor,
            sprintf(
                '%s::getProcessors must contain instance of %s',
                DefaultProcessorsProvider::clazz(), ServiceProcessor::clazz()
            )
        );
        $this->assertTrue(
            $hasServiceProcessor,
            sprintf(
                '%s::getProcessors must contain instance of %s',
                DefaultProcessorsProvider::clazz(), ServiceProcessor::clazz()
            )
        );
    }
}
