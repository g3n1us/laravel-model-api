<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="/assets/fontawesome-free-5.0.2/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

@stack('css')
    <style>
      html, body, #app{
        min-height: 100vh;
      }
      .bg-dark{
        background-color: black !important;
        border-bottom: 5px solid #E68A31;
      }
      .bg-dark.border-top{
        border-top: 5px solid #E68A31;
        border-bottom: none;
      }
      .text-black{
        color: black;
      }
      .bg-green{
        background-color: #75985D !important;
      }
.emammal-front-text {
    margin: 2em 0;
    padding: .5em 0;
    border-top: 2px dotted #ccc;
    border-bottom: 2px dotted #ccc;
    font-size: 1.5em;
    font-weight: 300;
    font-style: italic;
    text-align: center;
    
}      
		.admin-navbar{
  		box-shadow: 0 2px 7px #585858;
  		background-image: linear-gradient(#fff,#d6d6d6);
		}
		.admin-navbar .nav-link.active, .admin-navbar .nav-link{
			border-bottom: none;
		}
		button.nav-link{
  		-webkit-appearance: none;
  		-moz-appearance: none;
  		background: none;
  		border: none;
		}
		
		img{
  		max-width: 100%;
  		height: auto;
		}

    </style>
</head>
<body>
    <div id="app" class="d-flex flex-column">
@include('g3n1us_editor::toolbar')      
      
      
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/">
        <img src="http://expert-review-tool-dev.s3.amazonaws.com/src/app/images/logo.png" style="display: inline-block; max-height: 50px; margin:-5px 0;"> <span class="d-none d-sm-inline">See Wildlife, Do Science</span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse navbarSupportedContent">
        
        <ul class="nav navbar-nav ml-auto">
            <!-- Authentication Links -->
            @guest
                <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">Login</a></li>
                <li class="nav-item"><a href="{{ route('register') }}" class="nav-link">Register</a></li>
            @else
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>
    
                    <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                                Logout
                            </a>
    
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                    </div>
                </li>
            @endguest
        </ul>
        
    </div>
</nav>

<nav class="navbar navbar-expand-lg bg-green py-0">
  <div class="collapse navbar-collapse navbarSupportedContent">  
    <ul class="nav navbar-nav container justify-content-start">
      @foreach(output_nav() as $navpage)
        <li class="nav-item"><a href="{{$navpage->path}}" class="nav-link text-black">{{$navpage->title}}</a></li>
      @endforeach
<!--
        <li class="nav-item"><a href="/home" class="nav-link text-black">Home</a></li>
        <li class="nav-item"><a href="/home" class="nav-link text-black">About</a></li>
        <li class="nav-item"><a href="/home" class="nav-link text-black">View Photos</a></li>
        <li class="nav-item"><a href="/home" class="nav-link text-black">Explore Projects</a></li>
        <li class="nav-item"><a href="/home" class="nav-link text-black">Browse Data</a></li>
        <li class="nav-item"><a href="/home" class="nav-link text-black">Resources</a></li>
-->
    </ul>
    <form class="form-inline mb-2 mb-lg-0">
      <input type="search" name="search" class="form-control form-control-sm">
    </form>
  </div>
</nav>
    <div class="mb-3">
      <div class="container">
        <div class="row justify-content-center">
        	<div class="col-md-8">
          	@php
          	$public_models = array_map(function($v){
            	return '<code>'.strtolower(class_basename($v)).'</code>';
          	}, config('g3n1us_model_api.public_models', []));
          	@endphp
        		<h1 class="mt-5">Welcome to the eMammal API</h1>
        		<p>The available endpoints are listed below.</p>
        		<p><b>Primary endpoint:</b> <code>/api/{modelname}/{id?}/{property?}</code></p>
        		<h6>Documentation for Primary Endpoint</h6>
        		<p>
          		<code>modelname</code> can be one of: {!! implode(', ', $public_models) !!}. To return a paged set of results, use the plural form of the noun, otherwise the first result will be returned. 
          		eg. <a href="/api/project" target="_blank"><code>/api/project</code></a> or
          		eg. <a href="/api/projects" target="_blank"><code>/api/projects</code></a>
        		</p>
            <p><code>id</code> <small>(optional)</small> will return the specified model by it's id.
          		eg. <a href="/api/project/5" target="_blank"><code>/api/project/5</code></a>
            </p>
            <p><code>property</code> <small>(optional)</small> will return the specified property of the model. This can be either a static property or a related resource, eg. <a href="/api/project/5/sequences" target="_blank"><code>/api/project/5/sequences</code></a> will return the sequences associated with project #5. You can also use this approach with collections by specifying a dash (-) for the id, eg. <a href="/api/projects/-/sequences" target="_blank"><code>/api/projects/-/sequences</code></a>. This will return a collection of the resources associated with each of the original items in the collection.</p>
            <p>
              <b>Available options via query string:</b><br>
		<code>html     </code>: returns an html representation of each model if available<br>
		<code>offset   </code>: manually specify the page start<br>
		<code>limit    </code>: max number of results<br>
		<code>per_page </code>: alias for <code>limit</code><br>
		<code>template </code>: specify the template for each model to be output as, see <code>html</code> parameter<br>
		<code>paginated</code>: whether or not to use paging<br>
		<code>pluck    </code>: specify a single property to output for each model<br>
            </p>
            <p><b>Output defaults to JSON</b></p>
            
        		<h6>All Available Endpoints</h6>
            
        		<table class="table table-bordered" style="font-family: monospace">
        		@foreach($routes as $route)
        		<tr><td>{{$route['methods']}}</td><td>{{str_start($route['uri'], '/')}}</td></tr>
        		
        		@endforeach
        		</table>
        	</div>
        </div>
        
      </div>
        
    </div>
    <footer id="footer" class="bg-dark border-top text-white mt-auto">
      <nav class="nav justify-content-end">
        <a class="nav-link nav-item">&copy; {{date('Y')}} eMammal</a>
        <a class="nav-link nav-item">Privacy</a>
        <a class="nav-link nav-item">Terms of Use</a>
      </nav>
    </footer>
    </div>
    
</body>
</html>
