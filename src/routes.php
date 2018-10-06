<?php
use Illuminate\Http\Request;
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

  Route::get('api', function(){
//     dd(collect(\Route::getRoutes()->getIterator()));
    $routes = collect(\Route::getRoutes()->getIterator())->map(function($v,$k){
      $item = [];
      $item['methods'] = implode('|', $v->methods());
      $item['uri'] = $v->uri;
//       dd($item);
//       dd($v);
      return $item;
    })->filter(function($v){
      return starts_with($v['uri'], 'api');
    });
    return view('g3n1us_model_api::index', ['routes' => $routes]);
  });

	Route::get('api/{modelname}/{id?}/{property?}', function(Request $request, $modelname, $id = null, $property = false){
		header('Access-Control-Allow-Origin: *');
		$config = config('g3n1us_model_api.public_models', []);
		$basenames = array_map(function($v, $k){
  		return class_basename($v);
		}, $config, array_keys($config));

		$classmap = array_combine($basenames, $config);

		foreach($classmap as $k => $v)
  		$classmap[$k] = str_replace($k, '', $v);

		$modelbasename = studly_case(strtolower(str_singular($modelname)));
		$ns = isset($classmap[$modelbasename]) ? $classmap[$modelbasename] : "App\\";

		$classname = $ns . $modelbasename;
		$model = $classname;

		$public_models = config('g3n1us_model_api.public_models', []);
		abort_if(!auth()->check() && !in_array($classname, $public_models), 401, 'Unauthorized');
		$html = $request->input('html', false);
		$offset = $request->input('offset', 0);
		$limit = $request->input('limit');
		$per_page = $request->input('per_page');
		$template = $request->input('template');
		$paginated = $request->input('paginate', true);
		$pluck = $property = $request->input('pluck', $request->input('property', $property));
		$where = $request->input('where');

		$is_plural = is_plural($modelname) || $id === '-';
		if($id && !$is_plural) {
			$m = $model::findOrFail($id);
			if($html && method_exists($m, 'display')) return $m->display($template);
			return $property ? $m->$property : $m;
		}
		else if($is_plural){
  		if($id == 'where' || $where){

    		$where_args = $where ? $where : explode('|', $property);
    		$r = call_user_func_array([$model, 'where'], $where_args);
    		$results = $r->get();
    		$pluck = false;
  		}
			else if($paginated || $per_page)
				$results =  $model::paginate($per_page);
			else
				$results = $limit ? $model::skip($offset)->take($limit)->get() : $model::get();

			if($html && method_exists($results[0], 'display')){
				$return = [];
				foreach($results as $result)
					$return[] = $result->display();
				return implode("\n", $return);
			}

			return $pluck ? $results->pluck($pluck) : $results;
		}
		else {
			if($html && method_exists($model::first(), 'display'))
				return $model::first()->display();
			return $model::first();
		}

	}); //->middleware(['auth']);
