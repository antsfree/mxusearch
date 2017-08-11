<?php
namespace Antsfree\Mxusearch\Sdk;

class XSCommand extends XSComponent
{
    public $cmd = XS_CMD_NONE;
    public $arg1 = 0;
    public $arg2 = 0;
    public $buf = '';
    public $buf1 = '';
    public function __construct($cmd, $arg1 = 0, $arg2 = 0, $buf = '', $buf1 = '')
    {
        if (is_array($cmd)) {
            foreach ($cmd as $key => $value) {
                if ($key === 'arg' || property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        } else {
            $this->cmd = $cmd;
            $this->arg1 = $arg1;
            $this->arg2 = $arg2;
            $this->buf = $buf;
            $this->buf1 = $buf1;
        }
    }
    public function __toString()
    {
        if (strlen($this->buf1) > 0xff) {
            $this->buf1 = substr($this->buf1, 0, 0xff);
        }
        return pack('CCCCI', $this->cmd, $this->arg1, $this->arg2, strlen($this->buf1), strlen($this->buf)) . $this->buf . $this->buf1;
    }
    public function getArg()
    {
        return $this->arg2 | ($this->arg1 << 8);
    }
    public function setArg($arg)
    {
        $this->arg1 = ($arg >> 8) & 0xff;
        $this->arg2 = $arg & 0xff;
    }
}