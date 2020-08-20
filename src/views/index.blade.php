<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel Model API') }}</title>

    <!-- Styles -->
    <link href="/assets/fontawesome-free-5.0.2/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

@stack('css')
    <style>
      html, body, #app{
        min-height: 100vh;
      }

      code > a{
          color: inherit;
          text-decoration: none;
      }


    </style>
</head>
<body>
    <div id="app" class="d-flex flex-column">
    <div class="mb-3">
      <div class="container">
        <div class="row justify-content-center">
        	<div class="col-md-8">
        		<h1 class="mt-5">Welcome</h1>
        		<p>The available endpoints are listed below.</p>
        		<p><b>Primary endpoint:</b> <code>/{{$prefix}}/{modelname}/{id?}/{property?}</code></p>
        		<h6>Documentation for Primary Endpoint</h6>
        		<p>
          		<code>modelname</code> can be one of:
          		@foreach($route_names as [$url, $string])
                  		<code><a href="{{$url}}">{{$string}}</a></code>@if (!$loop->last), @endif
          		@endforeach
          		. To return a paged set of results, use the plural form of the noun, otherwise the first result will be returned.
          		eg. <a href="{{$eg_url}}" target="_blank"><code>{{$eg_url}}</code></a> or
          		eg. <a href="$eg_url_plural" target="_blank"><code>{{$eg_url_plural}}</code></a>
        		</p>
            <p><code>id</code> <small>(optional)</small> will return the specified model by it's id.
          		eg. <a href="{{$eg_url}}/5" target="_blank"><code>{{$eg_url}}/5</code></a>
            </p>
            <p><code>property</code> <small>(optional)</small> will return the specified property of the model. This can be either a static property or a related resource, eg. <a href="{{$eg_url}}/5/states" target="_blank"><code>{{$eg_url}}/5/property-name</code></a> will return the items associated with id #5. You can also use this approach with collections by specifying a dash (-) for the id, eg. <a href="{{$eg_url_plural}}/-/property-name" target="_blank"><code>{{$eg_url_plural}}/-/property-name</code></a>. This will return a collection of the resources associated with each of the original items in the collection.</p>
            <p>
              <b>Available options via query string:</b><br>
		<code>html     </code>: returns an html representation of each model if available<br>
		<code>offset   </code>: manually specify the page start<br>
		<code>limit    </code>: max number of results<br>
		<code>per_page </code>: alias for <code>limit</code><br>
		<code>template </code>: specify the template for each model to be output as, see <code>html</code> parameter<br>
		<code>paginated</code>: whether or not to use paging<br>
		<code>pluck    </code>: specify a single property to output for each model<br>
		<code>where    </code>: query the model eg. <code>?where[]=name&where[]=!=&where[]=Alf</code><br>
		<code>order_by    </code>: the column to sort results by<br>
		<code>order_direction    </code>: the sort direction either <code>asc</code> or <code>desc</code>, defaults to <code>asc</code><br>
            </p>
            <p><b>Output defaults to JSON</b></p>

        		<h6>All Available Endpoints</h6>

        		<table class="table table-bordered" style="font-family: monospace">
            		<thead>
		        		<tr><th>Methods</th><th>Path</th><th>Name</th></tr>
            		</thead>
            		<tbody>
        		@foreach($routes as $route)
        		<tr><td>{{$route['methods']}}</td><td>{{str_start($route['uri'], '/')}}</td><td>{{$route['name']}}</td></tr>

        		@endforeach
            		</tbody>
        		</table>
        	</div>
        </div>

      </div>

    </div>
    <footer id="footer" class="bg-dark border-top text-white mt-auto">
      <nav class="nav justify-content-end">
        <a class="nav-link nav-item">&copy; {{date('Y')}}</a>
        <a class="nav-link nav-item">Privacy</a>
        <a class="nav-link nav-item">Terms of Use</a>
      </nav>
    </footer>
    </div>

</body>
</html>
