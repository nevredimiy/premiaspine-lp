<?php
namespace it_hive\core\source;

abstract class source {
	final protected function __construct() {
	}

	abstract public static function update( $key, $value, $object );

	abstract public static function delete( $key, $object );

	abstract public static function get( $key, $object);
}