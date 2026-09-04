<?php
namespace it_hive\core\Blocks\customBlocks\buttonSettingsFull;
use \it_hive\THEME;
define('button_settings', __( 'Button Settings', THEME::$textdomain ));
define('text_on_button', __( 'Text on the Button', THEME::$textdomain ));
define('url_link', __( 'URL (Link)', THEME::$textdomain ));
define('open_link_new_tab', __( 'Open Link in New Tab', THEME::$textdomain ));
define('text_color', __( 'Text Color', THEME::$textdomain ));
define('button_color', __( 'Button Color', THEME::$textdomain ));
define('border_color', __( 'Border Color', THEME::$textdomain ));

class Block extends \it_hive\core\Blocks\group\Block {
    const defaults = [
        'label'     => button_settings,
        'children'  => [
            'text'	=> [
                'type'	=> 'text',
                'label' => text_on_button
            ],
            'link'	=> [
                'type'	=> 'text',
                'label' => url_link
            ],
            'target' => [
                'type'	  => 'checkbox',
                'label'	  => open_link_new_tab,
                'value'	  => '',
                'default' => 'blank'
            ],
            'color_text' => [
                'type'  => 'colorpicker',
                'label' => text_color
            ],
            'color' => [
                'type' => 'colorpicker',
                'label' => button_color
            ],
            'border' => [
                'type'  => 'colorpicker',
                'label' => border_color
            ]
        ]
    ];

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }
}