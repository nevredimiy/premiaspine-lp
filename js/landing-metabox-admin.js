(function ($) {
    'use strict';

    var SELECTOR_BOX = '#landing_page_options';
    var SELECTOR_REPEATERS = '[data-name^="landing_page_options"] .repeater-inner';
    var SELECTOR_ITEM = '.repeater-inner > .repeaterItem.postbox';
    var SELECTOR_TOGGLE = SELECTOR_ITEM + ' > .hndle-header, ' + SELECTOR_ITEM + ' > .handlediv';
    var SELECTOR_HERO_SLIDE = '[data-name*="[hero_slides][slides]"] .repeater-inner > .repeaterItem.postbox';

    function getHeroSlideFieldGroup($slideItem, key) {
        var namePattern = new RegExp('\\[' + key + '\\](\\[|$)');

        return $slideItem.children('.inside').children('.row.group').filter(function () {
            return $(this).find('[name]').filter(function () {
                var name = this.getAttribute('name') || '';
                return namePattern.test(name);
            }).length > 0;
        }).first();
    }

    function syncHeroSlideTypeVisibility($slideItem) {
        if (!isHeroSlideItem($slideItem)) {
            return;
        }

        var type = $slideItem.find('select[name*="[slide_type]"]').first().val();
        var $doctor = getHeroSlideFieldGroup($slideItem, 'doctor');
        var $patient = getHeroSlideFieldGroup($slideItem, 'patient');

        $slideItem.attr('data-hero-slide-type', type || '');

        if (type === 'doctor') {
            $doctor.show();
            $patient.hide();
        } else if (type === 'patient') {
            $doctor.hide();
            $patient.show();
        } else {
            $doctor.show();
            $patient.show();
        }
    }

    function getLandingMetaboxRoot() {
        var $box = $(SELECTOR_BOX);
        if ($box.length) {
            return $box;
        }

        return $(SELECTOR_REPEATERS).first().closest('#post, .wrap, body');
    }

    function getLandingRepeaterInners($scope) {
        var $roots = $scope && $scope.length ? $scope : getLandingMetaboxRoot();

        if ($roots.is('.repeater-inner')) {
            return $roots;
        }

        return $roots.find(SELECTOR_REPEATERS);
    }

    function disableItHiveSortableOnLandingRepeaters($scope) {
        getLandingRepeaterInners($scope).each(function () {
            var $inner = $(this);

            $inner.removeAttr('data-sortable');

            if ($inner.hasClass('ui-sortable')) {
                try {
                    $inner.sortable('destroy');
                } catch (error) {
                    $inner.removeClass('ui-sortable');
                }
            }
        });
    }

    function openRepeaterPanel($item) {
        if (!$item || !$item.length) {
            return;
        }

        $item.removeClass('closed');
        $item.children('.inside').show();
        $item.children('button.handlediv').attr('aria-expanded', 'true');
    }

    function reinitWpEditors($scope) {
        if (typeof tinymce === 'undefined' || !window.IT_Hive || !window.IT_Hive.controls.WPEditor) {
            return;
        }

        $scope.find('[data-control-type="WPEditor"]').each(function () {
            var id = $(this).find('textarea').attr('id');
            if (!id) {
                return;
            }

            window.IT_Hive.controls.WPEditor.editor.remove(id);
            setTimeout(function () {
                window.IT_Hive.controls.WPEditor.editor.initialize(
                    id,
                    window.IT_Hive.controls.WPEditor.editorSettings
                );
            }, 130);
        });
    }

    function createDragHandle() {
        return $(
            '<button type="button" class="landing-repeater-drag-handle dashicons dashicons-menu" ' +
            'title="Перетащить для смены порядка" aria-label="Перетащить"></button>'
        );
    }

    function ensureRepeaterDragHandles($inner) {
        $inner.children('.repeaterItem').each(function () {
            var $item = $(this);

            if ($item.children('.landing-repeater-drag-handle').length) {
                return;
            }

            var $toggle = $item.children('button.handlediv').first();

            if ($toggle.length) {
                $toggle.before(createDragHandle());
                return;
            }

            $item.prepend(
                createDragHandle().addClass('landing-repeater-drag-handle--row')
            );
        });
    }

    function initLandingRepeatersSortable($scope, force) {
        if (!$.fn.sortable) {
            return;
        }

        disableItHiveSortableOnLandingRepeaters($scope);

        getLandingRepeaterInners($scope).each(function () {
            var $inner = $(this);
            var $items = $inner.children('.repeaterItem');

            ensureRepeaterDragHandles($inner);

            if ($items.length < 2) {
                if ($inner.hasClass('ui-sortable')) {
                    $inner.sortable('destroy');
                }
                $inner.removeData('landingSortableInit');
                return;
            }

            if ($inner.data('landingSortableInit') && !force) {
                return;
            }

            if ($inner.hasClass('ui-sortable')) {
                $inner.sortable('destroy');
            }

            $inner.sortable({
                items: '> .repeaterItem',
                handle: '.landing-repeater-drag-handle',
                tolerance: 'pointer',
                axis: 'y',
                cursor: 'grabbing',
                distance: 3,
                scroll: true,
                scrollSensitivity: 40,
                helper: 'clone',
                appendTo: 'body',
                placeholder: 'landing-repeater-sortable-placeholder',
                forcePlaceholderSize: true,
                cancel: 'input,textarea,select,option,.upload-img,.delete-img,.wp-editor-wrap,.mce-container,.btn-controls a,.repeater-add-btn',
                start: function (e, ui) {
                    ui.helper.css({
                        width: ui.item.outerWidth(),
                        zIndex: 100000
                    });
                },
                stop: function (e, ui) {
                    reinitWpEditors(ui.item);
                }
            });

            $inner.data('landingSortableInit', true);
        });
    }

    function isHeroSlideItem($slideItem) {
        return $slideItem.closest('[data-name*="[hero_slides][slides]"]').length > 0;
    }

    function initHeroSlideItem($slideItem) {
        if (!isHeroSlideItem($slideItem)) {
            return;
        }

        $slideItem
            .find('select[name*="[slide_type]"]')
            .off('change.heroSlideType')
            .on('change.heroSlideType', function () {
                syncHeroSlideTypeVisibility($slideItem);
            });

        syncHeroSlideTypeVisibility($slideItem);
    }

    function initAllHeroSlideItems($scope) {
        var $roots = $scope && $scope.length ? $scope : getLandingMetaboxRoot();
        $roots.find(SELECTOR_HERO_SLIDE).each(function () {
            initHeroSlideItem($(this));
        });
    }

    function bindLandingRepeaterUi() {
        var $box = getLandingMetaboxRoot();
        if (!$box.length) {
            return;
        }

        $box.off('mousedown.landingDragHandle click.landingDragHandle', '.landing-repeater-drag-handle');
        $box.on('mousedown.landingDragHandle click.landingDragHandle', '.landing-repeater-drag-handle', function (e) {
            e.stopPropagation();
        });

        $box.off('click.landingRepeaterToggle', SELECTOR_TOGGLE);
        $box.on('click.landingRepeaterToggle', SELECTOR_TOGGLE, function (e) {
            if ($(e.target).closest('.landing-repeater-drag-handle').length) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.repeaterItem.postbox');
            var wasClosed = $item.hasClass('closed');

            $item.toggleClass('closed');

            if ($item.hasClass('closed')) {
                $item.children('.inside').hide();
                $item.children('button.handlediv').attr('aria-expanded', 'false');
                return;
            }

            openRepeaterPanel($item);
            initHeroSlideItem($item);

            if (wasClosed) {
                reinitWpEditors($item);
            }
        });

        $box.off('click.landingRepeaterAdd', '.repeater-add-btn');
        $box.on('click.landingRepeaterAdd', '.repeater-add-btn', function () {
            var $container = $(this).closest('.repeater-container');

            setTimeout(function () {
                var $item = $container.find(SELECTOR_ITEM).last();
                openRepeaterPanel($item);
                initHeroSlideItem($item);
                initLandingRepeatersSortable($container, true);
                normalizeLandingRepeaterDeleteLabels($container);
            }, 50);
        });

        $box.off('click.landingRepeaterDelete', '.delete-repeaterItem');
        $box.on('click.landingRepeaterDelete', '.delete-repeaterItem', function () {
            var $container = $(this).closest('.repeater-container');

            setTimeout(function () {
                initLandingRepeatersSortable($container, true);
            }, 50);
        });

        $box.find(SELECTOR_HERO_SLIDE).each(function () {
            initHeroSlideItem($(this));
        });

        initLandingRepeatersSortable($box);
    }

    function patchRepeaterAddItem() {
        if (!window.IT_Hive || !window.IT_Hive.controls || !window.IT_Hive.controls.repeater) {
            return;
        }

        var Repeater = window.IT_Hive.controls.repeater;
        if (Repeater.prototype.__landingMetaboxPatched) {
            return;
        }

        Repeater.prototype.__landingMetaboxPatched = true;

        var originalAddItem = Repeater.prototype.addItem;
        Repeater.prototype.addItem = function () {
            var item = originalAddItem.apply(this, arguments);

            setTimeout(function () {
                initLandingRepeatersSortable(this.container, true);
                normalizeLandingRepeaterDeleteLabels(this.container);
            }.bind(this), 50);

            if (!this.$el.is('[data-name*="[hero_slides][slides]"]')) {
                return item;
            }

            var $item = this.container.children('.repeaterItem.postbox').last();
            openRepeaterPanel($item);
            initHeroSlideItem($item);

            return item;
        };
    }

    function patchItHiveSortableInit() {
        if ($.fn.sortable.__landingMetaboxPatched) {
            return;
        }

        var originalSortable = $.fn.sortable;

        $.fn.sortable = function (option) {
            if (arguments.length && typeof option === 'object') {
                var $target = this.first();
                var isLandingRepeater = $target.hasClass('repeater-inner') && (
                    $target.closest('[data-name^="landing_page_options"]').length ||
                    $target.closest(SELECTOR_BOX).length
                );

                if (isLandingRepeater && (!option.handle || option.handle.indexOf('landing-repeater-drag-handle') === -1)) {
                    return this;
                }
            }

            return originalSortable.apply(this, arguments);
        };

        $.fn.sortable.__landingMetaboxPatched = true;
    }

    function normalizeLandingRepeaterDeleteLabels($scope) {
        var $roots = $scope && $scope.length ? $scope : getLandingMetaboxRoot();

        $roots.find('.delete-repeaterItem').each(function () {
            var $link = $(this);
            if ($link.text().trim() !== 'Delete') {
                $link.text('Delete');
            }
        });
    }

    function scheduleLandingRepeaterUiInit() {
        patchItHiveSortableInit();
        disableItHiveSortableOnLandingRepeaters();
        bindLandingRepeaterUi();
        patchRepeaterAddItem();
        initLandingRepeatersSortable(null, true);
        initAllHeroSlideItems();
        normalizeLandingRepeaterDeleteLabels();

        [100, 400, 1200].forEach(function (delay) {
            setTimeout(function () {
                disableItHiveSortableOnLandingRepeaters();
                initLandingRepeatersSortable(null, true);
                initAllHeroSlideItems();
                normalizeLandingRepeaterDeleteLabels();
            }, delay);
        });
    }

    patchItHiveSortableInit();

    $(function () {
        scheduleLandingRepeaterUiInit();
    });

    $(window).on('load', function () {
        scheduleLandingRepeaterUiInit();
    });
})(jQuery);
