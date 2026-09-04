<?php
/**
 * Use CF7 type-specific validation messages for empty required tel/email/url fields.
 *
 * By default SWV applies the generic invalid_required message to empty fields.
 * This maps the required rule to invalid_tel / invalid_email / invalid_url
 * so empty fields show the same texts as configured in the form Messages tab.
 */

add_action( 'wpcf7_swv_create_schema', 'premiaspine_cf7_swv_required_messages_by_type', 20, 2 );

function premiaspine_cf7_swv_required_messages_by_type( $schema, $contact_form ) {
	$type_message_keys = array(
		'tel'   => 'invalid_tel',
		'email' => 'invalid_email',
		'url'   => 'invalid_url',
	);

	$tags_by_name = array();

	foreach ( $contact_form->scan_form_tags() as $tag ) {
		if ( ! empty( $tag->name ) ) {
			$tags_by_name[ $tag->name ] = $tag;
		}
	}

	foreach ( $schema->rules() as $rule ) {
		if ( ! $rule instanceof \Contactable\SWV\RequiredRule ) {
			continue;
		}

		$field = $rule->get_property( 'field' );

		if ( empty( $field ) || empty( $tags_by_name[ $field ] ) ) {
			continue;
		}

		$basetype = $tags_by_name[ $field ]->basetype;

		if ( empty( $type_message_keys[ $basetype ] ) ) {
			continue;
		}

		$message = $contact_form->message( $type_message_keys[ $basetype ] );

		if ( '' === $message ) {
			continue;
		}

		premiaspine_cf7_swv_rule_set_error( $rule, $message );
	}
}

/**
 * @param \Contactable\SWV\Rule $rule SWV rule instance.
 * @param string                $message Validation message from CF7 form settings.
 */
function premiaspine_cf7_swv_rule_set_error( $rule, $message ) {
	$reflection = new ReflectionObject( $rule );
	$property   = $reflection->getProperty( 'properties' );

	$property->setAccessible( true );

	$properties         = $property->getValue( $rule );
	$properties['error'] = $message;

	$property->setValue( $rule, $properties );
}
