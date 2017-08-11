<?php
namespace Antsfree\Mxusearch\Sdk;

class XSTokenizerSplit implements XSTokenizer
{
    private $arg = ' ';
    public function __construct($arg = null)
    {
        if ($arg !== null && $arg !== '') {
            $this->arg = $arg;
        }
    }
    public function getTokens($value, XSDocument $doc = null)
    {
        if (strlen($this->arg) > 2 && substr($this->arg, 0, 1) == '/' && substr($this->arg, -1, 1) == '/') {
            return preg_split($this->arg, $value);
        }
        return explode($this->arg, $value);
    }
}