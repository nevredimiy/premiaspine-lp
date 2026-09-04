(function($ , $api){
	var url;
	var $document = $(document);
	$document.ready(function(){
		var _custom_media = true;
		$document.on('click', '.upload-img', function(e){
			var size_svg = '';
			var _orig_send_attachment = wp.media.editor.send.attachment;
			e.preventDefault();
			var button = $(this);
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				var parent_image = button.parents('.row').eq(0);
				if(attachment.mime == 'image/svg+xml'){
					url = attachment.url;
					size_svg = 'width="150" height="100"';
				}
				else{
					var size = attachment.sizes.medium;
					if(!size){
						size = attachment.sizes.full;
					}
					url = attachment.sizes.widget ? attachment.sizes.widget.url : size.url;
				}
				if(_custom_media){
					parent_image.find('>input[type="hidden"]').val(attachment.id);
					parent_image.find('>.image').html('<img src="' + url + '" ' + size_svg + ' />');
					if(!parent_image.find('.delete-img').length){
						parent_image.find('.btn-controls').append('<a class="delete-img" href="#">Remove</a>');
					}
				}
				else{
					return _orig_send_attachment.apply(this, [props, attachment]);
				}
			};
			wp.media.editor.open(button);
		});
		$document.on('click', '.delete-img', function(e){
			e.preventDefault();
			var parent = $(this).parents('.row').eq(0);
			parent.find('.image').html('');
			parent.find('>input[type="hidden"]').attr('value', 0);
		});
	});
})(jQuery, window.IT_Hive);