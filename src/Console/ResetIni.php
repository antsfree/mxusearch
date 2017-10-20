<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class ResetIni extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reset-ini';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Mxusearch INI file by new config params';

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
            Mxusearch::resetIniFile();

            return $this->line('INI配置文件重置成功!');
        } catch (\Exception $e) {
            return $this->error("讯搜服务异常\n");
        }
    }
}
