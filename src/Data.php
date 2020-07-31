<?php

namespace G3n1us\ModelApi;

class Data{

    public static function fetch($modelname, $id = null, $property = false){

        $controller = new ModelAPIController;

        if(is_array($id)){
            $props = $id;
            $id = null;
        }
        $controller->boot($modelname, $id, $property);

        foreach($props as $prop => $value){
            $controller->{$prop} = $value;
        }

//         dd($controller);

        return $controller->resolve_output();
    }

    public static function get_query($modelname, $id = null, $property = false){
        $controller = new ModelAPIController;

        $controller->boot($modelname, $id, $property);

        return $controller->resolveWhere();
    }

}
