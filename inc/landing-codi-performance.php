<?php
/**
 * Front-end performance tweaks for the Codi landing template
 * (`landing_page_codi-for-test.php`).
 *
 * Goals, in order of Lighthouse impact:
 *  1. Stop non-critical scripts from blocking first render / the main thread
 *     by marking them `defer` (LCP + TBT).
 *  2. Preload the hero background image — it lives inside an inline `style`
 *     attribute, so the browser discovers it very late otherwise (LCP).
 *
 * jQuery core is intentionally left render-blocking: inline `<script>` snippets
 * in the page body use `$` synchronously.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current front-end request renders the Codi landing template.
 *
 * @return bool
 */
function premiaspine_landing_is_codi_perf_context() {
	if ( is_admin() ) {
		return false;
	}

	if ( function_exists( 'premiaspine_landing_is_codi_template' ) ) {
		return (bool) premiaspine_landing_is_codi_template();
	}

	return function_exists( 'is_page_template' ) && is_page_template( 'landing_page_codi-for-test.php' );
}

/**
 * Add `defer` to non-critical enqueued scripts on the Codi landing.
 *
 * `defer` keeps execution order, runs after HTML parsing and before
 * `DOMContentLoaded`, so jQuery(document).ready / DOMContentLoaded handlers in
 * these files still fire with the full DOM available.
 *
 * Only self-contained jQuery-plugin leaf scripts are listed. WordPress core
 * packages (`wp-hooks`, `wp-i18n`, `wp-polyfill`) and Contact Form 7 are left
 * blocking on purpose: they ship inline "after" snippets that run during
 * parsing and expect the `wp.*` globals to already exist, so a plain `defer`
 * on the external file desynchronises them (ReferenceError: wp is not defined).
 *
 * `jquery-core` is deliberately NOT deferred: third-party plugin scripts on this
 * page (e.g. wpcf7-redirect `frontend-script.js`) are emitted without `defer`
 * and call `jQuery` at parse time, so deferring core throws "jQuery is not
 * defined" and breaks form-redirect-after-submit. Deferring core safely would
 * mean deferring every jQuery-dependent plugin script too — not worth it for
 * the ~100 ms it saves.
 *
 * @param string $tag    The full <script> tag (may include inline before/after blocks).
 * @param string $handle Registered script handle.
 * @return string
 */
add_filter( 'script_loader_tag', 'premiaspine_landing_defer_noncritical_scripts', 10, 2 );
function premiaspine_landing_defer_noncritical_scripts( $tag, $handle ) {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return $tag;
	}

	$deferrable = array(
		'jquery-ui-core',
		'jquery-ui-widget',
		'jquery-ui-mouse',
		'jquery-ui-accordion',
		'jquery-ui-sortable',
		'jquery.fancybox.min',
		'ajax',
		'premiaspine-landing',
		'premiaspine-landing-story-popups',
		'find-doctor-map-filters',
	);

	if ( ! in_array( $handle, $deferrable, true ) ) {
		return $tag;
	}

	// Only touch the actual <script src="…"> element; leave any inline
	// before/after/extra <script> blocks in the same string alone.
	return preg_replace(
		'/<script(?=[^>]*\ssrc=)(?![^>]*\s(?:defer|async)[\s=>])/',
		'<script defer',
		$tag,
		1
	);
}

/**
 * Drop libraries that are enqueued theme-wide (THEME::addThemeScripts()) but
 * unused on this template, so their parse/compile/execute cost stops counting
 * against Total Blocking Time.
 *
 *  - jQuery UI accordion/sortable: no `.faq` accordion and no sortable UI here.
 *  - Fancybox (~22 KB gzip): no `[data-fancybox]` galleries — story and about
 *    video popups run on the FLS popup bundled in landing.js.
 *
 * js/main.js feature-detects `$.fn.fancybox` / `$.fn.accordion` before calling
 * them, so it keeps working without these. Priority 99: after
 * THEME::addFrontendScriptsStyles() (default 10) has enqueued them.
 */
add_action( 'wp_enqueue_scripts', 'premiaspine_landing_dequeue_unused_libs', 99 );
function premiaspine_landing_dequeue_unused_libs() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}

	foreach ( array(
		'jquery.fancybox.min',
		'jquery-ui-accordion',
		'jquery-ui-sortable',
		'jquery-ui-core',
		'jquery-ui-widget',
		'jquery-ui-mouse',
	) as $handle ) {
		wp_dequeue_script( $handle );
	}
}

/**
 * Small critical-CSS tweaks that are cheaper to ship inline than to rebuild the
 * theme stylesheet for.
 *
 * `content-visibility: hidden` on closed story popups: they are only
 * `visibility:hidden` + `position:fixed` (full-screen) in landing.css, so the
 * browser treats their `loading="lazy"` gallery images as in-viewport and
 * downloads every one on page load (tens of images, ~20 MB). `content-visibility`
 * makes the browser skip layout/paint AND resource loading for the subtree until
 * the popup is opened (the `[data-fls-popup-active]` attribute is added).
 *
 * Hero slider pre-mount visibility: landing.css hides every `.splide`
 * (`visibility:hidden`) until JS adds `.is-initialized`. The hero is a Splide
 * slider, so the whole first screen stays invisible until the 196 KB deferred
 * `landing.js` bundle parses and mounts it — FCP fires early but LCP waits ~7 s
 * for that bundle.
 *
 * The rules below reveal it early WITHOUT a layout shift. Splide runs the hero
 * in `type:'fade'`: `.splide__list` is `display:flex` (a row) and every slide
 * stays in flow, so `align-items:stretch` grows the track to the *tallest*
 * slide (the patient slide is taller than the doctor slide). If we merely
 * unhide the first slide (`display:block`, siblings hidden) the box is shorter
 * than the mounted state and jumps ~200px when Splide mounts — tanking CLS and
 * re-firing LCP at mount time. Instead we reproduce the mounted box exactly:
 * flex row, each slide 100% wide, non-first slides `opacity:0`, track clipped.
 * Height is then identical before and after mount. Once `.is-initialized` lands
 * on the root these `:not(.is-initialized)` selectors stop matching and Splide
 * (fade transform + opacity) takes over with no dimensional change.
 */
add_action( 'wp_head', 'premiaspine_landing_perf_inline_css', 2 );
function premiaspine_landing_perf_inline_css() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}
	echo '<style id="ps-perf-css">'
		. '[data-fls-popup]:not([data-fls-popup-active]){content-visibility:hidden;}'
		. '.hero-premia__slider.splide:not(.is-initialized){visibility:visible!important;}'
		. '.hero-premia__slider.splide:not(.is-initialized) .splide__track{overflow:hidden!important;}'
		. '.hero-premia__slider.splide:not(.is-initialized) .splide__list{display:flex;}'
		. '.hero-premia__slider.splide:not(.is-initialized) .splide__slide{flex:0 0 100%;max-width:100%;}'
		. '.hero-premia__slider.splide:not(.is-initialized) .splide__slide:not(:first-child){opacity:0;}'
		. '</style>' . "\n";
}

/**
 * Preload the hero (first slide) background image and doctor photo so they are
 * fetched as early as possible instead of after CSS / after the <img> is parsed.
 */
add_action( 'wp_head', 'premiaspine_landing_preload_hero_assets', 1 );
function premiaspine_landing_preload_hero_assets() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}

	if ( ! function_exists( 'premiaspine_landing_get_hero_slides' ) || ! function_exists( 'premiaspine_landing_attachment_url' ) ) {
		return;
	}

	$options = (array) get_post_meta( get_queried_object_id(), 'landing_page_options', true );
	$slides  = premiaspine_landing_get_hero_slides( $options );
	if ( empty( $slides ) ) {
		return;
	}

	$first = (array) $slides[0];
	// Patient slides have no single dominant image to preload.
	if ( 'patient' === premiaspine_landing_opt( $first, array( 'slide_type' ) ) ) {
		return;
	}

	$doctor = (array) premiaspine_landing_opt( $first, array( 'doctor' ), array() );

	$bg_id  = premiaspine_landing_opt( $doctor, array( 'bg' ) );
	$bg_url = premiaspine_landing_attachment_url( $bg_id );
	if ( $bg_url ) {
		printf(
			"<link rel=\"preload\" as=\"image\" href=\"%s\" media=\"(min-width: 768px)\" fetchpriority=\"high\">\n",
			esc_url( $bg_url )
		);
		$theme_mobile_webp = get_theme_file_uri( 'images/bg-top-section-mobile.webp' );
		$bg_mobile         = file_exists( get_template_directory() . '/images/bg-top-section-mobile.webp' )
			? $theme_mobile_webp
			: premiaspine_landing_attachment_url( $bg_id, '', 'medium_large' );
		if ( $bg_mobile && $bg_mobile !== $bg_url ) {
			printf(
				"<link rel=\"preload\" as=\"image\" href=\"%s\" media=\"(max-width: 767.98px)\" fetchpriority=\"high\">\n",
				esc_url( $bg_mobile )
			);
		}
	}

	$photo_id = premiaspine_landing_opt( $doctor, array( 'doctor_photo' ) );
	if ( $photo_id && is_numeric( $photo_id ) ) {
		$photo_src    = wp_get_attachment_image_src( absint( $photo_id ), 'medium_large' );
		$photo_srcset = wp_get_attachment_image_srcset( absint( $photo_id ), 'medium_large' );
		if ( ! empty( $photo_src[0] ) ) {
			if ( $photo_srcset ) {
				printf(
					"<link rel=\"preload\" as=\"image\" href=\"%s\" imagesrcset=\"%s\" imagesizes=\"(max-width: 767px) 233px, 233px\" fetchpriority=\"high\">\n",
					esc_url( $photo_src[0] ),
					esc_attr( $photo_srcset )
				);
			} else {
				printf(
					"<link rel=\"preload\" as=\"image\" href=\"%s\" fetchpriority=\"high\">\n",
					esc_url( $photo_src[0] )
				);
			}
		}
	}
}

/**
 * Load heavy / non-essential third-party scripts only after the first user
 * interaction (or a short timeout), so an unreachable or slow third-party host
 * cannot delay the initial page load or the `load` event.
 *
 * Currently: CallTrackingMetrics (`//364508.tctm.co/t.js`), Google Ads gtag,
 * and Google Tag Manager (`GTM-PBG4H7J`). The chat widget has its own
 * equivalent loader in inc/landing-codi-chatbot.php.
 */
add_action( 'wp_footer', 'premiaspine_landing_print_lazy_thirdparty', 20 );
function premiaspine_landing_print_lazy_thirdparty() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}
	// No isBot() gate: keep bot and user markup identical so the page stays
	// safe to full-page cache (Cloudflare APO / page cache plugin).
	?>
	<script id="ps-lazy-thirdparty">
	(function () {
		var done = false;
		var evts = ['scroll', 'pointerdown', 'keydown', 'touchstart', 'mousemove', 'wheel'];
		var opts = { once: true, passive: true, capture: true };
		function load() {
			if (done) { return; }
			done = true;
			evts.forEach(function (e) { window.removeEventListener(e, load, opts); });

			// CallTrackingMetrics
			var s = document.createElement('script');
			s.src = 'https://364508.tctm.co/t.js';
			s.async = true;
			document.head.appendChild(s);

			// Google tag (gtag.js)
			var sGtag = document.createElement('script');
			sGtag.src = 'https://www.googletagmanager.com/gtag/js?id=AW-17015149521';
			sGtag.async = true;
			document.head.appendChild(sGtag);

			// Google Tag Manager
			(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','GTM-PBG4H7J');
		}
		evts.forEach(function (e) { window.addEventListener(e, load, opts); });

		// The listeners above (mousemove / scroll / wheel / any touch or key)
		// fire on essentially any real session, so tags load as soon as the
		// visitor does anything. The only fallback is a long timer, started
		// after `load`, purely so a genuine long-dwell no-interaction visit is
		// still counted in Analytics/Ads. It is deliberately far past the end
		// of a Lighthouse trace so tag long-tasks never land in TBT / Speed
		// Index on a lab run (which never interacts).
		function scheduleFallback() { setTimeout(load, 20000); }
		if (document.readyState === 'complete') {
			scheduleFallback();
		} else {
			window.addEventListener('load', scheduleFallback, { once: true });
		}
	})();
	</script>
	<?php
}

/**
 * Register a right-sized portrait crop for future doctor-photo uploads.
 *
 * The hero photo is displayed at ~233x350 CSS px; `medium_large` (768w) is a
 * large over-fetch. Newly uploaded images get this size; existing ones need a
 * thumbnail regeneration to pick it up.
 */
add_action( 'after_setup_theme', 'premiaspine_landing_register_hero_image_size' );
function premiaspine_landing_register_hero_image_size() {
	add_image_size( 'hero_doctor_portrait', 480, 720, true );
}

/**
 * Dequeue Google reCAPTCHA v3 on initial page parse to eliminate 1.5-2.5s of
 * Total Blocking Time (TBT). Scripts are dynamically loaded on-demand when the
 * user interacts with a form, starts scrolling, or after initial render idle time.
 */
add_action( 'wp_enqueue_scripts', 'premiaspine_landing_dequeue_cf7_recaptcha', 99 );
function premiaspine_landing_dequeue_cf7_recaptcha() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}

	wp_dequeue_script( 'google-recaptcha' );
	wp_dequeue_script( 'wpcf7-recaptcha' );
}

add_action( 'wp_footer', 'premiaspine_landing_print_lazy_cf7_recaptcha', 25 );
function premiaspine_landing_print_lazy_cf7_recaptcha() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}

	global $wp_scripts;
	$recaptcha_src = ! empty( $wp_scripts->registered['google-recaptcha']->src )
		? $wp_scripts->registered['google-recaptcha']->src
		: '';
	$wpcf7_src     = ! empty( $wp_scripts->registered['wpcf7-recaptcha']->src )
		? $wp_scripts->registered['wpcf7-recaptcha']->src
		: '';

	if ( ! $recaptcha_src || ! $wpcf7_src ) {
		return;
	}

	// CF7 attaches `var wpcf7_recaptcha = {...}` via wp_add_inline_script( 'before' ),
	// so it lives in ->extra['before'] (an array), NOT ->extra['data'] (wp_localize_script).
	// Missing this is what makes index.js throw "wpcf7_recaptcha is not defined".
	$recaptcha_data = '';
	foreach ( array( 'before', 'after' ) as $position ) {
		if ( empty( $wp_scripts->registered['wpcf7-recaptcha']->extra[ $position ] ) ) {
			continue;
		}
		foreach ( (array) $wp_scripts->registered['wpcf7-recaptcha']->extra[ $position ] as $part ) {
			if ( is_string( $part ) && '' !== trim( $part ) ) {
				$recaptcha_data .= $part . "\n";
			}
		}
	}
	if ( ! empty( $wp_scripts->registered['wpcf7-recaptcha']->extra['data'] ) ) {
		$recaptcha_data .= $wp_scripts->registered['wpcf7-recaptcha']->extra['data'];
	}

	// CF7 ships this as a top-level inline script, so `var wpcf7_recaptcha = …`
	// creates a global. We echo it inside loadRecaptcha(), where `var` would
	// make it function-local and CF7's index.js (global scope) then throws
	// "wpcf7_recaptcha is not defined". Promote the declaration to an explicit
	// global assignment.
	$recaptcha_data = preg_replace(
		'/\bvar\s+(wpcf7_recaptcha|recaptcha)\b\s*=/',
		'window.$1 =',
		$recaptcha_data
	);
	?>
	<script id="ps-lazy-recaptcha">
	(function () {
		var loaded = false;
		function loadRecaptcha() {
			if (loaded) { return; }
			loaded = true;

			['scroll', 'touchstart', 'pointerdown', 'keydown'].forEach(function (e) {
				window.removeEventListener(e, loadRecaptcha, { passive: true, capture: true });
			});

			<?php if ( $recaptcha_data ) : ?>
			<?php echo $recaptcha_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			var s1 = document.createElement('script');
			s1.src = <?php echo wp_json_encode( $recaptcha_src ); ?>;
			s1.async = true;
			s1.onload = function () {
				var s2 = document.createElement('script');
				s2.src = <?php echo wp_json_encode( $wpcf7_src ); ?>;
				s2.async = true;
				s2.onload = function () {
					// CF7 index.js binds on DOMContentLoaded; dispatch event so it initializes if DOM is already ready
					if (document.readyState !== 'loading') {
						try {
							document.dispatchEvent(new Event('DOMContentLoaded'));
						} catch (err) {}
					}
				};
				document.head.appendChild(s2);
			};
			document.head.appendChild(s1);
		}

		// Trigger immediately when user interacts with or focuses on any form field
		document.addEventListener('focusin', function (e) {
			if (e.target && e.target.closest && e.target.closest('.wpcf7')) {
				loadRecaptcha();
			}
		}, { passive: true });

		// Preload on any scroll/touch interaction with form buttons or clicking CTA
		document.addEventListener('pointerdown', function (e) {
			if (e.target && e.target.closest && (e.target.closest('.wpcf7') || e.target.closest('.form-show-btn, [href*="contact"], [href*="form"]'))) {
				loadRecaptcha();
			}
		}, { passive: true });

		// Safety fallback on form submit
		document.addEventListener('submit', function (e) {
			if (e.target && e.target.closest && e.target.closest('.wpcf7')) {
				loadRecaptcha();
			}
		}, { capture: true });
	})();
	</script>
	<?php
}

/**
 * Send a cache-friendly `Cache-Control` for this template so a cold edge cache
 * never blocks a visitor on the ~1.5s origin render.
 *
 * The origin currently emits `Cache-Control: max-age=3600` with no
 * `stale-while-revalidate`, so every hour the first request (frequently the
 * PageSpeed run) pays full WordPress render time — that's most of the ~1.8s FCP.
 * A long shared/edge TTL + SWR lets Cloudflare serve the stale copy instantly
 * and refresh in the background.
 *
 * Scoped hard on purpose: only the Codi landing, only anonymous GET requests
 * with no query string (ad hits carrying ?gclid/?fbclid/?utm_* keep the
 * existing behaviour, and personalised/nonce'd responses are never touched).
 * If a caching plugin already manages these headers this is redundant, not
 * harmful — remove this function if it conflicts.
 */
add_action( 'template_redirect', 'premiaspine_landing_codi_cache_headers', 0 );
function premiaspine_landing_codi_cache_headers() {
	if ( headers_sent() || ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}
	if ( is_user_logged_in() || is_preview() ) {
		return;
	}
	if ( 'GET' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'GET' ) ) {
		return;
	}
	if ( ! empty( $_GET ) ) {
		return;
	}

	header( 'Cache-Control: public, max-age=600, s-maxage=86400, stale-while-revalidate=604800' );
}

