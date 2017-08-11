<?php
namespace Antsfree\Mxusearch\Sdk;

class XSErrorException extends XSException
{
    private $_file, $_line;
    public function __construct($code, $message, $file, $line, $previous = null)
    {
        $this->_file = $file;
        $this->_line = $line;
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
    public function __toString()
    {
        $string = '[' . __CLASS__ . '] ' . $this->getRelPath($this->_file) . '(' . $this->_line . '): ';
        $string .= $this->getMessage() . '(' . $this->getCode() . ')';
        return $string;
    }
}