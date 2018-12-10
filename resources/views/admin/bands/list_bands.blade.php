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
                    <i class="fa fa-edit"></i>Bands List
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-toolbar">
                    <div class="btn-group">
                    <a href="{{ route('band-add') }}">
                            <button id="sample_editable_1_new" class="btn green">
                                Add New <i class="fa fa-plus"></i>
                            </button>
                        </a>                       
                    </div>
                </div>
                <table class="table table-striped table-hover table-bordered" id="bandTable">
                <thead>
                <tr>
                    <th>
                        Sr. no.
                    </th>
                    <th>
                        Band Name
                    </th>
                    <th>
                         Slug
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
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>
 @stop
 
 @section('script')
 
 <script>
$(document).ready( function () {
    $('#bandTable').DataTable({
       
		processing: true,
        serverSide: true,
        ajax: '{{ route("ajax_get_bands") }}',
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            $("td:first", nRow).html(iDisplayIndex + 1);
            return nRow;
        },
		columns: [{
            data: 'id',
            name: 'id'
        },
		{
            data: 'band_name',
            name: 'band_name'
        },
		{
            data: 'band_slug',
            name: 'band_slug'
        },
		
		{
            data: 'id',
            name: 'id'
        }
		
		
		],
		 /* aoColumnDefs: [{
            'bSortable': false,
            'aTargets': [-1],
            
        }], */
		columnDefs: [
			{
			targets: [3],
				render: function(data, type, row) {
					 return '<a href="' + base_url + '/admin/band-edit/' + data + '" class="btn btn-sm default"><i class="fa fa-edit"></i></a><a href="' + base_url + '/admin/band-delete/' + data + '" class="btn btn-sm default" onclick="return confirm('+"'Are you sure?'"+')"><i class="fa fa-times"></i></a>';
				}
			},
             {
                 orderable: false,
                 targets: -1
             }
         ]
        
    });
} );
</script>
 @stop