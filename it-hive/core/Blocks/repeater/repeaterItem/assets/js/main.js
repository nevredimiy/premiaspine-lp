(function($ , $api){
	class repeaterItem extends $api.controls.controlItem{
		constructor(el){
			super(el);
			this.addEvents();
		}

		addEvents(){
			var _this = this.$el;
			var _self = this;
			_this.find('.delete-repeaterItem[data-parent-control="' + this.el.id + '"]').click(function(e){
				$(_this).closest('.disabled-add').removeClass('disabled-add');
				e.preventDefault();
				_self.delete();
			});
// 			_this.find('.handlediv').unbind('click');
			_this.find('.handlediv').click(function(e){
				if($(this).parent().hasClass("closed")){
					var _self = this;
					setTimeout(function () {
						$(_self).parent().find('[data-control-type="WPEditor"]').each(function(index,el){
							var id = $(this).find('textarea').attr('id');
							window.IT_Hive.controls['WPEditor'].editor.remove(id);
							el.controle.init();
						});
					},100);
				}
				// $(this).parent().toggleClass("closed");
			});
			var self = this;
		}
	}
	$api.import(repeaterItem);
})(jQuery, window.IT_Hive);