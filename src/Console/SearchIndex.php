<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class SearchIndex extends Command
{
    const FULL_SEARCH = '(默认)全局搜索';
    const TITLE_ONLY = '仅标题';
    const CONTENT_ONLY = '仅正文';
    const KEYWORD_ONLY = '仅关键词';
    const SELF_CHOICE = '自定义字段';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search index by field and key params, support two search mode.';

    /**
     * SearchIndex constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $search_area = $this->choice('选择需要搜索的字段:', [self::FULL_SEARCH, self::TITLE_ONLY, self::CONTENT_ONLY, self::KEYWORD_ONLY, self::SELF_CHOICE]);
        switch ($search_area) {
            case self::FULL_SEARCH:
                $field = null;
                break;
            case self::TITLE_ONLY:
                $field = 'title';
                break;
            case self::CONTENT_ONLY:
                $field = 'content';
                break;
            case self::KEYWORD_ONLY:
                $field = 'keywords';
                break;
            case self::SELF_CHOICE:
                $field = $this->ask('请输入需要匹配的字段:');
                break;
            default:
                $field = null;
                break;
        }
        $key = $this->ask('请输入需要查询的内容:');

        // total index
        try {
            $total  = Mxusearch::getIndexCount();
            $search = Mxusearch::searchIndex($key, $field);
        } catch (\Exception $e) {
            return $this->error("讯搜服务异常\n");
        }
        $count    = isset($search['result']) ? count($search['result']) : 0;
        $duration = isset($search['duration']) ? $search['duration'] : 0;
        if (isset($search['result']) && $search['result']) {
            $n = 0;
            foreach ($search['result'] as $k => $v) {
                $n++;
                // 限制显示10条
                if ($n > 10) {
                    break;
                }
                $k += 1;
                $text = "第{$k}条:\n";
                foreach ($v as $kk => $vv) {
                    $text .= "[{$kk}]:{$vv}\n";
                }

                $this->line($text);
            }
            unset($text);
        }

        // 搜索结果
        $this->line("搜索类型:{$search_area};\n根据关键词 < $key >, 在 {$total} 条索引中共查询到 {$count} 条数据, 用时 {$duration} \n");
        // 相关搜索词
        $related_words = '';
        if ($search['related_words']) {
            foreach ($search['related_words'] as $related_word) {
                $related_words .= $related_word . " ";
            }
        }
        $this->line("相关搜索词: $related_words");
    }
}
