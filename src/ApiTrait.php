<?php

namespace G3n1us\ModelApi;

use Str;
use Arr;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use Illuminate\Database\Query\Builder as QueryBuilder;


trait ApiTrait{

	public $service;


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


	public static function is_plural($string){
		return Str::plural($string) == $string;
	}


	private function resolveModelName($modelname){
		$public_models = config('g3n1us_model_api.public_models', []);

		$basenames = array_map('class_basename', $public_models);

		$classmap = array_combine($basenames, $public_models);

		foreach($classmap as $k => $v)
            $classmap[$k] = str_replace($k, '', $v);

		$modelbasename = Str::studly(strtolower(Str::singular($modelname)));
		$ns = isset($classmap[$modelbasename]) ? $classmap[$modelbasename] : "App\\";

		$classname = $ns . $modelbasename;

		abort_if(!in_array($classname, $public_models), 401, 'Unauthorized');

		return $classname;
	}


	// returns boolean false or array of [method_name, query (array or string)]
	private function isWhere(){
		$where_method_names = $this->getWhereMethods();
		if($where_method_names->contains($this->parameters['id'])){

			[ 'id' => $method_name, 'property' => $query ] = $this->parameters;

			$this->parameters['id'] = null;
			$this->parameters['property'] = false;
			return collect([[
				'method_name' => $method_name,
				'query' => $query,
			]]);
		}
		else if($method_names = $where_method_names->intersect(array_keys(request()->all()))){
			return $method_names->map(function($v){
				return [
					'method_name' => $v,
					'query' => request()->input($v),
				];
			})->values();


		}

		return false;
	}


	protected function handleWhere($value){


		if($wheres = $this->isWhere());
		else return;

		$where_args = [];

		foreach($wheres as $where){
			[ 'method_name' => $method_name, 'query' => $queries ] = $where;

			foreach($queries as $query){
				if(is_string($query)){
					// is it an "in"
					if(preg_match('/^(.*?)\sin\s(.*?)$/', $query, $matches)){
						$matches[2] = array_map('trim', explode(',', $matches[2]));
						$method_name = 'whereIn';
						array_shift($matches);
					}
					else if(preg_match('/^.*?([\|,]).*?$/', $query, $separator)){
						$separator = @$separator[1] ?? '|';
						$matches = $this->as_array($query, $separator)[0];
					}
					else if(preg_match('/^(.*?)([!=<>]{1,3})(.*?)$/', $query, $matches)){
						array_shift($matches);
					}



				}
				else {
					$matches = $query;
				}

				$where_args[] = [$method_name, $matches];

			}
		}

		foreach($where_args as $v){
			[$method, $args] = $v;

			$this->eloquent_builder->{$method}(...$args);
		}

	}



    protected function handleOrderBy($order_by){

		if($order_by){

    		if( method_exists($this->model, 'static_outline') ){
        		// sort and pluck from relations is only available is used within LaravelReactSync

        		$outline = $this->model::static_outline();
        		$q = [];
        		Arr::set($q, $order_by, true);
        		$base_order_prop = key($q);
        		$has_subprop = $base_order_prop != $order_by;
        		$subprop = 'id';
        		if($has_subprop) $subprop = key(head($q));

        		$properties = $outline->get($base_order_prop, []);
        		@['type' => $type, 'relation_type' => $relation_type, 'definition' => $definition] = $properties;
        		if($type == 'relation'){
            		$rel = (new $this->model)->$base_order_prop();
            		$parent = $rel->getParent();
            		$related = $rel->getRelated();

            		$left = $rel->getQualifiedForeignKeyName();
            		$right = $rel->getQualifiedOwnerKeyName();

                    $this_table = $parent->getTable();
            		$other_table = $related->getTable();


            		$qualified_order_clause = "$other_table.$subprop";

            		$this->eloquent_builder->join($other_table, $left, '=', $right)
            		                       ->orderBy($qualified_order_clause, $this->order_direction)
            		                       ->select("$this_table.*");

//             		$users = User::join('roles', 'users.role_id', '=', 'roles.id')->orderBy('roles.label', $order)->select('users.*')->paginate(10);
        		}
        		else{
            		$this->eloquent_builder->orderBy($order_by, $this->order_direction);
        		}

    		}
    		else{
        		$this->eloquent_builder->orderBy($order_by, $this->order_direction);
    		}
		}

    }


    private function get_arg($arg){
        $out = (array) $arg;
        if(empty($out)){
	        return null;
        }
// 	    dd($out, $arg, $this->{$arg});
        $out = array_map(function($v) use($out){
	        if(!is_string($v)) return $v;
            return array_map('trim', explode(',', $v));
        }, $out);
        $out = Arr::flatten($out);
        return $out;
    }


    public function resolveWith(){
        $with = $this->get_arg('with');

        if(!empty($with)){
            call_user_func_array([$this->eloquent_builder, 'with'], $with);
        }

        return $with;
    }





}
