<?php
namespace it_hive;

trait init {
	protected static $initialized = array();

	protected static function init() {}

	public static function __init() {
		if( empty(static::$initialized[static::class]) ) {
			static::init();
			static::$initialized[static::class] = true;
		}
	}
}