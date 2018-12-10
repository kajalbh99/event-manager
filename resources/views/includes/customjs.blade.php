<script>
$(document).ready(function(){
	$('.removegalleryimages').click(function(){
		var con = confirm("Are you sure to remove this!");
		if(con)
		{
			var col = $( this ).closest(".col-sm-2"); 
			var gallery_id = $(this).data('gallery_id');
			var token = '{{ csrf_token() }}';
			$.ajax({
				url: '{{route("ajax_delete_user_gallery_image")}}',
				type: 'POST',
				data:{gallery_id:gallery_id,_method: 'delete', _token :token},
				success: function(result) {
					col.remove();  
				}
			});
		}
	});
});
</script>