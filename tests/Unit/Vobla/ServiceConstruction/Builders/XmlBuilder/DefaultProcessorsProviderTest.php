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

namespace Vobla\ServiceConstruction\Builders\XmlBuilder;

require_once __DIR__.'/../../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\DefaultProcessorsProvider,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ConfigProcessor,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\ImportProcessor;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class DefaultProcessorsProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProcessors()
    {
        $dpp = new DefaultProcessorsProvider();

        $hasServiceProcessor = $hasConfigProcessor = $hasImportProcessor = false;
        foreach ($dpp->getProcessors() as $processor) {
            if ($processor instanceof ServiceProcessor) {
                $hasServiceProcessor = true;
            }
            if ($processor instanceof ConfigProcessor) {
                $hasConfigProcessor = true;
            }
            if ($processor instanceof ImportProcessor) {
                $hasImportProcessor = true;
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
        $this->assertTrue(
            $hasImportProcessor,
            sprintf(
                '%s::getProcessors must contain instance of %s',
                DefaultProcessorsProvider::clazz(), ImportProcessor::clazz()
            )
        );
    }
}
