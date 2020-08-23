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



// Index and intro page


Route::prefix(config('g3n1us_model_api.uri_prefix', 'api'))->group(function() {
    $uri_prefix = config('g3n1us_model_api.uri_prefix', 'api');


    $public_models = config('g3n1us_model_api.public_models', []);


    $model_regex = collect($public_models)->map(function($c){
        return $c::rest_regex();
    })->flatten()->implode('|');


    $route_names = collect($public_models)->map(function($public_model){
        [$sngl, $plrl] = $public_model::rest_definitive_pathnames();
        return [
            [$public_model::rest_url($sngl), $sngl, $public_model, "rest.$plrl.get"],
            [$public_model::rest_url($plrl), $plrl, $public_model, "rest.$plrl.list"],
        ];
    })->flatten(1);

    Route::get('/', function() use($uri_prefix, $public_models, $model_regex, $route_names){
        $routes = collect(\Route::getRoutes()->getIterator())->filter(function($v) use($uri_prefix){
    		return starts_with($v->uri, $uri_prefix);
        })->map(function($v,$k){

    		$item = [];
    		$item['methods'] = implode('|', $v->methods());
    		$item['name'] = $v->getName();
    		$uri = $v->uri;
    		foreach($v->wheres as $key => $where){
        		$uri = str_replace("$key", $where, $uri);
    		}
    		$item['wheres'] = $v->wheres;
    		$item['uri'] = $uri;

    		return $item;
        });


        $eg = Arr::random($public_models);
        ['path' => $eg_url] = parse_url($eg::rest_singular_url());
        ['path' => $eg_url_plural] = parse_url($eg::rest_plural_url());

        return view('g3n1us_model_api::index', [
            'routes' => $routes,
            'prefix' => $uri_prefix,
            'public_models' => $public_models,
            'route_names' => $route_names,
            'eg' => $eg,
            'eg_url' => $eg_url,
            'eg_url_plural' => $eg_url_plural,
        ]);
    })->name('rest.index');



    foreach($route_names as $route_name){
        [$url, $slug, $model, $name] = $route_name;

        Route::prefix($slug)->group(function() use($url, $slug, $model, $name){
            Route::get("/{id?}/{property?}", 'G3n1us\\ModelApi\\ModelAPIController@route')->name($name);

            Route::delete("/{id}", 'G3n1us\\ModelApi\\ModelAPIController@destroy')->name("rest.$slug.destroy");

            Route::put("/{id}", 'G3n1us\\ModelApi\\ModelAPIController@update')->name("rest.$slug.update");

            Route::post("/", 'G3n1us\\ModelApi\\ModelAPIController@store')->name("rest.$slug.store");

// 			Route::resource('/', 'G3n1us\\ModelApi\\ModelAPIController');
            // if($slug === $model::rest_plural()){
                // set other methods
/*
                Route::resource('/', 'G3n1us\\ModelApi\\ModelAPIController')->only([
                    'store', 'update', 'destroy',
                ])->names([
                    'store' => "rest.$slug.store",
                    'update' => "rest.$slug.update",
                    'destroy' => "rest.$slug.destroy",
                ]);
*/

            // }
        });

    }


    if(!empty($model_regex)){
    	// Route::get('/{modelname}/{id?}/{property?}', 'G3n1us\\ModelApi\\ModelAPIController@redirect')->where('modelname', $model_regex);
    }

});


