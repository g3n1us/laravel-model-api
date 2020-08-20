<?php

namespace G3n1us\ModelApi;

use Str;
use Arr;
use Route;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use Illuminate\Database\Query\Builder as QueryBuilder;

trait ModelTrait{

    public static function rest_url($path = '', ...$args){
        if(count($args) === 1 && is_array($args[0])){
            $args = $args[0];
        }

        $prefix = config('g3n1us_model_api.uri_prefix', 'api');
        $pathname = Str::start($prefix, '/') . Str::start($path, '/');
        return url($pathname, $args);
    }

    public static function rest_definitive_pathnames(){
        $singular = Str::singular(class_basename(self::class));
        $plural = Str::plural($singular);
        $pair = array_map('Str::snake', [$singular, $plural]);

    	return array_map('Str::slug', $pair);
    }

    // The definitive singular path name
    public static function rest_singular(){
        return self::rest_definitive_pathnames()[0];
    }

    // The definitive plural path name
    public static function rest_plural(){
        return self::rest_definitive_pathnames()[1];
    }

    // The definitive singular path name
    public static function rest_singular_url(...$args){
        return self::rest_url(self::rest_singular(), $args);
    }

    // The definitive plural path name
    public static function rest_plural_url(...$args){
        return self::rest_url(self::rest_plural(), $args);
    }



    public static function rest_endpoints(){
        $pair = self::rest_definitive_pathnames();

    	$slugs = array_merge($pair, array_map(function($i){
        	return Str::snake(Str::studly($i));
    	}, $pair));

    	return array_map('static::rest_url', array_unique($slugs));
    }

    public static function rest_endpoints_basename(){
        return array_map('basename', self::rest_endpoints());
    }

    public static function rest_regex(){
        return implode('|', self::rest_endpoints_basename());
    }

    public static function rest_routes(){

//         dd( Route::getRoutes()->getRoutesByName() ) ;

        [$sngl, $plrl] = self::rest_definitive_pathnames();
        $prefix = config('g3n1us_model_api.uri_prefix', 'api');
        $rest_routes = collect(Route::getRoutes()->getRoutesByName())->filter(function($v, $k) use($sngl, $plrl){
            return starts_with($k, "rest.$sngl") || starts_with($k, "rest.$plrl");
        });
        return $rest_routes;
    }

    public static function rest_properties(){
        $keys = [
            'rest_url', 'rest_singular', 'rest_plural', 'rest_singular_url', 'rest_plural_url', 'rest_endpoints', 'rest_routes', 'rest_endpoints_basename', 'rest_regex',
        ];

        $out = [];
        foreach($keys as $key){
            $key_short = preg_replace('/^rest_(.*?)$/', '$1', $key);
            $out[$key_short] = self::{$key}();
        }

        return $out;
    }

}
