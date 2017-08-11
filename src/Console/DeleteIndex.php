<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class DeleteIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mxusearch:delete {str_ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete index by ids. exp: 1,2,3';

    /**
     * DeleteIndex constructor.
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
        $str_ids = $this->argument('str_ids');
        if (!$str_ids) {
            echo '请输入需要删除的索引ID';
        }
        $arr_ids = explode(',', $str_ids);
        $ret     = Mxusearch::deleteIndex($arr_ids);
        var_dump($ret);
    }
}
