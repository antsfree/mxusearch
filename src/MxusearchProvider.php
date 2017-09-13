<?php
namespace Antsfree\Mxusearch;

use Antsfree\Mxusearch\Console\AddIndex;
use Antsfree\Mxusearch\Console\CheckService;
use Antsfree\Mxusearch\Console\ClearIndex;
use Antsfree\Mxusearch\Console\DeleteIndex;
use Antsfree\Mxusearch\Console\FlushIndex;
use Antsfree\Mxusearch\Console\ScwsText;
use Antsfree\Mxusearch\Console\SearchIndex;
use Illuminate\Support\ServiceProvider;

class MxusearchProvider extends ServiceProvider
{
    protected $config = 'mxusearch';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // publish a config file
        $this->publishes([
            __DIR__ . '/../config/mxusearch.php' => config_path('mxusearch.php'),
        ], 'config');
        // import extend commands
        $this->commands(
            SearchIndex::class,
            ClearIndex::class,
            DeleteIndex::class,
            CheckService::class,
            FlushIndex::class,
            ScwsText::class
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mxusearch', function () {
            return new MxusearchService();
        });
    }
}
