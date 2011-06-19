<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

/**
 * Be aware, this implementation doesn't load a class!
 *
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
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
