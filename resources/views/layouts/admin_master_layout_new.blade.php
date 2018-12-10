<!DOCTYPE html>


<html lang="en" class="no-js">
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Admin Dashboard</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
{{--  <link href="{{asset('/public/plugins/uniform/css/uniform.default.css')}}" rel="stylesheet" type="text/css"/>  --}}
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="{{asset('/public/css/style-metronic.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/style.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/style-responsive.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('/public/css/themes/default.css')}}" rel="stylesheet" type="text/css" id="style_color"/>
<link href="{{asset('/public/css/custom.css')}}" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>

<!------ datatable ----->
<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{asset('public/plugins/data-tables/DT_bootstrap.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{asset('public/plugins/bootstrap-datepicker/css/datepicker.css')}}"/>
<script>
var base_url = {!! json_encode(url('/')) !!};
</script>
<!------------------------->
@yield('style')
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="page-header-fixed">
<!-- BEGIN HEADER -->
<div class="header navbar navbar-fixed-top">
	<!-- BEGIN TOP NAVIGATION BAR -->
	<div class="header-inner">
		<!-- BEGIN LOGO -->
		<a class="navbar-brand" href="index.html">
			<!--img src="{{asset('/public/img/logo.png')}}" alt="logo" class="img-responsive"/-->
		</a>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<img src="{{asset('/public/img/menu-toggler.png')}}" alt=""/>
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		<!-- BEGIN TOP NAVIGATION MENU -->
		<ul class="nav navbar-nav pull-right">
			
			<!-- END TODO DROPDOWN -->
			<!-- BEGIN USER LOGIN DROPDOWN -->
			<li class="dropdown user">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<img alt="" src="{{asset('/public/img/avatar1_small.jpg')}}"/>
					<span class="username">
						{{ Auth::user()->email }}
					</span>
					<i class="fa fa-angle-down"></i>
				</a>
				<ul class="dropdown-menu">
					<li>
						<a href="#">
							<i class="fa fa-user"></i> My Profile
						</a>
					</li>					
					<li>
						<a href="{{ route('admin-logout')}}">
							<i class="fa fa-key"></i> Log Out
						</a>
					</li>
				</ul>
			</li>
			<!-- END USER LOGIN DROPDOWN -->
		</ul>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END TOP NAVIGATION BAR -->
</div>
<!-- END HEADER -->
<div class="clearfix">
</div>
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<!-- add "navbar-no-scroll" class to disable the scrolling of the sidebar menu -->
			<!-- BEGIN SIDEBAR MENU -->
			<ul class="page-sidebar-menu" data-auto-scroll="true" data-slide-speed="200">
				<li class="sidebar-toggler-wrapper">
					<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
					<div class="sidebar-toggler hidden-phone">
					</div>
					<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
				</li>
				<li class="{{ isActiveRoute('admin-home') }}">
					<a href="{{ route('admin-home') }}">
						<i class="fa fa-home"></i>
						<span class="title">
							Dashboard
						</span>
						<span class="selected">
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['user-list', 'user-edit', 'user-add']) }}">
					<a href="{{ route('user-list')}}">
						<i class="fa fa-user"></i>
						<span class="title">
							Users
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['promoters-list','promoter-edit','promoter-add']) }}">
					<a href="{{ route('promoters-list')}}">
						<i class="fa fa-user"></i>
						<span class="title">
							Promoters
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['guard-list', 'guard-edit','guard-create']) }}">
					<a href="{{ route('guard-list')}}">
						<i class="fa fa-user"></i>
						<span class="title">
							Guards
						</span>
					</a>
				</li>
				<li class="{{  areActiveRoutes(['carnival-list', 'carnival-edit', 'carnival-add']) }}">
				<a href="{{ route('carnival-list')}}">
						<i class="fa fa-star-half-o"></i>
						<span class="title">
							Carnivals
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['event-list', 'event-edit', 'event-add']) }}">
					<a href="{{ route('event-list')}}">
						<i class="fa fa-calendar"></i>
						<span class="title">
							Events ({{ App\Models\Event::where(['is_approved'=>'0'])->count() }})
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['hotel-list', 'hotel-edit', 'hotel-add']) }}">
					<a href="{{ route('hotel-list')}}">
						<i class="fa fa-calendar"></i>
						<span class="title">
							Hotels
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['transportation-list', 'transportation-edit', 'transportation-add']) }}">
					<a href="{{ route('transportation-list')}}">
						<i class="fa fa-calendar"></i>
						<span class="title">
							Transportations
						</span>
					</a>
				</li>
				<li class="{{ areActiveRoutes(['band-list', 'band-edit', 'band-add']) }}">
					<a href="{{ route('band-list')}}">
						<i class="fa fa-cog"></i>
						<span class="title">
							Bands
						</span>
					</a>
				</li>	
				<li class="{{ areActiveRoutes(['tickets-list', 'ticket-edit', 'ticket-add','ticket-detail']) }}">
					<a href="{{ route('tickets-list')}}">
						<i class="fa fa-cog"></i>
						<span class="title">
						
							Tickets ({{ App\Models\Ticket::has('events')->where(['status'=>0])->count() }})
						</span>
					</a>
				</li>
				
				@php
				$BandReviewCount = App\Models\BandReview::where(['is_approved'=>0])->orWhere(['is_approved'=>null])->count();
				$EventReviewCount = App\Models\EventReview::where(['is_approved'=>0])->orWhere(['is_approved'=>null])->count();
				$TransportationReviewCount = App\Models\TransportationReview::where(['is_approved'=>0])->orWhere(['is_approved'=>null])->count();
				$HotelReviewCount = App\Models\HotelReview::where(['is_approved'=>0])->orWhere(['is_approved'=>null])->count();
				@endphp
				<li class="{{ areActiveRoutes(['review-list','view-event-review','view-band-review','view-hotel-review','view-transportation-review']) }}">					
				<a href="{{ route('review-list')}}">						<i class="fa fa-tags"></i>						<span class="title">							Unapproved Reviews ({{(int)($BandReviewCount+$EventReviewCount+$TransportationReviewCount+$HotelReviewCount)}})					</span>					</a>				
				</li>
				<li class="">
						<a href="{{ route('admin-logout')}}">
							<i class="fa fa-sign-out"></i>
							<span class="title">
								Logout
							</span>
						</a>
				</li>
				<!--li>
					<a href="javascript:;">
						<i class="fa fa-shopping-cart"></i>
						<span class="title">
							E-Commerce
						</span>
						<span class="arrow ">
						</span>
					</a>
					<ul class="sub-menu">
						<li>
							<a href="#
							">
								<i class="fa fa-bullhorn"></i>
								Dashboard
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-shopping-cart"></i>
								Orders
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-tags"></i>
								Order View
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-sitemap"></i>
								Products
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-file-o"></i>
								Product Edit
							</a>
						</li>
					</ul>
				</li-->																	
			</ul>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>
	<!-- END SIDEBAR -->
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">									
			<!-- BEGIN CONTAINER -->
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
			@yield('content') 
			<!-- END CONTAINER -->
			<div class="clearfix">
			</div>
		</div>
    </div>
	<!-- END CONTENT -->
</div>

 
<!-- BEGIN FOOTER -->
<div class="footer">
	<div class="footer-inner">
		
	</div>
	<div class="footer-tools">
		<span class="go-top">
			<i class="fa fa-angle-up"></i>
		</span>
	</div>
</div>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
<script src="{{asset('/public/plugins/jquery-migrate-1.2.1.min.js')}}" type="text/javascript"></script>
<script type="text/javascript">
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
<!------->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>



<script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
	<!-- Bootstrap JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
 <!------->
<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="{{asset('/public/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/public/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="{{asset('/public/scripts/core/app.js')}}" type="text/javascript"></script>
<script type="text/javascript" src="{{asset('public/plugins/data-tables/DT_bootstrap.js')}}"></script>
<script type="text/javascript" src="{{asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
<!-- END PAGE LEVEL SCRIPTS -->

@yield('script') 
@include('includes.customjs') 
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>