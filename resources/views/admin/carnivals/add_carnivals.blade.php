@extends('layouts.admin_master_layout')

 @section('style')

 @stop

 @section('content')
 <div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-edit"></i>Add Carnival
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>'carnival-add','files'=>true,'id' => 'carnivalForm','onsubmit'=>'return validate()')) }}
                    <div class="row">   
                        <div class="col-sm-8">
                            <div class="form-group">
                                {{ Form::label('carnival', 'Carnival Name') }}
                                {{ Form::text('carnival_name', '', ['class' => 'form-control','placeholder' => 'Carnival Name']) }}
                            </div>
                            <div class="form-group" style="display:none">
                                {{ Form::label('active', 'Active') }}
                                {{ Form::checkbox('is_active', 1, true, ['class' => '','id'=>'active']) }}                      
                            </div>
                        </div>
                    
                        <div class="col-sm-4">
                        <div class="form-group">
                            <div style="" class="form-body">
                                <div class="form-group">
                                    <table>
                                        <tr><td name="image" id="image" align="center"></td>       
                                                <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">    
                                        </tr></td>
                                        <tr><td>
                                            <span id="fileselector">                                     
                                                <label class="btn green" for="upload-file-selector">
                                                    <input onchange="readURL(this);" id="upload-file-selector" type="file" name="carnival_banner">
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
            
                     
                    
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->		<button type="submit" class="btn btn-primary">Submit</button>                {{ Form::close() }}
    </div>
</div>
 @stop
 
 @section('script')
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
 <script>
    jQuery(document).ready(function() {    
        App.init(); // initlayout and core plugins
        
    });
    </script>
    
    <script>
        function validate() {
            var submit = new Array();
            var filename=document.getElementById('upload-file-selector').value;
            if(filename != ""){
                var extension=filename.substr(filename.lastIndexOf('.')+1).toLowerCase();
            
                if(extension=='jpg' || extension=='jpeg' || extension=='png' ) {
                    return true;
                } else {
                    var error = $('<p class="help-block">upload jpg image</p>');
                    $("#image").append(error);
                    $("#new").attr('style',  'border:2px solid red;');
                    return false;
                }
            }
        }

        $( "#carnivalForm" ).validate( {
            rules: {
                carnival_name: "required",
                carnival_slug: "required",
                carnival_banner: {
                        required: true,
                        extension: "jpg,jpeg,png",
                        filesize: 5,
                        }	   
            },
            messages: {
                carnival_name: "Please enter carnival name",
                carnival_slug: "Please enter slug name",
                carnival_banner: {
                        required: "Please select banner image",
                        extension: "select jpg or png images",
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

    </script>
 @stop