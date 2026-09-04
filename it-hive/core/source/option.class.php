<?php
namespace it_hive\core\source;

class option extends source {
	public static function update( $key, $value, $object = null ) {
		update_option( $key, $value );
	}

	public static function delete( $key, $object = null ) {
		delete_option( $key );
	}

	public static function get( $key, $object = null ) {
		return get_option( $key );
	}
}