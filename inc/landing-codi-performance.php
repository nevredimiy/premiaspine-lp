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
 * @param string $tag    The full <script> tag.
 * @param string $handle Registered script handle.
 * @return string
 */
add_filter( 'script_loader_tag', 'premiaspine_landing_defer_noncritical_scripts', 10, 2 );
function premiaspine_landing_defer_noncritical_scripts( $tag, $handle ) {
	if ( ! premiaspine_landing_is_codi_perf_context() ) {
		return $tag;
	}

	$deferrable = array(
		'wp-polyfill',
		'hooks',
		'wp-hooks',
		'wp-i18n',
		'jquery-ui-core',
		'jquery-ui-widget',
		'jquery-ui-mouse',
		'jquery-ui-accordion',
		'jquery-ui-sortable',
		'jquery.fancybox.min',
		'ajax',
		'swv',
		'contact-form-7',
		'wpcf7-redirect-script-frontend',
		'wpcf7-recaptcha',
		'google-recaptcha',
		'premiaspine-landing',
		'premiaspine-landing-story-popups',
		'find-doctor-map-filters',
	);

	if ( ! in_array( $handle, $deferrable, true ) ) {
		return $tag;
	}

	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}

	return preg_replace( '/<script(?=[\s>])/', '<script defer', $tag, 1 );
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
			"<link rel=\"preload\" as=\"image\" href=\"%s\" fetchpriority=\"high\">\n",
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
