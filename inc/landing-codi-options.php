<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function premiaspine_landing_is_codi_template( $post_id = 0 ) {
	if ( $post_id ) {
		return 'landing_page_codi-for-test.php' === get_page_template_slug( (int) $post_id );
	}

	if ( function_exists( 'is_page_template' ) && is_page_template( 'landing_page_codi-for-test.php' ) ) {
		return true;
	}

	if ( is_singular( 'page' ) ) {
		$queried_id = get_queried_object_id();
		if ( $queried_id && 'landing_page_codi-for-test.php' === get_page_template_slug( $queried_id ) ) {
			return true;
		}
	}

	$body_class = get_body_class();

	return is_array( $body_class ) && in_array( 'page-template-landing-page-codi-for-test-php', $body_class, true );
}

function premiaspine_landing_is_empty_option_value( $value ) {
	if ( is_array( $value ) ) {
		return empty( $value );
	}

	if ( is_string( $value ) ) {
		return '' === trim( $value );
	}

	return null === $value || false === $value;
}

function premiaspine_landing_get_codi_default_options() {
	return array(
		'hero_patient' => array(
			'title' => '<p>Amazing results.<br>I have a new<br>lease on life!</p>',
			'name'  => 'Jim R',
		),
		'premia_benefits' => array(
			'title'    => 'Regain natural spine motion.',
			'subtitle' => 'Conquer lumbar spinal stenosis and spondylolisthesis.',
			'info_items' => array(
				array( 'text' => "Freedom\nfrom fusion" ),
				array( 'text' => "Protect your\nadjacent levels" ),
				array( 'text' => "Keep\nmoving" ),
			),
			'slides' => array(
				array(
					'title'      => 'You qualify for TOPS™ with...',
					'blue_style' => 'disable',
					'list'       => "Lumbar spinal stenosis\nDegenerative spondylolisthesis",
				),
				array(
					'title'      => 'The TOPS™ Benefits are...',
					'blue_style' => 'on',
					'list'       => "Preserves motion at the treated level\nReduces stress on adjacent levels\nDesigned for long-term stability",
				),
			),
		),
		'about_section' => array(
			'title' => 'What is TOPS™?',
		),
		'footer_codi' => array(
			'copyright' => 'All Rights Reserved to Premia Spine ©2026',
		),
	);
}

function premiaspine_landing_merge_empty_options( $options, $defaults ) {
	$options = is_array( $options ) ? $options : array();

	foreach ( (array) $defaults as $key => $default_value ) {
		if ( ! array_key_exists( $key, $options ) || premiaspine_landing_is_empty_option_value( $options[ $key ] ) ) {
			$options[ $key ] = $default_value;
			continue;
		}

		if ( is_array( $default_value ) && is_array( $options[ $key ] ) ) {
			$options[ $key ] = premiaspine_landing_merge_empty_options( $options[ $key ], $default_value );
		}
	}

	return $options;
}

function premiaspine_landing_extract_codi_options_from_legacy( $legacy ) {
	if ( ! is_array( $legacy ) ) {
		return array();
	}

	$header_keys = array( 'slogan', 'top_title', 'top_content', 'doctror_name', 'doctor_photo', 'top_section_bg', 'contact_form' );
	$options     = array();

	foreach ( array( 'hero_patient', 'premia_benefits', 'about_section', 'footer_codi', 'request_an_appointment_form_3' ) as $section_key ) {
		if ( ! empty( $legacy[ $section_key ] ) && is_array( $legacy[ $section_key ] ) ) {
			$options[ $section_key ] = $legacy[ $section_key ];
		}
	}

	if ( ! empty( $legacy['header'] ) && is_array( $legacy['header'] ) ) {
		$options['header'] = array();
		foreach ( $header_keys as $header_key ) {
			if ( isset( $legacy['header'][ $header_key ] ) ) {
				$options['header'][ $header_key ] = $legacy['header'][ $header_key ];
			}
		}
	}

	return $options;
}

function premiaspine_landing_get_codi_page_options( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	$options = $post_id ? get_post_meta( $post_id, 'landing_page_codi_options', true ) : array();

	if ( ! is_array( $options ) || empty( $options ) ) {
		$legacy = $post_id ? get_post_meta( $post_id, 'landing_page_options', true ) : array();
		$options = premiaspine_landing_extract_codi_options_from_legacy( $legacy );
	}

	return premiaspine_landing_apply_codi_defaults( is_array( $options ) ? $options : array() );
}

function premiaspine_landing_seed_codi_page_options( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! premiaspine_landing_is_codi_template( $post_id ) ) {
		return;
	}

	$options = get_post_meta( $post_id, 'landing_page_codi_options', true );
	if ( ! is_array( $options ) || empty( $options ) ) {
		$legacy = get_post_meta( $post_id, 'landing_page_options', true );
		$options = premiaspine_landing_extract_codi_options_from_legacy( $legacy );
	}

	$merged = premiaspine_landing_merge_empty_options(
		is_array( $options ) ? $options : array(),
		premiaspine_landing_get_codi_default_options()
	);

	if ( $merged !== $options ) {
		update_post_meta( $post_id, 'landing_page_codi_options', $merged );
	}
}

add_action(
	'add_meta_boxes',
	function () {
		global $post;
		if ( $post instanceof WP_Post ) {
			premiaspine_landing_seed_codi_page_options( $post->ID );
		}
	},
	5
);

add_action(
	'save_post_page',
	function ( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		premiaspine_landing_seed_codi_page_options( $post_id );
	},
	30
);
