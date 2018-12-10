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
                    <i class="fa fa-edit"></i>Add Transportation
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>'transportation-add','files'=>true,'id' => 'transportationForm', 'onsubmit'=>'return validate()' )) }}
                    <div class="row transportation">
                        <div class='col-sm-4'>                       
                            <div class="form-group">
                                {{ Form::label('transportation', 'Transportation Name',['class' => 'form-label']) }}
                                {{ Form::text('transportation_name', '', ['class' => 'form-control','placeholder' => 'Transportation Name']) }}
                            </div>                                           
                            <div class="form-group">
                                {{ Form::label('transportation', 'Transportation Location',['class' => 'form-label']) }}
                                {{ Form::text('transportation_location', '', ['class' => 'form-control','placeholder' => 'Transportation Location']) }}                    
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('event', 'Carnival',['class' => 'form-label']) }}
                                <select class="form-control" name="carnival_id" id="carnival_id" data-parsley-required="true">
                                    @foreach ($carnival_details as $carnival) 
                                    {
                                        <option value="{{ $carnival->id }}">{{ $carnival->carnival_name }}</option>
                                    }
                                    @endforeach
                                </select>
                            </div>                                  
                            <div class="form-group">
                                {{ Form::label('transportation', 'Country',['class' => 'form-label']) }}
                                <select class="form-control" name="country_id" id="country_id" data-parsley-required="true">
                                    @foreach ($country_details as $country) 
                                    {
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    }
                                    @endforeach
                                </select>
                            </div>							<div class="form-group state">								{{ Form::label('user state', 'State',['class' => 'form-label']) }}								<select disabled class="form-control" name="state_id" id="state" data-parsley-required="true">									<option value="">Select state</option>								</select>   							</div>
                         </div>
                         <!--div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('banner', 'transportation Banner',['class' => 'form-label']) }}
                                {{ Form::file('transportation_banner', ['class' => 'form-control']) }}                        
                            </div>
                         </div-->
                         <div class="col-sm-4">
                            <div class="form-group">
                                <div style="height: 190px;" class="form-body">
                                    <div class="form-group">
                                        <table>
                                            <tr><td name="image" id="image" align="center"></td>       
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">    
                                            </tr></td>
                                            <tr><td>
                                                <span id="fileselector">                                     
                                                    <label class="btn green" for="upload-file-selector">
                                                        <input onchange="readURL(this);" id="upload-file-selector" type="file" name="transportation_banner">
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
                        {{ Form::label('transportation', 'Transportation Description',['class' => 'form-label']) }}
                        {{ Form::textarea('transportation_description', '', ['size' => '30x5','class' => 'form-control','placeholder' => 'Transportation Description']) }}
                    </div>
                    
                    <div class="type form-group">
                        

                    

                    
                    <div class="row">
                        <div class="col-sm-4" style="display:none;">                           
                            <div class="form-group">
                                    {{ Form::label('active', 'Active',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_active', 1, true, ['class' => '','id'=>'active']) }}                      
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('transportationprivacy', 'Privacy',['class' => 'form-label']) }}<br>
                                {{ Form::label('public', 'Public') }}
                                {{ Form::radio('transportation_privacy', 'PUBLIC', true, array('id'=>'public','class'=>'radio-btn')) }}
                                {{ Form::label('private', 'Private') }}
                                {{ Form::radio('transportation_privacy', 'PRIVATE', false, array('id'=>'private','class'=>'radio-btn')) }}                      
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
							<input onchange="gallery(this);" class="upload-file-selector" id="upload-file-selector1" type="file" name="transportation_gallery[]">
							Upload
						</label>
					</span>
					<span id="add_more" style="display:none">
						<span id="extra">                                     
							<label class="btn blue" id="add_new">
								<input onchange="addmore(this);" class="upload-file-selector first" id="upload-file-selector2" type="file" name="transportation_gallery[]">Add More 
							</label>
						</span>
					</span>
				</div>
			</div>
			<div class="row">
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
		</div>
	</div>
	<button type="submit" class="btn btn-primary">Submit</button>
                {{ Form::close() }}
</div>
 @stop
 
 @section('script')

 <script>
    jQuery(document).ready(function() {    
        App.init(); // initlayout and core plugins
    });
 

    $( "#transportationForm" ).validate( {
        rules: {
            transportation_name: "required",
            transportation_location: "required",
            country_id: "required",			transportation_description: "required",
            transportation_privacy: "required",
            transportation_banner: {
                    required: true,
                    extension: "jpg,jpeg,png",
                    filesize: 5,
                    }	   
        },
        messages: {
            transportation_name: "Please enter carnival name",
            transportation_location: "Please enter your location",
             country_id: "Please select country",
           
            transportation_description: "Please enter transportation description",
            transportation_privacy: "Please select transportation privcacy",
           
            transportation_banner: {
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
        
        //pop false submitted for removed image
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
                            var e = $("<input onchange='addmore(this);' class='upload-file-selector' id='upload-file-selector"+i+"' type='file' name='transportation_gallery[]'>Add More");
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
        e.prtransportationDefault();
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
                e.prtransportationDefault();
                var fieldNum = this.id.charAt(this.id.length-1);
                var fieldID = "#field" + fieldNum;
                $(this).remove();
                $(fieldID).remove();
            });

        
    });        	 $('#country_id').on('change',function(){            var countryID = $(this).val();                            $("#state").attr("disabled", false);                var localurl = '{{ route("state-list", ":id") }}';                 localurl = localurl.replace(':id', countryID);                   if(countryID){                    $.ajax({                    url:localurl,                    success:function(res){                                       if(res){                            $("#state").empty();                            $.each(res,function(key,value){                                $("#state").append('<option value="'+key+'">'+value+'</option>');                            });                                            }else{                        $("#state").empty();                        }                    }                    });                            }else{                $("#state").attr("disabled", true);                $("#state").empty();            }            });
});
</script>       
 @stop