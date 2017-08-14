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
    protected $signature = 'search:clear-index';

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
        $is_empty = Mxusearch::cleanIndex();
        if ($is_empty) {
            echo '数据已清空';
        } else {
            echo '数据清空失败';
        }
    }
}
