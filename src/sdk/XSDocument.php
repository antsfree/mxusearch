<?php
namespace Antsfree\Mxusearch\Sdk;

class XSDocument implements \ArrayAccess, \IteratorAggregate
{
    private $_data;
    private $_terms, $_texts;
    private $_charset, $_meta;
    private static $_resSize = 20;
    private static $_resFormat = 'Idocid/Irank/Iccount/ipercent/fweight';
    public function __construct($p = null, $d = null)
    {
        $this->_data = array();
        if (is_array($p)) {
            $this->_data = $p;
        } elseif (is_string($p)) {
            if (strlen($p) !== self::$_resSize) {
                $this->setCharset($p);
                return;
            }
            $this->_meta = unpack(self::$_resFormat, $p);
        }
        if ($d !== null && is_string($d)) {
            $this->setCharset($d);
        }
    }
    public function __get($name)
    {
        if (!isset($this->_data[$name])) {
            return null;
        }
        return $this->autoConvert($this->_data[$name]);
    }
    public function __set($name, $value)
    {
        if ($this->_meta !== null) {
            throw new XSException('Magick property of result document is read-only');
        }
        $this->setField($name, $value);
    }
    public function __call($name, $args)
    {
        if ($this->_meta !== null) {
            $name = strtolower($name);
            if (isset($this->_meta[$name])) {
                return $this->_meta[$name];
            }
        }
        throw new XSException('Call to undefined method `' . get_class($this) . '::' . $name . '()\'');
    }
    public function getCharset()
    {
        return $this->_charset;
    }
    public function setCharset($charset)
    {
        $this->_charset = strtoupper($charset);
        if ($this->_charset == 'UTF8') {
            $this->_charset = 'UTF-8';
        }
    }
    public function getFields()
    {
        return $this->_data;
    }
    public function setFields($data)
    {
        if ($data === null) {
            $this->_data = array();
            $this->_meta = $this->_terms = $this->_texts = null;
        } else {
            $this->_data = array_merge($this->_data, $data);
        }
    }
    public function setField($name, $value, $isMeta = false)
    {
        if ($value === null) {
            if ($isMeta) {
                unset($this->_meta[$name]);
            } else {
                unset($this->_data[$name]);
            }
        } else {
            if ($isMeta) {
                $this->_meta[$name] = $value;
            } else {
                $this->_data[$name] = $value;
            }
        }
    }
    public function f($name)
    {
        return $this->__get(strval($name));
    }
    public function getAddTerms($field)
    {
        $field = strval($field);
        if ($this->_terms === null || !isset($this->_terms[$field])) {
            return null;
        }
        $terms = array();
        foreach ($this->_terms[$field] as $term => $weight) {
            $term = $this->autoConvert($term);
            $terms[$term] = $weight;
        }
        return $terms;
    }
    public function getAddIndex($field)
    {
        $field = strval($field);
        if ($this->_texts === null || !isset($this->_texts[$field])) {
            return null;
        }
        return $this->autoConvert($this->_texts[$field]);
    }
    public function addTerm($field, $term, $weight = 1)
    {
        $field = strval($field);
        if (!is_array($this->_terms)) {
            $this->_terms = array();
        }
        if (!isset($this->_terms[$field])) {
            $this->_terms[$field] = array($term => $weight);
        } elseif (!isset($this->_terms[$field][$term])) {
            $this->_terms[$field][$term] = $weight;
        } else {
            $this->_terms[$field][$term] += $weight;
        }
    }
    public function addIndex($field, $text)
    {
        $field = strval($field);
        if (!is_array($this->_texts)) {
            $this->_texts = array();
        }
        if (!isset($this->_texts[$field])) {
            $this->_texts[$field] = strval($text);
        } else {
            $this->_texts[$field] .= "\n" . strval($text);
        }
    }
    public function getIterator()
    {
        if ($this->_charset !== null && $this->_charset !== 'UTF-8') {
            $from = $this->_meta === null ? $this->_charset : 'UTF-8';
            $to = $this->_meta === null ? 'UTF-8' : $this->_charset;
            return new \ArrayIterator(XS::convert($this->_data, $to, $from));
        }
        return new \ArrayIterator($this->_data);
    }
    public function offsetExists($name)
    {
        return isset($this->_data[$name]);
    }
    public function offsetGet($name)
    {
        return $this->__get($name);
    }
    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            $this->__set(strval($name), $value);
        }
    }
    public function offsetUnset($name)
    {
        unset($this->_data[$name]);
    }
    public function beforeSubmit(XSIndex $index)
    {
        if ($this->_charset === null) {
            $this->_charset = $index->xs->getDefaultCharset();
        }
        return true;
    }
    public function afterSubmit($index)
    {
    }
    private function autoConvert($value)
    {
        if ($this->_charset === null || $this->_charset == 'UTF-8'
            || !is_string($value) || !preg_match('/[\x81-\xfe]/', $value)) {
            return $value;
        }
        $from = $this->_meta === null ? $this->_charset : 'UTF-8';
        $to = $this->_meta === null ? 'UTF-8' : $this->_charset;
        return XS::convert($value, $to, $from);
    }
}