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

namespace Vobla\ServiceConstruction\Assemblers\Injection;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Exception;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class FieldAccessReferenceInjector implements ReferenceInjector
{
    public function inject($obj, $paramName, $paramValue, ServiceDefinition $definition)
    {
        $reflClass = new \ReflectionClass($obj);

        if (!$reflClass->hasProperty($paramName)) {
            throw new Exception(
                sprintf(
                    'Unable to inject parameter "%s" because the field with the same name is missing in class "%s"',
                    $paramName, get_class($obj)
                )
            );
        }

        $reflProperty = $reflClass->getProperty($paramName);
        $reflProperty->setAccessible(true);

        $reflProperty->setValue($obj, $paramValue);
    }
}
