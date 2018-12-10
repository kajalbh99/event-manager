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
	
	<img src="http://carnivalist.com/event-manager/public/img/sticky_logo.png" style="max-width:100%; margin:0px auto;display:block;" />
	</div>
      <div class="email_template" style="width: 100%;
    float: left;
    background: #f4f4f4;">
	<div class="email_template_content" style="
    text-align:center;
    padding: 20px;">
	  
	    <h2 style="text-align:center;
		 margin-bottom:30px;">Request Approved</h2>
	  
        <p>Your ticket has been approved.<br/>
		Below are the details of ticket.
		<br/>
		
       
		@if($ticket)
		<table style="width:70%;margin:0px auto;" class="email_table">
		<tbody>
			<tr><th>Event</th><td>{{ucfirst($ticket->event->event_name)}}</td></tr>
			<tr><th>Ticket Type</th><td>{{ $ticket->type ? $ticket->type->ticket_type: ''}}</td></tr>
			<tr><th>Purchased tickets</th><td>{{ $ticket->count ? $ticket->count:0}}</td></tr>
			<tr><th>Ticket price per ticket ($)</th><td>{{ $ticket->type ? number_format($ticket->type->ticket_price,2): number_format('0',2) }}</td></tr>
			@if($ticket->tooken_id!=null)
				
			<tr><th>Service tax ({{Config::get('constants.tax')}}% + {{ Config::get('constants.additional_charges') ? number_format(Config::get('constants.additional_charges'),2): number_format('0',2) }})</th><td>{{ $sale_tax ? number_format($sale_tax,2): number_format('0',2) }}</td></tr>
			
			<tr><th>Total amount ($)</th><td>{{ $amount ? number_format($amount,2): number_format('0',2) }}</td></tr>
			@endif
			
		</tbody>
		</table>
		@endif
		<br/><br/>
		 Best Regards<br/>
        Carnivalist Team </p>
		

        </div>
</div>
		
</div>
    </body>
</html>

