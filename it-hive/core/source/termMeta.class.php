<?php
namespace it_hive\core\source;

class termMeta extends source {
	public static function update( $key, $value, $object ) {
		update_term_meta( $object, $key, $value );
	}

	public static function delete( $key, $object ) {
		 delete_term_meta( $object, $key );
	}

	public static function get( $key, $object ) {
		return  get_term_meta( $object, $key, true);
	}
}