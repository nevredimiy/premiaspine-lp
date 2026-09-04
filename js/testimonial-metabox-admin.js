(function ($) {
    'use strict';

    var config = window.premiaspineTestimonialMetabox || {};
    var SELECTOR_BOX = '#story_data';

    function getStoryTypeFromTaxonomy() {
        var patientsId = String(config.patientsTermId || '');
        var surgeonsId = String(config.surgeonsTermId || '');
        var $checked = $('#story_categorydiv input[type="checkbox"]:checked');

        if (!$checked.length) {
            return '';
        }

        var selectedIds = $checked.map(function () {
            return String(this.value);
        }).get();

        if (selectedIds.indexOf(surgeonsId) !== -1) {
            return 'surgeon';
        }
        if (selectedIds.indexOf(patientsId) !== -1) {
            return 'patient';
        }

        return '';
    }

    function getFieldGroup($root, key) {
        var namePattern = new RegExp('\\[' + key + '\\](\\[|$)');
    
        return $root.find('.row.group').filter(function () {
            return $(this).find('[name]').filter(function () {
                var name = this.getAttribute('name') || '';
                return namePattern.test(name);
            }).length > 0;
        }).first();
    }

    function syncTestimonialFieldsVisibility() {
        var $box = $(SELECTOR_BOX);
        if (!$box.length) {
            return;
        }

        var type = getStoryTypeFromTaxonomy();
        var $patient = getFieldGroup($box, 'patient');
        var $surgeon = getFieldGroup($box, 'surgeon');

        $box.attr('data-story-type', type);

        if (type === 'patient') {
            $patient.show();
            $surgeon.hide();
        } else if (type === 'surgeon') {
            $patient.hide();
            $surgeon.show();
        } else {
            $patient.hide();
            $surgeon.hide();
        }
    }

    function bindTestimonialMetaboxUi() {
        var $taxonomy = $('#story_categorydiv');
        if (!$taxonomy.length) {
            return;
        }

        $taxonomy.off('change.testimonialStoryType click.testimonialStoryType', 'input[type="checkbox"]');
        $taxonomy.on('change.testimonialStoryType click.testimonialStoryType', 'input[type="checkbox"]', function () {
            setTimeout(syncTestimonialFieldsVisibility, 0);
        });

        syncTestimonialFieldsVisibility();
    }

    $(function () {
        bindTestimonialMetaboxUi();
        normalizePopupGalleryDeleteLabels();
        setTimeout(syncTestimonialFieldsVisibility, 100);
        setTimeout(syncTestimonialFieldsVisibility, 500);
        setTimeout(normalizePopupGalleryDeleteLabels, 100);
        setTimeout(normalizePopupGalleryDeleteLabels, 500);
    });

    function normalizePopupGalleryDeleteLabels() {
        var $gallery = $(SELECTOR_BOX).find('[data-name*="[popup_gallery]"]');

        $gallery.find('.delete-repeaterItem').each(function () {
            var $link = $(this);

            if ($link.text().trim() !== 'Delete') {
                $link.text('Delete');
            }
        });
    }

    function patchTestimonialPopupGalleryRepeater() {
        if (!window.IT_Hive || !window.IT_Hive.controls || !window.IT_Hive.controls.repeater) {
            return;
        }

        var Repeater = window.IT_Hive.controls.repeater;
        if (Repeater.prototype.__testimonialPopupGalleryPatched) {
            return;
        }

        Repeater.prototype.__testimonialPopupGalleryPatched = true;

        var originalAddItem = Repeater.prototype.addItem;
        Repeater.prototype.addItem = function () {
            var item = originalAddItem.apply(this, arguments);

            if (this.$el.is('[data-name*="[popup_gallery]"]')) {
                setTimeout(normalizePopupGalleryDeleteLabels, 50);
            }

            return item;
        };
    }

    patchTestimonialPopupGalleryRepeater();

    $(window).on('load', function () {
        patchTestimonialPopupGalleryRepeater();
        normalizePopupGalleryDeleteLabels();
    });
})(jQuery);