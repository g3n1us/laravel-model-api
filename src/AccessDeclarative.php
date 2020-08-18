<?php

namespace G3n1us\ModelApi;

class AccessDeclarative{
	// use ApiTrait;

	public function __construct(){
		$this->service = app()->make(ApiService::class);

		$this->boot();

	}


	public function boot(){


	}
}
