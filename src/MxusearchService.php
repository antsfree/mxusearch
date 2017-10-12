<?php
namespace Antsfree\Mxusearch;

use Antsfree\Mxusearch\Sdk\XS;
use Antsfree\Mxusearch\Sdk\XSDocument as Doc;
use Antsfree\Mxusearch\Sdk\XSServer as Server;
use Antsfree\Mxusearch\Sdk\XSTokenizerScws;

class MxusearchService
{
    protected $xs;

    protected $doc;

    protected $server;

    /**
     * ini 配置文件绝对路径
     * @var string
     */
    protected $ini_file;

    public function __construct()
    {
        $this->ini_file = config_path(config('mxusearch.ini_file_name'));
        $this->xs       = new XS($this->ini_file);
        $this->doc      = new Doc();
        $this->server   = new Server();
    }

    /**
     *
     * 索引入口
     *
     * @return mixed
     */
    public function index()
    {
        return $this->xs->index;
    }

    /**
     * 搜索入口
     *
     * @return mixed
     */
    public function search()
    {
        return $this->xs->search;
    }

    /**
     * 增加索引方法
     *
     * @param $data
     *
     * @return mixed
     */
    public function addIndex($data)
    {
        $doc = $this->doc;
        $doc->setFields($data);
        $ret = $this->index()->add($doc);
        if ($ret) {
            $this->flushIndex();

            return true;
        }

        return false;
    }

    /**
     * 删除指定索引方法,支持批量删除
     *
     * @param array $arr_id
     *
     * @return mixed
     */
    public function deleteIndex(array $arr_id)
    {
        $ret = $this->index()->del($arr_id)->flushIndex();

        // return bool
        return $ret;
    }

    /**
     * 清空索引方法
     */
    public function cleanIndex()
    {
        $this->index()->clean();
        // 查询剩余索引数量
        $total = $this->getIndexCount() ?: 0;
        if ($total) {
            return false;
        }

        return true;
    }

    /**
     * 完全重建索引方法{暂不使用}
     *
     * @param $data
     */
    protected function rebuildIndex($data)
    {
        $doc = $this->doc;
        $doc->setFields($data);
        $this->index()->beginRebuild();
        $this->index()->add($doc);
        $this->index()->endRebuild();
    }

    /**
     * 获取索引总数
     *
     * @param null $key
     *
     * @return mixed
     */
    public function getIndexCount($key = null)
    {
        $total = $this->search()->count($key);

        return $total;
    }

    /**
     * 获取搜索内容匹配数量
     *
     * @param $text
     *
     * @return int
     */
    public function getMatchNum($text)
    {
        $count = $this->search()->setQuery($text)->count() ?: 0;

        return $count;
    }

    /**
     * 索引搜索
     *
     * @param        $key
     * @param string $field
     * @param int    $limit
     * @param int    $page
     *
     * @return array
     */
    public function searchIndex($key, $field = '', $limit = 0, $page = 1)
    {
        // 模糊搜索
        $this->search()->setFuzzy(true);
        // 同义词搜索
        $this->search()->setAutoSynonyms(true);
        // 查询
        $query = isset($field) && $field ? $field . ':' . $key : $key;
        // set query
        $this->search()->setQuery($query);
        // limit
        if ($limit) {
            $skip = ($page - 1) * $limit;
            $this->search()->setLimit($limit, $skip);
        }
        // search time
        $search_begin    = microtime(true);
        $doc             = $this->search()->search();
        $search_duration = microtime(true) - $search_begin;
        // get search result
        $result = [];
        if ($doc) {
            foreach ($doc as $k => $v) {
                foreach ($v as $kk => $vv) {
                    $result[$k][$kk] = $vv;
                }
            }
        }
        // 获取默认10个关联搜索词
        $related_words = $this->search()->getRelatedQuery($key, 10) ?: [];
        // 整合结果
        $search_result = [
            'result'        => $result,
            'duration'      => $search_duration,
            'related_words' => $related_words,
        ];
        // refresh search log
        $this->flushLogging();

        return $search_result;
    }

    /**
     * 强制刷新服务端的当前库的索引缓存
     *
     * @return bool
     */
    public function flushIndex()
    {
        $ret = $this->index()->flushIndex();
        if ($ret) {
            return true;
        }

        return false;
    }

    /**
     * 强制刷新服务端当前项目的搜索日志
     *
     * @return bool
     */
    public function flushLogging()
    {
        $ret = $this->index()->flushLogging();
        if ($ret) {
            return true;
        }

        return false;
    }

    /**
     * 搜索服务状态检测
     *
     * @return bool
     */
    public function checkServer()
    {
        if (!file_exists($this->ini_file)) {
            return false;
        }
        try {
            $count = Mxusearch::getIndexCount();
            if (isset($count)) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取热门关键词列表
     *
     * @return array
     */
    public function getHotWords()
    {
        $hot = $this->search()->getHotQuery();

        // array
        return $hot ?: [];
    }

    /**
     * 内容分词,获取关键词
     *
     * @param     $text
     * @param int $count
     *
     * @return array
     */
    public function getKeyWords($text, $count = 10)
    {
        $scws = new XSTokenizerScws();
        // 忽略标点
        $key_words = $scws->getTops($text, $count, '') ?: [];
        $words     = [];
        if ($key_words) {
            foreach ($key_words as $k => $v) {
                $words[] = $v['word'];
            }
        }

        // array
        return $words;
    }
}