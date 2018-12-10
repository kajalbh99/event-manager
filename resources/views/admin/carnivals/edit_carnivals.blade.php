@extends('layouts.admin_master_layout_new') @section('style') @stop @section('content') 
<div class="row">    <div class="col-md-12">      <div class="portlet box blue">            <div class="portlet-title">                <div class="caption">                    <i class="fa fa-edit"></i>Edit Carnival                </div>            </div>            <div class="portlet-body">                {{ Form::open(array('route'=>['carnival-edit',$carnival_details->id],'files'=>true,'id' => 'carnivalEditForm','onsubmit'=>'return validate()')) }}                <div class="row">					<div class="col-sm-8">                        <div class="form-group">                            {{ Form::label('carnival', 'Carnival Name') }}                            {{ Form::text('carnival_name', $carnival_details->carnival_name, ['class' => 'form-control','placeholder' => 'Carnival Name']) }}                        </div>                        <div class="form-group">                            {{ Form::label('carnival slug', 'Carnival Slug') }}                            {{ Form::text('carnival_slug', $carnival_details->carnival_slug, ['class' => 'form-control','placeholder' => 'Carnival Slug']) }}                        </div>                                    <div class="form-group" style="display:none">                            {{ Form::label('active', 'Active') }}                            {{ Form::checkbox('is_active', 1, ( $carnival_details->is_active ==  '1'), ['class' => '','id'=>'active']) }}                                        </div>                                                </div>                    <div class="col-sm-4">                        <div class="form-group">                            <div style="" class="form-body">                                <div class="form-group">                                    <table>                                        <tr><td name="image" id="image" align="center"></td>                                            @if(!empty($carnival_details->carnival_banner))                                                <img id="new" class="banner img-responsive" src="{{asset('public/uploads/carnival_banners/'.$carnival_details->id.'/'.$carnival_details->carnival_banner)}}">                                            @else                                                <img id="new" class="banner img-responsive" src="{{asset('public/img/NoImageAvailableLarge.jpg')}}">                                            @endif                                        </tr></td>                                        <tr><td>                                            <span id="fileselector">                                                                                     <label class="btn green" for="upload-file-selector">                                                    <input onchange="readURL(this);" id="upload-file-selector" type="file" name="carnival_banner">                                                    <i class="fa_icon icon-upload-alt margin-correction" ></i>upload New Banner                                                </label>                                            </span>                                                              <tr></td>                                    </table>                                </div>                            </div>                        </div>                    </div>                </div>                                  </div>        </div>		<div class="portlet box blue">            <div class="portlet-title">                <div class="caption">                    <i class="fa fa-edit"></i>Carnival Events                </div>            </div>            <div class="portlet-body">                                <div class="row event">					<div class="col-sm-12">						<div class="form-group">							<div style="" class="form-body">								<div class="form-group">									<table class="table table-striped table-hover table-bordered datatable">										<thead>											<tr>												<th>													Sr.No												</th>												<th>													Event Name												</th>												<th>													 Location												</th>												<th>													Event Date											   </th>											   <th>													Final Price												</th>												<th>													Active											   </th>												<th>													Action											   </th>																							</tr>										</thead>										<tbody>																					</tbody>									</table>								</div>							</div>						</div>					</div>                </div>                                </div>        </div>         

<button type="submit" class="btn btn-primary">Submit</button>                
@if($carnival_details->is_active=='0')
<a href="{{route('carnival_change_approve_status',[$carnival_details->id,1])}}" class="btn btn-success">Activate</a>
@else
<a href="{{route('carnival_change_approve_status',[$carnival_details->id,0])}}" class="btn btn-danger">
Deactivate</a>	
@endif
{{ Form::close() }} 
 </div></div> @stop  @section('script') 
<script>
    jQuery(document).ready(function() {
        App.init();
    });
</script>
<script>
    $("#carnivalEditForm").validate({
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
                extension: "select jpg or jpeg or png images",
                filesize: "maximum 5mb size is allowed",
            }
        },
        errorElement: "em",
        errorPlacement: function(error, element) {
            error.addClass("help-block");
            element.parents("label").addClass("has-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.parent("label"));
            } else {
                error.insertAfter(element);
            }
        },
    });

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#new').attr('src', e.target.result)
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function validate() {
        var submit = new Array();
        var filename = document.getElementById('upload-file-selector').value;
        if (filename != "") {
            var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();
            if (extension == 'jpg' || extension == 'jpeg' || extension == 'png') {
                return true;
            } else {
                var error = $('<p class="help-block">upload jpg image</p>');
                $("#image").append(error);
                $("#new").attr('style', 'border:2px solid red;');
                return false;
            }
        }
    }
</script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("ajax_carnival_events",$carnival_details->id) }}',
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                $("td:first", nRow).html(iDisplayIndex + 1);
                return nRow;
            },
            columns: [{
                data: 'id',
                name: 'id'
            }, {
                data: 'event_name',
                name: 'event_name'
            }, {
                data: 'event_location',
                name: 'event_location'
            }, {
                data: 'event_date',
                name: 'event_date'
            }, {
                data: 'final_ticket_price',
                name: 'final_ticket_price'
            }, {
                data: 'is_active',
                name: 'is_active'
            }, {
                data: 'id',
                name: 'id'
            }],
            columnDefs: [{
                targets: [5],
                render: function(data, type, row) {
                    return data == 1 ? 'Yes' : 'No';
                }
            }, {
                targets: [6],
                render: function(data, type, row) {
                    return '<a href="' + base_url + '/admin/event-edit/' + data + '" class="btn btn-sm default"><i class="fa fa-edit"></i></a><a href="' + base_url + '/admin/event-delete/' + data + '" class="btn btn-sm default" onclick="return confirm('+"'Are you sure?'"+')"><i class="fa fa-times"></i></a>';
                }
            }]
        });
    });
</script>
@stop