<?php namespace Raahul\LarryFour;

use Illuminate\Support\ServiceProvider;
use \Raahul\LarryFour\Command\Generate;
use \Raahul\LarryFour\Command\Models;
use \Raahul\LarryFour\Command\Migrations;

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
		// Initialize the generate command
		$this->app['larry.generate'] = $this->app->share(function($app) {
			return new Generate;
		});

		// Initialize the models command
		$this->app['larry.models'] = $this->app->share(function($app) {
			return new Models;
		});

		// Initialize the migrations command
		$this->app['larry.migrations'] = $this->app->share(function($app) {
			return new Migrations;
		});

		$this->commands('larry.generate');
		$this->commands('larry.models');
		$this->commands('larry.migrations');
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
