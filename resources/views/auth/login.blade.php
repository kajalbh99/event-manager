@extends('layouts.admin_login_layout')

 @section('style')

 @stop

 @section('content')
<!-- BEGIN LOGIN FORM -->
{!! Form::open(array('route'=>'admin-login','class'=>'login-form' )) !!}
    
    <h3 class="form-title">Admin Login</h3>
    <div class="alert alert-danger display-hide">
        <button class="close" data-close="alert"></button>
        <span>
            Enter any email and password.
        </span>
    </div>
    <div class="form-group">
        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
        {!! Form::label('email', 'Email', ['class' => 'control-label visible-ie8 visible-ie9']) !!}
        <div class="input-icon">
            <i class="fa fa-user"></i>
            {!! Form::email('email', '', ['class' => 'form-control placeholder-no-fix','placeholder' => 'email']) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('password', 'Password', ['class' => 'control-label visible-ie8 visible-ie9']) !!}
        <div class="input-icon">
            <i class="fa fa-lock"></i>
            {!! Form::password('password', ['class' => 'form-control placeholder-no-fix','placeholder' => 'Password']) !!}
        </div>
    </div>
    <div class="form-actions">
        <!--label class="checkbox">
        <input type="checkbox" name="remember" value="1"/> Remember me </label-->
        <button type="submit" class="btn green pull-right">
        Login <i class="m-icon-swapright m-icon-white"></i>
        </button>
    </div>
   
    {!! Form::close(); !!}
<!-- END LOGIN FORM -->
@stop

@section('script')

@stop