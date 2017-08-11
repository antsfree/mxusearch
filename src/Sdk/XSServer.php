<?php
namespace Antsfree\Mxusearch\Sdk;

class XSServer extends XSComponent
{
    const FILE = 0x01;
    const BROKEN = 0x02;
    public $xs;
    protected $_sock, $_conn;
    protected $_flag;
    protected $_project;
    protected $_sendBuffer;

    public function __construct($conn = null, $xs = null)
    {
        $this->xs = $xs;
        if ($conn !== null) {
            $this->open($conn);
        }
    }

    public function __destruct()
    {
        $this->xs = null;
        $this->close();
    }

    public function open($conn)
    {
        $this->close();
        $this->_conn = $conn;
        $this->_flag = self::BROKEN;
        $this->_sendBuffer = '';
        $this->_project = null;
        $this->connect();
        $this->_flag ^= self::BROKEN;
        if ($this->xs instanceof XS) {
            $this->setProject($this->xs->getName());
        }
    }

    public function reopen($force = false)
    {
        if ($this->_flag & self::BROKEN || $force === true) {
            $this->open($this->_conn);
        }
        return $this;
    }

    public function close($ioerr = false)
    {
        if ($this->_sock && !($this->_flag & self::BROKEN)) {
            if (!$ioerr && $this->_sendBuffer !== '') {
                $this->write($this->_sendBuffer);
                $this->_sendBuffer = '';
            }
            if (!$ioerr && !($this->_flag & self::FILE)) {
                $cmd = new XSCommand(XS_CMD_QUIT);
                fwrite($this->_sock, $cmd);
            }
            fclose($this->_sock);
            $this->_flag |= self::BROKEN;
        }
    }

    public function getConnString()
    {
        $str = $this->_conn;
        if (is_int($str) || is_numeric($str)) {
            $str = 'localhost:' . $str;
        } elseif (strpos($str, ':') === false) {
            $str = 'unix://' . $str;
        }
        return $str;
    }

    public function getSocket()
    {
        return $this->_sock;
    }

    public function getProject()
    {
        return $this->_project;
    }

    public function setProject($name, $home = '')
    {
        if ($name !== $this->_project) {
            $cmd = array('cmd' => XS_CMD_USE, 'buf' => $name, 'buf1' => $home);
            $this->execCommand($cmd, XS_CMD_OK_PROJECT);
            $this->_project = $name;
        }
    }

    public function setTimeout($sec)
    {
        $cmd = array('cmd' => XS_CMD_TIMEOUT, 'arg' => $sec);
        $this->execCommand($cmd, XS_CMD_OK_TIMEOUT_SET);
    }

    public function execCommand($cmd, $res_arg = XS_CMD_NONE, $res_cmd = XS_CMD_OK)
    {
        if (!$cmd instanceof XSCommand) {
            $cmd = new XSCommand($cmd);
        }
        if ($cmd->cmd & 0x80) {
            $this->_sendBuffer .= $cmd;
            return true;
        }
        $buf = $this->_sendBuffer . $cmd;
        $this->_sendBuffer = '';
        $this->write($buf);
        if ($this->_flag & self::FILE) {
            return true;
        }
        $res = $this->getRespond();
        if ($res->cmd === XS_CMD_ERR && $res_cmd != XS_CMD_ERR) {
            throw new XSException($res->buf, $res->arg);
        }
        if ($res->cmd != $res_cmd || ($res_arg != XS_CMD_NONE && $res->arg != $res_arg)) {
            throw new XSException('Unexpected respond {CMD:' . $res->cmd . ', ARG:' . $res->arg . '}');
        }
        return $res;
    }

    public function sendCommand($cmd)
    {
        if (!$cmd instanceof XSCommand) {
            $cmd = new XSCommand($cmd);
        }
        $this->write(strval($cmd));
    }

    public function getRespond()
    {
        $buf = $this->read(8);
        $hdr = unpack('Ccmd/Carg1/Carg2/Cblen1/Iblen', $buf);
        $res = new XSCommand($hdr);
        $res->buf = $this->read($hdr['blen']);
        $res->buf1 = $this->read($hdr['blen1']);
        return $res;
    }

    public function hasRespond()
    {
        if ($this->_sock === null || $this->_flag & (self::BROKEN | self::FILE)) {
            return false;
        }
        $wfds = $xfds = array();
        $rfds = array($this->_sock);
        $res = stream_select($rfds, $wfds, $xfds, 0, 0);
        return $res > 0;
    }

    protected function write($buf, $len = 0)
    {
        $buf = strval($buf);
        if ($len == 0 && ($len = $size = strlen($buf)) == 0) {
            return true;
        }
        $this->check();
        while (true) {
            $bytes = fwrite($this->_sock, $buf, $len);
            if ($bytes === false || $bytes === 0 || $bytes === $len) {
                break;
            }
            $len -= $bytes;
            $buf = substr($buf, $bytes);
        }
        if ($bytes === false || $bytes === 0) {
            $meta = stream_get_meta_data($this->_sock);
            $this->close(true);
            $reason = $meta['timed_out'] ? 'timeout' : ($meta['eof'] ? 'closed' : 'unknown');
            $msg = 'Failed to send the data to server completely ';
            $msg .= '(SIZE:' . ($size - $len) . '/' . $size . ', REASON:' . $reason . ')';
            throw new XSException($msg);
        }
    }

    protected function read($len)
    {
        if ($len == 0) {
            return '';
        }
        $this->check();
        for ($buf = '', $size = $len; ;) {
            $bytes = fread($this->_sock, $len);
            if ($bytes === false || strlen($bytes) == 0) {
                break;
            }
            $len -= strlen($bytes);
            $buf .= $bytes;
            if ($len === 0) {
                return $buf;
            }
        }
        $meta = stream_get_meta_data($this->_sock);
        $this->close(true);
        $reason = $meta['timed_out'] ? 'timeout' : ($meta['eof'] ? 'closed' : 'unknown');
        $msg = 'Failed to recv the data from server completely ';
        $msg .= '(SIZE:' . ($size - $len) . '/' . $size . ', REASON:' . $reason . ')';
        throw new XSException($msg);
    }

    protected function check()
    {
        if ($this->_sock === null) {
            throw new XSException('No server connection');
        }
        if ($this->_flag & self::BROKEN) {
            throw new XSException('Broken server connection');
        }
    }

    protected function connect()
    {
        $conn = $this->_conn;
        if (is_int($conn) || is_numeric($conn)) {
            $host = 'localhost';
            $port = intval($conn);
        } elseif (!strncmp($conn, 'file://', 7)) {
            $conn = substr($conn, 7);
            if (($sock = @fopen($conn, 'wb')) === false) {
                throw new XSException('Failed to open local file for writing: `' . $conn . '\'');
            }
            $this->_flag |= self::FILE;
            $this->_sock = $sock;
            return;
        } elseif (($pos = strpos($conn, ':')) !== false) {
            $host = substr($conn, 0, $pos);
            $port = intval(substr($conn, $pos + 1));
        } else {
            $host = 'unix://' . $conn;
            $port = -1;
        }
        if (($sock = @fsockopen($host, $port, $errno, $error, 5)) === false) {
            throw new XSException($error . '(C#' . $errno . ', ' . $host . ':' . $port . ')');
        }
        $timeout = ini_get('max_execution_time');
        $timeout = $timeout > 0 ? ($timeout - 1) : 30;
        stream_set_blocking($sock, true);
        stream_set_timeout($sock, $timeout);
        $this->_sock = $sock;
    }
}