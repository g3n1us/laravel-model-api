# laravel-model-api
A controller providing a logical url structure for accessing model data

Usage: modelname is the snake cased version of the model. If singular, will display either the first model or the one specified by id.
If plural, a paginated list of items will be output. 
If html is specified, the __toString method will explicitly be called, returning any overloaded version in the model, usually custom html output.

$_GET parameters for plural form only:  
`paginate` - 0 results in no pagination, default = 1
`per_page` = results per page, disables pagination, integer default is pagination default, 15
`offset` = offset, return results starting at this offset
`limit` - limit to return, disables pagination
`pluck`/`property` - pluck a value from the returned objects
$_GET parameters for singular form only:  
`html` - returns the model's overloaded __toString method instead of the default JSON representation, also an URL parameter
