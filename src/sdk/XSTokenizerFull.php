<?php
namespace Antsfree\Mxusearch\Sdk;

class XSTokenizerFull implements XSTokenizer
{
    public function getTokens($value, XSDocument $doc = null)
    {
        return array($value);
    }
}