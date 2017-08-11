<?php
namespace Antsfree\Mxusearch\Sdk;
class XSException extends \Exception
{
    public function __toString()
    {
        $string = '[' . __CLASS__ . '] ' . $this->getRelPath($this->getFile()) . '(' . $this->getLine() . '): ';
        $string .= $this->getMessage() . ($this->getCode() > 0 ? '(S#' . $this->getCode() . ')' : '');
        return $string;
    }
    public static function getRelPath($file)
    {
        $from = getcwd();
        $file = realpath($file);
        if (is_dir($file)) {
            $pos = false;
            $to = $file;
        } else {
            $pos = strrpos($file, '/');
            $to = substr($file, 0, $pos);
        }
        for ($rel = '';; $rel .= '../') {
            if ($from === $to) {
                break;
            }
            if ($from === dirname($from)) {
                $rel .= substr($to, 1);
                break;
            }
            if (!strncmp($from . '/', $to, strlen($from) + 1)) {
                $rel .= substr($to, strlen($from) + 1);
                break;
            }
            $from = dirname($from);
        }
        if (substr($rel, -1, 1) === '/') {
            $rel = substr($rel, 0, -1);
        }
        if ($pos !== false) {
            $rel .= substr($file, $pos);
        }
        return $rel;
    }
}