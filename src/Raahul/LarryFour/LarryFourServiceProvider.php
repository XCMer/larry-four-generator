<?php namespace Raahul\LarryFour;

use Illuminate\Support\ServiceProvider;
use \Raahul\LarryFour\Command\Generate;

class LarryFourServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('raahul/larryfour');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['larry.generate'] = $this->app->share(function($app) {
			return new Generate;
		});

		$this->commands('larry.generate');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
