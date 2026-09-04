<?php
namespace it_hive\core\source;

class userMeta extends source {
	public static function update( $key, $value, $object ) {
		update_user_meta( $object, $key, $value );
	}

	public static function delete( $key, $object ) {
		 delete_user_meta( $object, $key );
	}

	public static function get( $key, $object ) {
		return  get_user_meta( $object, $key, true);
	}
}