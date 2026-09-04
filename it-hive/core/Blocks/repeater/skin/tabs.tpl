<div class="row repeater-container" data-control-type="repeaterTabs" id="{{id}}" data-limit="{{limit}}" data-tpl="#{{templateID}}" data-name="{{name}}">
	{{label}}
	<a class="button repeater-add-btn" data-control-id="{{id}}" href="#">{{addText}}</a>
	<div class="row item" data-control-type="tabs">
		<ul class="tabset" {{sortable}}></ul>
		<div class="tabs-content repeater-inner">
			{{children}}
		</div>
	</div>
</div>