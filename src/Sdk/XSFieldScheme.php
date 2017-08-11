<?php
namespace Antsfree\Mxusearch\Sdk;

class XSFieldScheme implements \IteratorAggregate
{
    const MIXED_VNO = 255;
    private $_fields = array();
    private $_typeMap = array();
    private $_vnoMap = array();
    private static $_logger;
    public function __toString()
    {
        $str = '';
        foreach ($this->_fields as $field) {
            $str .= $field->toConfig() . "\n";
        }
        return $str;
    }
    public function getFieldId()
    {
        if (isset($this->_typeMap[XSFieldMeta::TYPE_ID])) {
            $name = $this->_typeMap[XSFieldMeta::TYPE_ID];
            return $this->_fields[$name];
        }
        return false;
    }
    public function getFieldTitle()
    {
        if (isset($this->_typeMap[XSFieldMeta::TYPE_TITLE])) {
            $name = $this->_typeMap[XSFieldMeta::TYPE_TITLE];
            return $this->_fields[$name];
        }
        foreach ($this->_fields as $name => $field) {
            if ($field->type === XSFieldMeta::TYPE_STRING && !$field->isBoolIndex()) {
                return $field;
            }
        }
        return false;
    }
    public function getFieldBody()
    {
        if (isset($this->_typeMap[XSFieldMeta::TYPE_BODY])) {
            $name = $this->_typeMap[XSFieldMeta::TYPE_BODY];
            return $this->_fields[$name];
        }
        return false;
    }
    public function getField($name, $throw = true)
    {
        if (is_int($name)) {
            if (!isset($this->_vnoMap[$name])) {
                if ($throw === true) {
                    throw new XSException('Not exists field with vno: `' . $name . '\'');
                }
                return false;
            }
            $name = $this->_vnoMap[$name];
        }
        if (!isset($this->_fields[$name])) {
            if ($throw === true) {
                throw new XSException('Not exists field with name: `' . $name . '\'');
            }
            return false;
        }
        return $this->_fields[$name];
    }
    public function getAllFields()
    {
        return $this->_fields;
    }
    public function getVnoMap()
    {
        return $this->_vnoMap;
    }
    public function addField($field, $config = null)
    {
        if (!$field instanceof XSFieldMeta) {
            $field = new XSFieldMeta($field, $config);
        }
        if (isset($this->_fields[$field->name])) {
            throw new XSException('Duplicated field name: `' . $field->name . '\'');
        }
        if ($field->isSpeical()) {
            if (isset($this->_typeMap[$field->type])) {
                $prev = $this->_typeMap[$field->type];
                throw new XSException('Duplicated ' . strtoupper($config['type']) . ' field: `' . $field->name . '\' and `' . $prev . '\'');
            }
            $this->_typeMap[$field->type] = $field->name;
        }
        $field->vno = ($field->type == XSFieldMeta::TYPE_BODY) ? self::MIXED_VNO : count($this->_vnoMap);
        $this->_vnoMap[$field->vno] = $field->name;
        if ($field->type == XSFieldMeta::TYPE_ID) {
            $this->_fields = array_merge(array($field->name => $field), $this->_fields);
        } else {
            $this->_fields[$field->name] = $field;
        }
    }
    public function checkValid($throw = false)
    {
        if (!isset($this->_typeMap[XSFieldMeta::TYPE_ID])) {
            if ($throw) {
                throw new XSException('Missing field of type ID');
            }
            return false;
        }
        return true;
    }
    public function getIterator()
    {
        return new ArrayIterator($this->_fields);
    }
    public static function logger()
    {
        if (self::$_logger === null) {
            $scheme = new self;
            $scheme->addField('id', array('type' => 'id'));
            $scheme->addField('pinyin');
            $scheme->addField('partial');
            $scheme->addField('total', array('type' => 'numeric', 'index' => 'self'));
            $scheme->addField('lastnum', array('type' => 'numeric', 'index' => 'self'));
            $scheme->addField('currnum', array('type' => 'numeric', 'index' => 'self'));
            $scheme->addField('currtag', array('type' => 'string'));
            $scheme->addField('body', array('type' => 'body'));
            self::$_logger = $scheme;
        }
        return self::$_logger;
    }
}