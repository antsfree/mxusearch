<?php
namespace Antsfree\Mxusearch;

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
        // add index
        $this->app->bindShared('mxusearch.index.add', function () {
            return new Console\AddIndex();
        });
        // search
        $this->app->bindShared('mxusearch.search', function () {
            return new Console\SearchIndex();
        });
        // clear
        $this->app->bindShared('mxusearch.clear', function () {
            return new Console\ClearIndex();
        });
        // delete by ids
        $this->app->bindShared('mxusearch.index.del', function () {
            return new Console\DeleteIndex();
        });

        $this->commands([
            'mxusearch.index.add',
            'mxusearch.search',
            'mxusearch.clear',
            'mxusearch.index.del',
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
