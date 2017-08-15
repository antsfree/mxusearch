<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class FlushIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:flush-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush search index and search log.';

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
        try {
            $index_flush = Mxusearch::flushIndex();
            if ($index_flush) {
                $this->line("索引强制刷新成功\n");
            } else {
                $this->error("索引强制刷新失败\n");
            }
            $log_flush = Mxusearch::flushLogging();
            if ($log_flush) {
                $this->line("搜索日志强制刷新成功\n");
            } else {
                $this->error("搜索日志强制刷新失败\n");
            }
        } catch (\Exception $e) {
            $this->error("讯搜服务异常\n");
        }
    }
}
