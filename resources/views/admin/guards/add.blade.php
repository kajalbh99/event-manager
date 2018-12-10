@extends('layouts.admin_master_layout_new')

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
                    <i class="fa fa-edit"></i>Add Guard
                </div>
            </div>
            <div class="portlet-body">

                {{ Form::open(array('route'=>'guard-add','files'=>true,'id' => 'guardForm','onsubmit'=>'return validate()')) }}

                <div class="row event">

						<div class="col-sm-4">

                            <div class="form-group">

                                {{ Form::label('user', 'User Name',['class' => 'form-label']) }}

                                {{ Form::text('user_name', '', ['class' => 'form-control','placeholder' => 'User Name']) }}

                            </div>

                            <div class="form-group">

                                {{ Form::label('password', 'Password',['class' => 'form-label']) }}

                                {{ Form::password('password', ['class' => 'form-control','placeholder' => '********']) }}

                            </div>
							<div class="form-group">

                                {{ Form::label('user', 'Event',['class' => 'form-label']) }}

                                <select class="form-control" name="event_id" id="event" data-parsley-required="true">

                                    <option value="">Select Event</option>

                                    @foreach ($events as $event) 

                                    {

                                        <option value="{{ $event->id }}">{{ ucfirst($event->event_name) }}</option>

                                    }

                                    @endforeach

                                </select> 

                            </div>
                            <div class="form-group">

                                {{ Form::label('user', 'Promoter',['class' => 'form-label']) }}

                                <select class="form-control" name="promoter_id" id="promoter" data-parsley-required="true">

                                    <option value="">Select Promoter</option>

                                    @foreach ($promoters as $promoter) 

                                    {

                                        <option value="{{ $promoter->id }}">{{ ucfirst($promoter->name) }}</option>

                                    }

                                    @endforeach

                                </select> 

                            </div>

                                                               

                         </div>

                         

                </div>

				<div class="row" style="display:none;">

					

					<div class="col-sm-4">

						<div class="form-group">

								{{ Form::label('active', 'Active',['class' => 'form-label']) }}

								{{ Form::checkbox('is_active', 1, true, ['class' => '','id'=>'active']) }}                      

						</div>    

					</div> 

				</div>

				

            </div>

                    
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
		<button type="submit" class="btn btn-primary">Submit</button>

                {{ Form::close() }}
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
 
    
    $( "#guardForm" ).validate( {
        rules: {
            user_name: "required",
            password: "required",
            event_id: "required",
               
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