(function($ , $api){
	class datepicker extends $api.controls.controlItem{
		constructor(el){
			super(el);
			this.$el.find('.date-picker').datepicker({
				dateFormat: "dd.mm.yy"
			});
		}
	}
	$api.import(datepicker);
})(jQuery, window.IT_Hive);