<?php
namespace Antsfree\Mxusearch\Console;

use Illuminate\Console\Command;
use Antsfree\Mxusearch\Mxusearch as Mxusearch;

class Clean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xunsearch:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a fulltext search result.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $res = Mxusearch::cleanIndex();
        if ($res) {
            echo "Clean Index Success !";
        }else{
            echo "Service Unavaiable !";
        }
    }
}