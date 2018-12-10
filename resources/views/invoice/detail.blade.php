@extends('layouts.invoice')

 @section('style')

 @stop

 @section('content')
	<div class="row">
		<div class="col-md-12">
			<!-- BEGIN EXAMPLE TABLE PORTLET-->
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Invoice
					</div>
				</div>
				<div class="portlet-body">
					<div class="col-md-12 text-center">
						<img id="image" src="{{asset('/public/img/carnival-logo.jpg')}}" alt="logo" width="200px" class="text-center"/>
					</div>
					<div class="col-md-12">&nbsp;</div>
					<div class="col-md-12">
						<div class="col-md-6">
							<b>From</b><br>Event-manager<br>quaysystechnology@gmail.com<br>
						</div>
						<div class="col-md-6">
							<b>To</b><br>{{ $invoice->user->name }}<br>{{ $invoice->user->email }}<br>
						</div>
					   
						
					
					</div>
					<div class="col-md-12">&nbsp;</div>
					<div class="col-md-12 table-responsive">
						<table id="" class="table table-bordered" style="width:60%">
							<tr>
								<td class="meta-head">Invoice #</td>
								<td>{{ $invoice->id }}</td>
							</tr>
							<tr>

								<td class="meta-head">Date</td>
								<td>{{ date('Y-m-d',strtotime($invoice->created_at)) }}</td>
							</tr>
						</table>
					</div>
					<div class="col-md-12 table-responsive">
						<table id="" class="table table-bordered">
						
						  <tr>
							  <th>Event</th>
							  <th>Event Date</th>
							  <th>Unit Cost</th>
							  <th>Quantity</th>
							  <th>Price</th>
						  </tr>
						  
						  <tr>
							  
							  <td class="description">{{$invoice->event->event_name}}</td>
							  <td>{{$invoice->event->event_date}}</td>
							  <td>{{$invoice->event->final_ticket_price}}</td>
							  <td>{{$invoice->ticket->count}}</td>
							   <td><span class="price">${{$charged_amount ? $charged_amount :0}}</span></td>
						  </tr>
						  
						 
						  
						   <tr>
							  <td colspan="2" class="blank"> </td>
							  <td colspan="2" class="total-line">Subtotal</td>
							  <td class="total-value"><div id="subtotal">${{$charged_amount ? $charged_amount :0}}</div></td>
						  </tr>
						  <tr>

							  <td colspan="2" class="blank"> </td>
							  <td colspan="2" class="total-line">Total</td>
							  <td class="total-value"><div id="total">${{$charged_amount ? $charged_amount :0}}</div></td>
						  </tr>
						  <tr>
							  <td colspan="2" class="blank"> </td>
							  <td colspan="2" class="total-line">Amount Paid</td>

							  <td class="total-value">${{$charged_amount ? $charged_amount :0}}</td>
						  </tr>
						 
						
						</table>
					</div>
					
					<div id="">
					  <h5>Terms</h5>
					  <ul>
						<li>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</li>
						<li>Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,</li>
					  </ul>
					  
					</div>	
				</div>
			</div>
			<!-- END EXAMPLE TABLE PORTLET-->
		</div>
	</div>
	
 
 @section('script')
     
 @stop