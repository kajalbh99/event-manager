@extends('layouts.admin_master_layout_new')

 @section('style')
 @stop

 @section('content')
 <!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box green">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Event Reviews
							</div>
						</div>
						<div class="portlet-body">
						
							<table class="table table-striped table-bordered table-hover" id="event_review">
							<thead>
							<tr>
								<th>
									 Sr. no 
								</th>
								<th>
									 Event Name
								</th>
								<th>
									 User
								</th>
								<!--<th>
									 Review Description
								</th>-->
								<th>
									 Rating
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							</tbody>
							</table>
							
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Band Reviews
							</div>						
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover table-full-width" id="band_review">
							<thead>
							<tr>
								<th>
									 Sr. no 
								</th>
								<th>
									 Band Name
								</th>
								<th>
									 User
								</th>
								<!--<th>
									 Review Description
								</th>-->
								<th>
									 Rating 
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							
							</tbody>
							</table>
							
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
					<!--Hotel Review-->
					<div class="portlet box green">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Hotel Reviews
							</div>						
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover table-full-width" id="hotel_review">
							<thead>
							<tr>
								<th>
									 Sr. no 
								</th>
								<th>
									 Hotel Name
								</th>
								<th>
									 User
								</th>
								<!--<th>
									 Review Description
								</th>-->
								<th>
									 Rating 
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							
							</tbody>
							</table>
							
						</div>
					</div>
					<!--End Hotel Review-->
						<!--Transportation Review-->
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Transportation Reviews
							</div>						
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover table-full-width" id="transportation_review">
							<thead>
							<tr>
								<th>
									 Sr. no 
								</th>
								<th>
									 Transportation Name
								</th>
								<th>
									 User
								</th>
								<!--<th>
									 Review Description
								</th>-->
								<th>
									 Rating 
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							
							</tbody>
							</table>
							
						</div>
					</div>
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
 @stop
 
 @section('script')
<script>
	$(document).ready( function () {
		var eventReviewTable= $('#event_review').DataTable({
			processing: true,
			serverSide: true,
			ajax: '{{ route("ajax_get_event_review") }}',
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				$("td:first", nRow).html(iDisplayIndex + 1);
				return nRow;
			},
				columns: [{
				data: 'id',
				name: 'id'
			},
			{
				data: 'event_name',
				name: 'event.event_name'
			},
			{
				data: 'user_name',
				name: 'user.user_name'
			},
			{
				data: 'rating',
				name: 'rating'
			},
			{
				data: 'id',
				name: 'id'
			}
			],
			columnDefs: [
			{
			targets: [4],
				render: function(data, type, row) {
					 //return '<a href="' + base_url + '/admin/band-edit/' + data + '" class="btn btn-sm default"><i class="fa fa-edit"></i></a><a href="' + base_url + '/admin/band-delete/' + data + '" class="btn btn-sm default"><i class="fa fa-times"></i></a>';
					 return '<button class="btn btn-primary test" onclick="eventapprove('+data+');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="eventdelete('+data+');" >Delete</button>&nbsp;<a class="btn btn-success" href="view-event-review/'+data+'">View</a>';
				}
			},
             {
                 orderable: false,
                 targets: -1
             }
         ]
		});

		var bandReviewTable = $('#band_review').DataTable({
			processing: true,
			serverSide: true,
			ajax: '{{ route("ajax_get_band_review") }}',
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				$("td:first", nRow).html(iDisplayIndex + 1);
				return nRow;
			},
			columns: [{
				data: 'id',
				name: 'id'
			},
			{
				data: 'band_name',
				name: 'band.band_name'
			},

			{
				data: 'user_name',
				name: 'user.user_name'
			},
			{
				data: 'rating',
				name: 'rating'
			},
			{
				data: 'id',
				name: 'id'
			}],
			columnDefs: [
			{
			targets: [4],
				render: function(data, type, row) {
					 return '<button class="btn btn-primary test" onclick="bandapprove('+data+');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="banddelete('+data+');" >Delete</button>&nbsp;<a class="btn btn-success" href="view-band-review/'+data+'">View</a>';
				}
			},
             {
                 orderable: false,
                 targets: -1
             }
         ]
		});

		var hotelReviewTable=$('#hotel_review').DataTable({
			processing: true,
			serverSide: true,
			ajax: '{{ route("ajax_get_hotel_review") }}',
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				$("td:first", nRow).html(iDisplayIndex + 1);
				return nRow;
			},
			columns: [{
				data: 'id',
				name: 'id'
			},
			{
				data: 'hotel_name',
				name: 'hotel.hotel_name'
			},

			{
				data: 'user_name',
				name: 'user.user_name'
			},
			{
				data: 'rating',
				name: 'rating'
			},
			{
				data: 'id',
				name: 'id'
			}],
			columnDefs: [
			{
				targets: [4],
					render: function(data, type, row) {
						 return '<button class="btn btn-primary test" onclick="hotelapprove('+data+');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="hoteldelete('+data+');" >Delete</button>&nbsp;<a class="btn btn-success" href="view-hotel-review/'+data+'">View</a>';
					}
				},
				 {
					 orderable: false,
					 targets: -1
				 }
			 ]
		});

		var transportationReviewTable= $('#transportation_review').DataTable({
			processing: true,
			serverSide: true,
			ajax: '{{ route("ajax_get_transportation_review") }}',
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				$("td:first", nRow).html(iDisplayIndex + 1);
				return nRow;
			},
			columns: [{
				data: 'id',
				name: 'id'
			},
			{
				data: 'transportation_name',
				name: 'transportation.transportation_name'
			},

			{
				data: 'user_name',
				name: 'user.user_name'
			},
			{
				data: 'rating',
				name: 'rating'
			},
			{
				data: 'id',
				name: 'id'
			}],
			columnDefs: [
			{
			targets: [4],
				render: function(data, type, row) {
					 return '<button class="btn btn-primary test" onclick="transportationapprove('+data+');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="transportationdelete('+data+');" >Delete</button>&nbsp;<a class="btn btn-success" href="view-transportation-review/'+data+'">View</a>';
				}
			},
			 {
				 orderable: false,
				 targets: -1
			 }
		 ]
		});
	});

	function bandapprove(value){
		
		if(confirm("Are you sure you want to Approve this?")){
			$.ajax({
				type: "post",
				url: "{{ route('band-review-approve') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#band_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	
	function eventapprove(value){
		
		if(confirm("Are you sure you want to Approve this?")){
			$.ajax({
				type: "post",
				url: "{{ route('event-review-approve') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#event_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	function eventdelete(value){
		
		if(confirm("Are you sure you want to delete this?")){
			$.ajax({
				type: "post",
				url: "{{ route('event-review-delete') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#event_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	function banddelete(value){
		
		if(confirm("Are you sure you want to delete this?")){
			$.ajax({
				type: "post",
				url: "{{ route('band-review-delete') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#band_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	function hoteldelete(value){
		
		if(confirm("Are you sure you want to delete this?")){
			$.ajax({
				type: "post",
				url: "{{ route('hotel-review-delete') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#hotel_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	
	function transportationdelete(value){
		
		if(confirm("Are you sure you want to delete this?")){
			$.ajax({
				type: "post",
				url: "{{ route('transportation-review-delete') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#transportation_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	
	function hotelapprove(value){
		
		if(confirm("Are you sure you want to Approve this?")){
			$.ajax({
				type: "post",
				url: "{{ route('hotel-review-approve') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#hotel_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
	function transportationapprove(value){
		
		if(confirm("Are you sure you want to Approve this?")){
			$.ajax({
				type: "post",
				url: "{{ route('transportation-review-approve') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					$('#transportation_review').dataTable().fnDraw();
				}
			});
		}
		else{
			return false;
		}
	}
</script>
 @stop