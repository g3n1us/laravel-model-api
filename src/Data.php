<?php

namespace G3n1us\ModelApi;

class Data{


    public function __construct(){
// 		resolve(AccessRest::class);
		$this->service = resolve(ApiService::class);

    }


    protected function fetch(){
		return $this->service->get();

    }

    protected function get_query(){

        return $this->service->eloquent_builder;
    }

    private static function define_args($args){

        $last = head(array_slice($args, -1));

        $config = [];
        if(is_array($last)){
	        $config = array_pop($args);
        }
        [$modelname, $id, $property] = $args + [null, null, false];
        return [[$modelname, $id, $property], $config];
    }


    public static function __callStatic($name, $args){
        [$parameters, $config] = static::define_args($args);

	    $instance = new static;

		[$modelname, $id, $property] = $parameters;

        $instance->service->parameters['modelname'] = $modelname;
        $instance->service->parameters['id'] = $id;
        $instance->service->parameters['property'] = $property;

		$instance->service->boot();
		foreach($config as $c => $v){
			$instance->service->{$c} = $v;
		}

		return $instance->{$name}();
    }

}
