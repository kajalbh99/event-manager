@extends('layouts.admin_master_layout')

 @section('style')
 <link rel="stylesheet" type="text/css" href="{{asset('public/plugins/bootstrap-datepicker/css/datepicker.css')}}"/>
  <!-- blueimp Gallery styles -->
<link rel="stylesheet" href="https://blueimp.github.io/Gallery/css/blueimp-gallery.min.css')}}">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="{{asset('/public/css/jquery.fileupload.css')}}">
<link rel="stylesheet" href="{{asset('/public/css/jquery.fileupload-ui.css')}}">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="{{asset('/public/css/jquery.fileupload-noscript.css')}}"></noscript>
<noscript><link rel="stylesheet" href="{{asset('/public/css/jquery.fileupload-ui-noscript.css')}}"></noscript>
<style>
.modaltable_button{
	margin-bottom:10px;
}
</style>
 @stop

 @section('content')
 <div class="row">
 {{ Form::open(array('route'=>['event-edit',$event_details->id],'files'=>true,'id' => 'eventForm', 'onsubmit'=>'return validate()' )) }}
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-edit"></i>Edit Event
                </div>
            </div>
            <div class="portlet-body">
                
                    <div class="row event">
                        <div class='col-sm-4'> 
							<input type="hidden" name="promoter_id" value="{{$event_details->user_id}}">
                            <div class="form-group">
                                {{ Form::label('event', 'Event Name',['class' => 'form-label']) }}
                                {{ Form::text('event_name',$event_details->event_name, ['class' => 'form-control','placeholder' => 'Event Name']) }}
                            </div>                                           
                            <div class="form-group">
                                {{ Form::label('event', 'Event Location',['class' => 'form-label']) }}
                                {{ Form::text('event_location',$event_details->event_location, ['class' => 'form-control','placeholder' => 'Event Location']) }}                    
                            </div>
                            <div class="form-group">
                                {{ Form::label('event', 'Event Slug',['class' => 'form-label']) }}
                                {{ Form::text('event_slug',$event_details->event_slug, ['class' => 'form-control','placeholder' => 'Event Slug']) }}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('event', 'Carnival',['class' => 'form-label']) }}
                                <select class="form-control" name="carnival_id" id="carnival_id" data-parsley-required="true">
                                    @foreach ($carnival_details as $carnival) 
                                    {
                                        <option {{ $carnival->id == $event_details->carnival_id ? 'selected="selected"' : '' }}  value="{{ $carnival->id }}">{{ $carnival->carnival_name }}</option>
                                    }
                                    @endforeach
                                </select>
                            </div>                                    
                            <div class="form-group">
                                {{ Form::label('event', 'Country',['class' => 'form-label']) }}
                                <select class="form-control" name="country_id" id="country_id" data-parsley-required="true">
                                    @foreach ($country_details as $country) 
                                    {
                                        <option {{ $country->id == $event_details->country_id ? 'selected="selected"' : '' }} value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    }
                                    @endforeach
                                </select>
                            </div>
                        </div>
                         <!--div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('banner', 'Event Banner',['class' => 'form-label']) }}
                                {{ Form::file('event_banner', ['class' => 'form-control']) }}                        
                            </div>
                         </div-->
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div style="height: 190px;" class="form-body">
                                    <div class="form-group">
                                        <table>
                                            <tr><td name="image" id="image" align="center"></td>       
                                                @if(!empty($event_details->event_banner))
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/uploads/event_banners/'.$event_details->id.'/'.$event_details->event_banner)}}">
                                                @else
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">
                                                @endif
                                            </tr></td>
                                            <tr><td>
                                                <span id="fileselector">                                     
                                                    <label class="btn green" for="upload-file-selector">
                                                        <input onchange="readURL(this);" id="upload-file-selector" type="file" name="event_banner">
                                                        <i class="fa_icon icon-upload-alt margin-correction" ></i>Upload New Banner
                                                    </label>
                                                </span>                      
                                            <tr></td>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        
                   
                    <div class="form-group">
                        {{ Form::label('event', 'Event Description',['class' => 'form-label']) }}
                        {{ Form::textarea('event_description',$event_details->event_description, ['size' => '30x5','class' => 'form-control','placeholder' => 'Event Description']) }}
                    </div>
                    <div class="form-group">

                        {{ Form::label('event', 'Third Party Ticket Purchase',['class' => 'form-label']) }}
						
						{{ Form::checkbox('ticketing_check', 1, ($event_details->ticketing_website != ""), ['class' => '','id'=>'ticketing_website_check']) }} 

                     

                    </div>
					  <div class="form-group" id="ticket-div" style="display:none;">                                               
						   {{ Form::text('ticketing_website', $event_details->ticketing_website, ['class' => 'form-control','placeholder' => 'Third Party Ticket Purchase']) }}                  
					</div>
                    <div class="type form-group">
                        <!-- {{ Form::label('event', 'Type',['class' => 'form-label']) }}
                        {{ Form::label('daily', 'Daily') }}
                        {{ Form::radio('event_type', 'daily', false, array('id'=>'daily','class'=>'radio-btn')) }} -->
                       <div style="display:none">{{ Form::label('one_time', 'One Time') }}
                        {{ Form::radio('event_type', 'one time',($event_details->event_type == "one time"), array('id'=>'one_time','class'=>'radio-btn')) }}</div>
                        <!-- {{ Form::label('fixed_dates', 'Fixed Dates') }}
                        {{ Form::radio('event_type', 'fixed dates', false, array('id'=>'fixed_dates','class'=>'radio-btn')) }}
                        {{ Form::label('fixed_weekdays', 'Fixed Weekdays') }}
                        {{ Form::radio('event_type', 'fixed weekdays', false, array('id'=>'fixed_weekdays','class'=>'radio-btn')) }} -->

                    <div class="row" id="daily_show" style="display:none;">
                        <div class="col-sm-2" >
                            <div class="form-group">
                                {{ Form::label('event start date', 'Start Date') }}
                                {{ Form::text('event_start_date', '', ['class' => 'form-control datepicker','id' => 'datepicker1']) }}
                            </div>
                         </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {{ Form::label('event end date', 'End Date') }}
                                {{ Form::text('event_end_date', '', ['class' => 'form-control datepicker','id' => 'datepicker2']) }}
                            </div>
                        </div>
                     </div>

                     <div class="row" id="one_time_show">
                        <div class="col-sm-2" >
                            <div class="form-group">
                                {{ Form::label('event start date', 'Event Date') }}
                                {{ Form::text('one_time_event_start_date', date('m/d/Y',strtotime($event_details->event_date)), ['class' => 'form-control datepicker','id' => 'one_time_event_start_date','autocomplete'=>'off','readonly'=>'readonly']) }}
                            </div>
                        </div>
						<!--div class="col-sm-2" >
                            <div class="form-group">
                                {{ Form::label('event end date', 'Event End Date') }}
                                {{ Form::text('one_time_event_end_date', $event_details->event_end_date ? date('m/d/Y',strtotime($event_details->event_end_date)):'', ['class' => 'form-control datepicker','id' => 'one_time_event_end_date','autocomplete'=>'off','readonly'=>'readonly']) }}
                            </div>
                        </div-->
                    </div>

                    <div class="row" id="weekdays_show" style="display:none;">
                        <div class="col-sm-12">
                        <div class="col-sm-2" >
                            <div class="form-group">
                                {{ Form::label('event start date', 'Start Date') }}
                                {{ Form::text('event_start_date', '', ['class' => 'form-control datepicker','id' => 'datepicker4']) }}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {{ Form::label('event end date', 'End Date') }}
                                {{ Form::text('event_end_date', '', ['class' => 'form-control datepicker','id' => 'datepicker5']) }}
                            </div>
                        </div>
                        </div>
                        <div class="col-sm-12">
                        <div class="col-sm-1" >
                            <div class="form-group">
                                {{ Form::label('monday', 'Monday') }}
                                {{ Form::checkbox('Monday', 1, null, ['class' => '','id'=>'monday']) }}
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                {{ Form::label('tuesday', 'Tuesday') }}
                                {{ Form::checkbox('Tuesday', 1, null, ['class' => '','id'=>'tuesday']) }}
                            </div>
                        </div>
                        <div class="col-sm-1 auto" >
                            <div class="form-group">
                                {{ Form::label('wednesday', 'Wednesday') }}
                                {{ Form::checkbox('Wednesday', 1, null, ['class' => '','id'=>'wednesday']) }}
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                {{ Form::label('thursday', 'Thursday') }}
                                {{ Form::checkbox('Thursday', 1, null, ['class' => '','id'=>'thursday']) }}
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                {{ Form::label('friday', 'Friday') }}
                                {{ Form::checkbox('Friday', 1, null, ['class' => '','id'=>'friday']) }}
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                {{ Form::label('saturday', 'Saturday') }}
                                {{ Form::checkbox('Saturday', 1, null, ['class' => '','id'=>'saturday']) }}
                            </div>
                        </div>
                        <div class="col-sm-1" >
                            <div class="form-group">
                                {{ Form::label('sunday', 'Sunday') }}
                                {{ Form::checkbox('Sunday', 1, null, ['class' => '','id'=>'sunday']) }}
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="row" id="fixed_date_show" style="display:none;">
                        <div class="col-sm-2" >    
                        <input type="hidden" name="count" value="1" />
                            <div class="control-group" id="fields">
                                <div class="controls" id="profs"> 
                                    <div class="input-append">
                                        <div id="field"><lable>Event Dates : </lable><input autocomplete="off" class="input form-control datepicker" id="field1" name="fixed_date" type="text" data-items="8"/><button id="b1" class="btn add-more" type="button">+</button></div>
                                    </div>
                                </div>
                            </div>
                        </div>                      
                            
                        <!--div class="col-sm-2" >
                            <div class="form-group">
                                {{ Form::label('event start date', 'Select Dates') }}
                                {{ Form::text('event_start_date', '', ['class' => 'form-control datepicker','id' => 'datepicker4']) }}
                                <button id="b1" class="btn add-more" type="button">+</button>
                            </div>
                        </div-->
                    </div>
                    <div class="row">
                        <div class="col-sm-2">                           
                            <div class="form-group">
                                    {{ Form::label('is_refundable', 'Are Tickets Refundable?',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_refundable', 1, ($event_details->is_refundable == "1"), ['class' => '','id'=>'is_refundable']) }}                      
                            </div>
                        </div>
						<div class="col-sm-2">                           
                            <div class="form-group">
                                    {{ Form::label('active', 'Active',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_active', 1, ($event_details->is_active == "1"), ['class' => '','id'=>'active']) }}                      
                            </div>
                        </div>
						<!--div class="col-sm-2">                           
                            <div class="form-group">
                                    {{ Form::label('approve', 'Approve',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_approved', 1, ($event_details->is_approved == "1"), ['class' => '','id'=>'approve']) }}                      
                            </div>
                        </div-->
						<div class="col-sm-4">                           
                            <div class="form-group">
                                    {{ Form::label('yearly', 'Yearly Event',['class' => 'form-label']) }}
                                    {{ Form::checkbox('yearly', 1, ($event_details->yearly == "1"), ['class' => '','id'=>'yearly']) }}                       
                            </div>
                        </div>
						  <div class="col-sm-4">
                           <!-- <div class="form-group">
                                {{ Form::label('evntprivacy', 'Privacy',['class' => 'form-label']) }}<br>
                                {{ Form::label('public', 'Public') }}
                                {{ Form::radio('event_privacy', 'PUBLIC',($event_details->event_privacy == 'PUBLIC' ), array('id'=>'public','class'=>'radio-btn')) }}
                                {{ Form::label('private', 'Private') }}
                                {{ Form::radio('event_privacy', 'PRIVATE', ($event_details->event_privacy == 'PRIVATE' ), array('id'=>'private','class'=>'radio-btn')) }}                      
                            </div>-->
							{{ Form::label('event', 'Carnival Type',['class' => 'form-label']) }}
							{{ Form::label('0', 'Carnival') }}
							{{ Form::radio('carnival_type', '0', ($event_details->carnival_type == '0' ), array('id'=>'0','class'=>'radio-btn')) }}
							{{ Form::label('1', 'Local') }}
							{{ Form::radio('carnival_type', '1', ($event_details->carnival_type == '1' ), array('id'=>'1','class'=>'radio-btn')) }}
                        </div>
                    </div>
					
                    
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
	<div class="portlet box blue">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-edit"></i>Gallery Images
			</div>
		</div>
		<div class="portlet-body">
			
			<div class="row">
				
				<div class="col-sm-2 add-btn">
					<span id="fileselector1">                                     
						<label class="btn green" for="upload-file-selector1">
							<input onchange="gallery(this);" class="upload-file-selector" id="upload-file-selector1" type="file" name="event_gallery[]">
							Upload
						</label>
					</span>
					<span id="add_more" style="display:none">
						<span id="extra">                                     
							<label class="btn blue" id="add_new">
								<input onchange="addmore(this);" class="upload-file-selector first" id="upload-file-selector2" type="file" name="event_gallery[]">Add More 
							</label>
						</span>
					</span>
				</div>
			</div>
			<div class="row">
				@foreach($event_gallery as $images )
					@foreach($images as $image)
						<div class="col-sm-2">
							<div class="form-group">
								<div style="height: 190px;" class="form-body">
									<div class="form-group">
										<a id="savedcross" class="1" onclick="removeSavedImg(this)" style="position: absolute; left: 0; top: 0;">
											<img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this image" align="top"> 
										</a>
										<img class="banner img-responsive" src="{{asset('public/uploads/event_gallery/'.$event_details->id.'/'.$image)}}">                                                                                                                         
									  </div>
								</div>
							</div>
						</div>
					@endforeach
				@endforeach
				<div id="gallery" style="display:none">                       
					<div class="col-sm-2">
						<div class="form-group">
							<div style="height: 190px;" class="form-body">
								<div class="form-group" id="new_image">
									<a id="cross"class="1" onclick="removeImg(this)" style="display:none; position: absolute; left: 0; top: 0;">
										<img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this image" align="top"> 
									</a>
									<img id="new_gallery" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">    
																																   
								  </div>
							</div>
						</div>
					</div>
				</div>                   
			</div>
		</div>
	</div>
	<button class="add_field_button btn btn-success" type="button">Add Ticket Types<i class="fa fa-plus"></i></button><br><br>
	@if($event_ticket_types)
	@foreach($event_ticket_types as $event_ticket_type)	
	<div class="portlet box blue">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-edit"></i>Ticket Type
			</div>
		</div>
		<div class="portlet-body">
			<div class="row">&nbsp;</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-2">
						<div class="form-group">
							 {{ Form::label('event', 'Ticket Type',['class' => 'form-label']) }}
							<input type="text" name="ticket_type[]" class="form-control" value="{{$event_ticket_type->ticket_type}}">
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							{{ Form::label('event', 'Ticket Price',['class' => 'form-label']) }}
							<input type="text" name="ticket_price[]" class="form-control" value="{{$event_ticket_type->ticket_price}}">
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							{{ Form::label('event', 'Total  Seats',['class' => 'form-label']) }}
							<input type="hidden" name="temp_id[]" value="temp_1_<?php echo time();?>" class="temp_ids">
							<input type="number" name="ticket_seats[]" class="form-control" value="{{$event_ticket_type->total_tickets}}"  @if($event_ticket_type->pdfs->count() > 0) readonly @endif>
						</div>
					</div>
					
					<div class="col-sm-2">
						<div class="form-group">
							{{ Form::label('event', 'Ticket start date',['class' => 'form-label']) }}
							<input type="text" name="ticket_start_date[]" class="form-control datepicker"  value="{{$event_ticket_type->ticket_start_date ? date('m/d/Y',strtotime($event_ticket_type->ticket_start_date)) : '' }}"  readonly>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							{{ Form::label('event', 'Ticket end date',['class' => 'form-label']) }}
							<input type="text" name="ticket_end_date[]" class="form-control datepicker"  value="{{$event_ticket_type->ticket_end_date ? date('m/d/Y',strtotime($event_ticket_type->ticket_end_date)) :''}}" readonly>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							&nbsp;<input type="hidden" name="ticket_ids[]" class="form-control" value="{{$event_ticket_type->id}}">
						</div>
					</div>
				</div>
			</div>	
			@if($event_ticket_type->pdfs)
			<div class="row">
				<div class="col-sm-12">
				<table class="table">
				@foreach($event_ticket_type->pdfs as $k=>$pdf)
				<tr>
					<td><a href="{{URL('/').'/public/uploads/ticket_pdfs/'.$event_ticket_type->id.'/'.$pdf->file}}" download>{{$pdf->file}}</a></td><td><button type="button" class="btn btn-danger removePdf" data-event_type_id = "{{$event_ticket_type->id}}"  data-pdf_id = "{{$pdf->id}}">Remove</button></td>
				</tr>
				@endforeach
				</table>
				</div>
			</div>
			@endif
		</div>
	</div>
	@endforeach
	@else
	<div class="portlet box blue">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-edit"></i>Ticket Type
			</div>
		</div>
		<div class="portlet-body">
			
			<div class="row">&nbsp;</div>
			
			<div class="row">
					<div class="col-sm-12">
						<div class="col-sm-2">
							<div class="form-group">
								 {{ Form::label('event', 'Ticket Type',['class' => 'form-label']) }}
								<input type="text" name="ticket_type[]" class="form-control" value="">
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								{{ Form::label('event', 'Ticket Price',['class' => 'form-label']) }}
								<input type="text" name="ticket_price[]" class="form-control" value="">
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								{{ Form::label('event', 'Total  Seats',['class' => 'form-label']) }}
								<input type="hidden" name="temp_id[]" value="temp_1_<?php echo time();?>" class="temp_ids">
								<input type="number" name="ticket_seats[]" class="form-control" value="0" min="0">
							</div>
						</div>
						
						<div class="col-sm-2">
							<div class="form-group">
								{{ Form::label('event', 'Ticket start date',['class' => 'form-label']) }}
								<input type="text" name="ticket_start_date[]" class="form-control datepicker"  value=""  readonly>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								{{ Form::label('event', 'Ticket end date',['class' => 'form-label']) }}
								<input type="text" name="ticket_end_date[]" class="form-control datepicker"  value="" readonly>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								&nbsp;<input type="hidden" name="ticket_ids[]" class="form-control" value="">
							</div>
						</div>
						
					</div>
					
				
				
			</div>	
			
			
			
		
		 
		</div>
	</div>
	@endif
	
	<div class="input_fields_wrap">
	
	</div>
	<input type="hidden" name="removed_img" id="removed_img">
	<input type="hidden" name="removed_saved_img" id="removed_saved_img">
</div>	

<div class="col-md-12">        <!-- BEGIN EXAMPLE TABLE PORTLET-->        <div class="portlet box blue">            <div class="portlet-title">                <div class="caption">                    <i class="fa fa-edit"></i>Reviews                </div>            </div>            <div class="portlet-body">  				@if(count($reviews)>0)                    <table class="table table-striped table-hover table-bordered" id="eventTable">						<thead>							<tr>								<th>									Sr. no.								</th>								<th>									User								</th>								<th>									 Review								</th>								<th>									rating								</th>															<th>									Action								</th>							</tr>						</thead>						<tbody>													@foreach($reviews as $key => $review)							<tr>								<td>									{{ $key+1 }}								</td>								<td>									{{$review->user ? $review->user->email : ''}}								</td>								<td>									{{ $review->review_description }}								</td>								<td>									{{$review->rating}}								</td>								<td> 									@if($review->is_approved)											<button class="btn btn-danger" onclick = "return disapprove('{{$review->id}}');" type="button">Disapprove</button>									@else										<button class="btn btn-success test" onclick="return approve('{{$review->id}}');"  type="button">Approve</button>																		@endif			
&nbsp;<a class="btn btn-primary" href="{{route('view-event-review',[$review->id])}}">View</a>
					</td>                        							</tr>							@endforeach																		</tbody>					</table>                                            				@else					<h4>No Reviews found.</h4>				@endif            </div>        </div>   	

<button type="submit" class="btn btn-primary">Submit</button>&nbsp;
@if($event_details->is_approved=='0')
<a href="{{route('event_change_approve_status',[$event_details->id,1])}}" class="btn btn-success">Approve</a>
@else
<a href="{{route('event_change_approve_status',[$event_details->id,0])}}" class="btn btn-danger">Disapprove</a>	
@endif
{{ Form::close() }}
</div>


</div>

@include('includes.modal')
<div id="modalpopups">
</div>
 @stop
 
 @section('script')
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
 <script type="text/javascript" src="{{asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>

 <script src="{{asset('/public/js/vendor/jquery.ui.widget.js')}}"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="https://blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!-- blueimp Gallery script -->
<script src="https://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="{{asset('/public/js/jquery.iframe-transport.js')}}"></script>
<!-- The basic File Upload plugin -->
<script src="{{asset('/public/js/jquery.fileupload.js')}}"></script>
<!-- The File Upload processing plugin -->
<script src="{{asset('/public/js/jquery.fileupload-process.js')}}"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="{{asset('/public/js/jquery.fileupload-image.js')}}"></script>
<!-- The File Upload audio preview plugin -->
<script src="{{asset('/public/js/jquery.fileupload-audio.js')}}"></script>
<!-- The File Upload video preview plugin -->
<script src="{{asset('/public/js/jquery.fileupload-video.js')}}"></script>
<!-- The File Upload validation plugin -->
<script src="{{asset('/public/js/jquery.fileupload-validate.js')}}"></script>
<!-- The File Upload user interface plugin -->
<!--script src="{{asset('/public/js/jquery.fileupload-ui.js')}}"></script-->
<script>	
	jQuery(document).ready(function() {
	 
	  /**** check third party *****/
	 
	var checkBox = document.getElementById("ticketing_website_check");
	var div = document.getElementById("ticket-div");
	
	if (checkBox.checked == true) {
		div.style.display = "block";
	} else {
		div.style.display = "none";
	}
	 
	 /****************/
	 App.init(); // initlayout and core plugins
	 
	
	});
	jQuery.validator.addMethod("greaterThan", 
	function(value, element, params) {

		if (!/Invalid|NaN/.test(new Date(value))) {
			return new Date(value) > new Date($(params).val());
		}

		return isNaN(value) && isNaN($(params).val()) 
			|| (Number(value) > Number($(params).val())); 
	},'Must be greater than {0}.');
 function disapprove(value) {
     if (confirm("Are you sure you want to Disapprove this?")) {
         $.ajax({
             type: "post",
             url: "{{ route('event-review-disapprove') }}",
             data: {
                 '_token': '{{ csrf_token() }}',
                 'id': value
             },
             success: function(result) {
                 location.reload();
             }
         });
     } else {
         return false;
     }
 }

 function approve(value) {
     if (confirm("Are you sure you want to Approve this?")) {
         $.ajax({
             type: "post",
             url: "{{ route('event-review-approve') }}",
             data: {
                 '_token': '{{ csrf_token() }}',
                 'id': value
             },
             success: function(result) {
                 location.reload();
             }
         });
     } else {
         return false;
     }
 }
 $('.datepicker').each(function() {
     $(this).datepicker({
         changeMonth: true,
         changeYear: true,
		 startDate:new Date()
     });
 });
 $('#ticketing_website_check').on('click', function() {
     var checkBox = document.getElementById("ticketing_website_check");
     var div = document.getElementById("ticket-div");
     if (checkBox.checked == true) {
         div.style.display = "block";
     } else {
         div.style.display = "none";
     }
 });
 
 function validate_form(){
	 $("#eventForm").validate({
     rules: {
         event_name: "required",
         event_location: "required",
         event_slug: "required",
         country_id: "required",
         carnival_id: "required",
         event_description: "required",
         event_type: "required",
         one_time_event_start_date: "required",
         one_time_event_end_date: { greaterThan: "#one_time_event_start_date" },
         event_privacy: "required",
         total_tickets: "required",
         basic_ticket_price: "required",
         ticket_service_tax: "required",
         event_banner: {
             required: true,
             extension: "jpg,jpeg,png",
             filesize: 5,
         },
		'ticket_type[]': {required:true,minlength: 1},
		'ticket_price[]': {required:true,minlength: 1, number: true},
		'ticket_seats[]': {required:true,minlength: 1, number: true},
		/* 'ticket_start_date[]': {required:true},
		'ticket_end_date[]': {required:true}, */
     },
     messages: {
         event_name: "Please enter event name",
         event_location: "Please enter location",
         event_slug: "Please enter slug name",
         country_id: "Please select country",
         carnival_id: "Please  select carnival",
         event_description: "Please enter event description",
         event_type: "Please select event type",
         one_time_event_start_date: "Please select event date",
         one_time_event_end_date: "Please select valid event end date",
         event_privacy: "Please select event privcacy",
         total_tickets: "Please enter total tickets",
         basic_ticket_price: "Please enter basic ticket price",
         ticket_service_tax: "Please enter service tax",
         event_banner: {
             required: "Please select banner image",
             extension: "select jpg or jpeg or png images",
             filesize: "maximum 5mb size is allowed",
         },
         
     },
     errorElement: "em",
     errorPlacement: function(error, element) {
         // Add the `help-block` class to the error element
         error.addClass("help-block");

         // Add `has-feedback` class to the parent div.form-group
         // in order to add icons to inputs
         element.parents("label").addClass("has-feedback");

         if (element.prop("type") === "checkbox") {
             error.insertAfter(element.parent("label"));
         } else {
             error.insertAfter(element);
         }
     },
	});
 }


 var count = 0;

 function validate() {
     $('.help-block').remove();
     var submit = new Array();

     var filename = document.getElementById('upload-file-selector').value;
     if (filename != "") {
         var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();

         if (extension == 'jpg' || extension == 'jpeg' || extension == 'png') {

         } else {
             var error = $('<p class="help-block">upload jpg,png image</p>');
             $("#image").append(error);
             $("#new").attr('style', 'border:2px solid red;');
             submit.push("false");
         }
     }


     var filename = document.getElementById('upload-file-selector1').value;
     if (filename != "") {
         var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();

         if (extension == 'jpg' || extension == 'jpeg' || extension == 'png') {

         } else {
             $("#new_gallery").attr('style', 'border:2px solid red;');
             var error = $('<p class="help-block">upload jpg,png image</p>');
             $("#new_image").append(error);
             submit.push("false");
         }
     }
     var i = 2;
     $(".upload-file-selector").each(function() {
         if (document.getElementById('upload-file-selector' + i)) {
             var filename = document.getElementById('upload-file-selector' + i).value;
             if (filename != "") {
                 var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();
                 if (extension == 'jpg' || extension == 'jpeg' || extension == 'png') {

                 } else {
                     i = i + 1;
                     $("#new_gallery" + i).attr('style', 'border:2px solid red;');
                     var error = $('<p class="help-block">upload jpg,png image</p>');
                     $("#image" + i).append(error);
                     submit.push("false");
                     i = i - 1;
                 }
             }
         }
         i++;
     });

     //pop false for removed image
     for (i = 1; i <= count; i++) {
         submit.pop();
     }
     console.log(submit);
     for (i = 0; i < submit.length; i++) {
         if (submit[i] == "false") {
             return false;
         }
     }

     return true;
 }

 function readURL(input) {
     if (input.files && input.files[0]) {
         var reader = new FileReader();
         reader.onload = function(e) {
             $('#new')
                 .attr('src', e.target.result)
         };
         reader.readAsDataURL(input.files[0]);
     }
 }

 function gallery(input) {
     if (input.files && input.files[0]) {
         var reader = new FileReader();
         reader.onload = function(e) {
             $('#new_gallery')
                 .attr('src', e.target.result);
             $('#add_more').show();
             $('#gallery').show();
             $('#fileselector1').hide();
             $('#cross').show();
         };
         reader.readAsDataURL(input.files[0]);
     }
 }

 var removed_img_id = new Array();

 function removeImg($this) {

     $($this).closest(".col-sm-2").remove();
     var input_file_id = $($this).attr('class');
     removed_img_id.push(input_file_id);
     $('input[id=removed_img]').val(removed_img_id);
     var error_image = $($this).next('.banner').next('.help-block').attr('class');
     if (error_image == 'help-block') {
         count++;
         console.log(count);
     }
 }

 var removed_saved_img_id = new Array();

 function removeSavedImg($this) {

     var input_file_name = $($this).next('.banner').attr('src');
     var pieces = input_file_name.split(/[\s/]+/);
     var input_file_name = pieces[pieces.length - 1];
     $($this).closest(".col-sm-2").remove();
     removed_saved_img_id.push(input_file_name);
     $('input[id=removed_saved_img]').val(removed_saved_img_id);
 }

 function addmore(input) {
     var i = 1;
     if (input.files && input.files[0]) {
         var reader = new FileReader();
         reader.onload = function(e) {
             // $('#new_gallery')
             //     .attr('src', e.target.result);
             $('#add_new').hide();
             while (i <= 100) {
                 $('#upload-file-selector' + i).hide();
                 $('#add_new' + i).hide();
                 if (!document.getElementById('upload-file-selector' + i)) {

                     var div = $("<div class='col-sm-2'><div class='form-group'><div style='height: 190px;' class='form-body'><div class='form-group' id='image" + i + "'></div></div></div></div>");
                     var image = $("<a class='" + i + "' onclick='removeImg(this)' style='position: absolute; left: 0; top: 0;'><img src='{{asset('public/img/cross.png')}}' class='removeImage' alt='Remove this image' align='top'></a><img id='new_gallery" + i + "' class='banner img-responsive' src=''>");
                     $('#gallery').append(div);
                     $('#image' + i).append(image);
                     $('#new_gallery' + i)
                         .attr('src', e.target.result);

                     var id = 'upload-file-selector' + i;
                     var e = $("<input onchange='addmore(this);' class='upload-file-selector' id='upload-file-selector" + i + "' type='file' name='event_gallery[]'>Add More");
                     var l = $("<label class='btn blue' id='add_new" + i + "'>Add More</label>");
                     $('#extra').append(l);
                     $('#add_new' + i).append(e);
                     return;
                 }
                 i++;
             }

         };
         reader.readAsDataURL(input.files[0]);
     }
 }

 $(document).ready(function() {
	 validate_form();
     $('.type input[type="radio"]').click(function() {
         var id = $(this).attr('id');
         if (id == 'one_time') {
             $('#one_time_show').show();
         } else {
             $('#one_time_show').hide();
         }
         if (id == 'daily') {
             $('#daily_show').show();
         } else {
             $('#daily_show').hide();
         }
         if (id == 'fixed_weekdays') {
             $('#weekdays_show').show();
         } else {
             $('#weekdays_show').hide();
         }
         if (id == 'fixed_dates') {
             $('#fixed_date_show').show();
         } else {
             $('#fixed_date_show').hide();
         }

         if (id == 'private') {
             $('#one_time_show').hide();
         } else {
             $('#one_time_show').show();
         }

     });

     var next = 1;
     $(".add-more").click(function(e) {
         e.preventDefault();
         var addto = "#field" + next;
         var addRemove = "#field" + (next);
         next = next + 1;
         var newIn = '<input autocomplete="off" class="input form-control datepicker" id="field' + next + '" name="field' + next + '" type="text">';
         var newInput = $(newIn);
         var removeBtn = '<button id="remove' + (next - 1) + '" class="btn btn-danger remove-me" >-</button></div><div id="field">';
         var removeButton = $(removeBtn);
         $(addto).after(newInput);
         $(addRemove).after(removeButton);
         $("#field" + next).attr('data-source', $(addto).attr('data-source'));
         $("#count").val(next);

         $('.remove-me').click(function(e) {
             e.preventDefault();
             var fieldNum = this.id.charAt(this.id.length - 1);
             var fieldID = "#field" + fieldNum;
             $(this).remove();
             $(fieldID).remove();
         });

         $('.datepicker').each(function() {
             $(this).datepicker({
                 changeMonth: true,
                 changeYear: true
             });
         });
     });
 });

 $(document).ready(function() {
	 var max_fields = 10; //maximum input boxes allowed
     var wrapper = $(".input_fields_wrap"); //Fields wrapper
     var add_button = $(".add_field_button"); //Add button ID

     var x = 1; //initlal text box count
	 var d = new Date();
	 var n = d.getTime();
     $(add_button).click(function(e) { //on add input button click
         e.preventDefault();
         if (x < max_fields) { //max input box allowed
             x++; //text box increment

            var hidden_html  = '<div class="portlet box blue"><div class="portlet-title"><div class="caption"><i class="fa fa-edit"></i>Ticket Type</div></div><div class="portlet-body"><div class="row">&nbsp;</div><div class="row"><div class="col-sm-12"><div class="col-sm-2"><div class="form-group"><label for="event" class="form-label">Ticket Type</label><input type="text" name="ticket_type[]" class="form-control"></div></div><div class="col-sm-2"><div class="form-group"><label for="event" class="form-label">Ticket Price</label><input type="text" name="ticket_price[]" class="form-control"></div></div><div class="col-sm-2"><div class="form-group"><label for="event" class="form-label">Total Seats</label><input type="hidden" name="temp_id[]" value="temp_'+x+'_'+n+'" class="temp_ids"><input type="number" name="ticket_seats[]" class="form-control" min="0" value="0"></div></div><div class="col-sm-2"><div class="form-group"><label for="event" class="form-label">Ticket Start Date</label><input type="text" name="ticket_start_date[]" class="form-control datepicker" readonly></div></div><div class="col-sm-2"><div class="form-group"><label for="event" class="form-label">Ticket End Date</label><input type="text" name="ticket_end_date[]" class="form-control datepicker" readonly></div></div><div class="col-sm-2"><div class="form-group"><label for="event" class="form-label">Upload Pdf</label><button type="button" class="btn btn-info openModal" data-toggle="modal" data-target="#modal_temp_'+x+'_'+n+'">Add Pdf</button><a href="#" class="remove_field"><img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this row" align="top"></a></div></div></div></div></div></div>';
			
			var modal_html = '<div id="modal_temp_'+x+'_'+n+'" class="modal fade" role="dialog"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <button type="button" class="close" data-dismiss="modal">&times;</button> <h4 class="modal-title">Upload tickets</h4> </div><div class="modal-body"><span class="btn btn-success fileinput-button"> <i class="glyphicon glyphicon-plus"></i> <span>Add pdf tickets</span> <input id="fileupload_temp_'+x+'_'+n+'" type="file" name="files[]" multiple></span><br><br><div id="" class="progress"><div class="progress-bar progress-bar-success"></div></div><table class="table"></table><br></div><div class="modal-footer"><button type="button" class="btn btn-primary fileUploadAll">Upload Tickets</button><button type="button" class="btn btn-default" data-dismiss="modal">Close</button> </div></div></div></div>';
            $(wrapper).append(hidden_html); //add input box
            $('#modalpopups').append(modal_html); //add input box
			
			$('.datepicker').each(function(){
				$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				startDate:new Date()
				});
			} );
			openModal();
			uploadPdf('temp_'+x+'_'+n+'');
			validate_form();
			
         }
     });

     $(wrapper).on("click",".remove_field", function(e){ 
        e.preventDefault(); $(this).parent().parent().parent().parent().parent().parent().remove(); x--;
    });
	
 });  
 var url = window.location.hostname === 'blueimp.github.io' ?
                '//jquery-file-upload.appspot.com/' : 'server/php/',
uploadButton = $('<button/>')
.addClass('btn btn-primary fileUpload')
.prop('disabled', true)
.attr('type', 'button')
.text('Processing...')
.css('display','none')
.on('click', function () {
var $this = $(this),
data = $this.data();
$this
.off('click')
.text('Abort')
.on('click', function () {
$this.remove();
data.abort();
});
data.submit().always(function () {
$this.remove();
});
});

function openModal(){
	$( ".openModal" ).click(function(){
		//var temp_id = $(this).prev('input').attr('value');
		var temp_id = $(this).parent().parent().parent().find('input[type=hidden]').attr('value');
		//uploadPdf(temp_id);
		validate_form();
	});
	
} 
function openFirstModal(){
	$( ".openFirstModal" ).click(function(){
		//var temp_id = $(this).prev('input').attr('value');
		var temp_id = $(this).parent().parent().parent().find('input[type=hidden]').attr('value');
		//uploadFirstPdf(temp_id);
		validate_form();
	});
	
}

function uploadPdf(temp_id){
	localStorage.setItem(temp_id,0);
	/* $('#fileupload').fileupload({
		url: '{{route("uploadpdf")}}',
		dataType: 'json',
		acceptFileTypes: /(\.|\/)(pdf)$/i,
		formData: {temp_id:temp_id,_token: "{{ csrf_token() }}"}
	}); */
	'use strict';
    // Change this to the location of your server-side upload handler:
   	
		$('#fileupload_'+temp_id+'').fileupload({
			url: '{{route("uploadpdf")}}',
			dataType: 'json',
			autoUpload: false,
			acceptFileTypes: /(\.|\/)(pdf)$/i,
			//maxFileSize: 999000,
			formData: {temp_id:temp_id,_token: "{{ csrf_token() }}"}
			
		}).on('fileuploadadd', function (e, data) {
			data.context = $('<tr/>').appendTo('#modal_'+temp_id+' .table');
			$.each(data.files, function (index, file) {
				var node1 =  $('<td/>');
				var node = $('<td/>')
						.append($('<span/>').text(file.name));
				if (!index) {
					node1.append(uploadButton.clone(true).data(data));
					node1.append("<input type='button' class='fileCancel btn btn-danger modaltable_button' value='cancel'>");
				}
				node.appendTo(data.context);
				node1.appendTo(data.context);
				$(".fileUpload").eq(-1).on("click",function(){
					  data.submit();
				 })
				 $(".fileCancel").eq(-1).on("click",function(){
					  $(this).parent().parent().remove()
				 })
				  
			});
		}).on('fileuploadprocessalways', function (e, data) {
			var index = data.index,
				file = data.files[index],
				node = $(data.context.children()[index]);
			if (file.preview) {
				node
					.prepend('<br>')
					.prepend(file.preview);
			}
			if (file.error) {
				node
					.append('<br>')
					.append($('<span class="text-danger"/>').text(file.error));
			}
			if (index + 1 === data.files.length) {
				data.context.find('button')
					.text('Upload')
					.prop('disabled', !!data.files.error);
			}
		}).on('fileuploadprogressall', function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#modal_'+temp_id+' .progress .progress-bar').css(
				'width',
				progress + '%'
			);
		}).on('fileuploaddone', function (e, data) {
			//let c = parseInt($('input[value='+temp_id+']').parent().find('input[type=number]').val());
			let c = parseInt(localStorage.getItem(temp_id));
			
			let deletebutton = '';	
			 $.each(data.result.files, function (index, file) {
				if (file.deleteUrl)
				{
					deletebutton = '<a class="btn btn-danger delete modaltable_button" type="button" data-type="'+file.deleteType+'" data-url="'+file.deleteUrl+'" href="jaavscript:void(0);" onclick="removePdfFile(\''+file.deleteUrl+'\')"><i class="glyphicon glyphicon-trash"></i><span>Delete</span></a>';
				}
				if (file.url) {
					var link = $('<a>')
						.attr('target', '_blank')
						.prop('href', file.url);
					$(data.context.children()[index])
						.wrap(link);
				} else if (file.error) {
					var error = $('<span class="text-danger"/>').text(file.error);
					$(data.context.children()[index])
						.append('<br>')
						.append(error);
				}
				data.context.find('.fileCancel').parent().append(deletebutton);
		
				data.context.find('.fileCancel')
					.text('Upload')
					.prop('disabled', true)
					.hide();
				//c = c+1;
				c = c+1;
				localStorage.setItem(temp_id,c);
				
			});
			
			$('input[value='+temp_id+']').parent().find('input[type=number]').val(parseInt(localStorage.getItem(temp_id)));
			if(parseInt(localStorage.getItem(temp_id))> 0){
				$('input[value='+temp_id+']').parent().find('input[type=number]').attr('readonly', true);
			}else{
				$('input[value='+temp_id+']').parent().find('input[type=number]').attr('readonly', false);
			}
			$('#modal_'+temp_id+'').find('button[data-dismiss]').click();
		}).on('fileuploadfail', function (e, data) {
			$.each(data.files, function (index) {
				var error = $('<span class="text-danger"/>').text('File upload failed.');
				$(data.context.children()[index])
					.append('<br>')
					.append(error);
			});
		}).prop('disabled', !$.support.fileInput)
			.parent().addClass($.support.fileInput ? undefined : 'disabled');

		$("#modal_"+temp_id+" .fileUploadAll").on("click", function() {
			$("#modal_"+temp_id+" .fileUpload:enabled").click(); // click all upload buttons
	    })
	
}

let temp_id = $('#temp_id').val();
localStorage.setItem(temp_id,0);
$('#fileupload').fileupload({
	url: '{{route("uploadpdf")}}',
	dataType: 'json',
	autoUpload: false,
	acceptFileTypes: /(\.|\/)(pdf)$/i,
	//maxFileSize: 999000,
	formData: {temp_id:temp_id,_token: "{{ csrf_token() }}"}
	
}).on('fileuploadadd', function (e, data) {
	
	data.context = $('<tr/>').appendTo('#myModal .table');
	$.each(data.files, function (index, file) {
		var node1 =  $('<td/>');
		var node = $('<td/>')
				.append($('<span/>').text(file.name));
		if (!index) {
			node1.append(uploadButton.clone(true).data(data));
			node1.append("<input type='button' class='fileCancel btn btn-danger modaltable_button' value='cancel'>");
		}
		node.appendTo(data.context);
		node1.appendTo(data.context);
		$(".fileUpload").eq(-1).on("click",function(){
			  data.submit();
		 })
		 $(".fileCancel").eq(-1).on("click",function(){
			  $(this).parent().parent().remove();
			  data.abort();
		 })
		  
	});
}).on('fileuploadprocessalways', function (e, data) {
	var index = data.index,
		file = data.files[index],
		node = $(data.context.children()[index]);
	if (file.preview) {
		node
			.prepend('<br>')
			.prepend(file.preview);
	}
	if (file.error) {
		node
			.append('<br>')
			.append($('<span class="text-danger"/>').text(file.error));
	}
	if (index + 1 === data.files.length) {
		data.context.find('button')
			.text('Upload')
			.prop('disabled', !!data.files.error);
		
	}
}).on('fileuploadprogressall', function (e, data) {
	var progress = parseInt(data.loaded / data.total * 100, 10);
	console.log(data.loaded+'------'+data.total);
	$('#myModal .progress .progress-bar').css(
		'width',
		progress + '%'
	);
}).on('fileuploaddone', function (e, data) {
	//let c = parseInt($('input[value='+temp_id+']').parent().find('input[type=number]').val());
	let c = parseInt(localStorage.getItem(temp_id));
	let deletebutton = '';	
	 $.each(data.result.files, function (index, file) {
		 
		 if (file.deleteUrl)
		 {
		 deletebutton = '<a class="btn btn-danger delete modaltable_button" type="button" data-type="'+file.deleteType+'" data-url="'+file.deleteUrl+'" href="jaavscript:void(0);" onclick="removePdfFile(\''+file.deleteUrl+'\')"><i class="glyphicon glyphicon-trash"></i><span>Delete</span></a>';
		 }
		
		
		if (file.url) {
			var link = $('<a>')
				.attr('target', '_blank')
				.prop('href', file.url);
			$(data.context.children()[index])
				.wrap(link);
		} else if (file.error) {
			var error = $('<span class="text-danger"/>').text(file.error);
			$(data.context.children()[index])
				.append('<br>')
				.append(error);
		}
		data.context.find('.fileCancel').parent().append(deletebutton);
		
		data.context.find('.fileCancel')
			.text('Upload')
			.prop('disabled', true)
			.hide();
		c = c+1;
		localStorage.setItem(temp_id,c);
		
			
	});
	
	//$('input[value='+temp_id+']').parent().find('input[type=number]').val(c);
	$('input[value='+temp_id+']').parent().find('input[type=number]').val(parseInt(localStorage.getItem(temp_id)));
	if(parseInt(localStorage.getItem(temp_id))> 0){
		$('input[value='+temp_id+']').parent().find('input[type=number]').attr('readonly', true);
	}else{
		$('input[value='+temp_id+']').parent().find('input[type=number]').attr('readonly', false);
	}
	$("#myModal").find('button[data-dismiss]').click();
}).on('fileuploadfail', function (e, data) {
	$.each(data.files, function (index) {
		var error = $('<span class="text-danger"/>').text('File upload failed.');
		$(data.context.children()[index])
			.append('<br>')
			.append(error);
	});
}).prop('disabled', !$.support.fileInput)
	.parent().addClass($.support.fileInput ? undefined : 'disabled');

$("#myModal .fileUploadAll").on("click", function() {
	$("#myModal .fileUpload:enabled").click(); // click all upload buttons
});


/***** remove pdf ****/
$(document).ready(function(){
	$('.removePdf').click(function(){
		let th = $(this);
		let c = confirm("Are you sure!");
		if(c){
			let event_type_id = $(this).data('event_type_id');
			let pdf_id = $(this).data('pdf_id');
			$.ajax({
				url:"{{route('ajax.removepdf')}}",
				type:"POST",
				data:{event_type_id:event_type_id,pdf_id:pdf_id,"_token":"{{csrf_token()}}"},
				success:function(){
					let seats = th.closest('div[class="portlet-body"]').find('input[type=number]').val();
					seats = seats-1;
					th.closest('div[class="portlet-body"]').find('input[type=number]').val(seats);
					th.closest('tr').remove();
				},
				error:function(error){
					alert(error.statusText);
					return false;
				}
			});
			

			return true;
		}else{
			return false;
		}
	});
});
function removePdf(event_type_id,pdf_id){
	console.log($(this).parent().parent().parent());
	return false;
}
/*************/
function removePdfFile(deleteurl){
	//let th = $(this);
	$.ajax({
		url:deleteurl,
		success:function(temp_id){
			
			console.log(temp_id);
			let v = localStorage.getItem(temp_id);
			
			
			v = parseInt(v)-1;
			localStorage.setItem(temp_id,v)
			$('input[value='+temp_id+']').parent().find('input[type=number]').val(v);
			if(parseInt(localStorage.getItem(temp_id))> 0){
				$('input[value='+temp_id+']').parent().find('input[type=number]').attr('readonly', true);
			}else{
				$('input[value='+temp_id+']').parent().find('input[type=number]').attr('readonly', false);
			}
			
			$('a[data-url="'+deleteurl+'"]').closest('tr').remove();
		}
	});
}
</script>       
 @stop