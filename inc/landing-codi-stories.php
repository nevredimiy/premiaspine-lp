<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function premiaspine_landing_story_item_dedupe_key( $item ) {
	if ( ! is_array( $item ) ) {
		return '';
	}

	$parts = array(
		strtolower( trim( (string) premiaspine_landing_opt( $item, array( 'title' ) ) ) ),
		strtolower( trim( (string) premiaspine_landing_opt( $item, array( 'popup_youtube_url' ) ) ) ),
		strtolower( trim( (string) premiaspine_landing_opt( $item, array( 'video_link' ) ) ) ),
		strtolower( trim( (string) premiaspine_landing_opt( $item, array( 'image_url' ) ) ) ),
		(string) absint( premiaspine_landing_opt( $item, array( 'image' ) ) ),
	);

	return implode( '|', array_filter( $parts ) );
}

function premiaspine_landing_dedupe_story_items( $items ) {
	$prepared = array();
	$seen     = array();

	foreach ( (array) $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$key = premiaspine_landing_story_item_dedupe_key( $item );
		if ( '' === $key || isset( $seen[ $key ] ) ) {
			continue;
		}

		$seen[ $key ] = true;
		$prepared[]   = $item;
	}

	return $prepared;
}

function premiaspine_landing_get_remote_codi_stories_payload( $force_refresh = false ) {
	$cache_key = 'premiaspine_landing_codi_stories_v3';

	if ( ! $force_refresh ) {
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
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
			add_query_arg( 'get_landing_codi_stories', '1', $endpoint ),
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
		if ( ! is_array( $body ) ) {
			continue;
		}

		$body['ok'] = true;
		$payload    = $body;
		break;
	}

	if ( is_array( $payload ) ) {
		set_transient( $cache_key, $payload, HOUR_IN_SECONDS );
	}

	return is_array( $payload ) ? $payload : array();
}

function premiaspine_landing_merge_remote_story_section( $local_section, $remote_section, $force_remote_items = false ) {
	$local_section  = is_array( $local_section ) ? $local_section : array();
	$remote_section = is_array( $remote_section ) ? $remote_section : array();

	$local_items  = isset( $local_section['items'] ) && is_array( $local_section['items'] ) ? $local_section['items'] : array();
	$remote_items = isset( $remote_section['items'] ) && is_array( $remote_section['items'] ) ? $remote_section['items'] : array();

	if ( empty( $local_items ) && ! empty( $remote_items ) ) {
		$local_section['items'] = premiaspine_landing_dedupe_story_items( $remote_items );
	}

	if ( ! empty( $remote_section['title'] ) && empty( $local_section['title'] ) ) {
		$local_section['title'] = $remote_section['title'];
	}

	return $local_section;
}

function premiaspine_landing_apply_remote_codi_stories( $options ) {
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$remote = premiaspine_landing_get_remote_codi_stories_payload();
	if ( empty( $remote['ok'] ) ) {
		return $options;
	}

	if ( isset( $remote['patient_stories_slider'] ) ) {
		$options['patient_stories_slider'] = premiaspine_landing_merge_remote_story_section(
			isset( $options['patient_stories_slider'] ) ? $options['patient_stories_slider'] : array(),
			$remote['patient_stories_slider']
		);
	}

	if ( isset( $remote['surgeons_stories_slider'] ) ) {
		$options['surgeons_stories_slider'] = premiaspine_landing_merge_remote_story_section(
			isset( $options['surgeons_stories_slider'] ) ? $options['surgeons_stories_slider'] : array(),
			$remote['surgeons_stories_slider']
		);
	}

	return $options;
}
