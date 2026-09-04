(function($, $api){

	$(document).on('keyup','input.select-post-ajax',function () {
		var $this = $(this);
		var data = {
			'action' : 'it-hive_query',
			'query' : $this.data('query')
		}
		data.query.title_like = this.value;
		console.log(data);
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function (data) {
				var $container = $this.siblings('.autocomplete-items');
				$container.html('');
				console.log(data);

				for(var i = 0; i < data.length; i++){
					$container.append('<a href="#" data-id="'+data[i].ID+'">'+data[i].post_title+'</a>');
				}
				$container.addClass('show');
			},
			dataType: 'json'
		});
	});
	$(document).on('focusout','input.select-post-ajax',function () {
		var $this = $(this);
		setTimeout(function () {
			$this.siblings('.autocomplete-items').removeClass('show');
		}, 200);
	});

	function selectAutocompletePost( $link ) {
		var $container = $link.parents('.it-hive-autocomplete');
		$container.find('.select-post-ajax').val($link.text());
		$container.find('.data-input').val($link.attr('data-id')).trigger('change');
		$link.parent('.autocomplete-items').removeClass('show');
	}

	// mousedown fires before input blur; click often misses the item
	$(document).on('mousedown', '.it-hive-autocomplete .autocomplete-items a', function (e) {
		e.preventDefault();
		selectAutocompletePost($(this));
	});

})(jQuery, window.IT_Hive);