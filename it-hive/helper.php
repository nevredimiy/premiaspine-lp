<?php

namespace it_hive\helper;

function title_like( $where, $wp_query )
{
    global $wpdb;
    if ( $wpse18703_title = $wp_query->get( 'title_like' ) ) {
	    $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $wpse18703_title ) ) . '%\'';
    }
    return $where;
}
add_filter( 'posts_where', '\it_hive\helper\title_like', 10, 2 );

function first_part_title( $where, $wp_query )
{
    global $wpdb;
    if ( $wpse18703_title = $wp_query->get( 'first_part' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $wpdb->esc_like( $wpse18703_title ) ) . '%\'';
    }
    return $where;
}
add_filter( 'posts_where', '\it_hive\helper\first_part_title', 10, 2 );



function ajax_query(){
    global $post;
    $args = $_POST['query'];
    $query = new \WP_Query($args);
	if($_POST['get_permalink']){
		foreach ($query->posts as $key=>$post){
			$query->posts[$key]->url = get_permalink($post->ID);
		}
	}
//	echo '<br>';
//
//	print_r($query->request);
//	echo '<br>';
//    print_r($query->posts);
	echo json_encode($query->posts);

	exit();
}

function additional_meta_fields( $fields, $wp_query ){
	if ( $keys = $wp_query->get( 'additional_meta' ) ) {
			if(!is_array($keys)){
				$keys = explode(',' , $keys);
			}
			for($i = 0; $i< count($keys); $i++){
				$fields.= ',addM'.$i.'.meta_value as \''.$keys[$i].'\'';
			}
	}
	return $fields;
}


function additional_meta_join( $join, $wp_query ){
	 global $wpdb;
	if ( $keys = $wp_query->get( 'additional_meta' ) ) {
			if(!is_array($keys)){
				$keys = explode(',' , $keys);
			}
			for($i = 0; $i< count($keys); $i++){
				$join.=' LEFT JOIN '.$wpdb->postmeta.' as addM'.$i.' ON (addM'.$i.'.post_id='.$wpdb->posts.'.ID AND addM'.$i.'.meta_key=\''.$keys[$i].'\')';
			}
	}

	return $join;
}


//add_filter( 'pre_user_query', 'get_user_query_max' );
//function get_user_query_max( $query ) {
//	if ( $query->query_vars['user_meta_fields'] ) {
//		$counter_query = 0;
//		foreach ( $query->query_vars['user_meta_fields'] as $meta ) {
//			$counter_query ++;
//			$query->query_fields .= ', u_meta'.$counter_query.'.meta_value as ' .$meta;
//			$query->query_from   .= ' LEFT JOIN wp_usermeta as u_meta'.$counter_query.' ON (wp_users.ID = u_meta'.$counter_query.'.user_id AND u_meta'.$counter_query.'.meta_key = \''.$meta.'\')';
//		}
//	}
//}
//remove_filter( 'pre_user_query', 'get_user_query_max' );


add_action('wp_ajax_it-hive_query', '\it_hive\helper\ajax_query');
add_action('wp_ajax_nopriv_it-hive_query', '\it_hive\helper\ajax_query');
add_filter( 'posts_fields', '\it_hive\helper\additional_meta_fields', 10, 2 );
add_filter( 'posts_join', '\it_hive\helper\additional_meta_join', 10, 2 );
?>