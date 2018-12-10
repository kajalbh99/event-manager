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
                    <i class="fa fa-edit"></i>Guards List
                </div>
            </div>
            <div class="portlet-body">
			<div class="table-toolbar"> 
				<div class="btn-group">

					<a href="{{ route('guard-create') }}">
						<button id="sample_editable_1_new" class="btn green">
							Add New <i class="fa fa-plus"></i>
						</button>

					</a>                       

				</div>
			    <div class="btn-group">						
					<select class="form-control" id="filterstatus">								
					  <option value="">All Guards
					  </option>							
					  <option value="1">Active Tickets
					  </option>							
					  <option value="0">Inactive Tickets
									  
					</select>                    
			    </div>                
          </div>
                <table class="table table-striped table-hover table-bordered" id="guardTable">
                <thead>
                <tr>
                    <th>
                        Sr. no.
                    </th>
                    
					<th>
                        User Name
                   </th>
                   <th class="select-filter">
                        Status
                   </th>
				   <th>
                        Action
                   </th>
				   
                   
                </tr>
                </thead>
				<tbody>								</tbody>
                </table>
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>
 @stop
 
@section('script')
<script>	
$(document).ready(function() {
    var table = $('#guardTable').DataTable({
initComplete: function () {
        this.api().columns('.select-filter').every( function () {
          var column = this;
          		$('#filterstatus').change(function() {
				var option_value=$(this).val();
				
            var val = $.fn.dataTable.util.escapeRegex(					   $(this).val()				   );
            column.search(val ? val  : '', true, false).draw();
          }
);
        }
                                                  );
      },
        processing: true,
        serverSide: true,
        ajax: '{{ route("ajax_get_guards") }}',
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            $("td:first", nRow).html(iDisplayIndex + 1);
            return nRow;
        },
        columns: [{
            data: 'id',
            name: 'id'
			}, {
				data: 'user_name',
				name: 'user_name'
			}, {
				data: 'is_active',
				name: 'is_active'
			},{
				data: 'id',
				name: 'id'
			}
		],
		 columnDefs: [{
            targets: [2],
				render: function(data, type, row) {
					//return data == '0' ? 'Pending' : 'Approved';
					return data == '0' ? '<button  class="btn btn-success btn_change_approve" val=1 id="'+row.id+'">Activate</button>' : data == '1' ? '<button  class="btn btn-danger btn_change_approve" val=0 id="'+row.id+'">Deactivate</button>' : '';
					
				}
			},{
            targets: [3],
				render: function(data, type, row) {
					return '<a href="'+base_url+'/admin/guard-edit/'+data+'" class="btn btn-sm default"><i class="fa fa-pencil"></i></a><a href="'+base_url+'/admin/guard-delete/'+data+'" class="btn btn-sm default" onclick="return confirm('+"'Are you sure?'"+')"><i class="fa fa-times"></i></a>';
					
				}
			},{
				orderable: false,
				targets: -1
			}
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
				url: '{{ route("ajax_guard_change_status") }}',
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
});	</script>
 @stop