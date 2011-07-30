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

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\GeneralAttributesProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConstructorProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\PropertiesProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\TagsProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\QualifierProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\TypeProcessor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConfigProcessor;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class DefaultProcessorsProvider implements ProcessorsProvider
{
    /**
     * @var array
     */
    protected $processors = array();

    public function __construct()
    {
        // Be aware that order of processors is highly important.
        // For example, if ConfigProcessor is positioned before
        // PropertiesProcessor then if there's a ConfigProperty
        // annotation for a property and it was initialized then
        // other Autowired annotations from PropertiesProcessor
        // will be ignore and vice versa
        $this->processors = array(
            new GeneralAttributesProcessor(),
            new ConstructorProcessor(),
            new PropertiesProcessor(),
            new QualifierProcessor(),
            new TagsProcessor(),
            new TypeProcessor()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessors()
    {
        return $this->processors;
    }

}
