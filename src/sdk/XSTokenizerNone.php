<?php
namespace Antsfree\Mxusearch\Sdk;

class XSTokenizerNone implements XSTokenizer
{
    public function getTokens($value, XSDocument $doc = null)
    {
        return array();
    }
}