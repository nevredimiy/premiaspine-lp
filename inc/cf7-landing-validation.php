<?php
/**
 * CF7: field-specific validation messages for landing contact forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function premiaspine_cf7_landing_name_field_names() {
	return apply_filters(
		'premiaspine_cf7_landing_name_field_names',
		array( 'your-name', 'name', 'fullname', 'full-name', 'full_name' )
	);
}

function premiaspine_cf7_landing_is_contact_section_form( $form ) {
	return is_string( $form ) && false !== strpos( $form, 'contact-section' );
}

add_filter( 'wpcf7_validate_text*', 'premiaspine_cf7_landing_validate_name', 20, 2 );
add_filter( 'wpcf7_validate_text', 'premiaspine_cf7_landing_validate_name', 20, 2 );

function premiaspine_cf7_landing_validate_name( $result, $tag ) {
	if ( ! is_object( $tag ) || ! method_exists( $tag, 'name' ) ) {
		return $result;
	}

	if ( ! in_array( $tag->name, premiaspine_cf7_landing_name_field_names(), true ) ) {
		return $result;
	}

	$value = isset( $_POST[ $tag->name ] ) ? trim( wp_unslash( (string) $_POST[ $tag->name ] ) ) : '';

	if ( '' === $value ) {
		$result->invalidate( $tag, __( 'Please enter full name', 'premiaspine' ) );
	}

	return $result;
}

add_filter( 'wpcf7_form_elements', 'premiaspine_cf7_landing_inject_contact_field_errors', 101 );

function premiaspine_cf7_landing_inject_contact_field_errors( $form ) {
	if ( ! premiaspine_cf7_landing_is_contact_section_form( $form ) ) {
		return $form;
	}

	$messages = apply_filters(
		'premiaspine_cf7_landing_contact_field_errors',
		array(
			'your-name' => __( 'Please enter full name', 'premiaspine' ),
			'name'      => __( 'Please enter full name', 'premiaspine' ),
			'fullname'  => __( 'Please enter full name', 'premiaspine' ),
			'full-name' => __( 'Please enter full name', 'premiaspine' ),
			'full_name' => __( 'Please enter full name', 'premiaspine' ),
		)
	);

	foreach ( $messages as $field_name => $message ) {
		if ( false === strpos( $form, 'data-name="' . $field_name . '"' ) ) {
			continue;
		}

		$pattern = '/(<span class="wpcf7-form-control-wrap" data-name="' . preg_quote( $field_name, '/' ) . '">.*?<\/span>)/s';

		$form = preg_replace_callback(
			$pattern,
			static function ( $matches ) use ( $message ) {
				if ( false !== strpos( $matches[0], 'error-text' ) ) {
					return $matches[0];
				}

				return $matches[1] . '<span class="error-text">' . esc_html( $message ) . '</span>';
			},
			$form,
			1
		);
	}

	return $form;
}
