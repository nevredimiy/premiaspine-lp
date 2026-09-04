(function($ , $api){
	var $document = $(document);
	$document.ready(function(){
		var _custom_media = true;
		$document.on('click', '.upload-video', function(e){
			var parent = $(this).parents('.row').eq(0);
			var _orig_send_attachment = wp.media.editor.send.attachment;
			var button = $(this);
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				if ( _custom_media ) {
					//parent.find('>input[type="hidden"]').val(attachment.id);
					parent.find('>input[type="text"').val(attachment.url);
					parent.find('.delete-video').css({'display':'block'});
				} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
				}
			}
			wp.media.editor.open(button);
			return false;
		});
		/*$document.on('keyup', '.url-file', function(){
			var parent = $(this).parent();
			if(!$(this).val()){
				parent.find('.btn-file-delete').css({'display':'none'});
				parent.find('.video-id').attr('value',0);
			}
			else{
				parent.find('.btn-file-delete').css({'display':'block'});
				parent.find('.video-id').attr('value',0);
			}
		});
		$document.on('click','.btn-file-delete',function(e){
			e.preventDefault();
			var parent = $(this).parent('.file');
			parent.find('.video-id').attr('value',0);
			parent.find('.url-file').attr('value','');
			$(this).css({'display':'none'});
		});*/
	});
})(jQuery,window.IT_Hive);