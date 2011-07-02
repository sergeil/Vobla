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

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector,
    Vobla\Exception;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ReferencesWeaverAssembler extends AbstractReferenceWeaverAssembler
{
    /**
     * @var \Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector
     */
    protected $referenceInjector;

    /**
     * @param \Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector $referenceInjector
     */
    public function setReferenceInjector($referenceInjector)
    {
        $this->referenceInjector = $referenceInjector;
    }

    /**
     * @return \Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector
     */
    public function getReferenceInjector()
    {
        return $this->referenceInjector;
    }
    
    public function __construct(ReferenceInjector $referenceInjector)
    {
        $this->referenceInjector = $referenceInjector;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AssemblersManager $assemblersManager, ServiceDefinition $definition, $obj = null)
    {
        try {
            foreach ($definition->getArguments() as $paramName => $paramValue) {
                $value = $this->derefenceParameter($paramValue);
                $this->getReferenceInjector()->inject($obj, $paramName, $value, $definition);
            }

            return $assemblersManager->proceed($definition, $obj);
        } catch (\Exception $e) {
            throw new Exception(
                sprintf('An error occurred while trying to deference service properties.'),
                null,
                $e
            );
        }
    }
}
