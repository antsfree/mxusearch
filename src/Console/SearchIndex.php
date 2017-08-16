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
        $search_area = $this->choice('选择需要搜索的字段:', [self::FULL_SEARCH, self::TITLE_ONLY, self::CONTENT_ONLY, self::KEYWORD_ONLY]);
        $key         = $this->ask('请输入需要查询的内容:');
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
            default:
                $field = null;
                break;
        }

        $ret = Mxusearch::searchIndex($key, $field);
        print_r($ret);
    }
}
