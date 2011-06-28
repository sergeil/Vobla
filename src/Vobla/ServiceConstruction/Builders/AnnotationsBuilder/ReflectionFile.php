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

/**
 * Be aware, this implementation doesn't load a class!
 *
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ReflectionFile
{
    protected $sourceCode;
    protected $namespace;
    protected $className;

    public function __construct($sourceCode)
    {
        $this->sourceCode = $sourceCode;
        $this->reflect();
    }

    protected function reflect()
    {
        $tokens = token_get_all($this->sourceCode);
        $this->className = $this->lookupClass($tokens);
        $this->namespace = $this->lookupNamespace($tokens);
    }

    protected function lookupClass($tokens)
    {
        $value = null;
        for ($i=0; $i<sizeof($tokens); $i++) {
            if ($tokens[$i][0] == \T_CLASS) {
                for ($i2=$i; $i2<sizeof($tokens) && $value === null; $i2++) {
                    if ((isset($tokens[$i2+1]) && $tokens[$i2+1][0] == \T_WHITESPACE) && isset($tokens[$i2+2])) {
                        $value = $tokens[$i2+2][1];
                    }
                }
            }
        }
        
        return $value;
    }

    protected function lookupNamespace($tokens)
    {
        $value = array();
        $semicolonReached = false;
        for ($i=0; $i<sizeof($tokens); $i++) {
            if ($tokens[$i][0] == \T_NAMESPACE) {
                for ($i2=$i; $i2<sizeof($tokens) && !$semicolonReached; $i2++) {
                    if (is_array($tokens[$i2]) && $tokens[$i2][0] == \T_STRING) {
                        $value[] = $tokens[$i2][1];
                    } else if ($tokens[$i2] == ';') {
                        $semicolonReached = true;
                    }
                }
            }
        }

        return implode('\\', $value);
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getClassName()
    {
        return $this->className;
    }
}
