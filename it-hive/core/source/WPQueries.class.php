<?php

namespace it_hive\core\source;

class WPQueries
{

	protected static $wpQueries = array();

	public static function wpQuery($args = array())
	{
		if (!empty($args)) {
			$key = md5(serialize($args));
			if (empty(self::$wpQueries[$key])) {
				self::$wpQueries[$key] = get_posts($args);
			}
			return self::$wpQueries[$key];
		}
	}

}