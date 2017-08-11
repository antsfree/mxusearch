<?php
namespace Antsfree\Mxusearch\Sdk;

class XSComponent
{
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        $msg = method_exists($this, 'set' . $name) ? 'Write-only' : 'Undefined';
        $msg .= ' property: ' . get_class($this) . '::$' . $name;
        throw new XSException($msg);
    }
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }
        $msg = method_exists($this, 'get' . $name) ? 'Read-only' : 'Undefined';
        $msg .= ' property: ' . get_class($this) . '::$' . $name;
        throw new XSException($msg);
    }
    public function __isset($name)
    {
        return method_exists($this, 'get' . $name);
    }
    public function __unset($name)
    {
        $this->__set($name, null);
    }
}
?>