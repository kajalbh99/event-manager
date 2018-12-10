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
                    <i class="fa fa-edit"></i>Add Event
                </div>
            </div>
            <div class="portlet-body">
                {{ Form::open(array('route'=>['promoter-add-event',$promoter_id],'files'=>true,'id' => 'eventForm', 'onsubmit'=>'return validate()' )) }}				   <div class="row event">
                        <div class='col-sm-4'>                       
                            <div class="form-group">
                                {{ Form::label('event', 'Event Name',['class' => 'form-label']) }}
                                {{ Form::text('event_name', '', ['class' => 'form-control','placeholder' => 'Event Name']) }}
                            </div>                                           
                            <div class="form-group">
                                {{ Form::label('event', 'Event Location',['class' => 'form-label']) }}
                                {{ Form::text('event_location', '', ['class' => 'form-control','placeholder' => 'Event Location']) }}                    
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
                                {{ Form::label('event', 'Country',['class' => 'form-label']) }}
                                <select class="form-control" name="country_id" id="country_id" data-parsley-required="true">
                                    @foreach ($country_details as $country) 
                                    {
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
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
                                                    <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">    
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
                        {{ Form::textarea('event_description', '', ['size' => '30x5','class' => 'form-control','placeholder' => 'Event Description']) }}
                    </div>
                    
                    <div class="type form-group">
                        <!-- {{ Form::label('event', 'Type',['class' => 'form-label']) }}
                        {{ Form::label('daily', 'Daily') }}
                        {{ Form::radio('event_type', 'daily', false, array('id'=>'daily','class'=>'radio-btn')) }} -->
                       <div style="display:none">{{ Form::label('one_time', 'One Time') }}
                        {{ Form::radio('event_type', 'one time', true, array('id'=>'one_time','class'=>'radio-btn')) }}</div>
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
                                {{ Form::text('one_time_event_start_date', '', ['class' => 'form-control datepicker','id' => 'datepicker3']) }}
                            </div>
                        </div>
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
                        <div class="col-sm-4">                           
                            <div class="form-group">
                                    {{ Form::label('active', 'Active',['class' => 'form-label']) }}
                                    {{ Form::checkbox('is_active', 1, true, ['class' => '','id'=>'active']) }}                      
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('evntprivacy', 'Privacy',['class' => 'form-label']) }}<br>
                                {{ Form::label('public', 'Public') }}
                                {{ Form::radio('event_privacy', 'PUBLIC', true, array('id'=>'public','class'=>'radio-btn')) }}
                                {{ Form::label('private', 'Private') }}
                                {{ Form::radio('event_privacy', 'PRIVATE', false, array('id'=>'private','class'=>'radio-btn')) }}                      
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('event', 'Total Tickets',['class' => 'form-label']) }}
                            {{ Form::number('total_tickets', '', ['class' => 'form-control','placeholder' => 'Total Tickets']) }}
                        </div>
                        </div>
                       
                        <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('event', 'Basic Ticket Price',['class' => 'form-label']) }}
                            {{ Form::text('basic_ticket_price', '', ['class' => 'form-control','placeholder' => 'Basic Ticket Price']) }}
                        </div>
                        </div>
                        <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('event', 'Ticket Service Tax',['class' => 'form-label']) }}
                            {{ Form::text('ticket_service_tax', '', ['class' => 'form-control','placeholder' => 'Ticket Service Tax']) }}
                        </div>
                        </div>
                        <div class="col-sm-3">
                        </div>
                    </div>					<div class="row">						<div class="col-sm-4">							<div class="form-group">							{{ Form::label('event', 'Committee members',['class' => 'form-label']) }}							<select class="form-control" name="committee_members" id="committee_members" data-parsley-required="true" multiple>								<option value="">Select Committee Member</option>								@foreach ($user_details as $user) 								{									<option value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>								}								@endforeach							</select>							</div>						</div>					</div>

                    <div class="row">
                        <div class="col-sm-2">
                            {{ Form::label('event', 'Gallery Images',['class' => 'form-label']) }}       
                        </div>
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
                    <button type="submit" class="btn btn-primary">Submit</button>
                {{ Form::close() }}
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
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
 
    $('.datepicker').each(function(){
        $(this).datepicker({
        changeMonth: true,
        changeYear: true
        });
    } );

    $( "#eventForm" ).validate( {
        rules: {
            event_name: "required",
            event_location: "required",
            country_id: "required",
            carnival_id: "required",
            event_description: "required",
            event_type: "required",
            one_time_event_start_date: "required",
            event_privacy: "required",
            total_tickets: "required",
            basic_ticket_price: {
                            required: true,
                            number: true,
                            },
            ticket_service_tax: {
                            required: true,
                            number: true,
                            },
            event_banner: {
                    required: true,
                    extension: "jpg,jpeg,png",
                    filesize: 5,
                    }	   
        },
        messages: {
            event_name: "Please enter carnival name",
            event_location: "Please enter your location",
             country_id: "Please select country",
            carnival_id: "Please  select carnival",
            event_description: "Please enter event description",
            event_type: "Please select event type",
            one_time_event_start_date: "Please select event date",
            event_privacy: "Please select event privcacy",
            total_tickets: "Please enter total tickets",
            basic_ticket_price:{
                    required:  "Please enter basic ticket price",
                    number:  "Please enter only numeric value",
                    },
            ticket_service_tax:{
                    required:  "Please enter service tax",
                    number:  "Please enter only numeric value",
                    },
            event_banner: {
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
                            var e = $("<input onchange='addmore(this);' class='upload-file-selector' id='upload-file-selector"+i+"' type='file' name='event_gallery[]'>Add More");
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
    $('.type input[type="radio"]').click(function() {
        var id = $(this).attr('id');
        if(id == 'one_time') {
                $('#one_time_show').show();           
        }
        else {
                $('#one_time_show').hide();   
        }
        if(id == 'daily') {
                $('#daily_show').show();           
        }
        else {
                $('#daily_show').hide();   
        }
        if(id == 'fixed_weekdays') {
                $('#weekdays_show').show();           
        }
        else {
                $('#weekdays_show').hide();   
        }
        if(id == 'fixed_dates') {
                $('#fixed_date_show').show();           
        }
        else {
                $('#fixed_date_show').hide();   
        }
    });

    var next = 1;
    $(".add-more").click(function(e){
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
        $("#field" + next).attr('data-source',$(addto).attr('data-source'));
        $("#count").val(next);  
        
            $('.remove-me').click(function(e){
                e.preventDefault();
                var fieldNum = this.id.charAt(this.id.length-1);
                var fieldID = "#field" + fieldNum;
                $(this).remove();
                $(fieldID).remove();
            });

        $('.datepicker').each(function(){
            $(this).datepicker({
            changeMonth: true,
            changeYear: true
            });
        } );
    });        
});$(function() {    $('#committee_members').change(function(e) {        var selected = $(e.target).val();        console.dir(selected);    }); });
</script>       
 @stop