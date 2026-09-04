<?php

namespace it_hive\metaBoxes\thank_you;

const DisplayDir = __DIR__ . DIRECTORY_SEPARATOR . 'displays' . DIRECTORY_SEPARATOR;

class MetaBox extends \it_hive\core\AdminBox\MetaBox {
	protected $params = array(
		'screen'      => 'page',
		'name'        => 'thank_you_options',
		'single'      => true,
		'title'       => 'Options',
        'forTemplate'     =>['thank_you.php'],
		'children'    => [
		    'title' => [
		        'type' => 'wpEditor',
                'label' => 'Title',
            ],
            'content' => [
                'type' => 'wpEditor',
                'label' => 'Content',
            ],
            'button' => [
                'type' => 'text',
                'label' => 'Button',
            ],
            'link' => [
                'type' => 'text',
                'label' => 'Link',
            ],
            'logo' => [
                'type' => 'image',
                'label' => 'Logo',
            ],
            'background' => [
                'type' => 'image',
                'label' => 'Background',
            ],
        ],
	);

	public function __construct( $params = array() ) {
		parent::__construct( $this->params );
	}
}
