<?php
namespace it_hive\core\Blocks\customBlocks\groupRetinaImage;
use \it_hive\THEME;
define('retina_images', __( 'Retina Images', THEME::$textdomain ));
define('default_image', __( 'Default Image', THEME::$textdomain ));
define('set_image', __( 'Set Image', THEME::$textdomain ));
define('imag_retina_display_2x', __( 'Image for Retina Display 2x', THEME::$textdomain ));
define('imag_retina_display_3x', __( 'Image for Retina Display 3x', THEME::$textdomain ));

class Block extends \it_hive\core\Blocks\group\Block {
    const defaults = array(
        'label'		=> retina_images,
        'children' 	=> array(
            'default_image' => [
                'type'			=> 'image',
                'label'			=> default_image,
                'textUpload' 	=> set_image,
                'value'			=> ''
            ],
            'retina2x_image' => [
                'type'			=> 'image',
                'label'			=> imag_retina_display_2x,
                'textUpload' 	=> set_image,
                'value'			=> ''
            ],
            'retina3x_image' => [
                'type'			=> 'image',
                'label'			=> imag_retina_display_3x,
                'textUpload' 	=> set_image,
                'value'			=> ''
            ]
        )
    );

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }
}