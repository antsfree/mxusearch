<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ClearIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mxusearch:clear';

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
