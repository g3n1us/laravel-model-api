<?php

namespace G3n1us\ModelApi;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use ReflectionClass;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use Str;


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

    protected $is_plural;
    protected $html;
    protected $offset;
    protected $limit;
    protected $per_page;
    protected $template;
    protected $paginated;
    protected $pluck;
    protected $where;

    protected $model;
    protected $connection;
    protected $query_builder;
    protected $eloquent_builder;

	public $parameters = [
		'modelname' => null,
		'id' => null,
		'property' => false,
	];

    public function __construct(Request $request){
	    $route = $request->route();
	    foreach($route->parameters() as $p => $v){
		    $this->parameters[$p] = $v;
	    }

		$this->model = $this->resolveModelName($this->parameters['modelname']);
	    $this->connection = (new $this->model)->getConnection();
	    $this->query_builder = new QueryBuilder($this->connection);
	    $this->eloquent_builder = new EloquentBuilder($this->query_builder);
		$this->eloquent_builder->setModel(new $this->model);

		$this->is_plural = self::is_plural($this->parameters['modelname']) || $this->parameters['id'] === '-';
		$this->html = $request->input('html', false);
		$this->offset = $request->input('offset', 0);
		$this->limit = $request->input('limit');
		$this->per_page = $request->input('per_page');
		$this->template = $request->input('template');
		$this->paginated = $request->input('paginate', true);
		$this->pluck = $request->input('pluck', $request->input('property'));
		$this->where = $this->resolveWhere();

    }


// $modelname, $id = null, $property = false
    public function route(){
	    $request = request();

		if(!$this->is_plural){
			$response = $this->returnSingle();
		}
		else{
			$response = $this->returnMany();
		}
        return response($response)->header('Access-Control-Allow-Origin', '*');
    }


	private function returnMany(){
		$this->resolveWhere();

		if($this->paginated || $this->per_page){
			$results =  $this->eloquent_builder->paginate($this->per_page);
		}

		else if($this->limit){
			$results = $this->eloquent_builder->skip($this->offset)->take($this->limit)->get();
		}
		else{
			$results = $this->eloquent_builder->get();
		}

		if($this->html && method_exists($results[0], 'display')){
			$return = [];
			foreach($results as $result)
				$return[] = $result->display();
			return implode("\n", $return);
		}

		return $this->pluck ? $results->pluck($pluck) : $results;
	}


    private function returnSingle(){
	    $this->resolveWhere();
		[ 'id' => $id ] = $this->parameters;
	    if($id){
			$m = $this->eloquent_builder->findOrFail($id);
			if($this->html && method_exists($m, 'display')) return $m->display($this->template);
			return $this->property ? $m->{$this->property} : $m;
	    }

		else{
			if($this->html && method_exists($this->eloquent_builder->first(), 'display'))
				return $this->eloquent_builder->first()->display();
			return $this->eloquent_builder->first();
		}
    }


	private function resolveModelName($modelname){
		$public_models = config('g3n1us_model_api.public_models', []);

		$basenames = array_map('class_basename', $public_models);

		$classmap = array_combine($basenames, $public_models);

		foreach($classmap as $k => $v)
            $classmap[$k] = str_replace($k, '', $v);

		$modelbasename = studly_case(strtolower(str_singular($modelname)));
		$ns = isset($classmap[$modelbasename]) ? $classmap[$modelbasename] : "App\\";

		$classname = $ns . $modelbasename;

		abort_if(!auth()->check() && !in_array($classname, $public_models), 401, 'Unauthorized');

		return $classname;
	}

// 	return collection
	private function getWhereMethods(){
		return collect((new \ReflectionClass(EloquentBuilder::class))->getMethods())
						->pluck('name')
						->filter(function($v){
							return Str::contains(strtolower($v), 'where');
						})
						->map(function($v){
							return [$v, Str::snake($v)];
						})
						->flatten()
						->unique()
						->values();
	}


	// returns boolean false or array of [method_name, query (array or string)]
	private function isWhere(){
		$where_method_names = $this->getWhereMethods();
		if($where_method_names->contains($this->parameters['id'])){

			[ 'id' => $method_name, 'property' => $query ] = $this->parameters;

			$this->parameters['id'] = null;
			$this->parameters['property'] = false;
			return [
				'method_name' => $method_name,
				'query' => $query,
			];
		}
		else if($method_name = $where_method_names->intersect(array_keys(request()->all()))->first()){
			return [
				'method_name' => $method_name,
				'query' => request()->input($method_name),
			];

		}

		return false;
	}


	private function resolveWhere(){
		if($where = $this->isWhere());
		else return;

		[ 'method_name' => $method_name, 'query' => $query ] = $where;

// 		$operators = $this->query_builder->operators;

		if(is_string($query)){
			// is it an "in"
			if(preg_match('/^(.*?)\sin\s(.*?)$/', $query, $matches)){
				$matches[2] = array_map('trim', explode(',', $matches[2]));
				$method_name = 'whereIn';
			}
			else if(preg_match('/^(.*?)\|(.*?)$/', $query)){
				$matches = array_map('trim', explode('|', "|$query"));
			}
			else if(preg_match('/^(.*?)([!=<>]{1,3})(.*?)$/', $query, $matches));

			array_shift($matches);
		}
		else $matches = $query;

		call_user_func_array([$this->eloquent_builder, $method_name], $matches);

		return $this->eloquent_builder;
	}




	public static function is_plural($string){
		return Str::plural($string) == $string;
	}



}
