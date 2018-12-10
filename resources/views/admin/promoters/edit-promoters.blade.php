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

                    <i class="fa fa-edit"></i>Edit Promoter

                </div>

            </div>

            <div class="portlet-body">

                {{ Form::open(array('route'=>['promoter-edit',$user_details->id],'files'=>true,'id' => 'userEditForm','method'=>'post','onsubmit'=>'return validate()')) }}

                <div class="row event">



                        <div class='col-sm-4'>                       

                            <div class="form-group">

                                {{ Form::label('user', 'Name',['class' => 'form-label']) }}

                                {{ Form::text('name',$user_details->name, ['class' => 'form-control','placeholder' => 'Name']) }}

                            </div>

                            <div class="form-group">

                                {{ Form::label('user email', 'Email',['class' => 'form-label']) }}

                                {{ Form::email('email',$user_details->email, ['class' => 'form-control','placeholder' => 'User Email']) }}

                            </div>

                            <div class="form-group">

                                {{ Form::label('event', 'Country',['class' => 'form-label']) }}

                                <select class="form-control" name="country_id" id="country" data-parsley-required="true">

                                    @foreach ($country_details as $country) 

                                    {

                                        <option  {{ $country->id == $user_details->country_id ? 'selected="selected"' : '' }} value="{{ $country->id }}">{{ $country->country_name }}</option>

                                    }

                                    @endforeach

                                </select>                 

                            </div>

                            <div class="form-group">

                                {{ Form::label('mobile', 'Mobile',['class' => 'form-label']) }}

                                {{ Form::text('mobile',$user_details->mobile, ['class' => 'form-control','placeholder' => 'mobile']) }}

                            </div>                                            
							<div class="form-group">
                                {{ Form::label('routing_number', 'Routing Number',['class' => 'form-label']) }}
                                {{ Form::text('routing_number', $user_details->account ? $user_details->account->routing_number:'', ['class' => 'form-control','placeholder' => 'Routing number','id'=>'routing_number']) }}
                            </div>
                            <div class="form-group">
								{{ Form::label('account_number', 'Account 	Number',['class' => 'form-label']) }}
								{{ Form::text('account_number', $user_details->account ? $user_details->account->account_number:'', ['class' => 'form-control','placeholder' => 'Account number','id'=>'account_number']) }}
							</div>
                            

                        </div>



                        <div class="col-sm-4">

                            <div class="form-group">

                                {{ Form::label('user', 'User Name',['class' => 'form-label']) }}

                                {{ Form::text('user_name',$user_details->user_name, ['class' => 'form-control','placeholder' => 'User Name']) }}

                            </div>

                            <div class="form-group">

                                {{ Form::label('password', 'Password',['class' => 'form-label']) }}

                                {{ Form::password('password', ['class' => 'form-control','placeholder' => '********']) }}

                            </div>

                            <div class="form-group state">

                                {{ Form::label('user state', 'State',['class' => 'form-label']) }}

                                <select class="form-control" name="state_id" id="state" data-parsley-required="true">

                                    @foreach ($state_details as $state) 

                                    {

                                        <option  value="{{ $state->id }}">{{ $state->state_name }}</option>

                                    }

                                    @endforeach

                                </select>   

                            </div>

                            <div class="row">

                                <div class="col-sm-6">

                                    <div class="dob">

                                        {{ Form::label('dob', 'Date Of birth',['class' => 'form-label']) }}

                                        {{ Form::text('dob',$user_details->dob, ['class' => 'form-control datepicker','id' => 'datepicker']) }}   

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

                                                @if($user_details->profile_photo)

                                                <img id="new" class="banner img-responsive" src="{{asset('public/uploads/user_profile/'.$user_details->id.'/'.$user_details->profile_photo)}}">

                                                @else       

                                                    <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">

                                                @endif    

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

                                {{ Form::radio('gender', '1', ($user_details->gender == "1"), array('id'=>'male','class'=>'radio-btn')) }}

                                {{ Form::label('female', 'Female') }}

                                {{ Form::radio('gender', '2', ($user_details->gender == "2"), array('id'=>'female','class'=>'radio-btn')) }}                     

                            </div>

                        </div>

                        <div class="col-sm-4">

                            <div class="form-group">

                                {{ Form::label('Type', 'Type',['class' => 'form-label']) }}

                                {{ Form::label('promoter', 'Promoter') }}

                                {{ Form::radio('type', 'promoter', true, array('id'=>'promoter','class'=>'radio-btn')) }}

                            </div>

                        </div>

                        <div class="col-sm-4"></div>

                        <div class="col-sm-4" style="display:none">

                            <div class="form-group">

                                    {{ Form::label('active', 'Active',['class' => 'form-label']) }}

                                    {{ Form::checkbox('is_active', 1, ($user_details->is_active == "1"), ['class' => '','id'=>'active']) }}                      

                            </div>    

                        </div> 

                    </div>

                   

            </div>

        </div>
		
		<div class="portlet box blue">

            <div class="portlet-title">

                <div class="caption">

                    <i class="fa fa-edit"></i>Promoter Events

                </div>

            </div>

            <div class="portlet-body">

                

                <div class="row event">

					<div class="col-sm-12">

						<div class="form-group">
							<div class="table-toolbar">

							<div class="btn-group">

								<div class="btn-group">
									<select class="form-control" id="filterstatus">	
										<option value="">All Events</option>
										<option value="1">Active Events</option>
										<option value="0">Inactive Events</option>
										
									</select>

								</div>                      

							</div> 

							</div>
							<div class="form-body">

								<div class="form-group">

									<table class="table table-striped table-hover table-bordered datatable">
										<thead>
											<tr>

												<th>

													Sr.No

												</th>

												<th>

													Event Name

												</th>

												<th>

													 Location

												</th>

												<th>

													Event Date

											   </th>

											   <th>

													Event End Date

												</th>

												<th class="select-filter">

													Active

											   </th>
												<th>

													Action

											   </th>
												
											</tr>
										</thead>
										<tbody>
											
										</tbody>
									</table>

								</div>

							</div>

						</div>

					</div>

                </div>

                    

            </div>

        </div>
		<div class="portlet box blue">

            <div class="portlet-title">

                <div class="caption">

                    <i class="fa fa-edit"></i>Promoter Gallery

                </div>

            </div>

            <div class="portlet-body">

                

                <div class="row event">

					<div class="col-sm-12">

						<div class="form-group">

						@forelse($user_details->gallery as $gid=>$gimage )
							<div class="col-sm-2">
								<div class="form-group">
									<div style="" class="form-body">
										<div class="form-group">
											<a id="" class="removegalleryimages"  style="position: absolute; left: 0; top: 0;" data-user_id = "{{$user_details->id}}" data-gallery_id = "{{$gimage->id}}">
												<img src="{{asset('public/img/cross.png')}}" class="removeImage" alt="Remove this image" align="top"> 
											</a>
											<img class="banner img-responsive" src="{{asset('public/uploads/user_photo_gallery/'.$user_details->id.'/'.$gimage->user_gallery_image)}}">                                                                                                                         
										 </div>
									</div>
								</div>
							</div>
						@empty
							<p>No image found</p>
                        @endforelse

						</div>

					</div>

                </div>

                    

            </div>

        </div>

        <!-- END EXAMPLE TABLE PORTLET-->
		 <button type="submit" class="btn btn-primary">Submit</button>
		@if($user_details->is_active=='0')
		<a href="{{route('user_change_approve_status',[$user_details->id,1])}}" class="btn btn-success">Activate</a>
		@else
		<a href="{{route('user_change_approve_status',[$user_details->id,0])}}" class="btn btn-danger">Deactivate</a>	
		@endif
		
        {{ Form::close() }}

    </div>

</div>

 @stop

 

 @section('script')


 <script>

    jQuery(document).ready(function() {    

        App.init(); // initlayout and core plugins

        

    });

    </script>

    

    <script>

        $( "#userEditForm" ).validate( {

            rules: {

                name: "required",

                user_name: "required",

                email: "required",

                mobile: "required",

                dob: "required",

                country_id: "required",

                state_id: "required",
				routing_number:{required:function(){
					if($('#account_number').val()!="" || $('#routing_number').val()!=""){
						return true;
					} else{
						return false;
					}
				},minlength:9},
				account_number:{required:function(){
					
					if($('#account_number').val()!="" || $('#routing_number').val()!=""){
						return true;
					} else{
						return false;
					}
				}}

            },

            messages: {

                name: "Please enter name",

                user_name: "Please enter user name",

                email: "requirPlease enter name email",

                mobile: "Please enter name mobile number",

                dob: "Please select date of birth",

                country_id: "Please select any country",

                state_id: "Please select any country",

                	   				

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

                error = $('<p class="help-block">upload jpg,png image</p>');

                $("#image").append(error);

                $("#new").attr('style',  'border:2px solid red;');

                return false;

            }

        }

        }



        </script>
		<script>
			$(document).ready(function() {
				$('.datatable').DataTable({
					initComplete: function () {
						this.api().columns('.select-filter').every( function () {
							var column = this;
							/* var select = $('<select><option value=""></option></select>')
								.appendTo( $(column.header()) )
								.on( 'change', function () {
									var val = $.fn.dataTable.util.escapeRegex(
										$(this).val()
									);
			 
									column
										.search( val ? '^'+val+'$' : '', true, false )
										.draw();
								} );
			 
							column.data().unique().sort().each( function ( d, j ) {
								select.append( '<option value="'+d+'">'+d+'</option>' )
							} ); */
							$('#filterstatus').change(function() {
								
							   var val = $.fn.dataTable.util.escapeRegex(
								   $(this).val()
							   );
							   column.search(val ? val  : '', true, false).draw();
						   });
						} );
					},
					processing: true,
					serverSide: true,
					ajax: '{{ route("ajax_promoter_events",$user_details->id) }}' ,
					"fnRowCallback" : function(nRow, aData, iDisplayIndex){
						$("td:first", nRow).html(iDisplayIndex +1);
					   return nRow;
					} , 
					 columns: [
						{ data: 'id', name: 'id' },
						{ data: 'event_name', name: 'event_name' },
						{ data: 'event_location', name: 'event_location' },
						{ data: 'event_date', name: 'event_date' },
						{ data: 'event_end_date', name: 'event_end_date' },
						{ data: 'is_active', name: 'is_active' },
						{ data: 'id', name: 'id' }
						
					],
					 columnDefs : [
						{ targets : [5],
						  render : function (data, type, row) {
							return data==1 ? 'Yes' : 'No';
						  }
						},
						{ targets : [6],
						  render : function (data, type, row) {
							 return '<a href="'+base_url+'/admin/event-edit/'+data+'" class="btn btn-sm default"><i class="fa fa-edit"></i></a><a href="'+base_url+'/admin/event-delete/'+data+'" class="btn btn-sm default" onclick="return confirm('+"'Are you sure?'"+')"><i class="fa fa-times"></i></a>';
						  }
						} 
				   ]
				});
			});
			
		</script>

 @stop