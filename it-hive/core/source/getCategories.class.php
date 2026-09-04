<?php
namespace it_hive\core\source;

class getCategories {

    protected static $categories = array();

    public static function get_categories ( $args = array() ) {
        if( !empty($args) ) {
            $key = md5(serialize($args));
            if( empty(self::$categories[$key]) ) {
                self::$categories[$key] = get_categories($args);
            }
            return self::$categories[$key];
        }

    }

}