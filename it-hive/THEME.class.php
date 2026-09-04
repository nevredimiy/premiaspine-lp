<?php
namespace it_hive;

class THEME implements __init {
	use init;

	use core\enqueue;

	use adminBox;

	const enqueueSettings = array( null, null, null, null, true );

	protected static $postTypes = array();

	protected static $taxonomies = array();

	protected static $sidebars = array();

	protected static $themeMenus = array();

	public static $textdomain = 'theme_textdomain';

	protected static $bodyClasses = array();

	protected static $imageSizes = array();

	protected function __construct() {}

	public static function addPostType( $singular_name = '', $plural_name = '', $labels = array(), $args = array() ) {
        $slug = strtolower(str_replace(' ', '_', $singular_name));
        $plural_name = __( ucwords($plural_name), self::$textdomain );
        $singular_name = __( ucwords($singular_name), self::$textdomain );

        $labelsPostType = array_merge(array(
            'name'				 => $plural_name,
            'singular_name'		 => $plural_name,
            'menu_name'			 => $plural_name,
            'name_admin_bar'	 => $plural_name,
            'add_new'			 => sprintf(__( 'Add New %s', self::$textdomain ), $singular_name),
            'add_new_item'		 => sprintf(__( 'Add New %s', self::$textdomain ), $singular_name),
            'new_item'			 => sprintf(__( 'New %s', self::$textdomain ), $singular_name),
            'edit_item'			 => sprintf(__( 'Edit %s', self::$textdomain ), $singular_name),
            'view_item'			 => sprintf(__( 'View %s', self::$textdomain ), $singular_name),
            'all_items'			 => sprintf(__( 'All %s', self::$textdomain ), $plural_name),
            'search_items'		 => sprintf(__( 'Search %s', self::$textdomain ), $singular_name),
            'parent_item_colon'	 => sprintf(__( 'Parent %s', self::$textdomain ), $singular_name),
            'not_found'			 => sprintf(__( 'No %s', self::$textdomain ), $singular_name),
            'not_found_in_trash' => sprintf(__( 'No %s found in Trash.', self::$textdomain ), $singular_name)
        ), $labels );

		$argsPostType = array_merge(array(
			'labels'				=> $labelsPostType,
			'public'				=> true,
			'publicly_queryable' => true,
			'show_ui'			 => true,
			'show_in_menu'		 => true,
			'query_var'			 => true,
			'rewrite'			 => array( 'slug' => $slug ),
			'capability_type'	 => 'page',
			'has_archive'		 => true,
			'hierarchical'		 => true,
			'menu_position'		 => null,
			'taxonomies'		 => array(),
			'supports'			 => array( 'title', 'editor', 'page-attributes', 'thumbnail', 'excerpt' )
		), $args );

		if( $singular_name && $plural_name) {
			self::$postTypes[$slug] = $argsPostType;
		}
	}

	public static function addTaxonomy( $taxonomy = '', $singular_name = '' , $plural_name = '', $to = null, $labels = array(), $args = array() ) {
        $taxonomy = strtolower(str_replace(' ', '_', $taxonomy));
        $singular_name = __( ucwords($singular_name), self::$textdomain );
        $plural_name = __( ucwords($plural_name), self::$textdomain );

        $labelsTaxonomy = array_merge(array(
            'name'				=> $plural_name,
            'singular_name'		=> $plural_name,
            'search_items'		=> sprintf(__( 'Search %s', self::$textdomain ), $singular_name),
            'all_items'			=> sprintf(__( 'All %s', self::$textdomain ), $plural_name),
            'parent_item'		=> sprintf(__( 'Parent %s', self::$textdomain ), $singular_name),
            'parent_item_colon'	=> sprintf(__( 'Parent %s:', self::$textdomain ), $singular_name),
            'edit_item'			=> sprintf(__( 'Edit %s', self::$textdomain ), $singular_name),
            'update_item'		=> sprintf(__( 'Update %s', self::$textdomain ), $singular_name),
            'add_new_item'		=> sprintf(__( 'Add New %s', self::$textdomain ), $singular_name),
            'new_item_name'		=> sprintf(__( 'New %s', self::$textdomain ), $singular_name),
            'menu_name'			=> $plural_name,
        ), $labels);

		$argsTaxonomy = array_merge(array(
			'hierarchical'		=> true,
			'labels'			=> $labelsTaxonomy,
			'show_ui'			=> true,
			'show_admin_column'	=> true,
			'query_var'			=> true
		), $args);

		if( $singular_name && $plural_name && $to ) {
			self::$taxonomies[$taxonomy]['args'] = $argsTaxonomy;
			self::$taxonomies[$taxonomy]['to'] = $to;
		}
	}

	protected static function registerPostTypes() {
		foreach( self::$postTypes as $slug => $args ) {
			register_post_type( $slug, $args );
		}
	}

	protected static function registerTaxonomies() {
		foreach( self::$taxonomies as $slug => $data ) {
			register_taxonomy( $slug, $data['to'], $data['args'] );
		}
	}

	public static function addSidebar( $id = '', $name = '', $args = array() ) {
		$id = strtolower(str_replace(' ', '_', $id));
		$name = ucwords($name);

		$argsSidebar = array_merge(array(
			'id'			=> $id,
			'name'			=> __( $name, self::$textdomain ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="title">',
			'after_title'	=> '</h3>'
		), $args);

		if( $id && $name) {
			self::$sidebars[$id] = $argsSidebar;
		}
	}

	protected static function registerSidebars() {
		if( function_exists('register_sidebar') ) {
			foreach( self::$sidebars as $id => $data ) {
				register_sidebar( $data );
			}
		}
	}

	public static function addMenus( $menus = array() ) {
		foreach( $menus as $key => $menu ) {
			$name = ucwords($menu);
			$id = strtolower(str_replace(' ', '_', $key));
			self::$themeMenus[$id] = array(
				$id	=> __( $name, self::$textdomain )
			);
		}
	}

	protected static function registerMenus() {
		foreach( self::$themeMenus as $id => $data ) {
			register_nav_menus( $data );
		}
	}

	public static function load_text_domain() {
		load_theme_textdomain( self::$textdomain );
	}

	public static function addBodyClasses( $classes = array() ) {
		foreach( $classes as $key => $class ) {
			self::$bodyClasses[$key] = $class;
		}
	}

	public static function themeBodyClasses( $classes ) {
		foreach( $classes as $value ) {
			foreach( self::$bodyClasses as $key => $class ) {
				if( $value == $key ) {
					$classes[] = $class;
				}
			}
		}
		return $classes;
	}

	public static function addImageSizes( $name = '', $width = '', $height = '', $crop = false ) {
		$name = strtolower(str_replace(' ', '_', $name));
		if( $name && $width && $height ) {
			self::$imageSizes[$name] = array(
				'width'		=> $width,
				'height'	=> $height,
				'crop'		=> $crop
			);
		}
	}

	protected static function registerImageSize() {
		foreach( self::$imageSizes as $key => $value ) {
			add_image_size( $key, $value['width'], $value['height'], $value['crop'] );
		}
	}

	public static function add_svg_to_upload_mimes( $upload_mimes ) {
		$upload_mimes['svg'] = 'image/svg+xml';
		$upload_mimes['svgz'] = 'image/svg+xml';
		return $upload_mimes;
	}

	public static function supportPostThumbnails() {
		add_theme_support( 'post-thumbnails' );
	}

	public  static function addPostFormats( $args = array() ) {
		add_theme_support( 'post-formats', $args );
	}

	protected function registerActions() {
		add_action( 'init', array( self::class, 'WPInit' ) );
		add_action( 'after_setup_theme', array( self::class, 'load_text_domain' ) );
		add_filter( 'body_class', array( self::class, 'themeBodyClasses' ) );
		add_filter( 'upload_mimes', array( self::class, 'add_svg_to_upload_mimes' ) );
	}

	public static function WPInit() {
		self::registerPostTypes();
		self::registerTaxonomies();
		self::registerSidebars();
		self::registerMenus();
		self::registerImageSize();
		self::supportPostThumbnails();
	}

	protected static function init() {
		require_once('helper.php');
		(new THEME())->registerActions();
		self::initEnqueue();
		self::addPostFormats();
	}

}
