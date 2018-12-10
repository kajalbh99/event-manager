<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
     <!-- Styles -->
	<link href="{{asset('/public/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
   
</head>
<body>
    <div id="app">
        

        @yield('content')
    </div>

    <!-- Scripts -->
	<script src="{{asset('/public/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
    
</body>
</html>
