<?php

namespace it_hive\options\theme_options;

define( 'theme_options', __( 'Theme Options', text_domain ) );
define( 'h2_theme_options', __( '<h2>Theme Options</h2>', text_domain ) );
define( 'save_text', __( 'Save', text_domain ) );
define( 'header', __( 'Header', text_domain ) );
define( 'footer', __( 'Footer', text_domain ) );



class OptionsPage extends \it_hive\core\AdminBox\OptionsPage {

    protected static $classes = false;
    protected $params = [
        'name'      => 'theme_options',
        'title'     => theme_options,
        'save_text' => save_text,
        'content'   => h2_theme_options,
        'single'    => true,
        'children'  => [
            'options' => [
                'type' => 'tabs',
                'tabs' => [
                    header => [
                        'header' => [
                            'type' => 'group',
                            'children' => [
                                'logo' => [
                                    'label' => 'Logo',
                                    'type' => 'image',
                                ],
                                'logo_dark' => [
                                    'label' => 'Logo Dark',
                                    'type' => 'image',
                                ],
                                'phone_display' => [
                                    'label' => 'Phone Number for Display',
                                    'type'  => 'text',
                                ],
                                'phone_tel' => [
                                    'label' => 'Phone Number for Link',
                                    'type'  => 'text',
                                ],
                                'slogan' => [
                                    'label' => 'Heading Text',
                                    'type'  => 'text',
                                ],
                            ],
                        ],
                    ],
                    footer => [
                        'footer' => [
                            'type' => 'group',
                            'children' => [
                                'copyright' => [
                                    'label' => 'Copyright',
                                    'type' => 'text',
                                ],
                                'logo' => [
                                    'type' => 'image',
                                    'label' => 'Logo',
                                ],
                                'text' => [
                                    'type' => 'wpEditor',
                                    'label' => 'Text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    protected static $adminScripts = array(
        'options-js' => 'main.js'
    );

    protected static $adminStyles = array(
        'jquery-ui-css' => 'jquery-ui.css'
    );

    public function __construct( $params = array() ) {
        parent::__construct( $this->params );
    }
}