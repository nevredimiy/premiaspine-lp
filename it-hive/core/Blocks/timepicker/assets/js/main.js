(function($ , $api){
	class timepicker extends $api.controls.controlItem{
		constructor(el){
			super(el);
			this.$el.find('.time-picker').timepicker({
				'timeFormat': 'h:ipm'
			});
		}
	}
	$api.import(timepicker);
})(jQuery, window.IT_Hive);