@extends('layouts.admin_master_layout_new')

 @section('style')

 @stop

 @section('content')
 <div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-edit"></i>Edit Hotel
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>['hotel-edit',$hotel_details->id],'files'=>true,'id' => 'hotelForm', 'onsubmit'=>'return validate()' )) }}
                    <div class="row hotel">
                        <div class='col-sm-4'>                       
                            <div class="form-group">
                                {{ Form::label('hotel', 'Hotel Name',['class' => 'form-label']) }}
                                {{ Form::text('hotel_name',$hotel_details->hotel_name, ['class' => 'form-control','placeholder' => 'Hotel Name']) }}
                            </div>                                           
                            <div class="form-group">
                                {{ Form::label('hotel', 'Hotel Location',['class' => 'form-label']) }}
                                {{ Form::text('hotel_location',$hotel_details->hotel_location, ['class' => 'form-control','placeholder' => 'Hotel Location']) }}                    
                            </div>
                            <div class="form-group">
                                {{ Form::label('hotel', 'Hotel Slug',['class' => 'form-label']) }}
                                {{ Form::text('hotel_slug',$hotel_details->hotel_slug, ['class' => 'form-control','placeholder' => 'Hotel Slug']) }}
                            </div>
                        </div>
                        <div class="col-sm-4">
							<div class="form-group">
                                {{ Form::label('event', 'Carnival',['class' => 'form-label']) }}
                                <select class="form-control" name="carnival_id" id="carnival_id" data-parsley-required="true">
                                    @foreach ($carnival_details as $carnival) 
                                    {
                                        <option {{ $carnival->id == $hotel_details->carnival_id ? 'selected="selected"' : '' }}  value="{{ $carnival->id }}">{{ $carnival->carnival_name }}</option>
                                    }
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                {{ Form::label('hotel', 'Country',['class' => 'form-label']) }}
                                <select class="form-control" name="country_id" id="country_id" data-parsley-required="true">
                                    @foreach ($country_details as $country) 
                                    {
                                        <option {{ $country->id == $hotel_details->country_id ? 'selected="selected"' : '' }} value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    }
                                    @endforeach
                                </select>
                            </div>
							<div class="form-group state">

                                {{ Form::label('user state', 'State',['class' => 'form-label']) }}

                                <select class="form-control" name="state_id" id="state" data-parsley-required="true">

                                    @foreach ($state_details as $state) 

                                    {

                                        <option  value="{{ $state->id }}" @if($hotel_details->state_id==$state->id) selected @endif>{{ $state->state_name }}</option>

                                    }

                                    @endforeach

                                </select>   

                            </div>
                        </div>
                         <!--div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('banner', 'hotel Banner',['class' => 'form-label']) }}
                                {{ Form::file('hotel_banner', ['class' => 'form-control']) }}                        
                            </div>
                         </div-->
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div style="height: 190px;" class="form-body">
                                    <div class="form-group">
                                        <table>
                                            <tr><td name="image" id="image" align="center"></td>       
                                                @if(!empty($hotel_details->hotel_banner))
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/uploads/hotel_banners/'.$hotel_details->id.'/'.$hotel_details->hotel_banner)}}">
                                                @else
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">
                                                @endif
                                            </tr></td>
                                            <tr><td>
                                                <span id="fileselector">                                     
                                                    <label class="btn green" for="upload-file-selector">
                                                        <input onchange="readURL(this);" id="upload-file-selector" type="file" name="hotel_banner">
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
                        {{ Form::label('hotel', 'Hotel Description',['class' => 'form-label']) }}
                        {{ Form::textarea('hotel_description',$hotel_details->hotel_description, ['size' => '30x5','class' => 'form-control','placeholder' => 'Hotel Description']) }}
                    </div>
                    
                    <div class="type form-group">
                       

                    
                    <div class="row">
                        <div class="col-sm-4" style="display:none">                           
                            <div class="form-group">
                                    {{ Form::label('active', 'Active',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_active', 1, ($hotel_details->is_active == "1"), ['class' => '','id'=>'active']) }}                      
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('evntprivacy', 'Privacy',['class' => 'form-label']) }}<br>
                                {{ Form::label('public', 'Public') }}
                                {{ Form::radio('hotel_privacy', 'PUBLIC',($hotel_details->hotel_privacy == 'PUBLIC' ), array('id'=>'public','class'=>'radio-btn')) }}
                                {{ Form::label('private', 'Private') }}
                                {{ Form::radio('hotel_privacy', 'PRIVATE', ($hotel_details->hotel_privacy == 'PRIVATE' ), array('id'=>'private','class'=>'radio-btn')) }}                      
                            </div>
                        </div>
                    </div>

                    

                    
                   
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
	<div class="portlet box blue">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-edit"></i>Gallery
			</div>
		</div>
		<div class="portlet-body">
			<div class="row">
				
				<div class="col-sm-2 add-btn">
					<span id="fileselector1">                                     
						<label class="btn green" for="upload-file-selector1">
							<input onchange="gallery(this);" class="upload-file-selector" id="upload-file-selector1" type="file" name="hotel_gallery[]">
							Upload
						</label>
					</span>
					<span id="add_more" style="display:none">
						<span id="extra">                                     
							<label class="btn blue" id="add_new">
								<input onchange="addmore(this);" class="upload-file-selector first" id="upload-file-selector2" type="file" name="hotel_gallery[]">Add More 
							</label>
						</span>
					</span>
				</div>
			</div>
			<div class="row">
				@foreach($hotel_gallery as $images )
					@foreach($images as $image)
						<div class="col-sm-2">
							<div class="form-group">
								<div style="height: 190px;" class="form-body">
									<div class="form-group">
										<a id="savedcross" class="1" onclick="removeSavedImg(this)" style="position: absolute; left: 0; top: 0;">
											<img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this image" align="top"> 
										</a>
										<img class="banner img-responsive" src="{{asset('public/uploads/hotel_gallery/'.$hotel_details->id.'/'.$image)}}">                                                                                                                         
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
			<input type="hidden" name="removed_img" id="removed_img">
			<input type="hidden" name="removed_saved_img" id="removed_saved_img">
		</div>
	</div>
	
</div>	

<div class="col-md-12">        <!-- BEGIN EXAMPLE TABLE PORTLET-->        <div class="portlet box blue">            <div class="portlet-title">                <div class="caption">                    <i class="fa fa-edit"></i>Reviews                </div>            </div>            <div class="portlet-body">  				@if(count($reviews)>0)                    <table class="table table-striped table-hover table-bordered" id="hotelTable">						<thead>							<tr>								<th>									Sr. no.								</th>								<th>									User								</th>								<th>									 Review								</th>								<th>									rating								</th>															<th>									Action								</th>							</tr>						</thead>						<tbody>													@foreach($reviews as $key => $review)							<tr>								<td>									{{ $key+1 }}								</td>								<td>									{{$review->user->email}}								</td>								<td>									{{ $review->review_description }}								</td>								<td>									{{$review->rating}}								</td>								<td> 									@if($review->is_approved)											<button class="btn btn-danger" onclick = "return disapprove('{{$review->id}}');" type="button">Disapprove</button>									@else										<button class="btn btn-success test" onclick="return approve('{{$review->id}}');"  type="button">Approve</button>																		@endif	&nbsp;<a class="btn btn-primary" href="{{route('view-hotel-review',[$review->id])}}">View</a>							</td>                        							</tr>							@endforeach																		</tbody>					</table>                                            				@else					<h4>No Reviews found.</h4>				@endif            </div>        </div>        <!-- END EXAMPLE TABLE PORTLET-->	</div>

 

</div>
<button type="submit" class="btn btn-primary">Submit</button>
@if($hotel_details->is_active=='0')
<a href="{{route('hotel_change_approve_status',[$hotel_details->id,1])}}" class="btn btn-success">Activate</a>
@else
<a href="{{route('hotel_change_approve_status',[$hotel_details->id,0])}}" class="btn btn-danger">Deactivate</a>	
@endif

{{ Form::close() }}
 @stop
 
 @section('script')
 
 <script>	
    jQuery(document).ready(function() {    
        App.init(); // initlayout and core plugins
    });		function disapprove(value){		if(confirm("Are you sure you want to Disapprove this?")){			$.ajax({				type: "post",				url: "{{ route('hotel-review-disapprove') }}",				data: {'_token':'{{ csrf_token() }}', 'id':value},				success: function(result){					location.reload();				}			});		}		else{			return false;		}					}		function approve(value){				if(confirm("Are you sure you want to Approve this?")){			$.ajax({				type: "post",				url: "{{ route('hotel-review-approve') }}",				data: {'_token':'{{ csrf_token() }}', 'id':value},				success: function(result){					location.reload();				}			});		}		else{			return false;		}	}
    $('.datepicker').each(function(){
        $(this).datepicker({
        changeMonth: true,
        changeYear: true
        });
    } );

    $( "#hotelForm" ).validate( {
        rules: {
            hotel_name: "required",
            hotel_location: "required",
            hotel_slug: "required",
            country_id: "required",
            carnival_id: "required",
            hotel_description: "required",
            hotel_type: "required",
            one_time_hotel_start_date: "required",
            hotel_privacy: "required",
            total_tickets: "required",
            basic_ticket_price: "required",
            ticket_service_tax: "required",
            hotel_banner: {
                    required: true,
                    extension: "jpg,jpeg,png",
                    filesize: 5,
                    }	   
        },
        messages: {
            hotel_name: "Please enter hotel name",
            hotel_location: "Please enter location",
            hotel_slug: "Please enter slug name",
            country_id: "Please select country",
            carnival_id: "Please  select carnival",
            hotel_description: "Please enter hotel description",
            hotel_type: "Please select hotel type",
            one_time_hotel_start_date: "Please select hotel date",
            hotel_privacy: "Please select hotel privcacy",
            total_tickets: "Please enter total tickets",
            basic_ticket_price: "Please enter basic ticket price",
            ticket_service_tax: "Please enter service tax",
            hotel_banner: {
                    required: "Please select banner image",
                    extension: "select jpg or jpeg or png images",
                    filesize: "maximum 5mb size is allowed",
                    }	   				
        },
        errorElement: "em",
        errorPlacement: function ( error, element ) {
            // Add the `help-block` class to the error element
            error.addClass( "help-block" );
    
            // Add `has-feedback` class to the parent div.form-group
            // in order to add icons to inputs
            element.parents( "label" ).addClass( "has-feedback" );
    
            if ( element.prop( "type" ) === "checkbox" ) {
                error.insertAfter( element.parent( "label" ) );
            } else {
                error.insertAfter( element );
            }
        },                 
    } );
    
    
    var count=0;
    function validate() {
        $('.help-block').remove();
        var submit = new Array();
        
        var filename=document.getElementById('upload-file-selector').value;
        if(filename != ""){
            var extension=filename.substr(filename.lastIndexOf('.')+1).toLowerCase();
        
            if(extension=='jpg' || extension=='jpeg' || extension=='png') {
                
            } else {
                var error = $('<p class="help-block">upload jpg,png image</p>');
                $("#image").append(error);
                $("#new").attr('style',  'border:2px solid red;');
                submit.push("false");
            }
        }
        
        
        var filename=document.getElementById('upload-file-selector1').value;
        if(filename != ""){
            var extension=filename.substr(filename.lastIndexOf('.')+1).toLowerCase();
            
            if(extension=='jpg' || extension=='jpeg' || extension=='png') {
                
            } else {
                $("#new_gallery").attr('style',  'border:2px solid red;');
                var error = $('<p class="help-block">upload jpg,png image</p>');
                $("#new_image").append(error);
                submit.push("false");
            }
        }
        var i =2;
        $(".upload-file-selector").each(function() {
            if(document.getElementById('upload-file-selector'+i)){
                var filename=document.getElementById('upload-file-selector'+i).value;
                if(filename!=""){
                    var extension=filename.substr(filename.lastIndexOf('.')+1).toLowerCase();
                    if(extension=='jpg' || extension=='jpeg' || extension=='png') {
                        
                    } else {
                        i=i+1;
                        $("#new_gallery"+i).attr('style',  'border:2px solid red;');
                        var error = $('<p class="help-block">upload jpg,png image</p>');
                        $("#image"+i).append(error);
                        submit.push("false");
                        i=i-1;
                    }
                }
            }
            i++;
        });
        
        //pop false for removed image
        for(i=1;i<=count;i++){
            submit.pop();
        }
        console.log(submit);
        for(i=0;i<submit.length;i++){
            if(submit[i] == "false"){
               return false;
            }
        }
        
        return true;         
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#new')
                    .attr('src', e.target.result)                      
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function gallery(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
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
    function removeImg($this){
        
        $( $this ).closest(".col-sm-2").remove();       
         var input_file_id = $( $this ).attr('class');
         removed_img_id.push(input_file_id);
         $('input[id=removed_img]').val(removed_img_id);
         var error_image = $( $this ).next('.banner').next('.help-block').attr('class');
         if(error_image == 'help-block'){
             count++;
             console.log(count); 
         }                
    }

    var removed_saved_img_id = new Array();
    function removeSavedImg($this){
        
        var input_file_name = $( $this ).next('.banner').attr('src');
        var pieces = input_file_name.split(/[\s/]+/);
        var input_file_name = pieces[pieces.length-1]; 
        $( $this ).closest(".col-sm-2").remove();       
        removed_saved_img_id.push(input_file_name);
        $('input[id=removed_saved_img]').val(removed_saved_img_id);                
    }

    function addmore(input) {
        var i= 1;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // $('#new_gallery')
                //     .attr('src', e.target.result);
                       $('#add_new').hide();
                    while(i<=100)
                    {   
                        $('#upload-file-selector'+i).hide();
                        $('#add_new'+i).hide();
                        if(!document.getElementById('upload-file-selector'+i))
                        {   

                            var div = $("<div class='col-sm-2'><div class='form-group'><div style='height: 190px;' class='form-body'><div class='form-group' id='image"+i+"'></div></div></div></div>");                                                                    
                            var image =$("<a class='"+i+"' onclick='removeImg(this)' style='position: absolute; left: 0; top: 0;'><img src='{{asset('public/img/cross.png')}}' class='removeImage' alt='Remove this image' align='top'></a><img id='new_gallery"+i+"' class='banner img-responsive' src=''>");    
                            $('#gallery').append(div);
                            $('#image'+i).append(image);    
                            $('#new_gallery'+i)
                            .attr('src', e.target.result);

                            var id = 'upload-file-selector'+i;
                            var e = $("<input onchange='addmore(this);' class='upload-file-selector' id='upload-file-selector"+i+"' type='file' name='hotel_gallery[]'>Add More");
                            var l =$("<label class='btn blue' id='add_new"+i+"'>Add More</label>");
                            $('#extra').append(l);
                            $('#add_new'+i).append(e);
                            return;                                            
                        }
                        i++;
                    }  
                                                                     
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    $(document).ready(function() {
    
    var next = 1;
    $(".add-more").click(function(e){
        e.prhotelDefault();
        var addto = "#field" + next;
        var addRemove = "#field" + (next);
        next = next + 1;
        var newIn = '<input autocomplete="off" class="input form-control datepicker" id="field' + next + '" name="field' + next + '" type="text">';
        var newInput = $(newIn);
        var removeBtn = '<button id="remove' + (next - 1) + '" class="btn btn-danger remove-me" >-</button></div><div id="field">';
        var removeButton = $(removeBtn);
        $(addto).after(newInput);
        $(addRemove).after(removeButton);
        $("#field" + next).attr('data-source',$(addto).attr('data-source'));
        $("#count").val(next);  
        
            $('.remove-me').click(function(e){
                e.prhotelDefault();
                var fieldNum = this.id.charAt(this.id.length-1);
                var fieldID = "#field" + fieldNum;
                $(this).remove();
                $(fieldID).remove();
            });

        
    }); 
	$('#country_id').on('change',function(){

            var countryID = $(this).val();

            
                $("#state").attr("disabled", false);

                var localurl = '{{ route("state-list", ":id") }}'; 

                localurl = localurl.replace(':id', countryID);   

                if(countryID){

                    $.ajax({

                    url:localurl,

                    success:function(res){               

                        if(res){

                            $("#state").empty();

                            $.each(res,function(key,value){

                                $("#state").append('<option value="'+key+'">'+value+'</option>');

                            });

                    

                        }else{

                        $("#state").empty();

                        }

                    }

                    });

                

            }else{

                $("#state").attr("disabled", true);

                $("#state").empty();

            }    

        });	
});
</script>       
 @stop