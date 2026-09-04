<?php
namespace it_hive\core;

trait enqueue {

	protected static $adminScripts = array();

	protected static $frontendScripts = array();

	protected static $inline = array();

	protected static $adminStyles = array();

	protected static $frontendStyles = array();

	public static function addThemeStyles( $styles = array() ) {
		foreach( $styles as $key => $style ) {
			self::$frontendStyles[$key] = $style;
		}
	}

	public static function addThemeScripts( $scripts = array() ) {
		foreach( $scripts as $key => $script ) {
			self::$frontendScripts[$key] = $script;
		}
	}

	public static function addAdminScripts() {
		self::enqueueScripts( self::$adminScripts );
	}

	public static function addAdminStyles() {
		self::enqueueStyles( self::$adminStyles );
	}

	public static function addFrontendScriptsStyles() {
		self::enqueueScripts( self::$frontendScripts );
		self::enqueueStyles( self::$frontendStyles );
	}

	public static function enqueueScripts( $scripts = array() ) {
		foreach( $scripts as $name => $params ) {
			if( !is_array($params) ) {
				$params = array( $params );
			}
			// TODO : надо подумать над этим
			//$params[0] = DIRECTORY_SEPARATOR == '\\' ? $params[0] : str_replace( '/', DIRECTORY_SEPARATOR, $params[0] );
			call_user_func_array('wp_enqueue_script', array_merge(
					array( $name ),
					$params
				) + self::enqueueSettings);
		}
	}

	public static function enqueueStyles( $styles = array() ) {
		foreach( $styles as $name => $params ) {
			if( !is_array($params) ) {
				$params = array( $params );
			}
			// TODO : надо подумать над этим
			//$params[0] = DIRECTORY_SEPARATOR == '\\' ? $params[0] : str_replace( '/', DIRECTORY_SEPARATOR, $params[0] );
			call_user_func_array('wp_enqueue_style', array_merge(
					array( $name ),
					$params
				) );
		}
	}

	public static function enqueueInline() {
		echo( join(self::$inline) );
	}

	private static function registerEnqueueActions() {
		//add_action('admin_enqueue_scripts',array(__CLASS__,'addAdminScripts'));
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'addFrontendScriptsStyles' ) );
		//add_action( 'admin_head', array( __CLASS__, 'addAdminStyles' ) );
		add_action( 'admin_footer', array( __CLASS__, 'addAdminStyles' ) );
		add_action( 'admin_footer', array( __CLASS__, 'addAdminScripts' ), -10 );
		add_action( 'admin_footer', array( __CLASS__, 'enqueueInline' ) );
	}

	protected static function normalizeUrls( &$urls = array(), $base ) {
		foreach( $urls as $name => $params ) {
			$params = is_array($params) ? $params : array( $params );
			if( $params[0] ) {
				$urlInfo = parse_url( $params[0] );
				if( $urlInfo && empty($urlInfo['host']) ) {
					$params[0] = $base . $params[0];
				}
			}
			$urls[$name] = $params;
		}
	}

	public static function defineStatic() {
		$reflection = new \ReflectionClass(static::class);
		$webDir = str_replace(ABSPATH, '/', dirname($reflection->getFileName()));
		$jsDir = $webDir . '/assets/js/';
		$cssDir = $webDir . '/assets/css/';
		if(!empty($reflection->getParentClass()->name)){
            $parent = $reflection->getParentClass()->name;
        }
		else{
		    $parent = false;
        }

		if( is_admin() ) {
			if( !$parent || $parent::$adminScripts != static::$adminScripts ) {
				self::normalizeUrls( static::$adminScripts, $jsDir );
				self::$adminScripts += static::$adminScripts;
			}
			if( !$parent || $parent::$adminStyles != static::$adminStyles ) {
				self::normalizeUrls( static::$adminStyles, $cssDir );
				self::$adminStyles += static::$adminStyles;
			}
			self::$inline += static::$inline;
		} else {
			if( !$parent || $parent::$frontendScripts != static::$frontendScripts ) {
				self::normalizeUrls( static::$frontendScripts, $jsDir );
				self::$frontendScripts += static::$frontendScripts;
			}
			if( !$parent || $parent::$frontendStyles != static::$frontendStyles ) {
				self::normalizeUrls( static::$frontendStyles, $cssDir );
				self::$frontendStyles += static::$frontendStyles;
			}
		}
	}

	public static function initEnqueue() {
		self::registerEnqueueActions();
		self::defineStatic();
	}
}