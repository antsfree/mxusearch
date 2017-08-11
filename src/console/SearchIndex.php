<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;

class SearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mxusearch:search {field?} {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search index by field and key params, support two search mode.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $field                       = $this->argument('field');
        $search_array['search_text'] = $this->argument('key');

        $ret = Mxusearch::searchIndex($search_array);
        print_r($ret);
    }
}
