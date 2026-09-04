<?php
namespace it_hive\core\Blocks\customBlocks\groupVideo;
use \it_hive\THEME;
define('background_video', __( 'Background Video', THEME::$textdomain ));
define('file_format_MP4', __( 'File Format MP4', THEME::$textdomain ));
define('file_format_WebM', __( 'File Format WebM4', THEME::$textdomain ));
define('file_format_Ogg', __( 'File Format Ogg', THEME::$textdomain ));

class Block extends \it_hive\core\Blocks\group\Block {
    const defaults = array(
        'label'	   => background_video,
        'children' => array(
                'mp4'	=> array(
                    'type' 	=> 'attachment',
                    'label'	=> file_format_MP4,
                    'value' => ''
                ),
                'webm'	=> array(
                    'type' 	=> 'attachment',
                    'label'	=> file_format_WebM,
                    'value' => ''
                ),
                'ogg'	=> array(
                    'type' 	=> 'attachment',
                    'label'	=> file_format_Ogg,
                    'value' => ''
                )
        )
    );

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults,$params);
        parent::__construct( $this->params );
    }
}