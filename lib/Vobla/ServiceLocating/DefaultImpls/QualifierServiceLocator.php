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

namespace Vobla\ServiceLocating\DefaultImpls;

use Vobla\ServiceLocating\AbstractServiceLocator,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class QualifierServiceLocator extends AbstractServiceLocator
{
    static public function createCriteria($qualifier)
    {
        return "byQualifier:$qualifier";
    }

    /**
     * @var array
     */
    protected $lookup = array();

    /**
     * {@inheritdoc}
     */
    public function analyze($id, ServiceDefinition $serviceDefinition)
    {
        $clr = $serviceDefinition->getQualifier();
        if ($clr != '') {
            $this->lookup[$clr] = $id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function locate($criteria)
    {
        $matches = array();
        if (preg_match('/^byQualifier:(.*)$/', $criteria, $matches)) {
            $qlr = $matches[1];
            return isset($this->lookup[$qlr]) ? array($this->lookup[$qlr]) : array();
        }

        return array();
    }
}
