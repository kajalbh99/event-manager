@extends('layouts.admin_master_layout_new') @section('style') @stop @section('content') 
<div class="row">    
  <div class="col-md-12">        
    <!-- BEGIN EXAMPLE TABLE PORTLET-->        
    <div class="portlet box blue">            
      <div class="portlet-title">                
        <div class="caption">                    
          <i class="fa fa-edit">
          </i>Events List                
        </div>            
      </div>            
      <div class="portlet-body">                
        <div class="table-toolbar">                    
          <div class="btn-group">                    
            <a href="{{ route('event-add') }}">                            
              <button id="sample_editable_1_new" class="btn green">                                Add New 
                <i class="fa fa-plus">
                </i>                            
              </button>                        
            </a>                                           
          </div>					
          <div class="btn-group">						
            <select class="form-control" id="filterstatus">								
              <option value="">Select Status
              </option>							
              <option value="1">Active Events
              </option>							
              <option value="0">Inactive Events
              </option>													
            </select>                    
          </div> 
		  <div class="btn-group">						
            <select class="form-control" id="filterapprove">								
              <option value="">Select Approval
              </option>							
              <option value="1">Approved Events
              </option>							
              <option value="0">Disapproved Events
              </option>													
            </select>                    
          </div> 		  
        </div>                
        <table class="table table-striped table-hover table-bordered" id="eventTable">                
          <thead>                    
            <tr>                        
              <th>                            Sr. no.                        
              </th>                        
              <th>                             Event Name                        
              </th>                        
              <th>                            Location                        
              </th>                        
              <th>                            Event Date                        
              </th> 
				<th class="select-filter-approval">  Approved  </th> 			  
              <!--th>                            Final Price                        
              </th-->                        
              <th class="select-filter">                            Active                        
              </th>                        
              <th>                            Action                        
              </th>                    
            </tr>                
          </thead>                
          <tbody>                                               
          </tbody>            
        </table>            
      </div>        
    </div>        
    <!-- END EXAMPLE TABLE PORTLET-->    
  </div>
</div> @stop  @section('script') 
<script>
$(document).ready(function(){
	var table = $('#eventTable').DataTable({
    initComplete: function() {
        this.api().columns('.select-filter').every(function() {
            var column = this;
            $('#filterstatus').change(function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? val : '', true, false).draw();
            });
			
			
        });
		this.api().columns('.select-filter-approval').every(function() {
            var column = this;
            $('#filterapprove').change(function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? val : '', true, false).draw();
            });
			
			
        });
    },
    processing: true,
    serverSide: true,
    ajax: '{{ route("ajax_get_events") }}',
    "fnRowCallback": function(nRow, aData, iDisplayIndex) {
        $("td:first", nRow).html(iDisplayIndex + 1);
        return nRow;
    },
    columns: [{
            data: 'id',
            name: 'id'
        },
        {
            data: 'event_name',
            name: 'event_name'
        },
        {
            data: 'event_location',
            name: 'event_location'
        },
        {
            data: 'event_date',
            name: 'event_date'
        },
        {
            data: 'is_approved',
            name: 'is_approved'
        }/* ,
        {
            data: 'final_ticket_price',
            name: 'final_ticket_price'
        } */,
        {
            data: 'is_active',
            name: 'is_active'
        },
        {
            data: 'id',
            name: 'id'
        }

    ],
    columnDefs: [{
            targets: [4],
            render: function(data, type, row) {
                //return data == 1 ? 'Yes' : 'No';
				return data == '0' ? '<button  class="btn btn-success btn_change_approve" val=1 id="'+row.id+'">Approve</button>' : data == '1' ? '<button  class="btn btn-danger btn_change_approve" val=0 id="'+row.id+'">Disapprove</button>' : '';
				
				
            }
        },{
            targets: [5],
            render: function(data, type, row) {
                return data == 1 ? 'Yes' : 'No';
				//return data == '0' ? 'No&nbsp;&nbsp;&nbsp;&nbsp;<button  class="btn btn-success btn_change_status" val=1 id="'+row.id+'">Activate</button>' : data == '1' ? 'Yes&nbsp;&nbsp;&nbsp;<button  class="btn btn-danger btn_change_approve" val=0 id="'+row.id+'">Deactivate</button>' : '';
            }
        },
		{
            targets: [6],
            render: function(data, type, row) {
                 return '<a href="' + base_url + '/admin/event-edit/' + data + '" class="btn btn-sm default"><i class="fa fa-edit"></i></a><a href="' + base_url + '/admin/event-delete/' + data + '" class="btn btn-sm default" onclick="return confirm('+"'Are you sure?'"+')"><i class="fa fa-times"></i></a>';
            }
        },
        {
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
			 $msg="Are you sure you want to Approve this?";
		 }
		 else{
			  $msg="Are you sure you want to Decline this?";
		 }
		 if(confirm($msg)){
			$.ajax({
				type: "POST",
				url: '{{ route("ajax_event_update_approval") }}',
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
	
	$(document).on( 'click', '.btn_change_status', function () {
		
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
				url: '{{ route("ajax_event_update_status") }}',
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
    });
});
function confirmdelete(){
	var c = conform("Are you sure to delete this event ? ");
	if(!c){
		return false;
	}
}
</script> @stop
