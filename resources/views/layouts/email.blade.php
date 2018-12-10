<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<style>
.email_table th{
text-align:right;
width:45%;
padding-right:5px;
}

.email_table td{
text-align:left;
width:45%;
padding-left:5px;
}
</style>
</head>

<body>
<div class="main" style="width:700px; margin:0px auto;">
	<div class="header" style="width:100%;
	float:left;
	background:#ffa73b;
	padding:0px 0px 5px 0px;">

		<img src="{{asset('/public/img/sticky_logo.png')}}" style="max-width:100%; margin:0px auto;display:block;" />
	</div>
	<div class="email_template" style="width: 100%;
	float: left;
	background: #f4f4f4;">
		<div class="email_template_content" style="
		text-align:center;
		padding: 20px;">

			@yield('content')


		</div>
	</div>

</div>
</body>
</html>