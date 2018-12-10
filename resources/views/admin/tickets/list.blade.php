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
                    <i class="fa fa-edit"></i>Tickets List
                </div>
            </div>
            <div class="portlet-body">
			<div class="table-toolbar">   
			   <div class="btn-group">						
				<select class="form-control" id="filterstatus">								
				  <option value="">All Tickets
				  </option>							
				  <option value="1">Approved Tickets
				  </option>							
				  <option value="0">Pending Tickets
				  </option>	
					<option value="2">Declined Tickets
				  </option>				  
				</select>                    
			  </div>                
          </div>
                <table class="table table-striped table-hover table-bordered" id="ticketTable">
                <thead>
                <tr>
                    <th>
                        Sr. no.
                    </th>
                    <th>
                        Event
                    </th>
                    <th>
                        Committee Member
                    </th>
                    <th>
                       Requested User
                   </th>
                   <th>
                        Tickets
                    </th>
					<th>
                        Ticket Type
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
<script>	$(document).ready(function() {
    var table = $('#ticketTable').DataTable({
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
        ajax: '{{ route("ajax_get_tickets") }}',
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            $("td:first", nRow).html(iDisplayIndex + 1);
            return nRow;
        },
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'event',
            name: 'events.event_name'
        }, {
            data: 'member_name',
            name: 'member.name'
        }, {
            data: 'user',
            name: 'requsted_user.name'
        }, {
            data: 'count',
            name: 'count'
        },{
            data: 'ticket_type',
            name: 'type.ticket_type'
        }, {
            data: 'status',
            name: 'status'
        },{
			data: 'id',
			name: 'id'
			
		}],
        columnDefs: [{
            targets: [6],
            render: function(data, type, row) {
				//var data1 = table.row( $(this).parents('tr') ).data();
				return data == '0' ? 'Pending <button  class="btn btn-primary test_btn" val=1 id="'+row.id+'">Approve</button><button  class="btn btn-danger test_btn" val=2 id="'+row.id+'">Decline</button>' : data == '1' ? 'Approved' : 'Declined';
				data1 += '<a href="'+base_url+'/admin/ticket-detail/'+data+'" class="btn btn-sm default"><i class="fa fa-edit"></i></a>';
				
            }
        },
		{
            targets: [7],
            render: function(data, type, row) {
				return '<a href="'+base_url+'/admin/ticket-detail/'+data+'" class="btn btn-sm default"><i class="fa fa-eye"></i></a>';
				
            }
        }
		,
        {
            orderable: false,
            targets: -1
        }
		]
    });
	
	 //table.column( 7 ).visible( false );
	$(document).on( 'click', '.test_btn', function () {
		
        var id=$(this).attr('id');
		 var val=$(this).attr('val');
		 if(val==1)
		 {
			 $msg="Are you sure you want to Approve this?";
		 }
		 else{
			  $msg="Are you sure you want to Decline this?";
		 }
		 if(confirm($msg)){
			$.ajax({
				type: "POST",
				url: '{{ route("ajax_ticket_update_status") }}',
				data: { ticket_id:id,value:val },
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