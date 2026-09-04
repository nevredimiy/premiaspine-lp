<?php

namespace it_hive\metaBoxes\testimonial;

class MetaBox extends \it_hive\core\AdminBox\MetaBox
{
    protected $params = array(
        'screen'   => 'testimonial',
        'name'     => 'story_data',
        'title'    => 'Story card fields',
        'single'   => true,
        'children' => array(
            'tag' => array(
                'type'  => 'text',
                'label' => 'Tag on photo',
            ),
            'text' => array(
                'type'  => 'wpEditor',
                'label' => 'Text (in slider)',
            ),
            'popup_text' => array(
                'type'  => 'wpEditor',
                'label' => 'Text in popup (if empty — uses "Text in slider")',
            ),
            'image' => array(
                'type'  => 'image',
                'label' => 'Image',
            ),
            'popup_gallery' => array(
                'type'           => 'repeater',
                'label'          => 'Gallery in popup',
                'sortable'       => true,
                'repeatSkin'     => 'default',
                'repeatHeading'  => 'Slide',
                'removeText'     => 'Delete',
                'repeat'         => array(
                    'image' => array(
                        'type'  => 'image',
                        'label' => 'Image',
                    ),
                    'youtube_url' => array(
                        'type'  => 'text',
                        'label' => 'YouTube URL (instead of image)',
                    ),
                ),
            ),
        ),
    );

    public function __construct( $params = array() ) {
        parent::__construct( $this->params );
    }
}
