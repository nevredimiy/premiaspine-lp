(function($, $api){
	'use strict'
	class tabs extends $api.controls.controlItem{
		constructor(el){
			super(el);
			this.tabs = jQuery(el).tabs();
		}
	}
	$api.import(tabs);
})(jQuery, window.IT_Hive);