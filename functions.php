<?php
use it_hive\THEME;

require_once( 'it-hive/loader.php' );
header("Access-Control-Allow-Origin: *");
$directory_uri = get_template_directory_uri();
require_once( 'inc/redirect.php');
require_once( 'inc/landing-codi-helpers.php');
require_once( 'inc/landing-codi-stories.php');
require_once( 'inc/landing-codi-map-filters.php');
require_once( 'inc/landing-codi-chatbot.php');
require_once( 'inc/cf7-validation-messages.php');

ini_set( 'display_errors', 1 );
define( 'text_domain', 'premiaspine' );


$styles        = array(
	'all-style' => $directory_uri . '/css/all.css?v=' . time(),
	'style'     => $directory_uri . '/style.css?v=' . time(),
    //'jquery.fancybox.min' => $directory_uri . '/css/jquery.fancybox.min.css?v=' . time(),
);
$scripts       = array(
    'jquery'  => '',
    'jquery-ui-accordion' => '',
    'jquery-ui-sortable' => '',
    'jquery.fancybox.min' => $directory_uri . '/js/jquery.fancybox.min.js?v=' . time(),
    'ajax' => $directory_uri . '/js/ajax.js?v=' . time(),
);
THEME::addThemeStyles( $styles );
THEME::addThemeScripts( $scripts );
THEME::$textdomain = text_domain;

add_action( 'wp_enqueue_scripts', 'premiaspine_enqueue_landing_codi_assets', 20 );
function premiaspine_enqueue_landing_codi_assets() {
	if ( ! is_page_template( 'landing_page_codi-for-test.php' ) ) {
		return;
	}

	$theme_dir      = get_template_directory();
	$theme_uri      = get_template_directory_uri();
	$main_theme_uri = 'https://premiaspine.com/wp-content/themes/premiaspine';
	$css_file       = $theme_dir . '/css/landing.css';
	$js_file        = $theme_dir . '/js/landing.js';

	wp_enqueue_style(
		'find-doctor-map-filters',
		$main_theme_uri . '/assets/css/find-doctor-map-filters.css',
		array(),
		null
	);

	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'premiaspine-landing',
			$theme_uri . '/css/landing.css',
			array( 'all-style', 'find-doctor-map-filters' ),
			filemtime( $css_file )
		);
	}

	if ( file_exists( $js_file ) ) {
		wp_enqueue_script(
			'premiaspine-landing',
			$theme_uri . '/js/landing.js',
			array( 'jquery' ),
			filemtime( $js_file ),
			true
		);
	}

	$story_popups_js = $theme_dir . '/js/landing-story-popups.js';
	if ( file_exists( $story_popups_js ) ) {
		wp_enqueue_script(
			'premiaspine-landing-story-popups',
			$theme_uri . '/js/landing-story-popups.js',
			array(),
			filemtime( $story_popups_js ),
			true
		);
	}

	wp_enqueue_script(
		'find-doctor-map-filters',
		$main_theme_uri . '/assets/js/find-doctor-map-filters.js',
		array( 'jquery' ),
		null,
		true
	);
	wp_localize_script(
		'find-doctor-map-filters',
		'wpgmpFindDoctorFilters',
		array(
			'markerScaleWithZoom' => true,
		)
	);

	premiaspine_landing_enqueue_wpgmp_styles();
}

function premiaspine_landing_enqueue_wpgmp_styles() {
	$plugin_base = 'https://premiaspine.com/wp-content/plugins/wp-google-map-gold';

	wp_enqueue_style(
		'wpgmp-frontend',
		$plugin_base . '/assets/css/frontend.min.css',
		array( 'premiaspine-landing' ),
		'6.4.2'
	);
	wp_enqueue_style(
		'fc-wpgmp-infowindow-default',
		$plugin_base . '/templates/infowindow/default/default.css',
		array( 'wpgmp-frontend' ),
		'6.4.2'
	);
	wp_enqueue_style(
		'fc-wpgmp-post-default',
		$plugin_base . '/templates/post/default/default.css',
		array( 'wpgmp-frontend' ),
		'6.4.2'
	);
	wp_enqueue_style(
		'fc-wpgmp-item-default',
		$plugin_base . '/templates/item/default/default.css',
		array( 'wpgmp-frontend' ),
		'6.4.2'
	);
}

function premiaspine_landing_sanitize_remote_map_markup( $html ) {
	if ( ! is_string( $html ) || $html === '' ) {
		return '';
	}

	// Only strip stylesheet links — they break global page styles in <body>.
	// Keep inline map init scripts from the remote response.
	return preg_replace( '/<link\b[^>]*>/i', '', $html );
}

function premiaspine_landing_print_wpgmp_runtime_assets() {
	$plugin_base = 'https://premiaspine.com/wp-content/plugins/wp-google-map-gold';
	?>
	<script src="https://maps.google.com/maps/api/js?key=AIzaSyCW4AVDKtIIiSrTSh880d2UhcMxs4GiJ8M&amp;libraries=geometry%2Cplaces%2Cweather%2Cpanoramio%2Cdrawing&amp;language=en&amp;ver=5.3.3" id="wpgmp-google-api-js"></script>
	<script src="<?php echo esc_url( $plugin_base . '/assets/js/maps.min.js?ver=5.3.3' ); ?>" id="wpgmp-google-map-main-js"></script>
	<script id="wpgmp-google-map-main-js-extra">
	var wpgmp_local = {"ajax_url":"https:\/\/premiaspine.com\/wp-admin\/admin-ajax.php","wpgmp_location_no_results":"No results found.","place_icon_url":"https:\/\/premiaspine.com\/wp-content\/plugins\/wp-google-map-gold\/assets\/images\/icons\/"};
	</script>
	<script src="<?php echo esc_url( $plugin_base . '/assets/js/frontend.min.js?ver=5.3.3' ); ?>" id="wpgmp-frontend-js"></script>
	<?php
}

// ДОБАВЛЯЕМ ВКЛАДКУ TESTIMONIALS
THEME::addPostType(
    'Testimonial',      // singular — slug будет testimonial
    'Testimonials',     // plural — название в меню
    array(),
    array(
        'public'              => false,   // не нужны отдельные URL на сайте
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'has_archive'         => false,
        'hierarchical'        => false,
        'supports'            => array( 'title' ), // заголовок = имя карточки в админке
        'menu_icon'           => 'dashicons-format-quote',
    )
);

THEME::addTaxonomy(
    'story_category',       // slug таксономии
    'Story category',       // singular
    'Story categories',     // plural
    'testimonial',          // привязка к CPT
    array(),
    array(
        'publicly_queryable' => false,
    )
);

//THEME::addOptionsPage( 'option_contact_form' );
THEME::loadMetaBox( 'testimonial' );
THEME::loadMetaBox('landing');
THEME::loadMetaBox('thank_you');

// Очистка legacy story items до рендера metabox (иначе белый экран).
add_action( 'load-post.php', 'premiaspine_landing_sanitize_story_slider_meta_early' );
function premiaspine_landing_sanitize_story_slider_meta_early() {
	if ( ! isset( $_GET['post'] ) ) {
		return;
	}

	$post_id = (int) $_GET['post'];
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return;
	}

	\it_hive\metaBoxes\landing\MetaBox::sanitize_legacy_story_slider_items_for_admin( $post_id );
}

//Показ только нужных полей для нового template
add_action( 'admin_enqueue_scripts', 'premiaspine_landing_metabox_admin_assets' );
function premiaspine_landing_metabox_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$css_file  = $theme_dir . '/css/landing-metabox-admin.css';
	$js_file   = $theme_dir . '/js/landing-metabox-admin.js';
	$select_posts_css = $theme_dir . '/it-hive/core/Blocks/selectPosts/assets/css/main.css';
	$select_posts_js  = $theme_dir . '/it-hive/core/Blocks/selectPosts/assets/js/main.js';

	if ( file_exists( $select_posts_css ) ) {
		wp_enqueue_style(
			'select-post',
			$theme_uri . '/it-hive/core/Blocks/selectPosts/assets/css/main.css',
			array(),
			filemtime( $select_posts_css )
		);
	}

	if ( file_exists( $css_file ) ) {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style(
			'premiaspine-landing-metabox-admin',
			$theme_uri . '/css/landing-metabox-admin.css',
			array( 'dashicons', 'select-post' ),
			filemtime( $css_file )
		);
	}

	if ( file_exists( $js_file ) ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			'premiaspine-landing-metabox-admin',
			$theme_uri . '/js/landing-metabox-admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			filemtime( $js_file ),
			true
		);
	}

	if ( file_exists( $select_posts_js ) ) {
		wp_enqueue_script(
			'select-post',
			$theme_uri . '/it-hive/core/Blocks/selectPosts/assets/js/main.js',
			array( 'jquery' ),
			filemtime( $select_posts_js ),
			true
		);
	}
}

add_action( 'admin_enqueue_scripts', 'premiaspine_testimonial_metabox_admin_assets' );
function premiaspine_testimonial_metabox_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'testimonial' !== $screen->post_type ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$js_file   = $theme_dir . '/js/testimonial-metabox-admin.js';

	if ( ! file_exists( $js_file ) ) {
		return;
	}

	wp_enqueue_script(
		'premiaspine-testimonial-metabox-admin',
		$theme_uri . '/js/testimonial-metabox-admin.js',
		array( 'jquery' ),
		filemtime( $js_file ),
		true
	);
}

add_action( 'admin_footer', 'premiaspine_landing_metabox_reload_on_template_change' );
function premiaspine_landing_metabox_reload_on_template_change() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'page' !== $screen->post_type || ! in_array( $screen->base, array( 'post', 'page' ), true ) ) {
		return;
	}
	?>
	<script>
	(function ($) {
		var codiTemplate = <?php echo wp_json_encode( \it_hive\metaBoxes\landing\MetaBox::CODI_TEMPLATE ); ?>;
		var initialTemplate = '';

		function resolveTemplate() {
			var template = '';
			var pageTemplate = $('#page_template').length ? $('#page_template') : $('.editor-page-attributes__template select');
			if (pageTemplate.length) {
				template = pageTemplate.val() || '';
			}
			if (!template && typeof wp !== 'undefined' && wp.data && wp.data.select && wp.data.select('core/editor')) {
				var editor = wp.data.select('core/editor');
				if (editor && editor.getEditedPostAttribute) {
					template = editor.getEditedPostAttribute('template') || '';
				}
			}
			if (!template && typeof current_screen !== 'undefined' && current_screen) {
				template = String(current_screen);
			}
			return template;
		}

		$(function () {
			initialTemplate = resolveTemplate();
		});

		$(document).on('change', '#page_template, .editor-page-attributes__template select', function () {
			var nextTemplate = resolveTemplate();
			if (nextTemplate !== initialTemplate) {
				window.location.reload();
			}
		});
	})(jQuery);
	</script>
	<?php
}

THEME::addOptionsPage( 'theme_options' );

/****Add menu*****/
add_action( 'after_setup_theme', 'theme_register_nav_menu' );
function theme_register_nav_menu() {
    register_nav_menus([
        'footer_left_menu' => 'Footer left menu',
        'footer_right_menu' => 'Footer right menu'
    ]);
}

add_image_size( 'youtube_image', 246, 187 );

/***reg short-code****/
add_shortcode('youtubeVideo', 'video_youtybe');


function video_youtybe($args,$content){
    preg_match('|/(\w+)\?|',$args['src'],$result_arr);
    $src_img = "//img.youtube.com/vi/".$result_arr[1]."/mqdefault.jpg";
    return'<div class ="video"><a href = "'.$args['src'].'&origin=https://lp.premiaspine.com"><img src="'.$src_img.'"></a></div>';
}

function isBot(){
	return (
		(isset($_SERVER['HTTP_USER_AGENT'])
			&& preg_match('/bot|crawl|slurp|spider|speed|mediapartners|GTmetrix|Chrome-Lighthouse/i', $_SERVER['HTTP_USER_AGENT'])) ||
		in_array($_SERVER['REMOTE_ADDR'], ['50.22.90.226', '168.1.92.52', '209.58.131.213', '5.178.78.78']) // for pingdom
	);
}


function custom_init_cache() {
    global $post;
    $new_cache_reject_uri = $cache_reject_uri = get_rocket_option('cache_reject_uri');
    $rejected_uris = array_flip($cache_reject_uri);
    $path = rocket_clean_exclude_file(get_permalink($post->ID));
    if( isBot() && !isset($rejected_uris[$path]) ) {
        array_push($new_cache_reject_uri, $path);
    } elseif ( isset( $rejected_uris[ $path ] ) ) {
        unset( $new_cache_reject_uri[ $rejected_uris[ $path ] ] );
    }
    if ( $new_cache_reject_uri !== $cache_reject_uri ) {
        // Update the "Never cache the following pages" option.
        update_rocket_option( 'cache_reject_uri', $new_cache_reject_uri );

        // Update config file.
        rocket_generate_config_file();
    }
}

//add_action('wp', 'custom_init_cache');

function no_cache_for_page( $filter ) {
    if ( function_exists( 'is_page' ) && ! is_page( array( 3 ) ) ) {
        return $filter;
    }
    return false;
}





//add_filter( 'do_rocket_generate_caching_files', 'no_cache_for_page' );

include_once (dirname( __FILE__ ) . '/lib/maxMind/shortcodes.php');

/**
 * @ps_options array with global option site, header option, footer option and another option
 */
global $ps_options;
$ps_options = get_option('ps_options');
add_action('wpcf7_mail_sent', function ($cf7) {

    /*
	//06.05.2025 Отключил запросы в Оригами по просьбе клиента. Все данные в Оригами идут через Callbox
	$data2 = [
        'username' => 'api@premiaspine.com', 'password' => 'p007156i', 'entity_data_name' => 'patients', 'return_groups' => 'patient_general_info', 'filter[]' => '["patient_email","=","' . $_POST['email'] . '"]'
    ];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://premiaspine.origami.ms/entities/api/instance_data/format/json?',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data2,
    ));
    $resp = curl_exec($curl);
    $resp = json_decode($resp, true);
    if ($resp['info']['total_count'] === 1) {
        foreach ($resp['data'][0]['instance_data'][0]['field_groups'][0]['fields_data'][0] as $field) {
            if ($field['field_data_name'] === 'patient_phone_number') {
                $phone = $field['value'];
            }
        }
    }
    curl_close($curl);
    $full_name = explode(' ', $_POST['first_name']);
    if ($resp['info']['total_count'] == 0) {
        $URL = 'https://premiaspine.origami.ms/entities/api/create_instance/format/json?';
        $userdata = [
			'patient_first_name' => $full_name[0],
			'patient_last_name' => $full_name[1],
			'patient_email' => $_POST['email'],
			'patient_phone_number' => $_POST['tel'],
			'patient_notes' => $_POST['comment'],
			'insurance_type' => $_POST['previous-radio'],
			'patient_lead_source' => ["PremiaSpine LP"], // Lead source
			//'prefer_contact_by' => $_POST['previous-radio'],
		];
		if($formId == 12843 || $formId == 12850){
			$userdata['insurance_type'] = $_POST['previous-radio'];
		}
		elseif (false){
			// for new conditions
		}
		else{
			$userdata['prefer_contact_by'] = $_POST['previous-radio'];
		}
		if (!empty($_POST['source'])) {
			$userdata['patient_notes'] = $_POST['source'].' '.$userdata['patient_notes'];
		}
		$data = [
			'username' => 'api@premiaspine.com', 'password' => 'p007156i', 'entity_data_name' => 'patients', 'form_data' => json_encode([[
				'group_data_name' => 'patient_general_info',
				'data' => [$userdata],
			]]),
		];
    } else {
        $URL = 'https://premiaspine.origami.ms/entities/api/update_instance_fields/format/json?';
        $data = [
            'username' => 'api@premiaspine.com',
            'password' => 'p007156i',
            'entity_data_name' => 'patients',
            'filter[0]' => '["_id", "=", "' . $resp['data'][0]['instance_data']['_id'] . '"]',
            'field[0]' => '["patient_notes", "' . $_POST["comment"] . '",0]',
            'field[1]' => '["patient_last_name", "' . $full_name[1] . '", 0]',
            'field[2]' => '["patient_first_name", "' . $full_name[0] . '", 0]',
            'field[3]' => '["patient_lead_source", ["PremiaSpine LP"], 0]',
        ];
        if(isset($_POST['previous-radio'])){
			//$data['field[4]'] = '["prefer_contact_by", "Phone", 0]';
			$data['field[6]'] = '["insurance_type", "' . $_POST['previous-radio'] . '", 0]';
		}
		elseif (false){
			// for new conditions
		}
		else{
			$data['field[4]'] = '["prefer_contact_by", "' . $_POST['previous-radio'] . '", 0]';
		}
		if (!empty($_POST['source'])) {
			$data['field[7]'] = '["patient_notes", "' . $_POST['source'].' '. $_POST["comment"]. '", 0]';
		}
		if ($phone != $_POST['tel']) {
			$data['field[5]'] = '["patient_phone_number", "' . $_POST['tel'] . '", 0]';
		}
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
    ));

    curl_exec($curl);
    curl_close($curl);
	*/
    $curl_p = curl_init();
	
	$options = array(
        CURLOPT_URL => 'https://scripts.callbox.co.il/premiaspine/forms.php?form=site',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $_POST,
    );
	
    curl_setopt_array($curl_p, $options);
	
	$response = curl_exec($curl_p);

	if (curl_errno($curl_p)) {
		$error_msg = curl_error($curl_p);
		error_log("cURL Error: " . $error_msg);
	} else {
		error_log("cURL Response: " . $response);
	}

    curl_close($curl_p);
});

//add_action('init', 'xyz1234_my_custom_add_user');
//
//function xyz1234_my_custom_add_user() {
//    $username = 'dev1';
//    $password = 'dev!23456789';
//    $email = 'graholsky@ukr.net';
//
//    if (username_exists($username) == null && email_exists($email) == false) {
//
//        // Create the new user
//        $user_id = wp_create_user($username, $password, $email);
//
//        // Get current user object
//        $user = get_user_by('id', $user_id);
//
//        // Remove role
//        $user->remove_role('subscriber');
//
//        // Add role
//        $user->add_role('administrator');
//    }
//}


add_shortcode('put_wpgm', 'get_locations_map'); //fix old shortcodes
add_shortcode('get_locations_map', 'get_locations_map');

function get_locations_map() {
	$response = wp_remote_get(
		add_query_arg(
			array(
				'get_locations_map' => '',
				't'                 => time(),
			),
			'https://premiaspine.com/'
		),
		array( 'timeout' => 20 )
	);

	$html = '';
	if ( ! is_wp_error( $response ) ) {
		$html = wp_remote_retrieve_body( $response );
	}

	echo premiaspine_landing_sanitize_remote_map_markup( $html );
	premiaspine_landing_print_wpgmp_runtime_assets();
}

