<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function premiaspine_landing_opt( $options, $keys, $default = '' ) {
    $value = $options;

    foreach ( (array) $keys as $key ) {
        if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
            return $default;
        }
        $value = $value[ $key ];
    }

    if ( is_string( $value ) ) {
        $value = trim( $value );
    }

    return ( $value === '' || $value === null ) ? $default : $value;
}

function premiaspine_landing_section_visible( $section ) {
    $hide = premiaspine_landing_opt( (array) $section, array( 'hide' ), 'disable' );

    return $hide === '' || $hide === 'disable';
}

function premiaspine_landing_attachment_url( $attachment_id, $fallback = '' ) {
    if ( ! empty( $attachment_id ) ) {
        $url = wp_get_attachment_image_url( $attachment_id, 'full' );
        if ( $url ) {
            return $url;
        }
    }

    return $fallback;
}

function premiaspine_landing_parse_list( $text ) {
    if ( ! is_string( $text ) || trim( $text ) === '' ) {
        return array();
    }

    $lines = preg_split( '/\r\n|\r|\n/', $text );

    return array_values( array_filter( array_map( 'trim', $lines ) ) );
}

function premiaspine_landing_format_inline_html( $text ) {
    if ( ! is_string( $text ) || trim( $text ) === '' ) {
        return '';
    }

    $allowed = array(
        'br'     => array(),
        'strong' => array(),
        'em'     => array(),
        'b'      => array(),
        'i'      => array(),
    );

    if ( preg_match( '/<br\s*\/?>/i', $text ) || preg_match( '/<(strong|em|b|i)\b/i', $text ) ) {
        return wp_kses( $text, $allowed );
    }

    return nl2br( esc_html( $text ) );
}

function premiaspine_landing_normalize_html_text( $text ) {
    if ( ! is_string( $text ) ) {
        return '';
    }

    $text = str_ireplace( array( '</br>', '<br>', '<BR>' ), '<br />', $text );

    return $text;
}

function premiaspine_landing_render_field_html( $text ) {
    $text = premiaspine_landing_normalize_html_text( $text );
    if ( trim( $text ) === '' ) {
        return '';
    }

    return wp_kses_post( $text );
}

function premiaspine_landing_render_editor_html( $text ) {
    $text = premiaspine_landing_normalize_html_text( $text );
    if ( trim( $text ) === '' ) {
        return '';
    }

    return apply_filters( 'the_content', $text );
}

function premiaspine_landing_attachment_file_url( $attachment_id, $fallback = '' ) {
    $attachment_id = absint( $attachment_id );
    if ( $attachment_id ) {
        $url = wp_get_attachment_url( $attachment_id );
        if ( $url ) {
            return $url;
        }
    }

    return $fallback;
}

function premiaspine_landing_resolve_media_source( $value ) {
    $value = trim( (string) $value );
    if ( '' === $value ) {
        return '';
    }

    if ( ctype_digit( $value ) ) {
        return premiaspine_landing_attachment_file_url( $value );
    }

    if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
        return $value;
    }

    return '';
}

function premiaspine_landing_youtube_hero_embed_url( $url ) {
    $video_id = premiaspine_landing_youtube_video_id( $url );
    if ( '' === $video_id ) {
        return '';
    }

    $origin = rawurlencode( home_url( '/' ) );

    return sprintf(
        'https://www.youtube.com/embed/%1$s?autoplay=1&mute=1&loop=1&playlist=%1$s&rel=0&modestbranding=1&playsinline=1&origin=%2$s',
        $video_id,
        $origin
    );
}

function premiaspine_landing_is_direct_video_url( $url ) {
    return (bool) preg_match( '/\.(mp4|webm|ogg|mov)(\?.*)?$/i', (string) $url );
}

function premiaspine_landing_get_patient_hero_default_images() {
    $images = array();

    for ( $i = 1; $i <= 8; $i++ ) {
        $images[] = array(
            'image'    => 0,
            'fallback' => get_theme_file_uri( 'assets/img/hero-reviews/' . sprintf( '%02d', ( ( $i - 1 ) % 4 ) + 1 ) . '.webp' ),
        );
    }

    return $images;
}

function premiaspine_landing_normalize_patient_hero_image_rows( $rows ) {
    $normalized = array();

    foreach ( (array) $rows as $row ) {
        $row = (array) $row;

        if ( ! empty( $row['fallback'] ) ) {
            $normalized[] = $row;
            continue;
        }

        $image_id = premiaspine_landing_opt( $row, array( 'image' ) );
        if ( $image_id ) {
            $normalized[] = array( 'image' => $image_id );
        }
    }

    return $normalized;
}

function premiaspine_landing_prepare_patient_hero_slide_images( $slide_images, $default_images = array() ) {
    $images = premiaspine_landing_normalize_patient_hero_image_rows( $slide_images );

    if ( empty( $images ) ) {
        $images = premiaspine_landing_normalize_patient_hero_image_rows( $default_images );
    }

    if ( empty( $images ) ) {
        return premiaspine_landing_get_patient_hero_default_images();
    }

    // Animation: 4 columns, each with 2 stacked images (keyframes move by -50%).
    if ( count( $images ) <= 4 ) {
        $duplicated = array();

        foreach ( $images as $row ) {
            $duplicated[] = $row;
            $duplicated[] = $row;
        }

        $images = $duplicated;
    }

    return array_slice( $images, 0, 8 );
}

function premiaspine_landing_migrate_legacy_hero_slides( $options ) {
    $slides = (array) premiaspine_landing_opt( $options, array( 'hero_slides', 'slides' ), array() );
    if ( ! empty( $slides ) ) {
        return $slides;
    }

    $migrated = array();
    $header   = (array) premiaspine_landing_opt( $options, array( 'header' ), array() );

    if (
        premiaspine_landing_opt( $header, array( 'top_title' ) )
        || premiaspine_landing_opt( $header, array( 'top_content' ) )
        || premiaspine_landing_opt( $header, array( 'top_section_bg' ) )
        || premiaspine_landing_opt( $header, array( 'doctor_photo' ) )
        || premiaspine_landing_opt( $header, array( 'doctror_name' ) )
    ) {
        $migrated[] = array(
            'slide_type' => 'doctor',
            'doctor'     => array(
                'bg'             => premiaspine_landing_opt( $header, array( 'top_section_bg' ) ),
                'doctor_photo'   => premiaspine_landing_opt( $header, array( 'doctor_photo' ) ),
                'doctor_name'    => premiaspine_landing_opt( $header, array( 'doctror_name' ) ),
                'doctor_address' => '',
                'title'          => premiaspine_landing_opt( $header, array( 'top_title' ) ),
                'icon'           => '',
                'text'           => premiaspine_landing_opt( $header, array( 'top_content' ) ),
            ),
        );
    }

    $hero_patient = (array) premiaspine_landing_opt( $options, array( 'hero_patient' ), array() );
    if ( premiaspine_landing_section_visible( $hero_patient ) ) {
        $migrated[] = array(
            'slide_type' => 'patient',
            'patient'    => array(
                'title' => premiaspine_landing_opt( $hero_patient, array( 'title' ) ),
                'name'  => premiaspine_landing_opt( $hero_patient, array( 'name' ) ),
                'video' => '',
            ),
        );
    }

    return $migrated;
}

function premiaspine_landing_get_hero_slides( $options ) {
    $slides = premiaspine_landing_migrate_legacy_hero_slides( $options );

    return array_values(
        array_filter(
            (array) $slides,
            static function ( $slide ) {
                $type = premiaspine_landing_opt( (array) $slide, array( 'slide_type' ) );

                return 'doctor' === $type || 'patient' === $type;
            }
        )
    );
}

function premiaspine_landing_is_meta_checkbox_checked( $value, $checked_values = array( 'on', 'disable', '1', 1, true ) ) {
    if ( $value === '' || $value === null ) {
        return false;
    }

    return in_array( $value, $checked_values, true );
}

function premiaspine_landing_hero_slide_link_open( $slide ) {
    $url = trim( (string) premiaspine_landing_opt( (array) $slide, array( 'link_url' ) ) );
    if ( '' === $url ) {
        return '';
    }

    $new_tab = premiaspine_landing_opt( (array) $slide, array( 'link_new_tab' ) );
    $target  = premiaspine_landing_is_meta_checkbox_checked( $new_tab )
        ? ' target="_blank" rel="noopener noreferrer"'
        : '';

    return '<a class="hero-slide__link" href="' . esc_url( $url ) . '"' . $target . '>';
}

function premiaspine_landing_hero_slide_link_close( $slide ) {
    $url = trim( (string) premiaspine_landing_opt( (array) $slide, array( 'link_url' ) ) );

    return '' !== $url ? '</a>' : '';
}

function premiaspine_landing_render_hero_doctor_slide( $slide ) {
    $slide  = (array) $slide;
    $doctor = (array) premiaspine_landing_opt( $slide, array( 'doctor' ), array() );
    $bg_url = premiaspine_landing_attachment_url( premiaspine_landing_opt( $doctor, array( 'bg' ) ) );
    ?>
    <div class="hero-doctor">
        <?php echo premiaspine_landing_hero_slide_link_open( $slide ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="holder"<?php if ( $bg_url ) : ?> style="background-image: url('<?php echo esc_url( $bg_url ); ?>');"<?php endif; ?>>
            <?php if ( $title = premiaspine_landing_opt( $doctor, array( 'title' ) ) ) : ?>
                <h1><?php echo premiaspine_landing_render_field_html( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
            <?php endif; ?>
            <?php
            $doctor_name    = premiaspine_landing_opt( $doctor, array( 'doctor_name' ) );
            $doctor_address = premiaspine_landing_opt( $doctor, array( 'doctor_address' ) );
            $name_parts     = array();
            if ( $doctor_name ) {
                $name_parts[] = premiaspine_landing_render_field_html( $doctor_name );
            }
            if ( $doctor_address ) {
                $name_parts[] = premiaspine_landing_render_field_html( $doctor_address );
            }
            $name_inner = implode( '<br />', $name_parts );

            $text     = premiaspine_landing_opt( $doctor, array( 'text' ) );
            $icon_url = premiaspine_landing_attachment_url( premiaspine_landing_opt( $doctor, array( 'icon' ) ) );
            if ( $text || $icon_url ) :
                ?>
                <div class="top-section-content">
                    <?php if ( '' !== $name_inner ) : ?>
                        <span class="name name--content"><?php echo $name_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <?php if ( $icon_url ) : ?>
                        <p><img alt="" class="alignleft size-full" src="<?php echo esc_url( $icon_url ); ?>"></p>
                    <?php endif; ?>
                    <?php if ( $text ) : ?>
                        <?php echo premiaspine_landing_render_editor_html( $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ( '' !== $name_inner ) : ?>
                <span class="name name--holder"><?php echo $name_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <?php endif; ?>
            <div class="person">
                <img src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $doctor, array( 'doctor_photo' ) ), get_stylesheet_directory_uri() . '/images/person.png' ) ); ?>" alt="">
            </div>
        </div>
        <?php echo premiaspine_landing_hero_slide_link_close( $slide ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
    <?php
}

function premiaspine_landing_render_hero_patient_slide( $slide, $default_images = array() ) {
    $slide   = (array) $slide;
    $patient = (array) premiaspine_landing_opt( $slide, array( 'patient' ), array() );
    $video   = premiaspine_landing_opt( $patient, array( 'video' ) );
    $media   = premiaspine_landing_resolve_media_source( $video );
    $youtube = $media ? premiaspine_landing_youtube_hero_embed_url( $media ) : '';
    $is_file = false;

    if ( $media && ! $youtube ) {
        if ( ctype_digit( trim( (string) $video ) ) ) {
            $mime    = get_post_mime_type( absint( $video ) );
            $is_file = $mime && 0 === strpos( $mime, 'video/' );
        } else {
            $is_file = premiaspine_landing_is_direct_video_url( $media );
        }
    }

    if ( empty( $default_images ) ) {
        $default_images = premiaspine_landing_get_patient_hero_default_images();
    }
    ?>
    <div class="hero-premia__patient patient-hero">
        <?php echo premiaspine_landing_hero_slide_link_open( $slide ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="patient-hero__body">
            <?php if ( $hero_title = premiaspine_landing_opt( $patient, array( 'title' ) ) ) : ?>
                <h2 class="patient-hero__title"><?php echo premiaspine_landing_render_editor_html( $hero_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
            <?php endif; ?>
            <?php if ( $hero_name = premiaspine_landing_opt( $patient, array( 'name' ) ) ) : ?>
                <div class="patient-hero__name"><?php echo premiaspine_landing_render_field_html( $hero_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>
        </div>
        <?php if ( $youtube ) : ?>
            <div class="patient-hero__bg-video patient-hero__bg-youtube" aria-hidden="true">
                <iframe allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy" src="<?php echo esc_url( $youtube ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( (string) premiaspine_landing_opt( $patient, array( 'title' ) ) ) ); ?>"></iframe>
            </div>
        <?php elseif ( $is_file ) : ?>
            <div class="patient-hero__bg-video" aria-hidden="true">
                <video autoplay muted loop playsinline preload="auto">
                    <source src="<?php echo esc_url( $media ); ?>">
                </video>
            </div>
        <?php else : ?>
            <div class="patient-hero__images">
                <?php
                $slide_images = premiaspine_landing_prepare_patient_hero_slide_images(
                    premiaspine_landing_opt( $patient, array( 'images' ), array() ),
                    $default_images
                );
                $chunks       = array_chunk( $slide_images, 2 );

                foreach ( $chunks as $chunk ) :
                    ?>
                    <div class="patient-hero__block">
                        <?php foreach ( $chunk as $image_row ) : ?>
                            <?php
                            $image_url = ! empty( $image_row['fallback'] )
                                ? $image_row['fallback']
                                : premiaspine_landing_attachment_url(
                                    premiaspine_landing_opt( $image_row, array( 'image' ) ),
                                    get_theme_file_uri( 'assets/img/hero-reviews/01.webp' )
                                );
                            ?>
                            <div class="patient-hero__image">
                                <img alt="Image" src="<?php echo esc_url( $image_url ); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php echo premiaspine_landing_hero_slide_link_close( $slide ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
    <?php
}

function premiaspine_landing_copy_options_from_page( $target_id, $source_id ) {
    $source_id = absint( $source_id );
    $target_id = absint( $target_id );

    if ( ! $source_id || ! $target_id || ! current_user_can( 'edit_post', $target_id ) ) {
        return false;
    }

    $options = get_post_meta( $source_id, 'landing_page_options', true );

    if ( empty( $options ) || ! is_array( $options ) ) {
        return false;
    }

    return (bool) update_post_meta( $target_id, 'landing_page_options', $options );
}

function premiaspine_landing_apply_codi_defaults( $options ) {
    if ( ! is_array( $options ) ) {
        $options = array();
    }

    $hero_slides = (array) premiaspine_landing_opt( $options, array( 'hero_slides', 'slides' ), array() );
    if ( empty( $hero_slides ) && empty( premiaspine_landing_opt( $options, array( 'hero_patient', 'title' ) ) ) ) {
        $options['hero_patient']['title'] = '<p>Amazing results.<br>I have a new<br>lease on life!</p>';
        $options['hero_patient']['name']  = 'Jim R';
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'patient_stories_slider', 'title' ) ) ) ) {
        $options['patient_stories_slider']['title'] = premiaspine_landing_opt( $options, array( 'testimonials', 'title' ), 'Our Patient Stories' );
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'patient_stories_slider', 'items' ) ) ) ) {
        $mapped = array();
        foreach ( (array) premiaspine_landing_opt( $options, array( 'testimonials', 'testimonials' ), array() ) as $row ) {
            $image_id = premiaspine_landing_opt( $row, array( 'image' ) );
            $youtube_link = trim( (string) premiaspine_landing_opt( $row, array( 'link' ) ) );
            $popup_gallery = array();
            if ( $image_id ) {
                $popup_gallery[] = array( 'image' => $image_id );
            }
            if ( $youtube_link ) {
                $popup_gallery[] = array( 'youtube_url' => $youtube_link );
            }
            $mapped[] = array(
                'title'         => premiaspine_landing_opt( $row, array( 'title_for_video' ) ),
                'tag'           => premiaspine_landing_opt( $row, array( 'description' ) ),
                'text'          => premiaspine_landing_opt( $row, array( 'text' ) ),
                'image'         => $image_id,
                'popup_text'    => premiaspine_landing_opt( $row, array( 'text' ) ),
                'popup_gallery' => $popup_gallery,
            );
        }
        if ( ! empty( $mapped ) ) {
            $options['patient_stories_slider']['items'] = $mapped;
        }
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'surgeons_stories_slider', 'title' ) ) ) ) {
        $options['surgeons_stories_slider']['title'] = 'Our Patient Stories';
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'premia_benefits', 'title' ) ) ) ) {
        $options['premia_benefits']['title']    = premiaspine_landing_opt( $options, array( 'intro_content', 'title' ), 'Regain natural spine motion.' );
        $options['premia_benefits']['subtitle'] = 'Conquer lumbar spinal stenosis and spondylolisthesis.';
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'premia_benefits', 'info_items' ) ) ) ) {
        $options['premia_benefits']['info_items'] = array(
            array( 'text' => "Freedom\nfrom fusion" ),
            array( 'text' => "Protect your\nadjacent levels" ),
            array( 'text' => "Keep\nmoving" ),
        );
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'premia_benefits', 'slides' ) ) ) ) {
        $qualify_items = array();
        foreach ( (array) premiaspine_landing_opt( $options, array( 'intro_content', 'advantages' ), array() ) as $row ) {
            if ( ! empty( $row['text'] ) ) {
                $qualify_items[] = $row['text'];
            }
        }

        $benefit_items = array();
        foreach ( (array) premiaspine_landing_opt( $options, array( 'header', 'benefits' ), array() ) as $row ) {
            if ( ! empty( $row['benefit'] ) ) {
                $benefit_items[] = $row['benefit'];
            }
        }

        $options['premia_benefits']['slides'] = array(
            array(
                'title'      => premiaspine_landing_opt( $options, array( 'intro_content', 'title' ), 'You qualify for TOPS™ with...' ),
                'blue_style' => 'disable',
                'list'       => implode( "\n", $qualify_items ),
            ),
            array(
                'title'      => premiaspine_landing_opt( $options, array( 'header', 'title_benefits' ), 'The TOPS™ Benefits are...' ),
                'blue_style' => 'on',
                'list'       => implode( "\n", $benefit_items ),
            ),
        );
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'about_section', 'title' ) ) ) ) {
        $options['about_section']['title'] = premiaspine_landing_opt( $options, array( 'content_area', 'title' ), 'What is TOPS™?' );
    }

    if ( empty( premiaspine_landing_opt( $options, array( 'about_section', 'content_top' ) ) ) ) {
        $chunks = (array) premiaspine_landing_opt( $options, array( 'content_area', 'text' ), array() );
        if ( ! empty( $chunks[0]['text'] ) ) {
            $options['about_section']['content_top'] = $chunks[0]['text'];
        }
        if ( ! empty( $chunks[1]['text'] ) ) {
            $options['about_section']['content_bottom'] = $chunks[1]['text'];
        }
    }

    // if ( empty( premiaspine_landing_opt( $options, array( 'footer_codi', 'copyright' ) ) ) ) {
    //     $theme_options = get_option( 'theme_options', true );
    //     $options['footer_codi']['copyright'] = premiaspine_landing_opt( (array) $theme_options, array( 'footer', 'copyright' ), 'All Rights Reserved to Premia Spine ©2026' );
    // }

    if ( function_exists( 'premiaspine_landing_apply_remote_codi_stories' ) ) {
        $options = premiaspine_landing_apply_remote_codi_stories( $options );
    }

    return $options;
}

function premiaspine_landing_story_item_has_content( $item ) {
    if ( ! is_array( $item ) ) {
        return false;
    }

    if ( premiaspine_landing_opt( $item, array( 'title' ) ) ) {
        return true;
    }
    if ( premiaspine_landing_opt( $item, array( 'tag' ) ) ) {
        return true;
    }
    if ( premiaspine_landing_opt( $item, array( 'text' ) ) ) {
        return true;
    }
    if ( premiaspine_landing_opt( $item, array( 'video_link' ) ) ) {
        return true;
    }
    if ( ! empty( premiaspine_landing_opt( $item, array( 'image' ) ) ) ) {
        return true;
    }

    return false;
}

function premiaspine_landing_filter_story_items( $items ) {
    if ( empty( $items ) || ! is_array( $items ) ) {
        return array();
    }

    return array_values(
        array_filter(
            $items,
            'premiaspine_landing_story_item_has_content'
        )
    );
}

function premiaspine_landing_normalize_story_item( $story ) {
    if ( ! is_array( $story ) ) {
        return array();
    }

    $gallery = (array) premiaspine_landing_opt( $story, array( 'popup_gallery' ), array() );
    $youtube = premiaspine_landing_surgeon_story_youtube_url( $story );

    if ( $youtube && empty( $gallery ) ) {
        $story['popup_gallery'] = array(
            array(
                'youtube_url' => $youtube,
            ),
        );
    }

    return $story;
}

function premiaspine_landing_get_story_from_post( $post_id ) {
    $post_id = (int) $post_id;
    if ( $post_id <= 0 ) {
        return array();
    }

    $data  = get_post_meta( $post_id, 'story_data', true );
    $story = array();

    if ( is_array( $data ) ) {
        if ( array_key_exists( 'tag', $data ) || array_key_exists( 'text', $data ) || array_key_exists( 'image', $data ) || array_key_exists( 'popup_gallery', $data ) ) {
            $story = $data;
        } elseif ( ! empty( $data['patient'] ) && is_array( $data['patient'] ) ) {
            $story = $data['patient'];
        } elseif ( ! empty( $data['surgeon'] ) && is_array( $data['surgeon'] ) ) {
            $story = $data['surgeon'];
        }
    }

    unset( $story['title'] );

    $post_title = get_the_title( $post_id );
    if ( $post_title ) {
        $story['title'] = $post_title;
    }

    return premiaspine_landing_normalize_story_item( (array) $story );
}

function premiaspine_landing_resolve_story_items( $rows ) {
    $items = array();

    foreach ( (array) $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $story_post_id = (int) premiaspine_landing_opt( $row, array( 'post_id' ) );
        if ( ! $story_post_id ) {
            $story_post_id = (int) premiaspine_landing_opt( $row, array( 'story_id' ) );
        }

        if ( $story_post_id > 0 ) {
            $story = premiaspine_landing_get_story_from_post( $story_post_id );
            if ( ! empty( $story ) ) {
                $items[] = $story;
                continue;
            }
        }

        if ( premiaspine_landing_story_item_has_content( $row ) ) {
            $items[] = premiaspine_landing_normalize_story_item( $row );
        }
    }

    return $items;
}

function premiaspine_landing_render_map_filters() {
    ?>
    <div class="find-doctor-map-filters find-doctor-map-filters--above find-doctor-map-filters--landing" role="form" aria-label="<?php esc_attr_e( 'Filter doctors on map', 'premiaspine' ); ?>">
        <h2 class="find-doctor-map-filters__title"><?php esc_html_e( 'Quick Search Options', 'premiaspine' ); ?></h2>
        <div class="find-doctor-map-filters__row">
            <div class="find-doctor-map-filters__field">
                <label class="find-doctor-map-filters__label" for="fdf-state">
                    <?php esc_html_e( 'Search by State', 'premiaspine' ); ?>
                    <span class="find-doctor-map-filters__tooltip">
                        <div class="find-doctor-map-filters__tooltip-content">
                            <?php esc_html_e( 'Only states with active doctors are selectable. This field is optional.', 'premiaspine' ); ?>
                        </div>
                    </span>
                </label>
                <div class="find-doctor-map-filters__select-wrap" data-fdf-field="state">
                    <input type="text" id="fdf-state" class="find-doctor-map-filters__input" autocomplete="off" placeholder="<?php esc_attr_e( 'Start typing requested state', 'premiaspine' ); ?>" data-fdf-value="">
                    <button type="button" class="find-doctor-map-filters__clear" aria-label="<?php esc_attr_e( 'Clear', 'premiaspine' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8" fill="none" aria-hidden="true" focusable="false">
                            <path opacity="0.5" d="M4.80002 4L8 7.19998L7.19998 8L4 4.80002L0.800023 8L0 7.19998L3.19998 4L0 0.800022L0.800023 0L4 3.19998L7.19998 0L8 0.800022L4.80002 4Z" fill="black"/>
                        </svg>
                    </button>
                    <ul class="find-doctor-map-filters__list" role="listbox" aria-hidden="true"></ul>
                </div>
            </div>
            <div class="find-doctor-map-filters__field">
                <label class="find-doctor-map-filters__label" for="fdf-city">
                    <?php esc_html_e( 'Search by City', 'premiaspine' ); ?>
                    <span class="find-doctor-map-filters__tooltip">
                        <div class="find-doctor-map-filters__tooltip-content">
                            <?php esc_html_e( 'Only cities with active doctors in the state selected are selectable. This field is optional.', 'premiaspine' ); ?>
                        </div>
                    </span>
                </label>
                <div class="find-doctor-map-filters__select-wrap" data-fdf-field="city">
                    <input type="text" id="fdf-city" class="find-doctor-map-filters__input" autocomplete="off" placeholder="<?php esc_attr_e( 'Start typing requested city', 'premiaspine' ); ?>" data-fdf-value="">
                    <button type="button" class="find-doctor-map-filters__clear" aria-label="<?php esc_attr_e( 'Clear', 'premiaspine' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8" fill="none" aria-hidden="true" focusable="false">
                            <path opacity="0.5" d="M4.80002 4L8 7.19998L7.19998 8L4 4.80002L0.800023 8L0 7.19998L3.19998 4L0 0.800022L0.800023 0L4 3.19998L7.19998 0L8 0.800022L4.80002 4Z" fill="black"/>
                        </svg>
                    </button>
                    <ul class="find-doctor-map-filters__list" role="listbox" aria-hidden="true"></ul>
                </div>
            </div>
            <div class="find-doctor-map-filters__field">
                <label class="find-doctor-map-filters__label" for="fdf-doctor">
                    <?php esc_html_e( 'Search by Doctor\'s Name', 'premiaspine' ); ?>
                    <span class="find-doctor-map-filters__tooltip">
                        <div class="find-doctor-map-filters__tooltip-content">
                            <?php esc_html_e( 'Only doctors in the state / city selected are selectable. This field is optional.', 'premiaspine' ); ?>
                        </div>
                    </span>
                </label>
                <div class="find-doctor-map-filters__select-wrap" data-fdf-field="doctor">
                    <input type="text" id="fdf-doctor" class="find-doctor-map-filters__input" autocomplete="off" placeholder="<?php esc_attr_e( 'Start typing requested doctor\'s name', 'premiaspine' ); ?>" data-fdf-value="">
                    <button type="button" class="find-doctor-map-filters__clear" aria-label="<?php esc_attr_e( 'Clear', 'premiaspine' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8" fill="none" aria-hidden="true" focusable="false">
                            <path opacity="0.5" d="M4.80002 4L8 7.19998L7.19998 8L4 4.80002L0.800023 8L0 7.19998L3.19998 4L0 0.800022L0.800023 0L4 3.19998L7.19998 0L8 0.800022L4.80002 4Z" fill="black"/>
                        </svg>
                    </button>
                    <ul class="find-doctor-map-filters__list" role="listbox" aria-hidden="true"></ul>
                </div>
            </div>
            <div class="find-doctor-map-filters__submit-wrap">
                <button type="button" class="find-doctor-map-filters__submit fls-button" data-fdf-submit disabled>
                    <span class="find-doctor-map-filters__submit-text"><?php esc_html_e( 'Show Results', 'premiaspine' ); ?></span>
                    <span class="find-doctor-map-filters__submit-count" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
    <?php
}

function premiaspine_landing_story_popup_id( $story_type, $index ) {
    return sanitize_html_class( $story_type . '-story-popup-' . (int) $index );
}

function premiaspine_landing_about_button_youtube_url( $about, $button_index ) {
    $button_index = (int) $button_index;
    $url          = premiaspine_landing_opt( $about, array( 'button_' . $button_index . '_youtube_url' ) );

    if ( ! $url ) {
        $url = premiaspine_landing_opt( $about, array( 'button_' . $button_index . '_link' ) );
    }

    return trim( (string) $url );
}

function premiaspine_landing_about_button_popup_title( $about, $button_index ) {
    $button_index = (int) $button_index;
    $title        = premiaspine_landing_opt( $about, array( 'popup_' . $button_index . '_title' ) );

    if ( ! $title ) {
        $title = premiaspine_landing_opt( $about, array( 'button_' . $button_index . '_text' ) );
    }

    return trim( (string) $title );
}

function premiaspine_landing_about_button_has_video( $about, $button_index ) {
    return '' !== premiaspine_landing_youtube_video_id(
        premiaspine_landing_about_button_youtube_url( $about, $button_index )
    );
}

function premiaspine_landing_about_video_popup_id( $button_index ) {
    return sanitize_html_class( 'about-video-popup-' . (int) $button_index );
}

function premiaspine_landing_render_youtube_popup_video( $title, $youtube_url, $autoplay = false ) {
    $embed_url = premiaspine_landing_youtube_embed_url( $youtube_url, $autoplay );
    if ( ! $embed_url ) {
        return;
    }

    $video_title = $title ? $title : __( 'YouTube video', 'premiaspine' );
    ?>
    <div class="info-popup__video">
        <iframe
            data-deferred-youtube-src="<?php echo esc_url( $embed_url ); ?>"
            <?php if ( $autoplay ) : ?>
                data-youtube-autoplay="1"
            <?php endif; ?>
            title="<?php echo esc_attr( $video_title ); ?>"
            frameborder="0"
            allowfullscreen
            allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
        ></iframe>
    </div>
    <?php
}

function premiaspine_landing_render_youtube_popup( $popup_id, $title, $youtube_url, $autoplay = true ) {
    $embed_url = premiaspine_landing_youtube_embed_url( $youtube_url, $autoplay );
    if ( ! $embed_url ) {
        return;
    }

    $popup_id = sanitize_html_class( $popup_id );
    ?>
    <div data-fls-popup="<?php echo esc_attr( $popup_id ); ?>" aria-hidden="true" class="popup popup--info">
        <div data-fls-popup-wrapper="" class="popup__wrapper">
            <div data-fls-popup-body="" class="popup__body">
                <div class="info-popup">
                    <div class="info-popup__header test">
                        <div class="info-popup__text-block text-block">
                            <?php if ( $title ) : ?>
                                <h2 class="text-block__title --wide"><?php echo esc_html( $title ); ?></h2>
                            <?php endif; ?>
                        </div>
                        <button type="button" data-fls-popup-close class="info-popup__close-btn pc"><?php esc_html_e( 'close', 'premiaspine' ); ?></button>
                        <button type="button" data-fls-popup-close class="info-popup__close-icon mobile _sprite-cross" aria-label="<?php esc_attr_e( 'Close', 'premiaspine' ); ?>"></button>
                    </div>
                    <?php premiaspine_landing_render_youtube_popup_video( $title, $youtube_url, $autoplay ); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function premiaspine_landing_render_about_video_button( $about, $button_index ) {
    $text = premiaspine_landing_opt( $about, array( 'button_' . $button_index . '_text' ) );
    if ( ! $text ) {
        return;
    }

    $has_video = premiaspine_landing_about_button_has_video( $about, $button_index );
    $popup_id  = premiaspine_landing_about_video_popup_id( $button_index );

    if ( $has_video ) {
        ?>
        <button type="button" class="about-pr__button _sprite-play" data-fls-popup-link="<?php echo esc_attr( $popup_id ); ?>">
            <?php echo esc_html( $text ); ?>
        </button>
        <?php
        return;
    }
    ?>
    <button type="button" class="about-pr__button _sprite-play" tabindex="-1" aria-hidden="true">
        <?php echo esc_html( $text ); ?>
    </button>
    <?php
}

function premiaspine_landing_render_about_video_popups( $about ) {
    foreach ( array( 1, 2 ) as $button_index ) {
        if ( ! premiaspine_landing_about_button_has_video( $about, $button_index ) ) {
            continue;
        }

        premiaspine_landing_render_youtube_popup(
            premiaspine_landing_about_video_popup_id( $button_index ),
            premiaspine_landing_about_button_popup_title( $about, $button_index ),
            premiaspine_landing_about_button_youtube_url( $about, $button_index )
        );
    }
}

function premiaspine_landing_youtube_video_id( $url ) {
    $url = trim( (string) $url );
    if ( '' === $url ) {
        return '';
    }

    $url = preg_replace( '/\s+/u', '', $url );

    $patterns = array(
        '/youtu\.be\/([a-zA-Z0-9_-]{11})/i',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/i',
        '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/i',
        '/[?&]v=([a-zA-Z0-9_-]{11})/i',
    );

    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $url, $matches ) ) {
            return $matches[1];
        }
    }

    return '';
}

function premiaspine_landing_youtube_embed_url( $url, $autoplay = false, $mute = false ) {
    $video_id = premiaspine_landing_youtube_video_id( $url );
    if ( '' === $video_id ) {
        return '';
    }

    $origin = rawurlencode( home_url( '/' ) );

    return sprintf(
        'https://www.youtube.com/embed/%1$s?autoplay=%3$s&mute=%4$s&rel=0&modestbranding=1&enablejsapi=1&playsinline=1&origin=%2$s',
        $video_id,
        $origin,
        $autoplay ? '1' : '0',
        $mute ? '1' : '0'
    );
}

function premiaspine_landing_get_youtube_embed_html( $url, $title = '' ) {
    $video_id = premiaspine_landing_youtube_video_id( $url );
    if ( '' === $video_id ) {
        return '';
    }

    $watch_url = 'https://www.youtube.com/watch?v=' . $video_id;
    $embed     = wp_oembed_get(
        $watch_url,
        array(
            'width'  => 1092,
            'height' => 614,
        )
    );

    if ( $embed ) {
        $embed = preg_replace( '/\s(width|height)="[^"]*"/i', '', $embed );
        if ( false === stripos( $embed, 'autoplay=1' ) ) {
            $embed = preg_replace_callback(
                '/src="([^"]+)"/i',
                function ( $matches ) {
                    $src       = $matches[1];
                    $separator = ( false === strpos( $src, '?' ) ) ? '?' : '&';

                    return 'src="' . $src . $separator . 'autoplay=1"';
                },
                $embed,
                1
            );
        }
        if ( $title && false === stripos( $embed, 'title=' ) ) {
            $embed = preg_replace( '/<iframe/i', '<iframe title="' . esc_attr( $title ) . '"', $embed, 1 );
        }

        $embed_url = premiaspine_landing_youtube_embed_url( $url );
        if ( $embed_url && false === stripos( $embed, 'data-youtube-src' ) ) {
            $embed = preg_replace(
                '/<iframe/i',
                '<iframe data-youtube-src="' . esc_attr( $embed_url ) . '"',
                $embed,
                1
            );
        }

        return $embed;
    }

    $embed_url = premiaspine_landing_youtube_embed_url( $url, false );
    if ( ! $embed_url ) {
        return '';
    }

    return sprintf(
        '<iframe data-youtube-src="%1$s" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture" title="%2$s" frameborder="0"></iframe>',
        esc_url( $embed_url ),
        esc_attr( $title ? $title : __( 'YouTube video', 'premiaspine' ) )
    );
}

function premiaspine_landing_defer_youtube_iframes_in_html( $html ) {
    if ( ! is_string( $html ) || '' === $html ) {
        return $html;
    }

    return preg_replace_callback(
        '/<iframe\b[^>]*\bsrc=(["\'])([^"\']+)\1[^>]*>/i',
        function ( $matches ) {
            $src = preg_replace( '/([?&])autoplay=1\b/i', '$1autoplay=0', $matches[2] );
            $tag = preg_replace( '/\bsrc=(["\'])[^"\']+\1/i', '', $matches[0] );
            $tag = preg_replace( '/\s+>/', '>', $tag );
            $tag = rtrim( $tag, '>' );

            return $tag . ' data-deferred-youtube-src="' . esc_attr( $src ) . '">';
        },
        $html
    );
}

function premiaspine_landing_patient_popup_gallery_slides( $item, $default_image ) {
    $slides = array();

    foreach ( (array) premiaspine_landing_opt( $item, array( 'popup_gallery' ), array() ) as $row ) {
        $youtube_url = trim( (string) premiaspine_landing_opt( $row, array( 'youtube_url' ) ) );

        if ( premiaspine_landing_youtube_video_id( $youtube_url ) ) {
            $slides[] = array(
                'type' => 'youtube',
                'url'  => $youtube_url,
            );
            continue;
        }

        $image_id = premiaspine_landing_opt( $row, array( 'image' ) );
        if ( ! $image_id ) {
            continue;
        }

        $image_url = premiaspine_landing_attachment_url( $image_id, $default_image );
        if ( ! $image_url ) {
            continue;
        }

        $slides[] = array(
            'type' => 'image',
            'url'  => $image_url,
        );
    }

    return $slides;
}

function premiaspine_landing_patient_popup_gallery_urls( $item, $default_image ) {
    $urls = array();

    foreach ( premiaspine_landing_patient_popup_gallery_slides( $item, $default_image ) as $slide ) {
        if ( 'image' === $slide['type'] && ! empty( $slide['url'] ) ) {
            $urls[] = $slide['url'];
        }
    }

    return array_values( array_unique( $urls ) );
}

function premiaspine_landing_render_patient_popup_gallery_slide( $slide, $title ) {
    if ( 'youtube' === $slide['type'] ) {
        $embed_url = premiaspine_landing_youtube_embed_url( $slide['url'], false );
        if ( ! $embed_url ) {
            return;
        }

        $video_title = $title ? $title : __( 'YouTube video', 'premiaspine' );
        ?>
        <li class="splide__slide">
            <div class="info-popup__slide info-popup__slide--video">
                <div class="info-popup__video info-popup__video--slide">
                    <iframe
                        data-deferred-youtube-src="<?php echo esc_url( $embed_url ); ?>"
                        title="<?php echo esc_attr( $video_title ); ?>"
                        frameborder="0"
                        allowfullscreen
                        allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                    ></iframe>
                </div>
            </div>
        </li>
        <?php
        return;
    }

    if ( empty( $slide['url'] ) ) {
        return;
    }
    ?>
    <li class="splide__slide">
        <div class="info-popup__slide info-popup__slide--image">
            <img class="ibg" alt="<?php echo esc_attr( $title ); ?>" src="<?php echo esc_url( $slide['url'] ); ?>">
        </div>
    </li>
    <?php
}

function premiaspine_landing_patient_story_has_popup( $item ) {
    if ( premiaspine_landing_opt( $item, array( 'popup_text' ) ) ) {
        return true;
    }

    if ( ! empty( premiaspine_landing_patient_popup_gallery_slides( $item, '' ) ) ) {
        return true;
    }

    return '' !== premiaspine_landing_youtube_video_id( premiaspine_landing_surgeon_story_youtube_url( $item ) );
}

function premiaspine_landing_surgeon_story_youtube_url( $item ) {
    $url = premiaspine_landing_opt( $item, array( 'popup_youtube_url' ) );
    if ( ! $url ) {
        $url = premiaspine_landing_opt( $item, array( 'video_link' ) );
    }

    return trim( (string) $url );
}

function premiaspine_landing_story_has_popup( $item, $story_type = 'patient' ) {
    return premiaspine_landing_patient_story_has_popup( $item );
}

// function premiaspine_landing_story_popup_title( $item, $story_type ) {
//     $popup_title = premiaspine_landing_opt( $item, array( 'popup_title' ) );
//     if ( $popup_title ) {
//         return $popup_title;
//     }

//     return premiaspine_landing_opt( $item, array( 'title' ) );
// }

function premiaspine_landing_render_story_slides( $items, $default_image, $story_type = 'patient' ) {
    $items = premiaspine_landing_filter_story_items( $items );

    if ( empty( $items ) ) {
        return;
    }

    foreach ( $items as $index => $item ) {
        $title    = premiaspine_landing_opt( $item, array( 'title' ) );
        $tag      = premiaspine_landing_opt( $item, array( 'tag' ) );
        $text     = premiaspine_landing_opt( $item, array( 'text' ) );
        $image    = premiaspine_landing_attachment_url( premiaspine_landing_opt( $item, array( 'image' ) ), $default_image );
        $popup_id = premiaspine_landing_story_popup_id( $story_type, $index );
        $has_popup = premiaspine_landing_story_has_popup( $item, $story_type );
        ?>
        <li class="splide__slide">
            <div class="info-item">
                <?php if ( $title ) : ?>
                    <h3 class="info-item__title"><?php echo esc_html( $title ); ?></h3>
                <?php endif; ?>
                <?php if ( $has_popup ) : ?>
                    <div data-fls-popup-link="<?php echo esc_attr( $popup_id ); ?>" class="info-item__image --clickable">
                    <img alt="<?php echo esc_attr( $title ); ?>" class="ibg" src="<?php echo esc_url( $image ); ?>">
                    <?php if ( $tag ) : ?>
                        <div class="info-item__tag"><?php echo esc_html( $tag ); ?></div>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="info-item__image">
                    <img alt="<?php echo esc_attr( $title ); ?>" class="ibg" src="<?php echo esc_url( $image ); ?>">
                    <?php if ( $tag ) : ?>
                        <div class="info-item__tag"><?php echo esc_html( $tag ); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ( $text ) : ?>
                    <div class="info-item__text">
                        <?php echo apply_filters( 'the_content', $text ); ?>
                    </div>
                <?php endif; ?>
                <?php if ( $has_popup ) : ?>
                    <button type="button" class="button info-item__play _sprite-play" data-fls-popup-link="<?php echo esc_attr( $popup_id ); ?>" aria-label="<?php esc_attr_e( 'Open story', 'premiaspine' ); ?>"></button>
                <?php else : ?>
                    <button type="button" class="button info-item__play _sprite-play" tabindex="-1" aria-hidden="true"></button>
                <?php endif; ?>
            </div>
        </li>
        <?php
    }
}

function premiaspine_landing_render_story_popups( $items, $default_image, $story_type = 'patient' ) {
    $items = premiaspine_landing_filter_story_items( $items );

    foreach ( $items as $index => $item ) {
        if ( ! premiaspine_landing_story_has_popup( $item, $story_type ) ) {
            continue;
        }

        $popup_id    = premiaspine_landing_story_popup_id( $story_type, $index );
        // $popup_title = premiaspine_landing_story_popup_title( $item, $story_type );
        $title       = premiaspine_landing_opt( $item, array( 'title' ) );
        ?>
        <div data-fls-popup="<?php echo esc_attr( $popup_id ); ?>" data-story-type="<?php echo esc_attr( $story_type ); ?>" aria-hidden="true" class="popup popup--info">
            <div data-fls-popup-wrapper="" class="popup__wrapper">
                <div data-fls-popup-body="" class="popup__body">
                    <div class="info-popup">
                        <div class="info-popup__header">
                            <div class="info-popup__text-block text-block">
                                <?php if ( $title ) : ?>
                                    <h2 class="text-block__title --wide"><?php echo esc_html( $title ); ?></h2>
                                <?php endif; ?>
                            </div>
                            <button type="button" data-fls-popup-close class="info-popup__close-btn pc"><?php esc_html_e( 'close', 'premiaspine' ); ?></button>
                            <button type="button" data-fls-popup-close class="info-popup__close-icon mobile _sprite-cross" aria-label="<?php esc_attr_e( 'Close', 'premiaspine' ); ?>"></button>
                        </div>
                        <?php
                        $gallery_slides = premiaspine_landing_patient_popup_gallery_slides( $item, $default_image );
                        if ( ! empty( $gallery_slides ) ) :
                            ?>
                            <div class="info-popup__slider splide">
                                <div class="splide__track">
                                    <ul class="splide__list">
                                        <?php
                                        foreach ( $gallery_slides as $gallery_slide ) {
                                            premiaspine_landing_render_patient_popup_gallery_slide( $gallery_slide, $title );
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        <?php else : ?>
                            <?php
                            premiaspine_landing_render_youtube_popup_video(
                                $title,
                                premiaspine_landing_surgeon_story_youtube_url( $item ),
                                true
                            );
                            ?>
                        <?php endif; ?>
                        <?php
                        $popup_text = premiaspine_landing_opt( $item, array( 'popup_text' ) );
                        if ( ! $popup_text ) {
                            $popup_text = premiaspine_landing_opt( $item, array( 'text' ) );
                        }
                        if ( $popup_text ) :
                            ?>
                            <div class="info-popup__text">
                                <?php echo premiaspine_landing_defer_youtube_iframes_in_html( apply_filters( 'the_content', $popup_text ) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

function premiaspine_landing_render_stories_slider( $section, $modifier_class, $default_image, $story_type = 'patient' ) {
    if ( ! premiaspine_landing_section_visible( $section ) ) {
        return;
    }

    $items = premiaspine_landing_filter_story_items(
        premiaspine_landing_resolve_story_items(
            (array) premiaspine_landing_opt( $section, array( 'items' ), array() )
        )
    );
    if ( empty( $items ) ) {
        return;
    }

    $title = premiaspine_landing_opt( $section, array( 'title' ), 'Our Patient Stories' );
    $to_map_button = premiaspine_landing_opt( $section, array( 'to_map_button' ), 'find a doctor' );
    ?>
    <div data-sl-wrapper="" class="premia__slider-section slider-section <?php echo esc_attr( $modifier_class ); ?>">
        <div class="slider-section__gr-container">
            <div class="slider-section__header">
                <div class="slider-section__text-block text-block">
                    <h2 class="text-block__title --wide"><?php echo esc_html( $title ); ?></h2>
                </div>
                <div data-sl-arrows="" class="slider-section__arrows pc">
                    <button type="button" data-sl-arrow-prev="" class="slider-section__arrow _sprite-ch-left"></button>
                    <button type="button" data-sl-arrow-next="" class="slider-section__arrow _sprite-ch-right"></button>
                </div>
            </div>
            <div data-fls-slider="" class="slider-section__slider splide">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php premiaspine_landing_render_story_slides( $items, $default_image, $story_type ); ?>
                    </ul>
                </div>
            </div>
            <?php if ( $story_type !== 'patient' ) : ?>
                <button data-fls-scrollto="#map-section" class="slider-section__button fls-button fls-button--blue">
                    <?php echo esc_html( $to_map_button ); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
    premiaspine_landing_render_story_popups( $items, $default_image, $story_type );
}
