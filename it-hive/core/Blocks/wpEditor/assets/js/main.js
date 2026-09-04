(function ($, $api) {
    console.log('window', window);

    class WPEditor extends $api.controls.controlItem {
        constructor(el) {
            super(el);
            var id = $(el).find('textarea').attr('id');
            setTimeout(function (args) {
                //wp.editor.initialize(id, WPEditor.editorSettings);
                WPEditor.editor.initialize(id, WPEditor.editorSettings);
            }, 20);
        }

        init() {
            this.editor_initialize();
        }

        editor_initialize() {
            WPEditor.editor.initialize(this.$el.find('textarea').attr('id'), WPEditor.editorSettings);
        }
    }

    $api.import(WPEditor);

    $(window).load(function () {
        //objs: tinymce, wp.editor, tinyMCEPreInit, wp.editor.getDefaultSettings(), window.tinyMCEPreInit.mceInit[id], window.tinyMCEPreInit.qtInit[id], window.tinymce.init(init), quicktags(tinyMCEPreInit.qtInit[id])
        WPEditor.editor = (typeof wp.editor.initialize != 'undefined' ? wp.editor : wp.oldEditor);
        WPEditor.editorSettings = {
            tinymce: {
                theme: 'modern',
                skin: 'lightgray',
                language: 'en',
                formats: {
                    alignleft: [{
                        selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
                        styles: {textAlign: "left"}
                    }, {selector: "img,table,dl.wp-caption", classes: "alignleft"}],
                    aligncenter: [{
                        selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
                        styles: {textAlign: "center"}
                    }, {selector: "img,table,dl.wp-caption", classes: "aligncenter"}],
                    alignright: [{
                        selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
                        styles: {textAlign: "right"}
                    }, {selector: "img,table,dl.wp-caption", classes: "alignright"}],
                    strikethrough: {inline: "del"}
                },
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                browser_spellcheck: true,
                fix_list_elements: true,
                entities: '38,amp,60,lt,62,gt',
                entity_encoding: 'raw',
                keep_styles: false,
                cache_suffix: 'wp-mce-4603-20170530',
                resize: false,
                menubar: false,
                branding: false,
                preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform',
                end_container_on_empty_block: true,
                wpeditimage_html5_captions: true,
                wp_lang_attr: 'en-US',
                wp_shortcut_labels: {
                    "Heading 1": "access1",
                    "Heading 2": "access2",
                    "Heading 3": "access3",
                    "Heading 4": "access4",
                    "Heading 5": "access5",
                    "Heading 6": "access6",
                    "Paragraph": "access7",
                    "Blockquote": "accessQ",
                    "Underline": "metaU",
                    "Strikethrough": "accessD",
                    "Bold": "metaB",
                    "Italic": "metaI",
                    "Code": "accessX",
                    "Align center": "accessC",
                    "Align right": "accessR",
                    "Align left": "accessL",
                    "Justify": "accessJ",
                    "Cut": "metaX",
                    "Copy": "metaC",
                    "Paste": "metaV",
                    "Select all": "metaA",
                    "Undo": "metaZ",
                    "Redo": "metaY",
                    "Bullet list": "accessU",
                    "Numbered list": "accessO",
                    "Insert\/edit image": "accessM",
                    "Remove link": "accessS",
                    "Toolbar Toggle": "accessZ",
                    "Insert Read More tag": "accessT",
                    "Insert Page Break tag": "accessP",
                    "Keyboard Shortcuts": "accessH"
                },
                plugins: 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
                wpautop: true,
                indent: false,
                toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_adv',
                toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                toolbar3: false,
                toolbar4: false,
                tabfocus_elements: 'content-html,save-post',
                wp_autoresize_on: true,
                add_unload_trigger: false
            },
            quicktags: {
                buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,add_media'
            },
            mediaButtons: true
        }
    });

})(jQuery, window.IT_Hive);