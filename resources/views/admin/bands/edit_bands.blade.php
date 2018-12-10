@extends('layouts.admin_master_layout')

 @section('style')
 <link rel="stylesheet" type="text/css" href="{{asset('public/plugins/bootstrap-datepicker/css/datepicker.css')}}"/>
 @stop

 @section('content')
 <div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-edit"></i>Edit Bands
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>['band-edit',$band_details->id],'files'=>true,'id' => 'bandForm', 'onsubmit'=>'return validate()' )) }}
                    <div class="row event">
                        <div class='col-sm-8'>                       
                            <div class="form-group">
                                {{ Form::label('band', 'Band Name',['class' => 'form-label']) }}
                                {{ Form::text('band_name',$band_details->band_name, ['class' => 'form-control','placeholder' => 'Band Name']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('band', 'Band Slug',['class' => 'form-label']) }}
                                {{ Form::text('band_slug',$band_details->band_slug, ['class' => 'form-control','placeholder' => 'Band Slug']) }}
                            </div>  
                            <div class="form-group">
                                {{ Form::label('band', 'Band Description',['class' => 'form-label']) }}
                                {{ Form::textarea('band_description',$band_details->band_description, ['size' => '30x5','class' => 'form-control','placeholder' => 'Band Description']) }}
                            </div>                                        
                        </div>

                    
                         <div class="col-sm-4">
                            <div class="form-group">
                                <div style="height: 190px;" class="form-body">
                                    <div class="form-group">
                                        <table>
                                            <tr><td name="image" id="image" align="center"></td>       
                                                @if(!empty($band_details->band_banner))
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/uploads/band_banners/'.$band_details->id.'/'.$band_details->band_banner)}}">
                                                @else
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">
                                                @endif    
                                            </tr></td>
                                            <tr><td>
                                                <span id="fileselector">                                     
                                                    <label class="btn green" for="upload-file-selector">
                                                        <input onchange="readURL(this);" id="upload-file-selector" type="file" name="band_banner">
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
                            {{ Form::label('carnival','Select Carnival',['class' => 'form-label']) }}<br>
                            </div>
                    <div class="row">
                        
                        @foreach($carnival_details as $carnival)
                        <div class="col-sm-3">
                            <div class="form-group">
                                    {{ Form::label('carnival', $carnival->carnival_name ,['class' => '']) }}
                                    {{ Form::checkbox('carnival[]',$carnival->id, in_array($carnival->id, $band_carnival) , ['class' => '','id'=>'carnival']) }}                      
                            </div>
                        </div>

                        @endforeach
                    </div>
             </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
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
								<input onchange="gallery(this);" class="upload-file-selector" id="upload-file-selector1" type="file" name="band_gallery[]">
								Upload
							</label>
						</span>
						<span id="add_more" style="display:none">
							<span id="extra">                                     
								<label class="btn blue" id="add_new">
									<input onchange="addmore(this);" class="upload-file-selector first" id="upload-file-selector2" type="file" name="band_gallery[]">Add More 
								</label>
							</span>
						</span>
					</div>
				</div>
				<div class="row">
						@foreach($band_gallery as $images )
						@foreach($images as $image)
							<div class="col-sm-2">
								<div class="form-group">
									<div style="height: 190px;" class="form-body">
										<div class="form-group">
											<a id="savedcross" class="1" onclick="removeSavedImg(this)" style="position: absolute; left: 0; top: 0;">
												<img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this image" align="top"> 
											</a>
											<img class="banner img-responsive" src="{{asset('public/uploads/band_gallery/'.$band_details->id.'/'.$image)}}">                                                                                                                         
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
<div class="col-md-12">        <!-- BEGIN EXAMPLE TABLE PORTLET-->        <div class="portlet box blue">            <div class="portlet-title">                <div class="caption">                    <i class="fa fa-edit"></i>Reviews                </div>            </div>            <div class="portlet-body">  				@if(count($reviews)>0)                    <table class="table table-striped table-hover table-bordered" id="eventTable">						<thead>							<tr>								<th>									Sr. no.								</th>								<th>									User								</th>								<th>									 Review								</th>								<th>									rating								</th>															<th>									Action								</th>							</tr>						</thead>						<tbody>													@foreach($reviews as $key => $review)							<tr>								<td>									{{ $key+1 }}								</td>								<td>									{{$review->user->email}}								</td>								<td>									{{ $review->review_description }}								</td>								<td>									{{$review->rating}}								</td>								<td> 									@if($review->is_approved)											<button class="btn btn-danger" onclick = "return disapprove('{{$review->id}}')"  type="button">Disapprove</button>									@else										<button class="btn btn-primary" onclick="return approve('{{$review->id}}')" type="button">Approve</button>									@endif	&nbsp;<a class="btn btn-primary" href="{{route('view-band-review',[$review->id])}}">View</a>							</td>                        							</tr>							@endforeach																		</tbody>					</table>                                            				@else					<h4>No Reviews found.</h4>				@endif            </div>       


 </div>        <!-- END EXAMPLE TABLE PORTLET-->	
 <button type="submit" class="btn btn-primary">Submit</button>
                {{ Form::close() }}
 </div></div>
 @stop
 
 @section('script')
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
 <script type="text/javascript" src="{{asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
 <script>	
    jQuery(document).ready(function() {    
        App.init(); // initlayout and core plugins
    });
	function disapprove(value){		if(confirm("Are you sure you want to Disapprove this?")){			$.ajax({				type: "post",				url: "{{ route('band-review-disapprove') }}",				data: {'_token':'{{ csrf_token() }}', 'id':value},				success: function(result){					location.reload();				}			});		}		else{			return false;		}					}		function approve(value){				if(confirm("Are you sure you want to Approve this?")){			$.ajax({				type: "post",				url: "{{ route('band-review-approve') }}",				data: {'_token':'{{ csrf_token() }}', 'id':value},				success: function(result){					location.reload();				}			});		}		else{			return false;		}	}

    $( "#bandForm" ).validate( {
        rules: {
            band_name: "required",          
            carnival_id: "required",
            band_description: "required",	   
        },
        messages: {
            band_name: "Please enter carnival name",
            carnival_id: "Please  select carnival",
            band_description: "Please enter event description",	   				
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
        
        for(i=0;i<submit.length;i++){
            if(submit[i] == "false"){
                return false;
            }
        }
        console.log(submit);
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
        count++;
        $( $this ).closest(".col-sm-2").remove();       
         var input_file_id = $( $this ).attr('class');
         removed_img_id.push(input_file_id);
         $('input[id=removed_img]').val(removed_img_id);                
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
                            var e = $("<input onchange='addmore(this);' class='upload-file-selector' id='upload-file-selector"+i+"' type='file' name='band_gallery[]'>Add More");
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
</script>       
 @stop