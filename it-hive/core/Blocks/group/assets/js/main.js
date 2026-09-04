(function($ , $api){
	class group extends $api.controls.controlItem{
		constructor(el){
			super(el);
			this.addEvents();
		}

		addEvents(){
			var _this = this.$el;
			_this.find('.handlediv, .hndle-header').unbind('click');
			_this.find('.handlediv, .hndle-header').click(function(e){
				if($(this).parent().hasClass("closed")){
					var _self = this;
					setTimeout(function () {
						$(_self).parent().find('[data-control-type="WPEditor"]').each(function(index,el){
							var id = $(this).find('textarea').attr('id');
							window.IT_Hive.controls['WPEditor'].editor.remove(id);
							el.controle.init();
						});
					},130);
				}
				$(this).parent().toggleClass("closed");
			});
		}
	}
	$api.import(group);
})(jQuery, window.IT_Hive);