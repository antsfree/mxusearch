<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class HotWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:list-hotwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List All Hot Words.';

    /**
     * CheckService constructor.
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
        try {
            // 服务获取的热词
            $hot_words = Mxusearch::getHotWords();
            if (!$hot_words) {
                return $this->line("暂无热词!\n");
            }
            foreach ($hot_words as $hot_word => $count) {
                $this->line("热词: [$hot_word] ,搜索频次为: [$count] 次。");
            }
        } catch (\Exception $e) {
            return $this->error("讯搜服务异常\n");
        }
    }
}
