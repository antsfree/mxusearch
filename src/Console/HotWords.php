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
            $server_hot = Mxusearch::getHotWords();
            if (!$server_hot) {
                return $this->line("搜索服务正常,当前共有 $count 条索引\n");
            }
        } catch (\Exception $e) {
            return $this->error("讯搜服务异常\n");
        }
    }
}
