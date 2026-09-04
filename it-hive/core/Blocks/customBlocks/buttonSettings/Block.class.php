<?php
namespace it_hive\core\Blocks\customBlocks\buttonSettings;
use \it_hive\THEME;
define('button_settings', __( 'Button Settings', THEME::$textdomain ));
define('text_on_button', __( 'Text on the Button', THEME::$textdomain ));
define('url_link', __( 'URL (Link)', THEME::$textdomain ));

class Block extends \it_hive\core\Blocks\group\Block {
    const defaults = array(
        'label'     => button_settings,
        'children'  => array(
            'text'	=> array(
                'type'	=> 'text',
                'label' => text_on_button,
            ),
            'link'	=> array(
                'type'	=> 'text',
                'label' => url_link,
            )
        )
    );

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }
}