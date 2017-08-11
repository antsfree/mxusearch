<?php
namespace Antsfree\Mxusearch\Sdk;

class XSTokenizerScws implements XSTokenizer
{
    const MULTI_MASK = 15;
    private static $_charset;
    private $_setting = array();
    private static $_server;
    public function __construct($arg = null)
    {
        if (self::$_server === null) {
            $xs = XS::getLastXS();
            if ($xs === null) {
                throw new XSException('An XS instance should be created before using ' . __CLASS__);
            }
            self::$_server = $xs->getScwsServer();
            self::$_server->setTimeout(0);
            self::$_charset = $xs->getDefaultCharset();
            if (!defined('SCWS_MULTI_NONE')) {
                define('SCWS_MULTI_NONE', 0);
                define('SCWS_MULTI_SHORT', 1);
                define('SCWS_MULTI_DUALITY', 2);
                define('SCWS_MULTI_ZMAIN', 4);
                define('SCWS_MULTI_ZALL', 8);
            }
            if (!defined('SCWS_XDICT_XDB')) {
                define('SCWS_XDICT_XDB', 1);
                define('SCWS_XDICT_MEM', 2);
                define('SCWS_XDICT_TXT', 4);
            }
        }
        if ($arg !== null && $arg !== '') {
            $this->setMulti($arg);
        }
    }
    public function getTokens($value, XSDocument $doc = null)
    {
        $tokens = array();
        $this->setIgnore(true);
        $_charset = self::$_charset;
        self::$_charset = 'UTF-8';
        $words = $this->getResult($value);
        foreach ($words as $word) {
            $tokens[] = $word['word'];
        }
        self::$_charset = $_charset;
        return $tokens;
    }
    public function setCharset($charset)
    {
        self::$_charset = strtoupper($charset);
        if (self::$_charset == 'UTF8') {
            self::$_charset = 'UTF-8';
        }
        return $this;
    }
    public function setIgnore($yes = true)
    {
        $this->_setting['ignore'] = new XSCommand(XS_CMD_SEARCH_SCWS_SET, XS_CMD_SCWS_SET_IGNORE, $yes === false
            ? 0 : 1);
        return $this;
    }
    public function setMulti($mode = 3)
    {
        $mode = intval($mode) & self::MULTI_MASK;
        $this->_setting['multi'] = new XSCommand(XS_CMD_SEARCH_SCWS_SET, XS_CMD_SCWS_SET_MULTI, $mode);
        return $this;
    }
    public function setDict($fpath, $mode = null)
    {
        if (!is_int($mode)) {
            $mode = stripos($fpath, '.txt') !== false ? SCWS_XDICT_TXT : SCWS_XDICT_XDB;
        }
        $this->_setting['set_dict'] = new XSCommand(XS_CMD_SEARCH_SCWS_SET, XS_CMD_SCWS_SET_DICT, $mode, $fpath);
        unset($this->_setting['add_dict']);
        return $this;
    }
    public function addDict($fpath, $mode = null)
    {
        if (!is_int($mode)) {
            $mode = stripos($fpath, '.txt') !== false ? SCWS_XDICT_TXT : SCWS_XDICT_XDB;
        }
        if (!isset($this->_setting['add_dict'])) {
            $this->_setting['add_dict'] = array();
        }
        $this->_setting['add_dict'][] = new XSCommand(XS_CMD_SEARCH_SCWS_SET, XS_CMD_SCWS_ADD_DICT, $mode, $fpath);
        return $this;
    }
    public function setDuality($yes = true)
    {
        $this->_setting['duality'] = new XSCommand(XS_CMD_SEARCH_SCWS_SET, XS_CMD_SCWS_SET_DUALITY, $yes === false
            ? 0 : 1);
        return $this;
    }
    public function getVersion()
    {
        $cmd = new XSCommand(XS_CMD_SEARCH_SCWS_GET, XS_CMD_SCWS_GET_VERSION);
        $res = self::$_server->execCommand($cmd, XS_CMD_OK_INFO);
        return $res->buf;
    }
    public function getResult($text)
    {
        $words = array();
        $text = $this->applySetting($text);
        $cmd = new XSCommand(XS_CMD_SEARCH_SCWS_GET, XS_CMD_SCWS_GET_RESULT, 0, $text);
        $res = self::$_server->execCommand($cmd, XS_CMD_OK_SCWS_RESULT);
        while ($res->buf !== '') {
            $tmp = unpack('Ioff/a4attr/a*word', $res->buf);
            $tmp['word'] = XS::convert($tmp['word'], self::$_charset, 'UTF-8');
            $words[] = $tmp;
            $res = self::$_server->getRespond();
        }
        return $words;
    }
    public function getTops($text, $limit = 10, $xattr = '')
    {
        $words = array();
        $text = $this->applySetting($text);
        $cmd = new XSCommand(XS_CMD_SEARCH_SCWS_GET, XS_CMD_SCWS_GET_TOPS, $limit, $text, $xattr);
        $res = self::$_server->execCommand($cmd, XS_CMD_OK_SCWS_TOPS);
        while ($res->buf !== '') {
            $tmp = unpack('Itimes/a4attr/a*word', $res->buf);
            $tmp['word'] = XS::convert($tmp['word'], self::$_charset, 'UTF-8');
            $words[] = $tmp;
            $res = self::$_server->getRespond();
        }
        return $words;
    }
    public function hasWord($text, $xattr)
    {
        $text = $this->applySetting($text);
        $cmd = new XSCommand(XS_CMD_SEARCH_SCWS_GET, XS_CMD_SCWS_HAS_WORD, 0, $text, $xattr);
        $res = self::$_server->execCommand($cmd, XS_CMD_OK_INFO);
        return $res->buf === 'OK';
    }
    private function applySetting($text)
    {
        self::$_server->reopen();
        foreach ($this->_setting as $key => $cmd) {
            if (is_array($cmd)) {
                foreach ($cmd as $_cmd) {
                    self::$_server->execCommand($_cmd);
                }
            } else {
                self::$_server->execCommand($cmd);
            }
        }
        return XS::convert($text, 'UTF-8', self::$_charset);
    }
}