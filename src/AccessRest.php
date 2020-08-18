<?php

namespace G3n1us\ModelApi;

use Str;

class AccessRest{
	//use ApiTrait;

	public function __construct(){
		$this->service = app()->make(ApiService::class);

		$this->boot();

	}


	public static function is_plural($string){
		return Str::plural($string) == $string;
	}


	public function boot(){
		['modelname' => $modelname, 'id' => $id, 'property' => $property] = $this->service->parameters;

		$this->service->model = $modelname;
		$this->service->eloquent_builder = $this->service->model::query();

		$this->service->pluck = request()->input('pluck', request()->input('property', $property));

		$this->service->is_plural = self::is_plural($modelname) || $id === '-';
		$this->service->html = request()->input('html', false);
		$this->service->offset = request()->input('offset', 0);
		$this->service->limit = request()->input('limit');
		$this->service->per_page = request()->input('per_page');
		$this->service->template = request()->input('template');
		$this->service->paginated = request()->input('paginate', true);
// 		$this->service->where = $this->resolveWhere();
		$this->service->where = request()->input('where');
		$this->service->with = request()->input('with');
		// ordering
		if(request()->has('sort_by') || request()->has('order_by')){
			$this->service->order_direction = request()->input('order_direction', 'asc');
			$this->service->order_by = request()->input('sort_by') ?? request()->input('order_by');

		}

	}

}
