<?php
namespace it_hive\core\source;

class meta extends source {
	public static function update( $key, $value, $object ) {
		update_post_meta( $object, $key, $value );
	}

	public static function delete( $key, $object ) {
		delete_post_meta( $object, $key );
	}

	public static function get( $key, $object ) {
		return get_post_meta( $object, $key, true);
	}
}