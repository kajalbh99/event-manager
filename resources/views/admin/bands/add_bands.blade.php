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
                    <i class="fa fa-edit"></i>Add Bands
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>'band-add','files'=>true,'id' => 'bandForm', 'onsubmit'=>'return validate()' )) }}
                    <div class="row event">
                        <div class='col-sm-8'>                       
                            <div class="form-group">
                                {{ Form::label('band', 'Band Name',['class' => 'form-label']) }}
                                {{ Form::text('band_name', '', ['class' => 'form-control','placeholder' => 'Band Name']) }}
                            </div> 
                            <div class="form-group">
                                {{ Form::label('band', 'Band Description',['class' => 'form-label']) }}
                                {{ Form::textarea('band_description', '', ['size' => '30x5','class' => 'form-control','placeholder' => 'Band Description']) }}
                            </div>                                        
                        </div>

                    
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
                                    {{ Form::checkbox('carnival[]',$carnival->id, false, ['class' => '','id'=>'carnival']) }}                      
                            </div>
                        </div>

                        @endforeach
                    </div>			</div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->		<div class="portlet box blue">			<div class="portlet-title">				<div class="caption">					<i class="fa fa-edit"></i>Gallery				</div>			</div>			<div class="portlet-body">				 <div class="row">					<div class="col-sm-2 add-btn">						<span id="fileselector1">                                     							<label class="btn green" for="upload-file-selector1">								<input onchange="gallery(this);" class="upload-file-selector" id="upload-file-selector1" type="file" name="band_gallery[]">								Upload							</label>						</span>						<span id="add_more" style="display:none">							<span id="extra">                                     								<label class="btn blue" id="add_new">									<input onchange="addmore(this);" class="upload-file-selector first" id="upload-file-selector2" type="file" name="band_gallery[]">Add More 								</label>							</span>						</span>					</div>				</div>				<div class="row">					<div id="gallery" style="display:none">                       						<div class="col-sm-2">							<div class="form-group">								<div style="height: 190px;" class="form-body">									<div class="form-group" id="new_image">										<a id="cross"class="1" onclick="removeImg(this)" style="display:none; position: absolute; left: 0; top: 0;">											<img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this image" align="top"> 										</a>										<img id="new_gallery" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">    																																	   									  </div>								</div>							</div>						</div>					</div>                   				</div>				<input type="hidden" name="removed_img" id="removed_img">			</div>		</div>		<button type="submit" class="btn btn-primary">Submit</button>        {{ Form::close() }}
    </div>
</div>
 @stop
 
 @section('script')
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
 <script type="text/javascript" src="{{asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
 <script>
    jQuery(document).ready(function() {    
        App.init(); // initlayout and core plugins
    });


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