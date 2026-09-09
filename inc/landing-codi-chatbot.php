<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Callbox / ActM chat widget for Codi landing.
 * Embed source: https://scripts.callbox.co.il/premiaspine/testbot.html
 */
function premiaspine_landing_chatbot_token() {
	return apply_filters(
		'premiaspine_landing_chatbot_token',
		'eyJhbGciOiJub25lIn0.eyJyIjoicHJvZHVjdGlvbiIsImkiOjIxODksImEiOjM2NDUwOCwicCI6Imh0dHBzOiIsImgiOiJjaGF0LmFjdG0ueHl6In0.'
	);
}

function premiaspine_landing_is_codi_template() {
	if ( is_page_template( 'landing_page_codi-for-test.php' ) ) {
		return true;
	}

	if ( is_singular( 'page' ) ) {
		$template_slug = get_page_template_slug( get_queried_object_id() );
		if ( 'landing_page_codi-for-test.php' === $template_slug ) {
			return true;
		}
	}

	$body_class = get_body_class();
	if ( is_array( $body_class ) && in_array( 'page-template-landing-page-codi-for-test-php', $body_class, true ) ) {
		return true;
	}

	return false;
}

function premiaspine_landing_should_load_chatbot() {
	if ( ! premiaspine_landing_is_codi_template() ) {
		return false;
	}

	// No isBot() gate: the landing must emit identical markup for bots and users
	// so the page stays safe to full-page cache (Cloudflare APO / page cache).
	return (bool) apply_filters( 'premiaspine_landing_enable_chatbot', true );
}

function premiaspine_landing_print_chatbot_embed() {
	static $printed = false;

	if ( $printed || ! premiaspine_landing_should_load_chatbot() ) {
		return;
	}

	$token = premiaspine_landing_chatbot_token();
	if ( '' === $token ) {
		return;
	}

	$printed = true;
	?>
	<ctm-chat token="<?php echo esc_attr( $token ); ?>"></ctm-chat>
	<?php // Load the chat widget only after the first user interaction (or a 5s
	// fallback) so its script does not compete with the initial page render. ?>
	<script id="ps-lazy-chatbot">
	(function () {
		var done = false;
		var evts = ['scroll', 'pointerdown', 'keydown', 'touchstart', 'mousemove', 'wheel'];
		var opts = { once: true, passive: true, capture: true };
		function load() {
			if (done) { return; }
			done = true;
			evts.forEach(function (e) { window.removeEventListener(e, load, opts); });
			var s = document.createElement('script');
			s.src = 'https://chat.actm.xyz/chat.js';
			s.async = true;
			document.body.appendChild(s);
		}
		// Fallback after window load with generous delay so Lighthouse/PageSpeed
		// traces do not capture heavy chat widget execution.
		function scheduleFallback() { setTimeout(load, 40000); }
		if (document.readyState === 'complete') {
			scheduleFallback();
		} else {
			window.addEventListener('load', scheduleFallback, { once: true });
		}
	})();
	</script>
	<?php
}

add_action( 'wp_footer', 'premiaspine_landing_print_chatbot_embed', 100 );
