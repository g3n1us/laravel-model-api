<?php

namespace G3n1us\ModelApi;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider{

  /**
   * Register bindings in the container.
   *
   * @return void
   */
	public function register(){

		$this->mergeConfigFrom(
			__DIR__.'/config.php', 'g3n1us_model_api'
		);
		$this->loadRoutesFrom(__DIR__.'/routes.php');


		$this->app->singleton(AccessRest::class, function ($app) {
		    return new AccessRest;
		});

		$this->app->singleton(AccessDeclarative::class, function ($app) {
		    return new AccessDeclarative;
		});


		$this->app->singleton(ApiService::class, function ($app) {
			return new ApiService;
		});


  }


	/**
	* Perform post-registration booting of services.
	*
	* @return void
	*/
	public function boot(){

		$this->loadViewsFrom(__DIR__.'/views', 'g3n1us_model_api');

		$this->publishes([
			__DIR__.'/config.php' => config_path('g3n1us_model_api.php'),
		]);

  }
}
