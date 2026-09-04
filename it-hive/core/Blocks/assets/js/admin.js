window.IT_Hive = {
	controls : {},
	import : function(item){
		this.controls[item.prototype.constructor.name] = item;
	}
};

(function($, $api){
	var $window = $(window);
	var $document = $(document);

	class controlItem{
		constructor(el){
			this.el = el;
			this.el.controle = this;
			this.$el = $(this.el);
			controlItem.elements.push(this);
			//console.log(controlItem.elements);
			this.construct();
		}

		construct(){
			this.beforeConstruct();
			var children = this.getChildren();
			children.each(function(){
				try{
					new $api.controls[$(this).attr('data-control-type')](this);
				}
				catch(e){
					console.error('controle "' + $(this).attr('data-control-type') + '" is undefined ');
				}
			});
		}

		delete(){
			this.el.parentElement.removeChild(this.el);
		}

		destruct(){

		}

		getChildren(){
			var _self=this;
			return $(this.el).find('[data-control-type]').filter(function(){
				return $(this).parents('[data-control-type]')[0] == _self.el;
			});
		}

		beforeConstruct(){

		}
	}
	controlItem.elements=[];

	$api.import(controlItem);


	class box extends window.IT_Hive.controls.controlItem{
		constructor(el){
			super(el);
		}
	}
	$api.import(box);

	$api.applyTplParams = function(tpl, params){
		params = params ? params : {};
		var reg = '';
		for(var param in params){
			reg = new RegExp('{{' + param + '}}','g');
			tpl = tpl.replace(reg, params[param])
		}
		return tpl;
	}
})(jQuery, window.IT_Hive);

(function($){
	var $document = $(document);
	var $window = $(window);

	var Metaboxes = [];


	//$document.ready(function(){
	$window.load(function(){
		$('[data-control-type="box"]').each(function () {
			new window.IT_Hive.controls.box(this);
		});

		var page_template = $('#page_template').length ? $('#page_template') : $('.editor-page-attributes__template select');
		$('.postbox .template-restriction').each(function (index,el) {
			$el = $(el);
			Metaboxes.push({
				self : $el.parents('.postbox').eq(0),
				container : $el.parents('.postbox').eq(0).parents().eq(0),
				restriction : $el.html() ? $el.html().split(',') : []
			});
		});
		$(document).on('change','#page_template, .editor-page-attributes__template select',function(){
			action_postboxes($(this).val());
		});
		if(typeof(current_screen) === "undefined"){
			current_screen=0;
		}
		console.log(current_screen);
		var template = page_template.val()? page_template.val() : current_screen ;
		//if(template){
			action_postboxes(template);
		//}

		if($('body').hasClass('post-type-event')){
			$('input[name="projects_title"]').parent().remove();
			$('input[name="show_projects"]').parent().remove();
			$('input[name="slider_items_title"]').parent().remove();
			$('input[name="show_works"]').parent().remove();
			$('div[data-name="projects"]').remove();
			$('div[data-name="slider_items"]').remove();
			$('input[name="exhibition_info[title]"]').closest('.row.repeaterItem.postbox').removeClass('closed');
		}
	});

	function action_postboxes(template){
		console.log(Metaboxes);
		for(var i=0; i < Metaboxes.length ; i++){
			if(!Metaboxes[i].restriction.length || Metaboxes[i].restriction.indexOf(template) != -1){
				if(!Metaboxes[i].self[0].parentNode){
					Metaboxes[i].container.append(Metaboxes[i].self);
				}
			}else {
				Metaboxes[i].self.detach();
			}
		}
	}

})(jQuery);