<?php
namespace it_hive\core\Blocks\customBlocks\IconBlock;
use \it_hive\THEME;
define('icon_image', __( 'Icon Image', THEME::$textdomain ));
define('set_image', __( 'Set Image', THEME::$textdomain ));
define('head_icon', __( 'Head icon', THEME::$textdomain ));
define('text_icon', __( 'Text icon', THEME::$textdomain ));

class Block extends \it_hive\core\Blocks\group\Block {
    const defaults = array(
        'children'  => array(
	        'icon_image' => [
		        'type'		 => 'image',
		        'label'		 => icon_image,
		        'textUpload' => set_image,
		        'value'		 => ''
	        ],
            'icon_head'	=> array(
                'type'	=> 'text',
                'label' => head_icon,
            ),
            'icon_text'	=> array(
                'type'	=> 'text',
                'label' => text_icon,
            )
        )
    );

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }
}