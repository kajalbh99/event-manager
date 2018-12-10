@extends('layouts.email')
@section('content')
	<h2 style="text-align:center;
		 margin-bottom:30px;">{{$heading}}</h2>
	  
	<p><b>{{$data}}</b><br/><br/>
	
	Best Regards<br/>		
	Carnivalist Team </p>
@stop