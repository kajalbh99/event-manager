@extends('layouts.admin_master_layout_new')@section('style')@stop @section('content') <div class="row">    <div class="col-md-12">        <!-- BEGIN EXAMPLE TABLE PORTLET-->        <div class="portlet box blue">            <div class="portlet-title">                <div class="caption">                    <i class="fa fa-edit"></i>Users List                </div>            </div>            <div class="portlet-body">                <div class="table-toolbar">                    <div class="btn-group">						<a href="{{ route('user-add') }}">							<button id="sample_editable_1_new" class="btn green">								Add New <i class="fa fa-plus"></i>							</button>						</a> 					</div>					<div class="btn-group">						<select class="form-control" id="filterstatus">								<option value="">All Users</option>							<option value="1">Active Users</option>							<option value="0">Inactive Users</option>													</select>                    </div>                </div>				<div class="table-responsive">					<table class="table table-striped table-hover table-bordered" id="userTable">					<thead>					<tr>						<th>							Sr. no.						</th>						<th>							Name						</th>						<th>							 Email						</th>						<th>							User Name					   </th>					   <th>							 Mobile						</th>						<th>							Date of birth					   </th>						<th>							State					   </th>					   <th>							country						</th>						<th>							Gender						</th>						<th>							Type						</th>						<th class="select-filter">							Active						</th>						<th>							Action					   </th>					</tr>					</thead>					<tbody>					</tbody>					</table>				</div>            </div>        </div>        <!-- END EXAMPLE TABLE PORTLET-->    </div></div> @stop  @section('script')
<script>
$(document).ready(function() {
   var table =  $('#userTable').DataTable({
        initComplete: function() {
            this.api().columns('.select-filter').every(function() {
                var column = this; /* var select = $('<select><option value=""></option></select>')						.appendTo( $(column.header()) )						.on( 'change', function () {							var val = $.fn.dataTable.util.escapeRegex(								$(this).val()							);	 							column								.search( val ? '^'+val+'$' : '', true, false )								.draw();						} );	 					column.data().unique().sort().each( function ( d, j ) {						select.append( '<option value="'+d+'">'+d+'</option>' )					} ); */
                $('#filterstatus').change(function() {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    column.search(val ? val : '', true, false).draw();
                });
            });
        },
        processing: true,
        serverSide: true,
        ajax: '{{ route("ajax_get_users") }}',
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            $("td:first", nRow).html(iDisplayIndex + 1);
            return nRow;
        },
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'email',
            name: 'email'
        }, {
            data: 'user_name',
            name: 'user_name'
        }, {
            data: 'mobile',
            name: 'mobile'
        }, {
            data: 'dob',
            name: 'dob'
        }, {
            data: 'state',
            name: 'state',
			searchable:false
        }, {
            data: 'country',
            name: 'country',
			searchable:false
        }, {
            data: 'gender',
            name: 'gender'
        }, {
            data: 'type',
            name: 'type'
        }, {
            data: 'is_active',
            name: 'is_active'
        }, {
            data: 'id',
            name: 'id'
        }],
        columnDefs: [{
            targets: [8],
            render: function(data, type, row) {
                return data == '1' ? 'Male' : 'Female'
            }
        }, {
            targets: [9],
            render: function(data, type, row) {
                switch (data) {
                    case 'admin':
                        return 'Admin';
                        break;
                    case 'promoter':
                        return 'Promoter';
                        break;
                    default:
                        return 'User';
                }
            }
        }, {
            targets: [10],
            render: function(data, type, row) {
                //return data == 1 ? 'Yes' : 'No';
				return data == '0' ? '<button  class="btn btn-success btn_change_approve" val=1 id="'+row.id+'">Activate</button>' : data == '1' ? '<button  class="btn btn-danger btn_change_approve" val=0 id="'+row.id+'">Deactivate</button>' : '';
            }
        }, {
            targets: [11],
            render: function(data, type, row) {
                return '<a href="' + base_url + '/admin/user-edit/' + data + '" class="btn btn-sm default"><i class="fa fa-edit"></i></a><a href="' + base_url + '/admin/user-delete/' + data + '" class="btn btn-sm default" onclick="return confirm('+"'Are you sure?'"+')"><i class="fa fa-times"></i></a>';
            }
        },
		{ orderable: false, targets: -1 }
		]
    });
	
	$(document).on( 'click', '.btn_change_approve', function () {
		
        var id=$(this).attr('id');
		 var val=$(this).attr('val');
		 if(val==1)
		 {
			 $msg="Are you sure you want to Activate this?";
		 }
		 else{
			  $msg="Are you sure you want to Deactivate this?";
		 }
		 if(confirm($msg)){
			$.ajax({
				type: "POST",
				url: '{{ route("ajax_user_change_status") }}',
				data: { id:id,value:val },
				success:function(result) {
					console.log(result);
					if(result.response=='0'){
						alert(result.data)	
					} else {
						table.draw();
					}
					
				},

			});
		}
		else{
			return false;
		}
    } );
});
</script> @stop