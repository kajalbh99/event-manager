@extends('layouts.admin_master_layout_new')

 @section('style')
  <style>
 span.fa.fa-star.star_rating{
	 font-size:20px;
 }
 span.fa.fa-star.star_rating.checked{
	 color:orange;
 }
 </style>
 @stop

 @section('content')
 
 <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box green">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Hotel Reviews
							</div>
						</div>
						<div class="portlet-body custom_review">
					@foreach($hotel_review as $key => $review)
						<div class="row event">
                        <div class="col-sm-12" style="margin-bottom:30px;">
                        <div class="row">						
						<div class="col-sm-6">
						<div class="well">
							<h4>Hotel Name:<span>{{ $review->hotel ? $review->hotel->hotel_name :'' }}</span></h4>
						   </div></div>
						    <div class='col-sm-6'>
                            <div class="well">							
							 <h4>User Email:<span>{{ $review->user ? $review->user->email : ''}}</span></h4></div>
                           </div></div></div>
						   <div class="col-sm-12" style="margin-bottom:30px;">
							<div class="row">						
								<div class="col-sm-12">
									<div class="well">
										@for($i=1;$i<=5;$i++)
										
											<span class="fa fa-star star_rating @if($i<=$review->rating)checked @endif"></span>
										
										@endfor
										
									</div>
								</div>
								
							</div>
						</div>
						    <div class='col-sm-12' style="margin-bottom:40px;">
                            <div class="well">							
							<h4>Review Description</h4>
                           <p>
									{{ $review->review_description }}
								</p>
						   </div></div>
						   
							@endforeach
							<div class="col-sm-12 event_review_images">
						<div class="row">
						
						@foreach($hotel_review_image as $key => $review_image)
					
						<div class="col-sm-3">
						<img src="{{URL::asset('public/uploads/hotel_review_gallery/'.$review_image->hotel_reviews_id.'/'.$review_image->hotel_review_image)}}" class="img-responsive" alt=""/>
						</div>
						@endforeach
						</div></div>
						</div>
						<div>
						</div>
						</div>
						</div>
						</div>
						@if($review->is_approved=='0')
			<div class="col-md-12">
				<button class="btn btn-success test" onclick="hotelapprove({{$review->id}});" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="hoteldelete({{$review->id}});" >Delete</button>
			</div>
		@endif	
						</div>
						
						
						@stop
@section('script')
<script>

function hotelapprove(value){
		
		if(confirm("Are you sure you want to Approve this?")){
			$.ajax({
				type: "post",
				url: "{{ route('hotel-review-approve') }}",
				data: {'_token':'{{ csrf_token() }}', 'id':value},
				success: function(result){
					window.location.href='{{route("review-list")}}';
					return false;
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
					window.location.href='{{route("review-list")}}';
					return false;
				}
			});
		}
		else{
			return false;
		}
	}
</script>
@stop