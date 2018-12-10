@extends('layouts.email')
@section('content')
	<h2 style="text-align:center;
		 margin-bottom:30px;">Your comment has been approved.</h2>
	  
	<p>Thanks for sharing your feedback. Your comment has been approved on transportation <b>{{$review->transportation->transportation_name}}</b><br/>
	Please keep sharing your feedbacks.
	<br/>
	
	Best Regards<br/>
		
	Carnivalist Team </p>
@stop