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
                    <i class="fa fa-eye"></i>Ticket Detail
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
						<div class="form-group">
							{{ Form::label('ticket', 'Commitee Member',['class' => 'form-label']) }}
							{{ $ticket->member ? ucfirst($ticket->member->name) :''}}
						</div>
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
				<div class="row table-responsive">
					<table class="table table-striped" style="width:95%;margin:0px auto;">
						<thead>
							<tr>
								<th>Ticket Type</th>
							
								<th>Ticket Price($)</th>
							
								<th>Total Tickets</th>
							
								<th>Total Tickets Sold</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{ $ticket->type ? $ticket->type->ticket_type: ''}}</td>
							
								<td>{{ $ticket->type ? $ticket->type->ticket_price: ''}}</td>
							
								<td>{{ $ticket->type ? $ticket->type->total_tickets: ''}}</td>
							
								<td>{{ $ticket->type ? $ticket->type->tickets_sold: ''}}</td>
							</tr>
						</tbody>
						
					</table>
					
				</div>
			</div>
        </div>
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
		@if($ticket->status==1 && $ticket->payment_response!='')
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
									<th>Amount($)</th>
									<td>{{$response->amount/100}}</td>
								</tr>
								<tr>
									<th>Amount_refunded</th>
									<td>{{$response->amount_refunded/100}}</td>
								</tr>
								<tr>
									<th>Currency</th>
									<td>{{ucfirst($response->currency)}}</td>
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
 </script> 
 @stop