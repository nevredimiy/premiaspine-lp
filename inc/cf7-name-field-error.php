<?php
/**
 * CF7: per-form setting for the name field validation error message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PREMIASPINE_CF7_NAME_ERROR_PROP       = 'premiaspine_name_error_message';
const PREMIASPINE_CF7_NAME_FIELD_PROP       = 'premiaspine_name_field_name';
const PREMIASPINE_CF7_NAME_ERROR_META       = '_premiaspine_name_error_message';
const PREMIASPINE_CF7_NAME_FIELD_META       = '_premiaspine_name_field_name';
const PREMIASPINE_CF7_NAME_ERROR_PANEL_KEY  = 'premiaspine-name-error-panel';

function premiaspine_cf7_default_name_field_names() {
	return apply_filters(
		'premiaspine_cf7_default_name_field_names',
		array( 'first_name', 'your-name', 'name', 'fullname', 'full-name', 'full_name' )
	);
}

function premiaspine_cf7_get_submitted_contact_form() {
	if ( class_exists( 'WPCF7_ContactForm' ) ) {
		$contact_form = WPCF7_ContactForm::get_current();
		if ( $contact_form ) {
			return $contact_form;
		}
	}

	$form_id = isset( $_POST['_wpcf7'] ) ? absint( $_POST['_wpcf7'] ) : 0;

	if ( $form_id > 0 && function_exists( 'wpcf7_contact_form' ) ) {
		return wpcf7_contact_form( $form_id );
	}

	return null;
}

function premiaspine_cf7_get_name_error_message( $contact_form ) {
	if ( ! $contact_form || ! method_exists( $contact_form, 'prop' ) ) {
		return '';
	}

	$message = trim( (string) $contact_form->prop( PREMIASPINE_CF7_NAME_ERROR_PROP ) );

	if ( '' === $message && method_exists( $contact_form, 'id' ) ) {
		$message = trim( (string) get_post_meta( $contact_form->id(), PREMIASPINE_CF7_NAME_ERROR_META, true ) );
	}

	return $message;
}

function premiaspine_cf7_get_name_field_name( $contact_form ) {
	if ( ! $contact_form || ! method_exists( $contact_form, 'prop' ) ) {
		return '';
	}

	$field_name = trim( (string) $contact_form->prop( PREMIASPINE_CF7_NAME_FIELD_PROP ) );

	if ( '' === $field_name && method_exists( $contact_form, 'id' ) ) {
		$field_name = trim( (string) get_post_meta( $contact_form->id(), PREMIASPINE_CF7_NAME_FIELD_META, true ) );
	}

	return sanitize_key( $field_name );
}

function premiaspine_cf7_resolve_name_field_names( $contact_form ) {
	$custom_name = premiaspine_cf7_get_name_field_name( $contact_form );

	if ( '' !== $custom_name ) {
		return array( $custom_name );
	}

	return premiaspine_cf7_default_name_field_names();
}

add_filter( 'wpcf7_pre_construct_contact_form_properties', 'premiaspine_cf7_register_name_error_properties', 10, 2 );

function premiaspine_cf7_register_name_error_properties( $properties, $contact_form ) {
	$properties[ PREMIASPINE_CF7_NAME_ERROR_PROP ] = isset( $properties[ PREMIASPINE_CF7_NAME_ERROR_PROP ] )
		? (string) $properties[ PREMIASPINE_CF7_NAME_ERROR_PROP ]
		: '';
	$properties[ PREMIASPINE_CF7_NAME_FIELD_PROP ] = isset( $properties[ PREMIASPINE_CF7_NAME_FIELD_PROP ] )
		? (string) $properties[ PREMIASPINE_CF7_NAME_FIELD_PROP ]
		: '';

	if ( $contact_form && method_exists( $contact_form, 'id' ) ) {
		$post_id = (int) $contact_form->id();

		if ( $post_id > 0 && '' === $properties[ PREMIASPINE_CF7_NAME_ERROR_PROP ] ) {
			$properties[ PREMIASPINE_CF7_NAME_ERROR_PROP ] = (string) get_post_meta( $post_id, PREMIASPINE_CF7_NAME_ERROR_META, true );
		}

		if ( $post_id > 0 && '' === $properties[ PREMIASPINE_CF7_NAME_FIELD_PROP ] ) {
			$properties[ PREMIASPINE_CF7_NAME_FIELD_PROP ] = (string) get_post_meta( $post_id, PREMIASPINE_CF7_NAME_FIELD_META, true );
		}
	}

	return $properties;
}

add_filter( 'wpcf7_editor_panels', 'premiaspine_cf7_add_name_error_panel' );

function premiaspine_cf7_add_name_error_panel( $panels ) {
	$panels[ PREMIASPINE_CF7_NAME_ERROR_PANEL_KEY ] = array(
		'title'    => __( 'Name Field Error', 'premiaspine' ),
		'callback' => 'premiaspine_cf7_render_name_error_panel',
	);

	return $panels;
}

function premiaspine_cf7_render_name_error_panel( $contact_form ) {
	$message    = premiaspine_cf7_get_name_error_message( $contact_form );
	$field_name = premiaspine_cf7_get_name_field_name( $contact_form );
	$defaults   = implode( ', ', premiaspine_cf7_default_name_field_names() );
	?>
	<h2><?php esc_html_e( 'Name Field Error', 'premiaspine' ); ?></h2>
	<fieldset>
		<legend><?php esc_html_e( 'Custom validation message for the name field', 'premiaspine' ); ?></legend>
		<p class="description">
			<?php esc_html_e( 'Fill in both fields below and save the form. The message is used only for this contact form.', 'premiaspine' ); ?>
		</p>
		<p>
			<label for="premiaspine-name-error-message">
				<strong><?php esc_html_e( 'Error message', 'premiaspine' ); ?></strong>
			</label><br>
			<input
				type="text"
				class="large-text"
				id="premiaspine-name-error-message"
				name="<?php echo esc_attr( PREMIASPINE_CF7_NAME_ERROR_PROP ); ?>"
				value="<?php echo esc_attr( $message ); ?>"
				placeholder="<?php esc_attr_e( 'Please enter full name', 'premiaspine' ); ?>"
			>
		</p>
		<p>
			<label for="premiaspine-name-field-name">
				<strong><?php esc_html_e( 'Name field tag', 'premiaspine' ); ?></strong>
			</label><br>
			<input
				type="text"
				class="regular-text"
				id="premiaspine-name-field-name"
				name="<?php echo esc_attr( PREMIASPINE_CF7_NAME_FIELD_PROP ); ?>"
				value="<?php echo esc_attr( $field_name ); ?>"
				placeholder="first_name"
			>
		</p>
		<p class="description">
			<?php
			printf(
				/* translators: %s: comma-separated CF7 field names */
				esc_html__( 'For your form use: first_name. If the tag field is empty, these names are checked: %s', 'premiaspine' ),
				esc_html( $defaults )
			);
			?>
		</p>
	</fieldset>
	<?php
}

add_action( 'wpcf7_save_contact_form', 'premiaspine_cf7_save_name_error_settings', 10, 3 );

function premiaspine_cf7_save_name_error_settings( $contact_form, $args, $context ) {
	if ( ! in_array( $context, array( 'save', 'copy' ), true ) ) {
		return;
	}

	if ( ! $contact_form || ! method_exists( $contact_form, 'id' ) || ! method_exists( $contact_form, 'set_properties' ) ) {
		return;
	}

	$post_id = (int) $contact_form->id();
	if ( $post_id <= 0 ) {
		return;
	}

	$message    = premiaspine_cf7_read_name_error_setting_from_request( PREMIASPINE_CF7_NAME_ERROR_PROP );
	$field_name = sanitize_key( premiaspine_cf7_read_name_error_setting_from_request( PREMIASPINE_CF7_NAME_FIELD_PROP ) );

	update_post_meta( $post_id, PREMIASPINE_CF7_NAME_ERROR_META, $message );
	update_post_meta( $post_id, PREMIASPINE_CF7_NAME_FIELD_META, $field_name );

	$properties = $contact_form->get_properties();
	$properties[ PREMIASPINE_CF7_NAME_ERROR_PROP ] = $message;
	$properties[ PREMIASPINE_CF7_NAME_FIELD_PROP ]  = $field_name;
	$contact_form->set_properties( $properties );
}

function premiaspine_cf7_read_name_error_setting_from_request( $key ) {
	if ( isset( $_POST[ $key ] ) ) {
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	}

	if ( isset( $_POST['wpcf7'][ $key ] ) ) {
		return sanitize_text_field( wp_unslash( $_POST['wpcf7'][ $key ] ) );
	}

	if ( isset( $_POST['wpcf7-properties'][ $key ] ) ) {
		return sanitize_text_field( wp_unslash( $_POST['wpcf7-properties'][ $key ] ) );
	}

	return '';
}

function premiaspine_cf7_apply_name_field_error( $result, $tag ) {
	if ( ! is_object( $result ) || ! method_exists( $result, 'invalidate' ) ) {
		return $result;
	}

	if ( ! is_object( $tag ) || empty( $tag->name ) ) {
		return $result;
	}

	$contact_form = premiaspine_cf7_get_submitted_contact_form();
	if ( ! $contact_form ) {
		return $result;
	}

	$message = premiaspine_cf7_get_name_error_message( $contact_form );
	if ( '' === $message ) {
		return $result;
	}

	if ( ! in_array( $tag->name, premiaspine_cf7_resolve_name_field_names( $contact_form ), true ) ) {
		return $result;
	}

	$value = isset( $_POST[ $tag->name ] ) ? trim( wp_unslash( (string) $_POST[ $tag->name ] ) ) : '';

	if ( '' === $value ) {
		$result->invalidate( $tag, $message );
	}

	return $result;
}

add_filter( 'wpcf7_validate_text*', 'premiaspine_cf7_apply_name_field_error', 20, 2 );
add_filter( 'wpcf7_validate_text', 'premiaspine_cf7_apply_name_field_error', 20, 2 );

add_filter( 'wpcf7_validate', 'premiaspine_cf7_validate_name_field_on_form', 20, 2 );

function premiaspine_cf7_validate_name_field_on_form( $result, $tags ) {
	if ( ! is_array( $tags ) ) {
		return $result;
	}

	foreach ( $tags as $tag ) {
		$result = premiaspine_cf7_apply_name_field_error( $result, $tag );
	}

	return $result;
}

add_filter( 'wpcf7_feedback_response', 'premiaspine_cf7_patch_name_field_feedback', 10, 2 );

function premiaspine_cf7_patch_name_field_feedback( $response, $result ) {
	$contact_form = premiaspine_cf7_get_submitted_contact_form();
	if ( ! $contact_form ) {
		return $response;
	}

	$message = premiaspine_cf7_get_name_error_message( $contact_form );
	if ( '' === $message || empty( $response['invalid_fields'] ) || ! is_array( $response['invalid_fields'] ) ) {
		return $response;
	}

	$field_names = premiaspine_cf7_resolve_name_field_names( $contact_form );

	foreach ( $response['invalid_fields'] as $index => $field ) {
		if ( empty( $field['field'] ) || ! in_array( $field['field'], $field_names, true ) ) {
			continue;
		}

		$response['invalid_fields'][ $index ]['message'] = $message;
	}

	return $response;
}

add_action( 'wp_footer', 'premiaspine_cf7_name_field_error_script', 99 );

function premiaspine_cf7_name_field_error_script() {
	if ( ! function_exists( 'wpcf7_contact_form' ) ) {
		return;
	}
	?>
	<script id="premiaspine-cf7-name-field-error">
	(function () {
		document.addEventListener('wpcf7invalid', function (event) {
			var formEl = event.target;
			if (!formEl || !event.detail || !Array.isArray(event.detail.apiResponse.invalid_fields)) {
				return;
			}

			event.detail.apiResponse.invalid_fields.forEach(function (field) {
				if (!field || !field.field || !field.message) {
					return;
				}

				var wrap = formEl.querySelector('.wpcf7-form-control-wrap[data-name="' + field.field + '"]');
				if (!wrap) {
					return;
				}

				var tip = wrap.querySelector('.wpcf7-not-valid-tip');
				if (tip) {
					tip.textContent = field.message;
				}
			});
		});
	})();
	</script>
	<?php
}
