@extends('layouts.email')
@section('content')
	<h2 style="text-align:center;
		 margin-bottom:30px;">New Comment Approval Request</h2>
	  
	<p>You have new comment approval request.<br/>
	Please see the below details and go for approval process.
	<br/>
	@if($review)
		<table style="width:70%;margin:0px auto;" class="email_table">
		<tbody>
			<tr><th>Band</th><td>{{$review->band->band_name}}</td></tr>
			<tr><th>Rating</th><td>{{$review->rating}}</td></tr>
			<tr><th>Description</th><td>{{$review->review_description}}</td></tr>
		</tbody>
		</table>
		<br/><br/>
	@endif
	Best Regards<br/>
		
	Carnivalist Team </p>
@stop