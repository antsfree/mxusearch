<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class ClearIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Fulltext index.';

    /**
     * ClearIndex constructor.
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
            $is_empty = Mxusearch::cleanIndex();
            if ($is_empty) {
                return $this->line("数据已清空\n");
            } else {
                return $this->error("数据清空失败\n");
            }
        } catch (\Exception $e) {
            return $this->error("讯搜服务异常\n");
        }
    }
}
