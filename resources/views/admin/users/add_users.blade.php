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
                    <i class="fa fa-edit"></i>Add Users
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>'user-add','files'=>true,'id' => 'userForm','onsubmit'=>'return validate()')) }}
                <div class="row event">

                        <div class='col-sm-4'>                       
                            <div class="form-group">
                                {{ Form::label('user', 'Name',['class' => 'form-label']) }}
                                {{ Form::text('name', '', ['class' => 'form-control','placeholder' => 'Name']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('user email', 'Email',['class' => 'form-label']) }}
                                {{ Form::email('email', '', ['class' => 'form-control','placeholder' => 'User Email']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('event', 'Country',['class' => 'form-label']) }}
                                <select class="form-control" name="country_id" id="country" data-parsley-required="true">
                                    <option value="">Select Country</option>
                                    @foreach ($country_details as $country) 
                                    {
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    }
                                    @endforeach
                                </select>                 
                            </div>
                            <div class="form-group">
                                {{ Form::label('mobile', 'Mobile',['class' => 'form-label']) }}
                                {{ Form::text('mobile', '', ['class' => 'form-control','placeholder' => 'mobile']) }}
                            </div>                                            
                            
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('user', 'User Name',['class' => 'form-label']) }}
                                {{ Form::text('user_name', '', ['class' => 'form-control','placeholder' => 'User Name']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('password', 'Password',['class' => 'form-label']) }}
                                {{ Form::password('password', ['class' => 'form-control','placeholder' => '********']) }}
                            </div>
                            <div class="form-group state">
                                    {{ Form::label('user state', 'State',['class' => 'form-label']) }}
                                    <select disabled class="form-control" name="state_id" id="state" data-parsley-required="true">
                                        <option value="">Select state</option>
                                    </select>   
                                </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="dob">
                                        {{ Form::label('dob', 'Date Of birth',['class' => 'form-label']) }}
                                        {{ Form::text('dob', '', ['class' => 'form-control datepicker','id' => 'datepicker']) }}   
                                    </div> 
                                </div>
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
                                                        <input onchange="readURL(this);" id="upload-file-selector" type="file" name="profile_photo">
                                                        <i class="fa_icon icon-upload-alt margin-correction" ></i>Upload Profile Photo
                                                    </label>
                                                </span>                      
                                            <tr></td>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row event">
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('Gender', 'Gender',['class' => 'form-label']) }}
                                {{ Form::label('male', 'Male') }}
                                {{ Form::radio('gender', '1', true, array('id'=>'male','class'=>'radio-btn')) }}
                                {{ Form::label('female', 'Female') }}
                                {{ Form::radio('gender', '2', false, array('id'=>'female','class'=>'radio-btn')) }}                     
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('Type', 'Type',['class' => 'form-label']) }}
                                {{ Form::label('admin', 'Admin') }}
                                {{ Form::radio('type', 'admin', false, array('id'=>'admin','class'=>'radio-btn')) }}
                                {{ Form::label('user', 'User') }}
                                {{ Form::radio('type', 'user', true, array('id'=>'user','class'=>'radio-btn')) }}
                               
							   {{-- Form::label('promoter', 'Promoter') }}
                                {{ Form::radio('type', 'promoter', false, array('id'=>'promoter','class'=>'radio-btn')) --}}
                            </div>
                        </div>
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4" style="display:none;">
                            <div class="form-group">
                                    {{ Form::label('active', 'Active',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_active', 1, true, ['class' => '','id'=>'active']) }}                      
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
 <script type="text/javascript" src="{{asset('public/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
 <script>
    jQuery(document).ready(function() {    
        App.init(); // initlayout and core plugins
        
    });
    </script>
    
    <script>
        $( "#userForm" ).validate( {
            rules: {
                name: "required",
                user_name: "required",
                email: "required",
                mobile: "required",
                dob: "required",
                password: "required",
                country_id: "required",
                //state_id: "required",	   
            },
            messages: {
                name: "Please enter name",
                user_name: "Please enter user name",
                email: "Please enter email",
                mobile: "Please enter mobile number",
                dob: "Please select date of birth",
                password: "Please enter password",
                country_id: "Please select any country",
               // state_id: "Please select first US Country",
                	   				
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


        $('#country').on('change',function(){
            var countryID = $(this).val();
            if(countryID == 239){
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
                    $("#state").empty();
                }
            }else{
                $("#state").attr("disabled", true);
                $("#state").empty();
            }    
        });

        $('#datepicker').datepicker({
            changeMonth: true,
            changeYear: true
        });

        function validate() {
        var submit = new Array();
        var filename=document.getElementById('upload-file-selector').value;
        if(filename != ""){
            var extension=filename.substr(filename.lastIndexOf('.')+1).toLowerCase();
        
            if(extension=='jpg' || extension=='jpeg' || extension=='png') {
                return true;
            } else {
                var error = $('<p class="help-block">upload jpg,png image</p>');
                $("#image").append(error);
                $("#new").attr('style',  'border:2px solid red;');
                return false;
            }
        }
        }

        </script>
 @stop