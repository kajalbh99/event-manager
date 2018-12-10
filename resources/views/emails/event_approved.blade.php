@extends('layouts.email')
@section('content')
	<h2 style="text-align:center;
		 margin-bottom:30px;">Event request approved.</h2>
	  
	
	<p>Your request for approval of event <b>{{ $event->event_name }}</b> has been approved.<p>
	<br><br>
	Best Regards<br/>
		
	Carnivalist Team </p>
@stop