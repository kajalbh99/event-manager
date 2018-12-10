
<style>
 /* @font-face {
    font-family: 'Open Sans', sans-serif;
  }
  body {
    font-family: 'Open Sans', sans-serif;
	}
  */
  </style>
<table width="100%">
	<thead>
		<tr>		
			<th width="30%" align="left" style="padding-bottom:5px;">
				<img src="{{asset('/public/img/sticky_logo.png')}}">
			</th>
			<th  width="40%" style="padding-bottom:5px; text-align:center">
				<h2 class="bold">Invoice</h2>
			</th>
			<th width="30%" align="right" style="padding-bottom:5px;">
				
			</th>
		</tr>
		<tr>
			<td colspan="3" style="border-top: 1px solid #000;">
			</td>
		</tr>
	</thead>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td style="vertical-align:top;width:70%">
				
				<h4 style="font-size:18px;margin:10px 0px 5px 0px">Carnivalist</h4>
					Email : info@carnivalist.com <br>
				
					Booking / Invoice ID : {{ $invoice->id }}
				<br>
					Date : {{ date('Y-m-d',strtotime($invoice->created_at)) }}
				<br>
				<br>
				
			</td>
			<td style="vertical-align:top;width:30%" align="left">
				<h4 style="font-size:18px;margin:10px 0px 5px 0px">Customer :</h4>
				<ul style="list-style:none; padding:0;margin:0">
					
					<li>
						{{ $invoice->user ? $invoice->user->name: '' }}
					</li>
					<li>
						{{ $invoice->user ? $invoice->user->email :'' }}
					</li>
				</ul>
			</td>
		</tr>
	</tbody>
</table>	

<table width="100%" style="padding-top: 30px ; border-top:1px solid #000; border-bottom:1px solid #000;" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<th colspan="2" style="height:6px">
				&nbsp;
			</th>
		</tr>
		<tr>
			<td style="vertical-align:top;width:40%;text-align:left" >
				
				<strong>Event : </strong>
				{{$invoice->event ? $invoice->event->event_name : ''}}
				<br/>
				<br/>
				<strong>Event Date : </strong>
				{{$invoice->event ? $invoice->event->event_date: ''}}
				<br/>
				<br/>
				<strong>Event Location : </strong>
				{{$invoice->event? $invoice->event->event_location : ''}}
				<br/>
				</br>
				
				<td style="vertical-align:top;width:40%;text-align:right;">
					<strong style="text-align:right">Unit Cost : </strong>
					{{ $basic_amount ? number_format($basic_amount,2):  number_format('0',2)}}
					<br/>
					<strong style="text-align:right">Quantity : </strong>
					{{ $invoice->ticket ? $invoice->ticket->count : 0 }}
					<br/>
					@if($invoice->ticket)
						@if($invoice->ticket->token_id!=null)
						<strong>Sales Tax ($<?php echo Config::get('constants.tax'); ?>%) : </strong>
						${{ number_format($sales_tax_amount,2) }}
						<br/>
						<strong style="text-align:right">Additional : </strong>
						$<?php echo number_format(Config::get('constants.additional_charges'),2); ?>
						<br/>
						<strong style="text-align:right">Total Price : </strong>
						${{number_format($final_charged_amount,2)}}
						
						@endif
					@endif
					<br/>
			    </td>			
			</td>
		</tr>
		<tr>
			<th colspan="2" style="height:6px">
				&nbsp;
			</th>
		</tr>
	</tbody>
</table>
@if($invoice->ticket)
	@if($invoice->ticket->token_id!=null)
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td style="height:10px;"></td>
			</tr>
			<tr>
				<td style="vertical-align:top;width:40%">
					<h4 style="font-size:18px">Transaction Details :</h4>
				</td>
			</tr>
		</tbody>
	</table>


	<table class="table" border="1" style="border-collapse: collapse" width="100%">
		<thead>
		<tr>
			<!--<th>Booking ID</th>-->

			<th style="padding: 0px 10px;">Transaction ID </th>
			<th style="padding: 0px 10px;">Transaction Date </th>
			<th style="padding: 0px 10px;">Total</th>
			<th style="padding: 0px 10px;">Amount Paid <?php echo Config::get('constants.currency_code'); ?> </th>
		</tr>
		</thead>
		<tbody>
			@if($payment_response)
			<tr>			
				<td style="padding: 0px 10px;font-size:12px">
					{{$payment_response->id}}
				</td>
				<td style="padding: 0px 10px;font-size:12px">
					{{ date('Y-m-d',strtotime($transaction_date)) }}
				</td>
				<td style="padding: 0px 10px">
					${{number_format($final_charged_amount,2)}}
				</td>
				<td style="padding: 0px 10px;" align="right">
					${{number_format($final_charged_amount,2)}}
				</td>
			</tr>
			@endif
		</tbody>
	</table>
	@endif
@endif
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td style="height:10px;"></td>
		</tr>
		<tr>
			<td style="vertical-align:top;width:40%;text-align:right">
				<h4 style="font-size:18px">Thank You.</h4>
			</td>
		</tr>
	</tbody>
</table>
