<?php

require 'vendor/autoload.php';

use GeoIp2\Database\Reader;

function get_country_by_ip() {
	$ip = '';
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED'];
	} else if ( isset( $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_FORWARDED_FOR'];
	} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
		$ip = $_SERVER['HTTP_FORWARDED'];
	} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip = 'UNKNOWN';
	}
	$country = 'unknown country top';
	$state  = 'unknown record top';

//    if(!empty($_COOKIE['city']) && !empty($_COOKIE['country'])){
//        $state  = $_COOKIE['city'];
//        $country  = $_COOKIE['country'];
//    }
//    else{
//        try {
//            $reader  = new Reader( dirname( __FILE__ ) . '/GeoLite2-Country.mmdb' );
//            $reader2  = new Reader( dirname( __FILE__ ) . '/GeoIP2-City.mmdb' );
//            $country = $reader->country( $ip )->country->name;
//            $state = $reader2->city( $ip )->mostSpecificSubdivision->name;
//            $reader->close();
//            $reader2->close();
//        } catch ( Exception $e ) {
//            $country = 'unknown country';
//            $state  = 'unknown record';
//        }
//    }

	return [ 'country'=>$country, 'state'=>$state , 'ip'=>$ip ];
}

function stateByIp() {
	$result = get_country_by_ip();
	return $result['state'];

}

add_shortcode( 'stateByIp', 'stateByIp' );

function countryByIp() {
	$result = get_country_by_ip();
	return $result['country'];

}

add_shortcode( 'countryByIp', 'countryByIp' );

function userIp() {
	$result = get_country_by_ip();
	return $result['ip'];

}

add_shortcode( 'userIp', 'userIp' );

function get_source_from_url_param() {
    if (isset($_GET['source'])) {
        return sanitize_text_field($_GET['source']);
    }
    return '';
}
add_shortcode('source_from_url', 'get_source_from_url_param');
