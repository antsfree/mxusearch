<?php
namespace Antsfree\Mxusearch\Console;

use Illuminate\Console\Command;
use Antsfree\Mxusearch\Mxusearch as Mxusearch;

class Search extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xunsearch:search {key}';

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
        $key = $this->argument('key');
        $res = Mxusearch::searchIndex($key);
    }
}