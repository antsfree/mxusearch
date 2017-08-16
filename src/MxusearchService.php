<?php
namespace Antsfree\Mxusearch;

use Antsfree\Mxusearch\Sdk\XS;
use Antsfree\Mxusearch\Sdk\XSDocument as Doc;
use Antsfree\Mxusearch\Sdk\XSServer as Server;

class MxusearchService
{
    protected $xs;

    protected $doc;

    protected $server;

    public function __construct()
    {
        $ini_file = config_path('mxusearch.ini');
        $this->xs = new XS($ini_file);
        // 文档实例化
        $this->doc    = new Doc();
        $this->server = new Server();
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
     * @param $key @关键词
     * @param null $field @匹配字段
     *
     * @return array
     */
    public function searchIndex($key, $field = null)
    {
        // 模糊搜索
        $this->search()->setFuzzy(true);
        // 同义词搜索
        $this->search()->setAutoSynonyms(true);
        // 查询
        if (!$field) {
            // set query
            $this->search()->setQuery($key);
        } else {
            // set query
            $this->search()->setQuery($field . ':' . $key);
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
        // 整合结果
        $search_result = [
            'result'   => $result,
            'duration' => $search_duration,
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
        $ini = config_path('mxusearch.ini');
        if (!file_exists($ini)) {
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

        return $hot ?: [];
    }
}