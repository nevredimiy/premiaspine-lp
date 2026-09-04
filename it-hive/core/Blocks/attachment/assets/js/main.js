(function($, $api){
	var $document = $(document);
	$document.ready(function(){
		var _custom_media = true;
		$document.on('click', '.upload-video', function(e){
			var parent = $(this).parents('.attachment');
			var _orig_send_attachment = wp.media.editor.send.attachment;
			var button = $(this);
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				if(_custom_media){
					parent.find('>input[type="text"]').val(attachment.url);
				}
				else{
					return _orig_send_attachment.apply(this, [props, attachment]);
				}
			}
			wp.media.editor.open(button);
			return false;
		});
		$document.on('click', '.delete-video', function(e){
			e.preventDefault();
			$(this).parents('.attachment').find('input').attr('value', '');
		});
	});
})(jQuery, window.IT_Hive);