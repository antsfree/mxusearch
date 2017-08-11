<?php
namespace Antsfree\Mxusearch\Sdk;

use Illuminate\Support\ServiceProvider;

class XsServiceProvider extends ServiceProvider
{
	public function boot(){
		
	}
	
    /**
     * register service provider
     */
    public function register()
    {
        $this->app->singleton('XS', function() { return new XS(); });
    }
}