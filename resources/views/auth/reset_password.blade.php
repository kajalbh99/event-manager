@extends('layouts.admin_login_layout')

 @section('style')

 @stop

 @section('content')
<!-- BEGIN LOGIN FORM -->
{!! Form::open(array('route'=>'change-password','class'=>'password-form','id' => 'passwordForm' )) !!}
    
    <h3 class="form-title">Reset Password</h3>
    <div class="alert alert-danger display-hide">
        <button class="close" data-close="alert"></button>
        <span>
            Enter any email and password.
        </span>
    </div>
    <div class="form-group">
		
		{!! Form::hidden('user_id', $requested_user) !!}		
        {!! Form::label('password', 'Password', ['class' => 'control-label visible-ie8 visible-ie9']) !!}
        <div class="input-icon">
            <i class="fa fa-lock"></i>
            {!! Form::password('password', ['class' => 'form-control placeholder-no-fix','placeholder' => 'Enter new Password']) !!}
        </div>
    </div>
	<div class="form-group">
        {!! Form::label('confirm-password', 'Confirm Password', ['class' => 'control-label visible-ie8 visible-ie9']) !!}
        <div class="input-icon">
            <i class="fa fa-lock"></i>
            {!! Form::password('confirm_password', ['class' => 'form-control placeholder-no-fix','placeholder' => 'Enter Confirm Password']) !!}
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn green pull-right">
        Reset <i class="m-icon-swapright m-icon-white"></i>
        </button>
    </div>
   
    {!! Form::close(); !!}
<!-- END LOGIN FORM -->
@stop

@section('script')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>
<script>
$( "#passwordForm" ).validate( {
            rules: { 
                password: "required",  
				confirm_password: { required: true,
									equalTo: "#password",
									}
            },
            messages: {          
                password: "Password is required", 
				confirm_password: {
					required: "Re-enter password",
					equalTo : "password does not match",
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
</script>
@stop