<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class ScwsText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:scws';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scws Text for keywords.';

    /**
     * FlushIndex constructor.
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
        $text     = $this->ask('请输入需要分词的内容:');
        $word_num = $this->ask('请设置分词后返回关键词的数量:');
        $count    = $word_num ? abs(intval($word_num)) : 10;
        try {
            $keywords = Mxusearch::getKeyWords($text, $count);
            if ($keywords) {
                foreach ($keywords as $k => $v) {
                    $k += 1;
                    $this->line("关键词[{$k}]:{$v}");
                }
                $word_num = count($keywords);
                $this->line("该内容共产生 < {$word_num} > 个关键词");
            } else {
                $this->error("未获取到分词,请检查服务\n");
            }
        } catch (\Exception $e) {
            return $this->error("讯搜服务异常\n");
        }
    }
}
