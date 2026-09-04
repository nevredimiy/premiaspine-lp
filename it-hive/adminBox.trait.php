<?php
namespace it_hive;

trait adminBox {
	protected static $mataboxes = array();

	protected static $termBoxes = array();

	protected static $userBoxes = array();

	protected static $optionsPages = array();

	public static function loadMetaBox( $name ) {
		if( $name && empty(self::$mataboxes[$name] )) {
			$_class = '\\it_hive\\metaBoxes\\' . $name . '\\MetaBox';
			self::$mataboxes[$name] = new $_class();
		}
	}

	public static function loadTermBox( $name ) {
		if( $name && !self::$termBoxes[$name] ) {
			$_class = '\\it_hive\\termBoxes\\' . $name . '\\TermBox';
			self::$termBoxes[$name] = new $_class();
		}
	}

	public static function loadUserBox( $name ) {
		if( $name && !self::$userBoxes[$name] ) {
			$_class = '\\it_hive\\userBoxes\\' . $name . '\\UserBox';
			self::$userBoxes[$name] = new $_class();
		}
	}

	public static function addOptionsPage( $name ) {
		if( $name && empty(self::$optionsPages[$name]) ) {
			$_class = '\\it_hive\\options\\' . $name . '\\OptionsPage';
			self::$optionsPages[$name] = new $_class();
		}
	}

	public static function getTermBox($name){
		return self::$termBoxes[$name];
	}

	public static function getMetaBox($name){
		return self::$mataboxes[$name];
	}
	public static function getUserBox($name){
		return self::$userBoxes[$name];
	}
}