(function($, $api){
	var $document = $(document);
	$document.ready(function(){
		$('.theme-options #it-hive-8 .repeaterItem').each(function(){
			var heading = $(this).find('input[type="text"]').eq(0).val();
			if(heading){
				$(this).find('.hndle-header').text(heading);
			}
		});
		$document.on('change', '.theme-options #it-hive-8 .repeaterItem input[type="text"]', function(){
			var heading = $(this).val() ? $(this).val() : 'Default Item';
			$(this).parents('.repeaterItem').find('.hndle-header').text(heading);			
		});
	});
})(jQuery, window.IT_Hive);