(function($ , $api){
	class colorpicker extends $api.controls.controlItem{
		constructor(el){
			super(el);
			this.$el.find('.color-picker').wpColorPicker();
		}
	}
	$api.import(colorpicker);
})(jQuery, window.IT_Hive);