<?php
namespace Antsfree\Mxusearch\Sdk;

interface XSTokenizer
{
    const DFL = 0;
    public function getTokens($value, XSDocument $doc = null);
}