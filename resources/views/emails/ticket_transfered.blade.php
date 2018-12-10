@extends('layouts.email')
@section('content')
	<h2 style="text-align:center;
		 margin-bottom:30px;">Ticket Transfered.</h2>
	  
	<p>Below are the details of ticket.<br/><br/>
	@if($ticket)
		<table style="width:70%;margin:0px auto;" class="email_table">
		<tbody>
			<tr><th>Event</th><td>{{ucfirst($ticket->event->event_name)}}</td></tr>
			<tr><th>Ticket Type</th><td>{{ $ticket->type ? $ticket->type->ticket_type: ''}}</td></tr>
			<tr><th>Purchased tickets</th><td>{{ $ticket->count ? $ticket->count:0}}</td></tr>
			<tr><th>Ticket price per ticket ($)</th><td>{{ $ticket->type ? number_format($ticket->type->ticket_price,2): number_format('0',2) }}</td></tr>
		    @if($ticket->tooken_id!=null)
			
			<tr><th>Service tax ({{Config::get('constants.tax')}}% + {{ Config::get('constants.additional_charges') ? number_format(Config::get('constants.additional_charges'),2): number_format('0',2) }})</th><td>{{ $sale_tax ? number_format($sale_tax,2): number_format('0',2) }}</td></tr>
			@endif
			<tr><th>Total amount ($)</th><td>{{ $amount ? number_format($amount,2): number_format('0',2) }}</td></tr>
			
			
		</tbody>
		</table>
		<br/><br/>
	@endif
	
	<br><br>
	Best Regards<br/>
		
	Carnivalist Team </p>
@stop