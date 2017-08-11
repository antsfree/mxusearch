<?php
namespace Antsfree\Mxusearch\Sdk;

class XSFieldMeta
{
    const MAX_WDF = 0x3f;
    const TYPE_STRING = 0;
    const TYPE_NUMERIC = 1;
    const TYPE_DATE = 2;
    const TYPE_ID = 10;
    const TYPE_TITLE = 11;
    const TYPE_BODY = 12;
    const FLAG_INDEX_SELF = 0x01;
    const FLAG_INDEX_MIXED = 0x02;
    const FLAG_INDEX_BOTH = 0x03;
    const FLAG_WITH_POSITION = 0x10;
    const FLAG_NON_BOOL = 0x80; // 强制让该字段参与权重计算 (非布尔)
    public $name;
    public $cutlen = 0;
    public $weight = 1;
    public $type = 0;
    public $vno = 0;
    private $tokenizer = XSTokenizer::DFL;
    private $flag = 0;
    private static $_tokenizers = array();
    public function __construct($name, $config = null)
    {
        $this->name = strval($name);
        if (is_array($config)) {
            $this->fromConfig($config);
        }
    }
    public function __toString()
    {
        return $this->name;
    }
    public function val($value)
    {
        if ($this->type == self::TYPE_DATE) {
            if (!is_numeric($value) || strlen($value) !== 8) {
                $value = date('Ymd', is_numeric($value) ? $value : strtotime($value));
            }
        }
        return $value;
    }
    public function withPos()
    {
        return ($this->flag & self::FLAG_WITH_POSITION) ? true : false;
    }
    public function isBoolIndex()
    {
        if ($this->flag & self::FLAG_NON_BOOL) {
            return false;
        }
        return (!$this->hasIndex() || $this->tokenizer !== XSTokenizer::DFL);
    }
    public function isNumeric()
    {
        return ($this->type == self::TYPE_NUMERIC);
    }
    public function isSpeical()
    {
        return ($this->type == self::TYPE_ID || $this->type == self::TYPE_TITLE || $this->type == self::TYPE_BODY);
    }
    public function hasIndex()
    {
        return ($this->flag & self::FLAG_INDEX_BOTH) ? true : false;
    }
    public function hasIndexMixed()
    {
        return ($this->flag & self::FLAG_INDEX_MIXED) ? true : false;
    }
    public function hasIndexSelf()
    {
        return ($this->flag & self::FLAG_INDEX_SELF) ? true : false;
    }
    public function hasCustomTokenizer()
    {
        return ($this->tokenizer !== XSTokenizer::DFL);
    }
    public function getCustomTokenizer()
    {
        if (isset(self::$_tokenizers[$this->tokenizer])) {
            return self::$_tokenizers[$this->tokenizer];
        } else {
            if (($pos1 = strpos($this->tokenizer, '(')) !== false
                && ($pos2 = strrpos($this->tokenizer, ')', $pos1 + 1))) {
                $name = 'XSTokenizer' . ucfirst(trim(substr($this->tokenizer, 0, $pos1)));
                $arg = substr($this->tokenizer, $pos1 + 1, $pos2 - $pos1 - 1);
            } else {
                $name = 'XSTokenizer' . ucfirst($this->tokenizer);
                $arg = null;
            }
            $name = 'Antsfree\Mxusearch\Sdk\\'.$name;
            if (!class_exists($name)) {
                $file = $name . '.class.php';
                if (file_exists($file)) {
                    require_once $file;
                } else if (file_exists(XS_LIB_ROOT . DIRECTORY_SEPARATOR . $file)) {
                    require_once XS_LIB_ROOT . DIRECTORY_SEPARATOR . $file;
                }
                if (!class_exists($name)) {
                    throw new XSException('Undefined custom tokenizer \'' . $this->tokenizer . '\' for field \'' . $this->name . '\'');
                }
            }
            $obj = $arg === null ? new $name : new $name($arg);
            if (!$obj instanceof XSTokenizer) {
                throw new XSException($name . ' for field `' . $this->name . '\' dose not implement the interface: XSTokenizer');
            }
            self::$_tokenizers[$this->tokenizer] = $obj;
            return $obj;
        }
    }
    public function toConfig()
    {
        $str = "[" . $this->name . "]\n";
        if ($this->type === self::TYPE_NUMERIC) {
            $str .= "type = numeric\n";
        } elseif ($this->type === self::TYPE_DATE) {
            $str .= "type = date\n";
        } elseif ($this->type === self::TYPE_ID) {
            $str .= "type = id\n";
        } elseif ($this->type === self::TYPE_TITLE) {
            $str .= "type = title\n";
        } elseif ($this->type === self::TYPE_BODY) {
            $str .= "type = body\n";
        }
        if ($this->type !== self::TYPE_BODY && ($index = ($this->flag & self::FLAG_INDEX_BOTH))) {
            if ($index === self::FLAG_INDEX_BOTH) {
                if ($this->type !== self::TYPE_TITLE) {
                    $str .= "index = both\n";
                }
            } elseif ($index === self::FLAG_INDEX_MIXED) {
                $str .= "index = mixed\n";
            } else {
                if ($this->type !== self::TYPE_ID) {
                    $str .= "index = self\n";
                }
            }
        }
        if ($this->type !== self::TYPE_ID && $this->tokenizer !== XSTokenizer::DFL) {
            $str .= "tokenizer = " . $this->tokenizer . "\n";
        }
        if ($this->cutlen > 0 && !($this->cutlen === 300 && $this->type === self::TYPE_BODY)) {
            $str .= "cutlen = " . $this->cutlen . "\n";
        }
        if ($this->weight !== 1 && !($this->weight === 5 && $this->type === self::TYPE_TITLE)) {
            $str .= "weight = " . $this->weight . "\n";
        }
        if ($this->flag & self::FLAG_WITH_POSITION) {
            if ($this->type !== self::TYPE_BODY && $this->type !== self::TYPE_TITLE) {
                $str .= "phrase = yes\n";
            }
        } else {
            if ($this->type === self::TYPE_BODY || $this->type === self::TYPE_TITLE) {
                $str .= "phrase = no\n";
            }
        }
        if ($this->flag & self::FLAG_NON_BOOL) {
            $str .= "non_bool = yes\n";
        }
        return $str;
    }
    public function fromConfig($config)
    {
        if (isset($config['type'])) {
            $predef = 'self::TYPE_' . strtoupper($config['type']);
            if (defined($predef)) {
                $this->type = constant($predef);
                if ($this->type == self::TYPE_ID) {
                    $this->flag = self::FLAG_INDEX_SELF;
                    $this->tokenizer = 'full';
                } elseif ($this->type == self::TYPE_TITLE) {
                    $this->flag = self::FLAG_INDEX_BOTH | self::FLAG_WITH_POSITION;
                    $this->weight = 5;
                } elseif ($this->type == self::TYPE_BODY) {
                    $this->vno = XSFieldScheme::MIXED_VNO;
                    $this->flag = self::FLAG_INDEX_SELF | self::FLAG_WITH_POSITION;
                    $this->cutlen = 300;
                }
            }
        }
        if (isset($config['index']) && $this->type != self::TYPE_BODY) {
            $predef = 'self::FLAG_INDEX_' . strtoupper($config['index']);
            if (defined($predef)) {
                $this->flag &= ~ self::FLAG_INDEX_BOTH;
                $this->flag |= constant($predef);
            }
            if ($this->type == self::TYPE_ID) {
                $this->flag |= self::FLAG_INDEX_SELF;
            }
        }
        if (isset($config['cutlen'])) {
            $this->cutlen = intval($config['cutlen']);
        }
        if (isset($config['weight']) && $this->type != self::TYPE_BODY) {
            $this->weight = intval($config['weight']) & self::MAX_WDF;
        }
        if (isset($config['phrase'])) {
            if (!strcasecmp($config['phrase'], 'yes')) {
                $this->flag |= self::FLAG_WITH_POSITION;
            } elseif (!strcasecmp($config['phrase'], 'no')) {
                $this->flag &= ~ self::FLAG_WITH_POSITION;
            }
        }
        if (isset($config['non_bool'])) {
            if (!strcasecmp($config['non_bool'], 'yes')) {
                $this->flag |= self::FLAG_NON_BOOL;
            } elseif (!strcasecmp($config['non_bool'], 'no')) {
                $this->flag &= ~ self::FLAG_NON_BOOL;
            }
        }
        if (isset($config['tokenizer']) && $this->type != self::TYPE_ID
            && $config['tokenizer'] != 'default') {
            $this->tokenizer = $config['tokenizer'];
        }
    }
}