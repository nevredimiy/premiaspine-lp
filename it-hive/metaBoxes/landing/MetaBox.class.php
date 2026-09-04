<?php

namespace it_hive\metaBoxes\landing;
ini_set( 'display_errors', 1 );
const DisplayDir = __DIR__ . DIRECTORY_SEPARATOR . 'displays' . DIRECTORY_SEPARATOR;

class MetaBox extends \it_hive\core\AdminBox\MetaBox {
	const CODI_TEMPLATE = 'landing_page_codi-for-test.php';

	/** Секции, которые показываются только на Codi-шаблоне */
	const SECTIONS_CODI_ONLY = array(
		'hero_patient',
		'patient_stories_slider',
		'premia_benefits',
        'map_section',
		'hero_slides',
		'surgeons_stories_slider',
		'about_section',
		'footer_codi',
	);

	/** Секции, которые скрываются на Codi-шаблоне */
	const SECTIONS_CODI_HIDDEN = array(
		'advertising',
		'intro_content',
		'content_area',
		'testimonials',
		'request_an_appointment_form_1',
		'request_an_appointment_form_2',
		'questions_and_answer',
		'locations',
	);

	/** Поля внутри Header, которые не нужны на Codi */
	const HEADER_FIELDS_CODI_HIDDEN = array(
		'phone_title',
		'advertising_benefits',
		'title_benefits',
		'benefits',
	);

    // Поля внутри Benefits, которые не нужны на Codi
    // const BENEFITS_FIELDS_CODI_HIDDEN = array(
    //     'slides',
    // );

    // Устанавливаем какие поля и секции нужны для нового шаблона
	public static function get_field_visibility_config() {
		return array(
			'metaKey'           => 'landing_page_options',
			'codiTemplate'      => self::CODI_TEMPLATE,
			'codiOnlySections'  => self::SECTIONS_CODI_ONLY,
			'codiHiddenSections'=> self::SECTIONS_CODI_HIDDEN,
			'codiHiddenHeader'  => self::HEADER_FIELDS_CODI_HIDDEN,
		);
	}

    // Получаем template страницы
	public static function get_page_template( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			return '';
		}

		$template = get_page_template_slug( $post_id );
		if ( ! $template ) {
			$template = (string) get_post_meta( $post_id, '_wp_page_template', true );
		}

		return $template;
	}

	public static function is_codi_template( $template ) {
		return self::CODI_TEMPLATE === $template;
	}

	// Порядок секций для нового шаблона
	protected static function get_section_order_for_template( $template ) {
		if ( self::is_codi_template( $template ) ) {
			return array(
				'header',
				'hero_slides',
				'hero_patient',
				'patient_stories_slider',
				'premia_benefits',
				'surgeons_stories_slider',
				'about_section',
                'map_section',
				'request_an_appointment_form_3',
				'footer_codi',
			);
		}

		return array(
			'header',
			'advertising',
			'intro_content',
			'content_area',
			'testimonials',
			'request_an_appointment_form_1',
			'request_an_appointment_form_2',
			'questions_and_answer',
			'locations',
			'request_an_appointment_form_3',
		);
	}

	// Упорядочиваем секции для нового шаблона
	protected static function order_children_for_template( array $children, $template ) {
		$ordered  = array();
		$sections = self::get_section_order_for_template( $template );

		foreach ( $sections as $section_key ) {
			if ( isset( $children[ $section_key ] ) ) {
				$ordered[ $section_key ] = $children[ $section_key ];
			}
		}

		return $ordered;
	}

    // Скрываем лишние поля для нового шаблона
	protected static function filter_children_for_template( array $children, $template ) {
		if ( self::is_codi_template( $template ) ) {
			foreach ( self::SECTIONS_CODI_HIDDEN as $section_key ) {
				unset( $children[ $section_key ] );
			}

            // удаляем лишние поля из header
			if ( isset( $children['header']['children'] ) && is_array( $children['header']['children'] ) ) {
				foreach ( self::HEADER_FIELDS_CODI_HIDDEN as $field_key ) {
					unset( $children['header']['children'][ $field_key ] );
				}
			}

            // удаляем лишние поля из premia_benefits
            // if ( isset( $children['premia_benefits']['children'] ) ) {
            //     foreach ( self::BENEFITS_FIELDS_CODI_HIDDEN as $field_key ) {
            //         unset( $children['premia_benefits']['children'][ $field_key ] );
            //     }
            // }

			return self::order_children_for_template( $children, $template );
		}

		foreach ( self::SECTIONS_CODI_ONLY as $section_key ) {
			unset( $children[ $section_key ] );
		}

		return self::order_children_for_template( $children, $template );
	}

	protected static function get_preserved_meta_for_template( array $existing, $template ) {
		$preserve = array();

		if ( self::is_codi_template( $template ) ) {
			foreach ( self::SECTIONS_CODI_HIDDEN as $section_key ) {
				if ( array_key_exists( $section_key, $existing ) ) {
					$preserve[ $section_key ] = $existing[ $section_key ];
				}
			}

			if ( isset( $existing['header'] ) && is_array( $existing['header'] ) ) {
				foreach ( self::HEADER_FIELDS_CODI_HIDDEN as $field_key ) {
					if ( array_key_exists( $field_key, $existing['header'] ) ) {
						if ( ! isset( $preserve['header'] ) ) {
							$preserve['header'] = array();
						}
						$preserve['header'][ $field_key ] = $existing['header'][ $field_key ];
					}
				}
			}

			return $preserve;
		}

		foreach ( self::SECTIONS_CODI_ONLY as $section_key ) {
			if ( array_key_exists( $section_key, $existing ) ) {
				$preserve[ $section_key ] = $existing[ $section_key ];
			}
		}

		return $preserve;
	}

	protected static function merge_preserved_meta( array $saved, array $preserve ) {
		foreach ( $preserve as $key => $value ) {
			if ( 'header' === $key && is_array( $value ) ) {
				if ( ! isset( $saved['header'] ) || ! is_array( $saved['header'] ) ) {
					$saved['header'] = array();
				}
				foreach ( $value as $field_key => $field_value ) {
					$saved['header'][ $field_key ] = $field_value;
				}
				continue;
			}

			$saved[ $key ] = $value;
		}

		return $saved;
	}

	/**
	 * Legacy landing cards stored full story payloads inside repeater rows.
	 * After switching to Testimonial CPT selects, those rows can exhaust memory in admin.
	 */
	public static function sanitize_legacy_story_slider_items_for_admin( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			return;
		}

		$meta = get_post_meta( $post_id, 'landing_page_options', true );
		if ( ! is_array( $meta ) ) {
			return;
		}

		$changed      = false;
		$section_keys = array( 'patient_stories_slider', 'surgeons_stories_slider' );

		foreach ( $section_keys as $section_key ) {
			if ( empty( $meta[ $section_key ]['items'] ) || ! is_array( $meta[ $section_key ]['items'] ) ) {
				continue;
			}

			$sanitized = array();

			foreach ( $meta[ $section_key ]['items'] as $row ) {
				if ( ! is_array( $row ) ) {
					$changed = true;
					continue;
				}

				$story_post_id = 0;
				if ( ! empty( $row['post_id'] ) ) {
					$story_post_id = (int) $row['post_id'];
				} elseif ( ! empty( $row['story_id'] ) ) {
					$story_post_id = (int) $row['story_id'];
				}

				if ( $story_post_id > 0 ) {
					$sanitized[] = array( 'post_id' => $story_post_id );
					if ( count( $row ) > 1 || empty( $row['post_id'] ) ) {
						$changed = true;
					}
					continue;
				}

				if ( isset( $row['title'] ) || isset( $row['text'] ) || isset( $row['image'] ) || isset( $row['popup_gallery'] ) || isset( $row['popup_youtube_url'] ) ) {
					$changed = true;
				}
			}

			if ( $changed || count( $sanitized ) !== count( $meta[ $section_key ]['items'] ) ) {
				$meta[ $section_key ]['items'] = $sanitized;
				$changed                       = true;
			}
		}

		if ( $changed ) {
			update_post_meta( $post_id, 'landing_page_options', $meta );
		}
	}

	protected $params = array(
		'screen'      => 'page',
		'name'        => 'landing_page_options',
		'single'      => true,
		'title'       => 'Page Content',
        'forTemplate'     => ['landing_page.php', 'landing_page_old.php', 'landing_page_052026.php', 'landing_page_codi-for-test.php', 'page-info.php'],
		'children'    => [
		    'header' => [
		        'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '1. Hero Form',
                'children' => [
                    // 'top_title' =>[
                    //     'type'  => 'text',
                    //     'label' => 'Заголовок H1',
                    // ],
                    // 'top_content' =>[
                    //     'type'  => 'wpEditor',
                    //     'label' => 'Текст под заголовком',
                    // ],
                    // 'doctror_name' =>[
                    //     'type'  => 'text',
                    //     'label' => 'Имя врача',
                    // ],
                    // 'doctor_photo' =>[
                    //     'type'  => 'image',
                    //     'label' => 'Фото врача',
                    // ],
                    // 'top_section_bg' =>[
                    //     'type'  => 'image',
                    //     'label' => 'Фон hero-блока',
                    // ],
                    'contact_form' => [
                        'type' => 'text',
                        'label' => 'Shortcode for the contact form in the hero',
                    ],
                    'phone_title' =>[
                        'type'  => 'text',
                        'label' => 'Phone title (old template)',
                    ],
                    'to_form_button' =>[
                        'type'  => 'text',
                        'label' => 'Title of button "To form"',
                    ],
                    // 'advertising_benefits' =>[
                    //     'type'  => 'text',
                    //     'label' => 'Рекламная полоса — текст',
                    // ],
                    // 'title_benefits' =>[
                    //     'type'  => 'text',
                    //     'label' => 'Заголовок списка преимуществ',
                    // ],
                    // 'benefits'       => [
                    //     'type'  => 'repeater',
                    //     'label' => 'Пункты списка преимуществ',
                    //     'repeat' => [
                    //         'benefit' => [
                    //             'type' => 'text',
                    //             'label' => 'Пункт',
                    //         ],
                    //     ],
                    // ],
                ],
            ],
            'hero_slides' => [
                'type'  => 'group',
                'label' => '2. Hero Slider',
                'skin'  => 'slideToggle',
                'children' => [
                    'slides' => [
                        'type'              => 'repeater',
                        'label'             => 'Slides',
                        'sortable'          => true,
                        'repeatSkin'        => 'slideToggle',
                        'repeatHeading'     => 'Slide',
                        'repeatHeadingFrom' => 'slide_type',
                        'repeat'            => [
                            'slide_type' => [
                                'type'         => 'select',
                                'label'        => 'Slide type',
                                'defaultLabel' => 'Select type',
                                'values'       => [
                                    'doctor'  => 'Doctor',
                                    'patient' => 'Patient',
                                ],
                            ],
                            'link_url' => [
                                'type'  => 'text',
                                'label' => 'Link (if filled — slide is clickable)',
                            ],
                            'link_new_tab' => [
                                'type'    => 'checkbox',
                                'label'   => 'Open in a new tab',
                                'default' => 'on',
                            ],
                            'doctor' => [
                                'type'     => 'group',
                                'label'    => 'Doctor',
                                'skin'     => 'default',
                                'children' => [
                                    'bg' => [
                                        'type'  => 'image',
                                        'label' => 'Background',
                                    ],
                                    'doctor_photo' => [
                                        'type'  => 'image',
                                        'label' => 'Doctor photo',
                                    ],
                                    'doctor_name' => [
                                        'type'  => 'text',
                                        'label' => 'Name',
                                    ],
                                    'doctor_address' => [
                                        'type'  => 'text',
                                        'label' => 'Address / position',
                                    ],
                                    'title' => [
                                        'type'  => 'text',
                                        'label' => 'H1 title',
                                    ],
                                    'icon' => [
                                        'type'  => 'image',
                                        'label' => 'Icon',
                                    ],
                                    'text' => [
                                        'type'  => 'wpEditor',
                                        'label' => 'Text',
                                    ],
                                ],
                            ],
                            'patient' => [
                                'type'     => 'group',
                                'label'    => 'Patient',
                                'skin'     => 'default',
                                'children' => [
                                    'title' => [
                                        'type'  => 'wpEditor',
                                        'label' => 'Title',
                                    ],
                                    'name' => [
                                        'type'  => 'text',
                                        'label' => 'Patient name',
                                    ],
                                    'images' => [
                                        'type'  => 'repeater',
                                        'label' => 'Images',
                                        'repeat' => [
                                            'image' => [
                                                'type' => 'image',
                                                'label' => 'Image',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'patient_stories_slider' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '3. Patients Carousel',
                'children' => [
                    'hide' => [
                        'type' => 'checkbox',
                        'label' => 'Hide section',
                        'default' => 'on',
                    ],
                    'title' => [
                        'type' => 'text',
                        'label' => 'Section title',
                    ],
                    'items' => array(
                        'type'              => 'repeater',
                        'label'             => 'Patient stories',
                        'sortable'          => true,
                        'repeatSkin'        => 'default',
                        'repeatHeading'     => 'Story',
                        'repeatHeadingFrom' => 'post_id',
                        'repeat'            => array(
                            'post_id' => array(
                                'type'        => 'selectPosts',
                                'post_type'   => 'testimonial',
                                'skin'        => 'ajax',
                                'label'       => 'Select patient',
                                'placeholder' => 'Enter the name...',
                                'taxonomy'    => array( 'story_category', 12 ),
                            ),
                        ),
                    ),
                ],
            ],
            'premia_benefits' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '4. Benefits',
                'children' => [
                    'hide' => [
                        'type' => 'checkbox',
                        'label' => 'Hide section',
                        'default' => 'on',
                    ],
                    'bg_pc' => [
                        'type' => 'image',
                        'label' => 'Background — desktop',
                    ],
                    'bg_mobile' => [
                        'type' => 'image',
                        'label' => 'Background — mobile',
                    ],
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                    'subtitle' => [
                        'type' => 'text',
                        'label' => 'Subtitle',
                    ],
                    'info_items' => [
                        'type' => 'repeater',
                        'label' => 'Icons with text',
                        'limit' => 3,
                        'sortable' => true,
                        'removeText' => 'Delete',
                        'repeatSkin' => 'slideToggleWithoutHeading',
                        'repeatHeading' => 'Icon',
                        'repeatHeadingFrom' => 'text',
                        'repeat' => [
                            'icon' => [
                                'type' => 'image',
                                'label' => 'Icon',
                            ],
                            'text' => [
                                'type' => 'text',
                                'label' => 'Text',
                            ],
                        ],
                    ],
                    'slides' => [
                        'type' => 'repeater',
                        'label' => 'Slides with lists',
                        'limit' => 2,
                        'sortable' => true,
                        'repeatSkin' => 'slideToggle',
                        'repeatHeadingFrom' => 'title',
                        'repeat' => [
                            'title' => [
                                'type' => 'text',
                                'label' => 'Slide title',
                            ],
                            // 'blue_style' => [
                            //     'type' => 'checkbox',
                            //     'label' => 'Blue style card',
                            //     'default' => 'on',
                            // ],
                            'list' => [
                                'type' => 'textarea',
                                'label' => 'List items (one per line)',
                            ],
                        ],
                    ],
                ],
            ],
            'surgeons_stories_slider' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '5. Surgeons Carousel',
                'children' => [
                    'hide' => [
                        'type' => 'checkbox',
                        'label' => 'Hide section',
                        'default' => 'on',
                    ],
                    'title' => [
                        'type' => 'text',
                        'label' => 'Section title',
                    ],
                    'to_map_button' =>[
                        'type'  => 'text',
                        'label' => 'Title of button "To map"',
                    ],
                    'items' => array(
                        'type'              => 'repeater',
                        'label'             => 'Surgeon stories',
                        'sortable'          => true,
                        'repeatSkin'        => 'default',
                        'repeatHeading'     => 'Story',
                        'repeatHeadingFrom' => 'post_id',
                        'repeat'            => array(
                            'post_id' => array(
                                'type'        => 'selectPosts',
                                'post_type'   => 'testimonial',
                                'skin'        => 'ajax',
                                'label'       => 'Select surgeon',
                                'placeholder' => 'Enter the name...',
                                'taxonomy'    => array( 'story_category', 13 ),
                            ),
                        ),
                    ),
                ],
            ],
            'about_section' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '6. Information',
                'children' => [
                    'hide' => [
                        'type' => 'checkbox',
                        'label' => 'Hide section',
                        'default' => 'on',
                    ],
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                    'content_top' => [
                        'type' => 'wpEditor',
                        'label' => 'Text above video',
                    ],
                    'youtube_url' => [
                        'type' => 'text',
                        'label' => 'YouTube embed URL (video in block)',
                    ],
                    'content_bottom' => [
                        'type' => 'wpEditor',
                        'label' => 'Text below video',
                    ],
                    'bg_pc' => [
                        'type' => 'image',
                        'label' => 'Desktop background',
                    ],
                    'bg_mobile' => [
                        'type' => 'image',
                        'label' => 'Background — mobile',
                    ],
                    'button_1_text' => [
                        'type' => 'text',
                        'label' => 'Button 1 — text',
                    ],
                    'popup_1_title' => [
                        'type' => 'text',
                        'label' => 'Popup 1 — title',
                    ],
                    'button_1_youtube_url' => [
                        'type' => 'text',
                        'label' => 'Button 1 — YouTube URL (popup)',
                    ],
                    'button_2_text' => [
                        'type' => 'text',
                        'label' => 'Button 2 — text',
                    ],
                    'popup_2_title' => [
                        'type' => 'text',
                        'label' => 'Popup 2 — title',
                    ],
                    'button_2_youtube_url' => [
                        'type' => 'text',
                        'label' => 'Button 2 — YouTube URL (popup)',
                    ],
                ],
            ],
            'map_section' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '7.Map',
                'children' => [
                    'title' => [
                        'label' => 'Map title',
                        'type' => 'text',
                    ],
                ],
            ],
            'request_an_appointment_form_3' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => '8. Contact Form',
                'children' => [
                    'request_an_appointment_form' => [
                        'label' => 'Shortcode for the form',
                        'type' => 'text',
                    ],
                ],
            ],
            // 'footer_codi' => [
            //     'type' => 'group',
            //     'skin' => 'slideToggle',
            //     'label' => '8. Footer',
            //     'children' => [
            //         'logo' => [
            //             'type' => 'image',
            //             'label' => 'Logo',
            //         ],
            //         'text' => [
            //             'type' => 'textarea',
            //             'label' => 'Text',
            //         ],
            //         'copyright' => [
            //             'type' => 'text',
            //             'label' => 'Copyright',
            //         ],
            //     ],
            // ],
            'advertising' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Advertisement',
                'children' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                ],
            ],
            'intro_content' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Intro content',
                'children' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                    'advantages' => [
                        'type' => 'repeater',
                        'label' => 'Advantages',
                        'repeat' => [
                            'text' => [
                                'type' => 'textarea',
                                'label' => 'Text',
                            ]
                        ],
                    ],
                ],
            ],
            'content_area' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Content area',
                'children' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                    'text' => [
                        'label' => 'Text blocks',
                        'type' => 'repeater',
                        'repeat' => [
                            'text' => [
                                'type' => 'wpEditor',
                                'label' => 'Text',
                            ]
                        ],
                    ],
                ],
            ],
            'testimonials' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Testimonials (video)',
                'children' => [
                    'hide' => [
                        'type' => 'checkbox',
                        'label' => 'Hide section',
                        'default' => 'on'
                    ],
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                    'testimonials' => [
                        'type' => 'repeater',
                        'sortable' => true,
                        'repeatSkin'=>'slideToggle',
                        'repeatHeadingFrom'=>'text',
                        'repeatHeading' => 'Testimonial',
                        'label' => 'List of testimonials',
                        'repeat' => [
                            'text' => [
                                'type' => 'wpEditor',
                                'label' => 'Text',
                            ],
                            'image' => [
                                'label' => 'YouTube preview',
                                'type' => 'image',
                            ],
                            'link' => [
                                'label' => 'Link to YouTube',
                                'type' => 'text',
                            ],
                            'description' => [
                                'label' => 'Video description',
                                'type' => 'text',
                            ],
                            'title_for_video' => [
                                'label' =>  'Video title',
                                'type' => 'text',
                            ],

                        ],
                    ],
                ],
            ],
            'request_an_appointment_form_1' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Request form 1',
                'children' => [
                    'request_an_appointment_form' => [
                        'label' => 'Shortcode for the form',
                        'type' => 'text',
                    ],
                ],
            ],
            'request_an_appointment_form_2' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Request form 2',
                'children' => [
                    'request_an_appointment_form' => [
                        'label' => 'Shortcode for the form',
                        'type' => 'text',
                    ],
                ],
            ],
            'questions_and_answer' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Frequently Asked Questions (FAQ)',
                'children' => [
                    'hide' => [
                        'type' => 'checkbox',
                        'label' => 'Hide section',
                        'default' => 'on'
                    ],
                    'title' => [
                        'label' => 'Title',
                        'type' => 'text',
                    ],
                    'questions_and_answer' => [
                        'type' => 'repeater',
                        'repeatSkin'=>'slideToggle',
                        'repeatHeadingFrom'=>'question',
                        'repeatHeading' => 'Question',
                        'label' => 'List of questions',
                        'repeat' => [
                            'question' => [
                                'type' => 'text',
                                'label' => 'Question',
                            ],
                            'answer' => [
                                'type' => 'wpEditor',
                                'label' => 'Answer',
                            ],
                        ],
                    ],
                ],
            ],
            'locations' => [
                'type' => 'group',
                'skin' => 'slideToggle',
                'label' => 'Map and locations',
                'children' => [
                    'title' => [
                        'label' => 'Title',
                        'type' => 'text',
                    ],
                    'subtitle' => [
                        'label' => 'Subtitle',
                        'type' => 'text',
                    ],
                    'map_shortcode' => [
                        'label' => 'Shortcode for the map',
                        'type' => 'text',
                    ],
                    'first_column' => [
                        'type' => 'group',
                        'skin' => 'slideToggle',
                        'label' => 'Column 1',
                        'children' => [
                            'row' => [
                                'type' => 'repeater',
                                'label' => 'Rows',
                                'repeat' => [
                                    'row' => [
                                        'label' => 'Row',
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'second_column' => [
                        'type' => 'group',
                        'skin' => 'slideToggle',
                        'label' => 'Column 2',
                        'children' => [
                            'row' => [
                                'type' => 'repeater',
                                'label' => 'Rows',
                                'repeat' => [
                                    'row' => [
                                        'label' => 'Row',
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'third_column' => [
                        'type' => 'group',
                        'skin' => 'slideToggle',
                        'label' => 'Column 3',
                        'children' => [
                            'row' => [
                                'type' => 'repeater',
                                'label' => 'Rows',
                                'repeat' => [
                                    'row' => [
                                        'label' => 'Row',
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'fourth_column' => [
                        'type' => 'group',
                        'skin' => 'slideToggle',
                        'label' => 'Column 4',
                        'children' => [
                            'row' => [
                                'type' => 'repeater',
                                'label' => 'Rows',
                                'repeat' => [
                                    'row' => [
                                        'label' => 'Row',
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
	);

	public function __construct( $params = array() ) {
		parent::__construct( $this->params );
	}

	public function show( $post = null, $args = array() ) {
		$post_id = ( $post && isset( $post->ID ) ) ? (int) $post->ID : 0;
		$all_children = $this->params['children'];

		$this->params['children'] = self::filter_children_for_template(
			$all_children,
			self::get_page_template( $post_id )
		);

		if ( $post_id > 0 ) {
			self::sanitize_legacy_story_slider_items_for_admin( $post_id );
		}

		parent::show( $post, $args );

		$this->params['children'] = $all_children;
	}

	public function save( $id = 0 ) {
		$id = (int) $id;
		$existing = get_post_meta( $id, $this->params['name'], true );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		$preserve = self::get_preserved_meta_for_template(
			$existing,
			self::get_page_template( $id )
		);

		$result = parent::save( $id );

		if ( false === $result || empty( $preserve ) ) {
			return $result;
		}

		$saved = get_post_meta( $id, $this->params['name'], true );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		update_post_meta(
			$id,
			$this->params['name'],
			self::merge_preserved_meta( $saved, $preserve )
		);

		return $result;
	}
}

