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
 * Small critical-CSS tweaks that are cheaper to ship inline than to rebuild the
 * theme stylesheet for.
 *
 * `content-visibility: hidden` on closed story popups: they are only
 * `visibility:hidden` + `position:fixed` (full-screen) in landing.css, so the
 * browser treats their `loading="lazy"` gallery images as in-viewport and
 * downloads every one on page load (tens of images, ~20 MB). `content-visibility`
 * makes the browser skip layout/paint AND resource loading for the subtree until
 * the popup is opened (the `[data-fls-popup-active]` attribute is added).
 */
add_action( 'wp_head', 'premiaspine_landing_perf_inline_css', 2 );
function premiaspine_landing_perf_inline_css() {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return;
	}
	echo '<style id="ps-perf-css">[data-fls-popup]:not([data-fls-popup-active]){content-visibility:hidden;}</style>' . "\n";
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

	$bg_url = premiaspine_landing_attachment_url( premiaspine_landing_opt( $doctor, array( 'bg' ) ) );
	if ( $bg_url ) {
		printf(
			"<link rel=\"preload\" as=\"image\" href=\"%s\" media=\"(min-width: 768px)\" fetchpriority=\"high\">\n",
			esc_url( $bg_url )
		);
	}

	$photo_id = premiaspine_landing_opt( $doctor, array( 'doctor_photo' ) );
	if ( $photo_id && is_numeric( $photo_id ) ) {
		$photo_src = wp_get_attachment_image_src( absint( $photo_id ), 'medium_large' );
		if ( ! empty( $photo_src[0] ) ) {
			printf(
				"<link rel=\"preload\" as=\"image\" href=\"%s\" fetchpriority=\"high\">\n",
				esc_url( $photo_src[0] )
			);
		}
	}
}

/**
 * Load heavy / non-essential third-party scripts only after the first user
 * interaction (or a short timeout), so an unreachable or slow third-party host
 * cannot delay the initial page load or the `load` event.
 *
 * Currently: CallTrackingMetrics (`//364508.tctm.co/t.js`). The chat widget has
 * its own equivalent loader in inc/landing-codi-chatbot.php.
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
			var s = document.createElement('script');
			s.src = 'https://364508.tctm.co/t.js';
			s.async = true;
			document.head.appendChild(s);
		}
		evts.forEach(function (e) { window.addEventListener(e, load, opts); });
		setTimeout(load, 4000);
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

	$recaptcha_data = '';
	if ( ! empty( $wp_scripts->registered['wpcf7-recaptcha']->extra['data'] ) ) {
		$recaptcha_data = $wp_scripts->registered['wpcf7-recaptcha']->extra['data'];
	}
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

		// Trigger immediately when user focuses or clicks on any form field
		document.addEventListener('focusin', function (e) {
			if (e.target && e.target.closest && e.target.closest('.wpcf7')) {
				loadRecaptcha();
			}
		}, { passive: true });

		document.addEventListener('pointerdown', function (e) {
			if (e.target && e.target.closest && e.target.closest('.wpcf7')) {
				loadRecaptcha();
			}
		}, { passive: true });

		// Or when scrolling / touching
		['scroll', 'touchstart', 'keydown'].forEach(function (e) {
			window.addEventListener(e, loadRecaptcha, { once: true, passive: true });
		});

		// Fallback idle timer
		setTimeout(loadRecaptcha, 3500);
	})();
	</script>
	<?php
}

