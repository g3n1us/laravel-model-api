<?php

namespace G3n1us\ModelApi;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use ReflectionClass;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Database\Query\Builder as QueryBuilder;

use Str;
use Arr;
use Route;


// Usage: modelname is the snake cased version of the model. If singular, will display either the first model or the one specified by id.
// If plural, a paginated list of items will be output.
// If html is specified, the __toString method will explicitly be called, returning any overloaded verion in the model, usually custom html output.

//	$_GET parameters for plural form only:
//	paginate - 0 results in no pagination, default = 1
//	per_page = results per page, disables pagination, integer default is pagination default, 15
//	offset = offset, return results starting at this offset
//	limit - limit to return, disables pagination
//	pluck/property - pluck a value from the returned objects
//	$_GET parameters for singular form only:
//	html - returns the model's overloaded __toString method instead of the default JSON representation, also an URL parameter


class ModelAPIController extends BaseController{

	public $service;

	public $modelname;

	public $model;


    public function __construct(ApiService $service){
		$this->service = $service;
		if($current_route = Route::current()){
    		$this->modelname = basename($current_route->getPrefix());
            $this->service->parameters['modelname'] = $this->modelname;
		}
        $this->model = $this->findModel();
    }


    private function findModel(){
        $model = collect(config('g3n1us_model_api.public_models', []))->first(function($m){
            return preg_match('/'.$m::rest_regex().'/', $this->modelname);
        });
        return $model;
    }


    public function route($id = null, $property = null){
        $this->service->parameters['id'] = $id;

        $this->service->parameters['property'] = $property;

	    $this->service->is_api = true;

	    $this->service->boot();

        return response($this->service->toArray())->header('Access-Control-Allow-Origin', '*');
    }



    public function store(){
	    $request = request();
	    $created = $this->model::create($request->all());
	    return $created;
    }


    public function destroy($id){
        $this->service->parameters['id'] = $id;
	    $this->service->is_api = true;
	    return $this->service->findOrFail()->delete();
    }


    public function update($id){
        $this->service->parameters['id'] = $id;
	    $this->service->is_api = true;

	    $updated = $this->service->findOrFail()->update(request()->all());
	    return $updated;
    }



}

