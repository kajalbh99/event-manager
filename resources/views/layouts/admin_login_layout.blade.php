<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<link href="{{asset('/public/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/plugins/uniform/css/uniform.default.css')}}" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="{{asset('/public/plugins/select2/select2.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{asset('/public/plugins/select2/select2-metronic.css')}}"/>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- BEGIN THEME STYLES -->
<link href="{{asset('/public/css/style-metronic.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/style.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/style-responsive.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/plugins.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/themes/default.css')}}" rel="stylesheet" type="text/css" id="style_color"/>
<link href="{{asset('/public/css/pages/login.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/custom.css')}}" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
@yield('style') 
</head>
<!-- BEGIN BODY -->
<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
	<a href="index.html">
		<!--img src="{{asset('public/img/logo-big.png')}}" alt=""/-->
	</a>
</div>
<!-- END LOGO -->

<!-- BEGIN LOGIN -->
<div class="content">
@if (count($errors) > 0 || Session::has('message') || Session::has('error'))   
	@if(Session::has('message'))
		<div style="font-size:14px" class="alert alert-success">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<span>{{Session::get('message')}}</span>
	@else
		<div style="font-size:14px" class="alert alert-danger">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<span>{{Session::get('error')}}</span>
			@endif
			<ul>
				@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
 @endif
<!-- BEGIN PAGE CONTENT LOGIN -->
	 @yield('content') 
<!-- END PAGE CONTENT -->
</div>
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="assets/plugins/respond.min.js"></script>
<script src="assets/plugins/excanvas.min.js"></script> 
<![endif]-->

<script src="{{asset('/public/plugins/jquery-1.10.2.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/jquery-migrate-1.2.1.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/jquery.cokie.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/uniform/jquery.uniform.min.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="{{asset('/public/plugins/jquery-validation/dist/jquery.validate.min.js')}}" type="text/javascript"></script>
<script type="text/javascript" src="{{asset('/public/plugins/select2/select2.min.js')}}"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="{{asset('/public/scripts/core/app.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/scripts/custom/login.js')}}" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {     
    App.init();
    Login.init();
});
</script>
@yield('script') 
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>