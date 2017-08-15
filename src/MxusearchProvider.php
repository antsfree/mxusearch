<?php
namespace Antsfree\Mxusearch;

use Antsfree\Mxusearch\Console\AddIndex;
use Antsfree\Mxusearch\Console\CheckService;
use Antsfree\Mxusearch\Console\ClearIndex;
use Antsfree\Mxusearch\Console\DeleteIndex;
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
        $this->addConsoleCommands();
    }

    /**
     * 扩展 console 命令
     */
    public function addConsoleCommands()
    {
        // search
        $this->app->bindShared('mxusearch.search', function () {
            return new SearchIndex();
        });
        // clear
        $this->app->bindShared('mxusearch.clear', function () {
            return new ClearIndex();
        });
        // delete by ids
        $this->app->bindShared('mxusearch.index.del', function () {
            return new DeleteIndex();
        });
        // delete by ids
        $this->app->bindShared('mxusearch.check.service', function () {
            return new CheckService();
        });

        $this->commands([
            'mxusearch.search',
            'mxusearch.clear',
            'mxusearch.index.del',
            'mxusearch.check.service',
        ]);
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
