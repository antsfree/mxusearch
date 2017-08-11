<?php
namespace Antsfree\Mxusearch;

use Antsfree\Mxusearch\SDK\XS;
use Antsfree\Mxusearch\SDK\XSDocument as Doc;

class MxusearchService
{
    protected $xs;

    protected $doc;

    public function __construct()
    {
        $ini_file = realpath(__DIR__ . "/../config/mxusearch.ini");
        $this->xs = new XS($ini_file);
        // 文档实例化
        $this->doc = new Doc();
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
        $ret = $this->index()->add($doc)->flushIndex();

        // return bool
        return $ret;
    }

    /**
     * 删除指定索引方法
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
     * 更新索引方法
     *
     * @param $data
     */
    public function updateIndex($data)
    {
        $doc = $this->doc;
        $doc->setFields($data);
        $this->index()->update($data)->flushIndex();
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
     * 完全重建索引方法{暂不开放使用}
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
     * @return mixed
     */
    public function getIndexCount($key = null)
    {
        $total = $this->search()->count($key);

        return $total;
    }

    /**
     * 获取热词,默认选取本周
     * {建议自动收录实现一周一次的自动收录}
     * {内含搜索频次}
     *
     * @return array
     */
    public function getHotWordsWithRate()
    {
        // 获取热词,默认最大 50 个.currnum 表示本周
        $words = $this->search()->getHotQuery(config('mxusearch.max_hot_words'), 'currnum') ?: [];

        return $words;
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
     * @param $search_array
     */
    public function searchIndex($search_array)
    {
        // 搜索模式
        $search_mode = config('mxusearch.search_mode');
        // 搜索内容
        $search_text = $search_array['search_text'];
        // 查询
        if ($search_mode) {
            $this->search()->search($search_text);
        } else {
            $this->search()->search('title:' . $search_text);
        }
    }
}