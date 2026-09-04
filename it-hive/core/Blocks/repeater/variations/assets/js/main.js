(function($, $api){
	'use strict'
	class repeaterVariations extends $api.controls.repeater{
		constructor(el){
			super(el);
			this.variationSelect = this.$el.find('.variations[data-control-id="' + this.el.id + '"]');
			var data = this.$el.data('tpl');
			for(var i = 0; i < data.length; i++){
				this.variationSelect[0].options[i]=new Option(data[i].title,data[i].id);
			}
		}

		getTpl(){
			return $(this.variationSelect.val()).html();
		}
	}
	$api.import(repeaterVariations);

	class repeaterVariationsTabs extends $api.controls.repeaterTabs{
		constructor(el){
			super(el);
			this.variationSelect = this.$el.find('.variations[data-control-id="' + this.el.id + '"]');
			var data = this.$el.data('tpl');
			for(var i = 0; i < data.length; i++){
				this.variationSelect[0].options[i]=new Option(data[i].title,data[i].id);
			}
		}

		getTpl(){
			return $(this.variationSelect.val()).html();
		}
	}
	$api.import(repeaterVariationsTabs);
})(jQuery, window.IT_Hive);