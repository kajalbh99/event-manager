@extends('layouts.admin_master_layout')

 @section('style')
 <link rel="stylesheet" type="text/css" href="{{asset('public/plugins/bootstrap-datepicker/css/datepicker.css')}}"/>
 @stop

 @section('content')
 <div class="row">
 {{ csrf_field() }}
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-eye"></i>Event Detail
                </div>
            </div>
            <div class="portlet-body">
				<div class="row">
					
					<div class="col-md-6">
						
						<div class="form-group">
							{{ Form::label('ticket', 'Event',['class' => 'form-label']) }}
							{{ $ticket->event ? $ticket->event->event_name: ''}}
						</div>
						<div class="form-group">
							{{ Form::label('ticket', 'Event Location',['class' => 'form-label']) }}
							{{ $ticket->event ? $ticket->event->event_location: ''}}
						</div>
						<div class="form-group">
							{{ Form::label('ticket', 'Event Date',['class' => 'form-label']) }}
							{{ $ticket->event ? $ticket->event->event_date: ''}}
						</div>
						<div class="form-group">
							{{ Form::label('ticket', 'Event Description',['class' => 'form-label']) }}
							{{ $ticket->event ? $ticket->event->event_description: ''}}
						</div>
						
						
					</div>
					<div class="col-md-6">
						
						<div class="form-group">
							{{ Form::label('ticket', 'Status',['class' => 'form-label']) }}
							@if($ticket->status== 0) {{ 'Pending' }} @endif
							@if($ticket->status== 1) {{ 'Approved' }}@endif
							@if($ticket->status== 2) {{ 'Declined' }}@endif
						</div>
						<!--div class="form-group">
							{{ Form::label('ticket', 'Commitee Member',['class' => 'form-label']) }}
							{{ $ticket->member ? ucfirst($ticket->member->name) :''}}
						</div-->
						<div class="form-group">
							{{ Form::label('ticket', 'Requsted User',['class' => 'form-label']) }}
							{{ $ticket->requsted_user ? ucfirst($ticket->requsted_user->name) :''}}
						</div>
						<div class="form-group">
							{{ Form::label('ticket', 'Ticket Scanned',['class' => 'form-label']) }}
							{{ $ticket->scanned==1 ? 'Yes' :'No'}}
						</div>
					</div>
				</div>
				
			</div>
        </div>
		<div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-eye"></i>Ticket Detail
                </div>
            </div>
            <div class="portlet-body">
				<div class="row table-responsive">
					<table class="table table-striped" style="width:95%;margin:0px auto;">
						<tbody>
							<tr>
								<th>Ticket Type</th><td>{{ $ticket->type ? $ticket->type->ticket_type: ''}}</td>
							</tr>	
							<tr>
								<th>Purchased tickets</th><td>{{ $ticket->count ? $ticket->count:0}}</td>
							</tr>	
							<tr>
								<th>Ticket price per ticket ($)</th><td>{{ $ticket->type ? number_format($ticket->type->ticket_price,2): number_format('0',2) }}</td>
							</tr>
							@if($ticket->token_id!=null)
							<tr>
							
								<th>Service tax ({{Config::get('constants.tax')}}% + {{ Config::get('constants.additional_charges') ? number_format(Config::get('constants.additional_charges'),2): number_format('0',2) }})
								</th><td>{{ $sale_tax ? number_format($sale_tax,2): number_format('0',2) }}</td>
							</tr>
							
							<tr>
								<th>Total amount ($)</th><td>{{ $amount ? number_format($amount,2): number_format('0',2) }}</td>
							</tr>
							@endif
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
		@if(count($ticket->allocatedPdfs)>0)
		
		<div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-eye"></i>Allocated Tickets
                </div>
            </div>
            <div class="portlet-body">
				<div class="row table-responsive">
					<table class="table table-striped" style="width:95%;margin:0px auto;">
						<thead>
							<tr><th>User</th><th>File</th></tr>
							
						</thead>
						<tbody>
							@foreach($ticket->allocatedPdfs as $pdf)
							
							@if($pdf->pdfFile)
								<tr>
									<td>{{$pdf->user ? ucfirst($pdf->user->name):''}}</td><td><a href="{{URL('/').'/public/uploads/ticket_pdfs/'.$pdf->ticket_type->id.'/'.$pdf->pdfFile->file }}" download>{{$pdf->pdfFile->file}}</a></td>
								</tr>	
							@endif
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
        </div>
		@endif
		
		@if($ticket->transfer)
		<div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-eye"></i>Ticket Transfered
                </div>
            </div>
            <div class="portlet-body">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							{{ Form::label('ticket', 'Transfered To',['class' => 'form-label']) }}
							{{ $ticket->transfer->receiver ? ucfirst($ticket->transfer->receiver->name): ''}}
						</div>
						<div class="form-group">
							{{ Form::label('ticket', 'Transfered On',['class' => 'form-label']) }}
							{{ date('Y-m-d',strtotime($ticket->transfer->created_at))}}
						</div>
						
					</div>
					
				</div>
			</div>
        </div>
		@endif
		@if($ticket->payment_response!='' || $ticket->payment_response!=null)
		@php $response = json_decode($ticket->payment_response); @endphp
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-eye"></i>Ticket Payment
				</div>
			</div>
			<div class="portlet-body" style="padding:0px">
				<div class="col-md-12">
					<div class="row table-responsive">
					
						<table class="table table-striped">
							<tr>
								<th>Txn Id</th>
								<td>{{$response->id}}</td>
							</tr>
							<tr>
								<th>Amount ($)</th>
								<td>{{$response->amount/100}}&nbsp;&nbsp;
								@if($ticket->status==1)
									@if($ticket->token_id!=null)
										@if($ticket->event)
											@if($ticket->event->is_refundable == '1')
												@if($ticket->event->event_date > date('Y-m-d'))
													<button  class="btn btn-primary refund_btn" type="button" data_id = "{{$ticket->id}}">Refund</button>
												@endif
											@endif
										@endif
									@endif
								@endif
											</td>
							</tr>
							<!--tr>
								<th>Amount_refunded</th>
								<td>{{$response->amount_refunded/100}}</td>
							</tr-->
							<tr>
								<th>Currency</th>
								<td>{{strtoupper($response->currency)}}</td>
							</tr>
							<tr>
								<th>Txn Description</th>
								<td>{{$response->description}}</td>
							</tr>
							
						</table>
					</div>
				</div>
			</div>
		</div>
		@endif
		@if($ticket->refund_response!='' || $ticket->refund_response!=null)
		@php $refund_response = json_decode($ticket->refund_response); @endphp
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-eye"></i>Refunded Payment
				</div>
			</div>
			<div class="portlet-body" style="padding:0px">
				<div class="col-md-12">
					<div class="row table-responsive">
					
						<table class="table table-striped">
							<tr>
								<th>Txn Id</th>
								<td>{{$refund_response->id}}</td>
							</tr>
							<tr>
								<th>Amount ($)</th>
								<td>{{$refund_response->amount/100}}&nbsp;&nbsp;<a  class="btn btn-danger" type="button" href="javascript:void(0);">Refunded</a></td>
							</tr>
							
							<tr>
								<th>Currency</th>
								<td>{{strtoupper($refund_response->currency)}}</td>
							</tr>
							<tr>
								<th>Reason</th>
								<td>{{$refund_response->reason}}</td>
							</tr>
							
						</table>
					</div>
				</div>
			</div>
		</div>
		@endif
		
		@if($ticket->status==0)
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-eye"></i>Action
				</div>
			</div>
			<div class="portlet-body">
				<button  class="btn btn-primary test_btn" val="1" data_id = "{{$ticket->id}}">Approve</button><button  class="btn btn-danger test_btn" val="2" data_id = "{{$ticket->id}}">Decline</button>
			</div>
		</div>
		{{--@elseif($ticket->status==1)
			@if($ticket->token_id!=null)
				@if($ticket->event)
					@if($ticket->event->event_date > date('Y-m-d'))
						<div class="portlet box blue">
							<div class="portlet-title">
								<div class="caption">
									<i class="fa fa-undo"></i>Refund
								</div>
							</div>
							
							<div class="portlet-body">
								<button  class="btn btn-primary refund_btn" type="button" data_id = "{{$ticket->id}}">Refund</button>
							</div>
							
						</div>
					@endif
				@endif
		@endif --}}
		@endif
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
	
	
</div>	

</div>
 @stop
 
 @section('script')
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
 <script type="text/javascript" src="{{asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
 <script>
 $(document).on( 'click', '.test_btn', function () {
	var _token = $('input[name="_token"]').val();	
	var id=$(this).attr('data_id');
	var val=$(this).attr('val');
	 
	if(val==1)
	{
	 $msg="Are you sure you want to Approve this?";
	}
	else{
	  $msg="Are you sure you want to Decline this?";
	}
	if(confirm($msg)){
		$.ajax({
			type: "POST",
			url: '{{ route("ajax_ticket_update_status") }}',
			data: { ticket_id:id,value:val,_token : _token  },
			success:function(result) {
				console.log(result);
				if(result.response=='0'){
					alert(result.data);
				} else {
					location.reload();
				}
				
			},

		});
	}
	else{
	return false;
	}
} );

$('.refund_btn').click(function(){
	var _token = $('input[name="_token"]').val();	
	var id=$(this).attr('data_id');
	
	if(confirm('Are you sure to refund payment ?')){
		$.ajax({
			type: "POST",
			url: '{{ route("ajax_ticket_refund_payment") }}',
			data: { ticket_id:id,_token : _token  },
			success:function(result) {
				console.log(result);
				if(result.response=='0'){
					alert(result.data);
				} else {
					//location.reload();
				}
				
			},
			error:function(error){
				console.log(error);
				alert(error.statusText);
				return false;
			}

		});
	}
	else{
	return false;
	}
});
 </script> 
 @stop