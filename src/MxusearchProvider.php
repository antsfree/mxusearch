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
        $ini_dir = __DIR__ . '/../config/mxusearch.ini';
        $php_dir = __DIR__ . '/../config/mxusearch.php';
        if (function_exists('config_path')) {
            $publishIniPath = config_path('mxusearch.ini');
            $publishPhpPath = config_path('mxusearch.php');
        } else {
            $publishIniPath = base_path('config/mxusearch.ini');
            $publishPhpPath = base_path('config/mxusearch.php');
        }
        $this->publishes([
            $ini_dir => $publishIniPath,
            $php_dir => $publishPhpPath,
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
