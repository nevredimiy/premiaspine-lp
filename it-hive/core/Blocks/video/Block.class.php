<?php
namespace it_hive\core\Blocks\video;
use \it_hive\THEME;
if(!defined('remove')){
    define( 'remove', __( 'Remove', THEME::$textdomain ) );
}
if(!defined('upload')){
    define( 'upload', __( 'Upload', THEME::$textdomain ) );
}

class Block extends \it_hive\core\Blocks\DataBlock {

    const defaults = array(
        'textUpload' => upload,
        'textRemove' => remove,
    );

    protected static $adminScripts = array(
        'wp-video-control' => 'main.js'
    );

    protected static $adminStyles = array(
        'wp-video-control' => 'all.css'
    );

	public function __construct( $params ) {
		$this->params = array_merge(self::defaults, $params);
		parent::__construct( $this->params );
	}

    protected function applyParams() {
	    if($this->get()){
            $this->params['video'] =
                '<video width="150" height="100">'.PHP_EOL.
                    '<source src="'.wp_get_attachment_url( stripslashes($this->get())).'">'.PHP_EOL.
                '</video>';
        }else{
            $this->params['video'] = '';
        }
        return parent::applyParams();
    }

    protected static function init() {
        wp_enqueue_media();
        parent::init();
    }

}