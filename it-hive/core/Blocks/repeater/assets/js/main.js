(function($ , $api){
    'use strict'
    class repeater extends $api.controls.controlItem{
        constructor(el){
            super(el);
            this.addEvents();
            this.container = this.$el.find('.repeater-inner').eq(0);
            this.counter = this.$el.data('counter');
            this.limit = this.$el.data('limit');
            this.counter = this.counter ? this.counter : this.container.find('>*').length;
            this.name = this.$el.data('name');
            if(this.counter == this.limit){
                $(this.el).addClass('disabled-add');
            }
        }

        getTpl(){
            if(!this.tpl){
                this.tpl = $(this.$el.data('tpl')).html();
            }
            return this.tpl
        }

        addItem(){
            if(this.limit && this.limit <= this.container.find('>*').length){
                return false;
            }
            var params = {
                'counter': this.counter,
                'parentName' : this.name,
                'idPrefix' : this.el.id + '-' + this.counter + '-'
            }
            var el = $($api.applyTplParams(this.getTpl(), params))[0];
            this.container.append(el);
            this.counter++;
            console.log(this.counter);
            console.log(this.limit);

            if(this.limit && this.limit <= this.counter){
                console.log(this.el);

                $(this.el).addClass('disabled-add');
            }
            return new $api.controls.repeaterItem(el)
        }

        addEvents(){
            var self = this;
            this.$el.find('.repeater-add-btn[data-control-id="' + this.el.id + '"]').click(function (e) {
                e.preventDefault();
                self.addItem();
            });
        }
    }
    $api.import(repeater);

    /********************tabs*************************/
    class repeaterTabs extends repeater{
        constructor(el){
            super(el);
        }
        beforeConstruct(){
            super.beforeConstruct()
            this.tabset = this.$el.find('>.row>ul.tabset');
            this.$el.find('>.row>.tabs-content').find('>.repeaterItem').each(function (index,el) {
                var $el= $(el);
                this.tabset.append('<li><a href="#'+el.id+'">'+$el.find('>span.repeater-item-heading').text()+'</a></li>');
                $el.find('>span.repeater-item-heading').css({'dispay':'none'});
            }.bind(this));
        }

        addItem(){
            var item = super.addItem();
            this.tabset.append('<li><a href="#'+item.el.id+'">'+item.$el.find('>span.repeater-item-heading').text()+'</a></li>');
            this.$el.find('>[data-control-type="tabs"]' ).tabs( "refresh" );
        }

    }
    $api.import(repeaterTabs);
    /********************tabs end*************************/
})(jQuery, window.IT_Hive);

(function($){
    var $document = $(document);

    $document.ready(function(){
        $('[data-sortable="sortable"]').each(function(){
            $(this).sortable({
                items: '> div,>li',
                start: function(e, ui){},
                stop: function(e, ui){
                    var element = $(e.target);
                    if(ui.item.find('>a.ui-tabs-anchor').length){
                        element = $(ui.item.find('>a.ui-tabs-anchor').attr('href'));
                        var index = ui.item.parent().find('>*').index(ui.item);
                        var siblings = element.parent().find('>*');
                        elementIndex = siblings.index(element);
                        if(index){
                            element.insertAfter(siblings.eq( elementIndex > index ? index-1 : index));
                        }
                        else {
                            element.parent().prepend(element)
                        }
                    }
                    if(tinymce){
                        element.find('[data-control-type="WPEditor"]').each(function(){
                            var id = $(this).find('textarea').attr('id');
                            window.IT_Hive.controls['WPEditor'].editor.remove(id);
                            editor_initialize(id);
                        });
                    }
                }
            });
        });

        $document.on('click', '.repeater-inner button.handlediv', function(e){
            var parent = $(this).parent();
            parent.find('.wp-editor-container').each(function(){
                var id = $(this).find('textarea').attr('id');
                setTimeout(function(){
                    window.IT_Hive.controls['WPEditor'].editor.remove(id);
                    editor_initialize(id);
                }, 130);
            });
        });
    });

    function editor_initialize(id) {
        window.IT_Hive.controls['WPEditor'].editor.initialize(id, window.IT_Hive.controls['WPEditor'].editorSettings);
    }
})(jQuery);