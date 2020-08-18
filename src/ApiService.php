<?php

namespace G3n1us\ModelApi;

use Str;
use Arr;

use Illuminate\Database\Eloquent\Relations\Relation;

class ApiService{
	use ApiTrait;


    protected $is_plural;
    protected $html = false;
    protected $offset = 0;
    protected $limit = -1;
    protected $per_page = null;
    protected $template;
    protected $paginated = true;
    protected $pluck = null;
    protected $where = [];
    protected $with = [];
    protected $model = false;
    protected $connection = 'default';

    protected $eloquent_builder = false;

    // ordering
    protected $order_direction = 'asc';
    protected $order_by;

    protected $is_api = false;

    public $parameters = [
	    'modelname' => null,
	    'id' => null,
	    'property' => null,
    ];



	public function __construct(){
		//
	}


    public function boot(){
		app()->make(AccessRest::class);
		if(!$this->is_api){
			app()->make(AccessDeclarative::class);
		}

		$this->booted = true;

    }



	public function __get($name){
		return $this->{$name};
	}



	protected $queue = [

	];


	public $resolved = false;

	public $previously_set = [];

	public function __set($name, $value){
		if(in_array($name, $this->previously_set)){
			return;
		}
		else{
			$this->previously_set[] = $name;
		}
		$pre_handler = 'handle' . Str::studly($name) . 'Before';

		if(!$this->resolved){
			if(method_exists($this, $pre_handler)){
				$this->{$name} = $this->$pre_handler($value);
			}
			else{
				$this->queue[$name] = $value;
			}

		}
		else{
			$this->{$name} = $value;
		}

	}


	protected function handleModelBefore($value){
		return $this->resolveModelName($value);
	}

	protected function handleOrderDirectionBefore($value){
		return $this->order_direction = $value;
	}


	public function resolve(){
		$this->resolved = true;

		$queue = [];
		// $queue[] = ['model', $this->queue['model']];
		$queue[] = ['is_plural', $this->queue['is_plural']];
		$queue[] = ['eloquent_builder', $this->queue['eloquent_builder']];
		$queue[] = ['pluck', $this->queue['pluck']];

		foreach(Arr::except($this->queue, ['model', 'pluck', 'eloquent_builder', 'is_plural']) as $k => $v){
			$queue[] = [$k, $v];
		}


		foreach($queue as $pair){
			[$name, $value] = $pair;

			$handler = 'handle' . Str::studly($name);

			if(method_exists($this, $handler)){
				$this->{$name} = $this->$handler($value);
			}
			else{
				$this->{$name} = $value;
			}

		}

	}


	protected function getModelInfo(){
        if($outline = $this->model::static_outline()){
	        return $outline;
        }
        return collect([]);
	}


	protected function handleWith($value){
		$value = ($this->as_array($value));

		if(!empty($value)){
			$this->eloquent_builder->with(...(array) $value);
		}


		return $value;
	}


	protected function handlePluck($value){
		if($value && $extra = $this->getModelInfo()->get($value)){
			if($this->is_plural === false && $extra['type'] == 'relation'){
				[ 'id' => $id ] = $this->parameters;

				if($id){
					$rel = ($this->model::findOrFail($id))->$value();
				}
				else{
					$rel = ($this->model::firstOrFail())->$value();
				}

				$this->eloquent_builder = $rel;
				$this->model = $rel->getRelated();

				// TODO! adjust is_plural to account for the type of relation and what it returns
				$this->is_plural = true;
				return null;
			}
		}

		return $value;

	}


    public function get(){
	    $this->resolve();
		if(!$this->is_plural){
			return $this->returnSingle();
		}
		else{
			return $this->returnMany();
		}
    }

	public function __toString(){
		return $this->get()->toJSON();
	}

	private function returnMany(){


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

		if($this->pluck){
			$results = $results->setCollection($results->getCollection()->pluck($this->pluck));
		}

		return $results;
	}



    private function returnSingle(){

		[ 'id' => $id ] = $this->parameters;
	    if($id){
			$m = $this->eloquent_builder->findOrFail($id);
			if($this->html && method_exists($m, 'display')) return $m->display($this->template);
			return $this->pluck ? $m->{$this->pluck} : $m;
	    }

		else{
			if($this->html && method_exists($this->eloquent_builder->first(), 'display'))
				return $this->eloquent_builder->first()->display();
			return $this->eloquent_builder->first();
		}
    }



    private function as_array($arg, $separator = ','){

        $out = (array) $arg;
        if(empty($out)){
	        return null;
        }
// 	    dd($out, $arg, $this->{$arg});
        $out = array_map(function($v) use($out, $separator){
	        if(!is_string($v)) return $v;
            return array_map('trim', explode($separator, $v));
        }, $out);

//         dump($arg, $out);

//         $out = Arr::flatten($out);
        return $out;
    }



}
