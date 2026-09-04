<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function premiaspine_landing_get_map_filter_payload( $force_refresh = false ) {
	$cache_key = 'premiaspine_landing_map_filters_v2';

	if ( ! $force_refresh ) {
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) && ! empty( $cached['source']['doctors'] ) ) {
			return $cached;
		}
	}

	$endpoints = array(
		'https://premiaspine.com/',
		'https://www.premiaspine.com/',
	);

	$payload = null;

	foreach ( $endpoints as $endpoint ) {
		$response = wp_remote_get(
			add_query_arg( 'get_locations_map_filters', '1', $endpoint ),
			array(
				'timeout'   => 20,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			continue;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			continue;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['source'] ) || ! is_array( $body['source'] ) ) {
			continue;
		}

		$payload = array(
			'map_id' => ! empty( $body['map_id'] ) ? (string) absint( $body['map_id'] ) : '1',
			'source' => $body['source'],
		);

		if ( ! empty( $payload['source']['doctors'] ) ) {
			break;
		}
	}

	if ( is_array( $payload ) ) {
		set_transient( $cache_key, $payload, HOUR_IN_SECONDS );
	}

	return $payload;
}

function premiaspine_landing_print_map_filter_bootstrap_script() {
	if ( ! empty( $GLOBALS['premiaspine_landing_map_filters_bootstrapped'] ) ) {
		return;
	}

	$payload = premiaspine_landing_get_map_filter_payload();
	if ( empty( $payload['source'] ) || empty( $payload['source']['doctors'] ) ) {
		return;
	}

	$GLOBALS['premiaspine_landing_map_filters_bootstrapped'] = true;
	?>
	<script>
	window.WPGMP_FILTER_SOURCE = <?php echo wp_json_encode( $payload['source'] ); ?>;
	window.WPGMP_FIND_DOCTOR_MAP_ID = <?php echo wp_json_encode( $payload['map_id'] ); ?>;
	</script>
	<?php
}

add_action( 'wp_enqueue_scripts', 'premiaspine_landing_enqueue_map_filter_data', 25 );
function premiaspine_landing_enqueue_map_filter_data() {
	if ( ! is_page_template( 'landing_page_codi-for-test.php' ) ) {
		return;
	}

	$payload = premiaspine_landing_get_map_filter_payload();
	if ( empty( $payload['source'] ) || empty( $payload['source']['doctors'] ) ) {
		return;
	}

	wp_add_inline_script(
		'find-doctor-map-filters',
		'window.WPGMP_FILTER_SOURCE = ' . wp_json_encode( $payload['source'] ) . ';' .
		'window.WPGMP_FIND_DOCTOR_MAP_ID = ' . wp_json_encode( $payload['map_id'] ) . ';',
		'before'
	);

	$GLOBALS['premiaspine_landing_map_filters_bootstrapped'] = true;
}

add_action( 'wp_footer', 'premiaspine_landing_output_map_filter_bootstrap', 5 );
function premiaspine_landing_output_map_filter_bootstrap() {
	if ( ! is_page_template( 'landing_page_codi-for-test.php' ) ) {
		return;
	}

	premiaspine_landing_print_map_filter_bootstrap_script();
}
