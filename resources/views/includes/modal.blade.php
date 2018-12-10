<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Upload tickets</h4>
      </div>
      <div class="modal-body">
		<span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Add pdf tickets</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="fileupload" type="file" name="files[]" multiple>
		</span>
		
		<br>
		<br>
		<!-- The global progress bar -->
		<div id="" class="progress">
			<div class="progress-bar progress-bar-success"></div>
		</div>
		<!-- The container for the uploaded files -->
		<table class="table">
		
		</table>
		<br>
		
		
		
		
		</div>
  <div class="modal-footer">
	<button type="button" class="btn btn-primary fileUploadAll">Upload Tickets</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  </div>
</div>

  </div>
  
</div>
