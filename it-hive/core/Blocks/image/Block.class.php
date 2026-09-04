<?php
namespace it_hive\core\Blocks\image;
use \it_hive\THEME;

define( 'remove', __( 'Remove', THEME::$textdomain ) );
define( 'upload', __( 'Upload', THEME::$textdomain ) );

class Block extends \it_hive\core\Blocks\DataBlock {

    const defaults = array(
        'textUpload' => upload,
        'textRemove' => remove,
    );

    protected static $define = false;

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }

    protected static $adminScripts = array(
        'wp-image-control' => 'main.js'
    );

    public function render(){
        $mime_type =  get_post_mime_type( $this->get() );
        if( $mime_type == 'image/svg+xml' ) {
            $this->params['image'] =  '<img src="' . wp_get_attachment_url( $this->get() ) . '" width="150" height="100">';
        }
        else {
            $this->params['image'] =  $this->get() ? wp_get_attachment_image( stripslashes($this->get() ), 'medium') : '';
        }
        $this->params['label'] = $this->params['label'] ? '<h4>' . $this->params['label'] . '</h4>' : '';
        $html = parent::render();
        return $html;
    }

    protected static function init() {
        wp_enqueue_media();
        parent::init();
    }
}