<?php
/**
 * CF7: ссылка Privacy Policy в чекбоксе consent.
 * Текст в админке CF7 — с «Privacy Policy» без HTML.
 */

if ( defined( 'PREMIASPINE_CF7_CONSENT_LOADED' ) ) {
    return;
}
define( 'PREMIASPINE_CF7_CONSENT_LOADED', true );

add_filter( 'wpcf7_kses_allowed_html', 'premiaspine_cf7_allow_consent_links', 10, 2 );

function premiaspine_cf7_allow_consent_links( $allowed_html, $context ) {
    if ( 'form' === $context ) {
        $allowed_html['a'] = array(
            'href'   => true,
            'target' => true,
            'rel'    => true,
            'class'  => true,
        );
    }

    return $allowed_html;
}

add_filter( 'wpcf7_form_elements', 'premiaspine_consent_privacy_policy_link', 99 );

function premiaspine_consent_privacy_policy_link( $form ) {
    if ( strpos( $form, 'data-name="consent"' ) === false ) {
        return $form;
    }

    $link = '<a href="https://lp.premiaspine.com/privacy-policy/" target="_blank" rel="noopener noreferrer" class="consent-privacy-link">Privacy Policy</a>';

    return preg_replace_callback(
        '/(<span class="wpcf7-form-control-wrap" data-name="consent">.*?<span class="wpcf7-list-item-label">)(.*?)(<\/span>)/s',
        function ( $matches ) use ( $link ) {
            $label = $matches[2];

            if ( strpos( $label, 'consent-privacy-link' ) !== false ) {
                return $matches[0];
            }

            $label = preg_replace( '/&lt;a\b[^&]*&gt;\s*Privacy Policy\s*&lt;\/a&gt;/i', 'Privacy Policy', $label );
            $label = preg_replace( '/<a\b[^>]*>\s*Privacy Policy\s*<\/a>/i', 'Privacy Policy', $label );
            $label = preg_replace( '/Privacy Policy/', $link, $label, 1 );

            return $matches[1] . $label . $matches[3];
        },
        $form,
        1
    );
}

add_filter( 'wpcf7_form_elements', 'premiaspine_cf7_consent_checked_by_default', 100 );

function premiaspine_cf7_consent_checked_by_default( $form ) {
    if ( strpos( $form, 'name="consent[]"' ) === false ) {
        return $form;
    }

    if ( preg_match( '/name="consent\[\]"\s+checked/i', $form ) ) {
        return $form;
    }

    return preg_replace( '/name="consent\[\]"/', 'name="consent[]" checked', $form, 1 );
}

add_action( 'wp_footer', 'premiaspine_consent_privacy_link_script', 99 );

function premiaspine_consent_privacy_link_script() {
    if ( ! function_exists( 'wpcf7_contact_form' ) ) {
        return;
    }
    ?>
    <script id="premiaspine-consent-privacy-link">
    (function () {
        function linkConsent(root) {
            var scope = root || document;
            scope.querySelectorAll('.wpcf7-form-control-wrap[data-name="consent"] .wpcf7-list-item-label').forEach(function (label) {
                if (label.querySelector('a.consent-privacy-link')) {
                    return;
                }
                var html = label.innerHTML || '';
                html = html.replace(/&lt;a\b[^&]*&gt;\s*Privacy Policy\s*&lt;\/a&gt;/gi, 'Privacy Policy');
                html = html.replace(/<a\b[^>]*>\s*Privacy Policy\s*<\/a>/gi, 'Privacy Policy');
                if (html.indexOf('Privacy Policy') === -1) {
                    return;
                }
                html = html.replace(
                    'Privacy Policy',
                    '<a href="https://lp.premiaspine.com/privacy-policy/" target="_blank" rel="noopener noreferrer" class="consent-privacy-link">Privacy Policy</a>'
                );
                label.innerHTML = html;
            });
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('.consent-checkbox .consent-privacy-link')) {
                e.stopPropagation();
            }
        }, true);

        function checkConsentByDefault(root) {
            var scope = root || document;
            scope.querySelectorAll('.wpcf7-form-control-wrap[data-name="consent"] input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.checked = true;
                checkbox.setAttribute('checked', 'checked');
            });
        }

        var EXTRA_SELECTOR = '.consent-checkbox, .previous-radios, .previous-radios-short';
        var EXTRA_ANIMATION_MS = 400;

        function isPermanentExtra(block) {
            return !!block.closest('.new-landing .top-section .request-appointment-section');
        }

        function getFormExtras(form) {
            if (!form) {
                return [];
            }

            return Array.prototype.filter.call(
                form.querySelectorAll(EXTRA_SELECTOR),
                function (block) {
                    return !isPermanentExtra(block);
                }
            );
        }

        function whenJQueryReady(callback) {
            if (window.jQuery) {
                callback(window.jQuery);
                return;
            }

            var done = false;

            function run() {
                if (done || !window.jQuery) {
                    return;
                }

                done = true;
                callback(window.jQuery);
            }

            document.addEventListener('DOMContentLoaded', run);
            window.addEventListener('load', run);

            var attempts = 0;
            var interval = window.setInterval(function () {
                if (window.jQuery || ++attempts > 100) {
                    window.clearInterval(interval);
                    run();
                }
            }, 50);
        }

        function showFormExtras(form, $) {
            var $extras = $(getFormExtras(form));

            if (!$extras.length) {
                return;
            }

            $extras.stop(true, true).slideDown(EXTRA_ANIMATION_MS);
        }

        function hideFormExtras(form, $) {
            var $extras = $(getFormExtras(form));

            if (!$extras.length) {
                return;
            }

            $extras.stop(true, true).slideUp(EXTRA_ANIMATION_MS);
        }

        function bindConsentReveal(root) {
            var scope = root || document;
            var fieldSelector = 'input[type="text"], input[type="email"], input[type="tel"], input[type="password"], textarea, select';

            scope.querySelectorAll(
                '.request-form ' + fieldSelector + ', .content-area form ' + fieldSelector + ', .request-appointment form ' + fieldSelector
            ).forEach(function (field) {
                if (field.dataset.consentRevealBound) {
                    return;
                }

                field.dataset.consentRevealBound = '1';
                field.addEventListener('focus', function () {
                    var form = field.closest('.request-form, .request-appointment, .wpcf7-form, form');

                    whenJQueryReady(function ($) {
                        showFormExtras(form, $);
                    });
                });
            });
        }

        function bindConsentHide() {
            if (document.documentElement.dataset.consentHideBound) {
                return;
            }

            document.documentElement.dataset.consentHideBound = '1';

            document.addEventListener('mouseup', function (e) {
                whenJQueryReady(function ($) {
                    document.querySelectorAll('.top-section .request-form, .request-appointment .request-form, .content-area form').forEach(function (form) {
                        if (!form.contains(e.target)) {
                            hideFormExtras(form, $);
                        }
                    });
                });
            });
        }

        function initConsentBehavior(root) {
            linkConsent(root);
            checkConsentByDefault(root);
            bindConsentReveal(root);
            bindConsentHide();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { initConsentBehavior(); });
        } else {
            initConsentBehavior();
        }

        document.addEventListener('wpcf7DOMContentLoaded', function (e) {
            initConsentBehavior(e.target);
        });
    })();
    </script>
    <?php
}
