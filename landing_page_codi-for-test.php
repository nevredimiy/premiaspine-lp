<?php
/*
 * Template Name: New Landing Page 2026
 */

require_once get_template_directory() . '/inc/landing-codi-helpers.php';

get_header( 'landing_052026' );

the_post();
$options              = premiaspine_landing_apply_codi_defaults( get_post_meta( get_queried_object_id(), 'landing_page_options', true ) );
$hero_slides          = premiaspine_landing_get_hero_slides( $options );
$benefits             = (array) premiaspine_landing_opt( $options, array( 'premia_benefits' ), array() );
$about                = (array) premiaspine_landing_opt( $options, array( 'about_section' ), array() );
// $footer               = (array) premiaspine_landing_opt( $options, array( 'footer_codi' ), array() );
$theme_options        = get_option( 'theme_options', true );
$footer               = (array) premiaspine_landing_opt( (array) $theme_options, array( 'footer' ), array() );
$patient_default_images = premiaspine_landing_get_patient_hero_default_images();
$to_form_button = premiaspine_landing_opt($options, array('header', 'to_form_button'), '');
// echo '<pre>';
// print_r($options);
// echo '</pre>';
// exit;
?>

<button class="form-show-btn fls-button fls-button--blue">
    <?php echo esc_html( $to_form_button ?: 'click to fill in your details' ); ?>
</button>

<div class="premia__hero hero-premia">
    <div class="hero-premia__container">
        <div class="top-section">
            <div class="hero-premia__wrapper">
                <div class="hero-premia__slider splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ( $hero_slides as $slide ) : ?>
                                <li class="splide__slide">
                                    <?php
                                    if ( 'patient' === premiaspine_landing_opt( (array) $slide, array( 'slide_type' ) ) ) {
                                        premiaspine_landing_render_hero_patient_slide( $slide, $patient_default_images );
                                    } else {
                                        premiaspine_landing_render_hero_doctor_slide( $slide );
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php if ( $contact_form = premiaspine_landing_opt( $options, array( 'header', 'contact_form' ) ) ) : ?>
                    <div class="request-appointment-section">
                        <?php echo do_shortcode( $contact_form ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ( ! isBot() ) : ?>
<div id="main" class="main-alternate">
    <div class="page__premia premia">
        <?php
        premiaspine_landing_render_stories_slider(
            (array) premiaspine_landing_opt( $options, array( 'patient_stories_slider' ), array() ),
            '',
            get_theme_file_uri( 'assets/img/patient.webp' ),
            'patient'
        );
        ?>
        <?php if ( premiaspine_landing_section_visible( $benefits ) ) : ?>
            <div class="premia__benefits benefits-pr">
                <div class="benefits-pr__image">
                    <img alt="Image" class="ibg pc" src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $benefits, array( 'bg_pc' ) ), get_theme_file_uri( 'assets/img/benefits/bg.webp' ) ) ); ?>">
                    <img alt="Image" class="ibg mobile" src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $benefits, array( 'bg_mobile' ) ), get_theme_file_uri( 'assets/img/benefits/bg-mob.webp' ) ) ); ?>">
                </div>
                <div class="benefits-pr__container">
                    <div class="benefits-pr__text-block text-block text-block--blue">
                        <?php if ( $benefits_title = premiaspine_landing_opt( $benefits, array( 'title' ) ) ) : ?>
                            <h2 class="text-block__title"><?php echo esc_html( $benefits_title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $benefits_subtitle = premiaspine_landing_opt( $benefits, array( 'subtitle' ) ) ) : ?>
                            <div class="text-block__subtitle"><?php echo esc_html( $benefits_subtitle ); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php $info_items = (array) premiaspine_landing_opt( $benefits, array( 'info_items' ), array() ); ?>
                    <?php if ( ! empty( $info_items ) ) : ?>
                        <div class="benefits-pr__info">
                            <?php foreach ( $info_items as $index => $info_item ) : ?>

                                <?php if ( !empty( $info_item ) ) : ?>
                                    <div class="benefits-pr__info-item">
                                        <img src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $info_item, array( 'icon' ) ), get_theme_file_uri( 'assets/img/benefits/' . sprintf( '%02d', $index + 1 ) . '.svg' ) ) ); ?>" alt="Image">
                                        <span><?php echo premiaspine_landing_format_inline_html( premiaspine_landing_opt( $info_item, array( 'text' ) ) ); ?></span>
                                    </div>
                                <?php endif; ?>


                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php $slides = (array) premiaspine_landing_opt( $benefits, array( 'slides' ), array() ); ?>
                    <?php if ( ! empty( $slides ) ) : ?>
                        <div data-fls-watcher="" data-fls-watcher-once="" data-fls-watcher-threshold="0.3" class="benefits-pr__slider splide">
                            <div class="splide__track">
                                <ul class="splide__list first-slider">
                                    <?php $i = 0; ?>
                                    <?php foreach ( $slides as $slide ) : ?>
                                        
                                        <?php
                                            $i++;

                                            // второй элемент делаем синим
                                            $is_blue = ($i === 2);

                                            $list = premiaspine_landing_parse_list(
                                                premiaspine_landing_opt( $slide, array( 'list' ) )
                                            );
                                        ?>

                                        <?php
                                        // $is_blue = premiaspine_landing_opt( $slide, array( 'blue_style' ) ) !== 'disable' && premiaspine_landing_opt( $slide, array( 'blue_style' ) ) !== '';
                                        $list    = premiaspine_landing_parse_list( premiaspine_landing_opt( $slide, array( 'list' ) ) );
                                        ?>
                                        <li class="splide__slide">
                                            <div class="benefits-pr__item item-benefits">
                                                <div class="item-benefits__text-block text-block<?php echo $is_blue ? ' text-block--blue' : ''; ?>">
                                                    <?php if ( $slide_title = premiaspine_landing_opt( $slide, array( 'title' ) ) ) : ?>
                                                        <h2 class="text-block__title"><?php echo esc_html( $slide_title ); ?></h2>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ( ! empty( $list ) ) : ?>
                                                    <ul class="item-benefits__list<?php echo $is_blue ? ' item-benefits__list--blue' : ''; ?>">
                                                        <?php foreach ( $list as $list_item ) : ?>
                                                            <li class="item-benefits__l-item"><?php echo premiaspine_landing_format_inline_html( $list_item ); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        premiaspine_landing_render_stories_slider(
            (array) premiaspine_landing_opt( $options, array( 'surgeons_stories_slider' ), array() ),
            'slider-section--surgeons',
            get_theme_file_uri( 'assets/img/surgeon.webp' ),
            'surgeon'
        );
        ?>

        <?php if ( premiaspine_landing_section_visible( $about ) ) : ?>
            <div class="premia__about about-pr">
                <div class="about-pr__container">
                    <?php if ( $about_title = premiaspine_landing_opt( $about, array( 'title' ) ) ) : ?>
                        <div class="about-pr__text-block text-block">
                            <h2 class="text-block__title --wide"><?php echo esc_html( $about_title ); ?></h2>
                        </div>
                    <?php endif; ?>
                    <div class="about-pr__text text">
                        <?php if ( $about_top = premiaspine_landing_opt( $about, array( 'content_top' ) ) ) : ?>
                            <?php echo apply_filters( 'the_content', $about_top ); ?>
                        <?php endif; ?>
                        <?php
                        $about_youtube_embed = premiaspine_landing_youtube_embed_url(
                            premiaspine_landing_opt( $about, array( 'youtube_url' ) ),
                            false
                        );
                        if ( $about_youtube_embed ) :
                            ?>
                            <div class="yt-video">
                                <iframe
                                    allowfullscreen
                                    allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                                    title="<?php echo esc_attr( $about_title ? $about_title : __( 'YouTube video', 'premiaspine' ) ); ?>"
                                    src="<?php echo esc_url( $about_youtube_embed ); ?>"
                                ></iframe>
                            </div>
                        <?php endif; ?>
                        <?php if ( $about_bottom = premiaspine_landing_opt( $about, array( 'content_bottom' ) ) ) : ?>
                            <?php echo apply_filters( 'the_content', $about_bottom ); ?>
                        <?php endif; ?>
                    </div>
                    <div class="about-pr__body">
                        <div class="about-pr__image">
                            <img alt="Image" class="ibg pc" src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $about, array( 'bg_pc' ) ), get_theme_file_uri( 'assets/img/about/bg.webp' ) ) ); ?>">
                            <img alt="Image" class="ibg mobile" src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $about, array( 'bg_mobile' ) ), get_theme_file_uri( 'assets/img/about/bg-mob.webp' ) ) ); ?>">
                        </div>
                        <div class="about-pr__actions">
                            <?php premiaspine_landing_render_about_video_button( $about, 1 ); ?>
                            <?php premiaspine_landing_render_about_video_button( $about, 2 ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php premiaspine_landing_render_about_video_popups( $about ); ?>
        <?php endif; ?>

        <?php if ( premiaspine_landing_opt( $options, array( 'locations', 'title' ) ) || premiaspine_landing_opt( $options, array( 'locations', 'map_shortcode' ) ) ) : ?>
            <div id="map-section" class="premia__map-section map-section">
                <div class="map-section__container">
                    <div class="map-section__text-block text-block">
                        <h2 class="text-block__title --wide"><?php echo esc_html( premiaspine_landing_opt( $options, array( 'map_section', 'title' ), 'Sites Across the US' ) ); ?></h2>
                    </div>
                </div>
                <div class="map-section__big__container">
                    <div class="map-section__body">
                        <div data-fls-spoilers="767.98, max" class="map-section__spoiler">
                            <details class="map-section__details" open data-fls-spoilers-open>
                                <summary class="map-section__title _sprite-ch-down --spoiler-active">Quick Search Options</summary>
                                <div class="map-section__wrapper">
                                    <?php premiaspine_landing_render_map_filters(); ?>
                                </div>
                            </details>
                        </div>
                        <div class="map-section__map find-doctor-map-container map">
                            <?php
                            $map_shortcode = premiaspine_landing_opt( $options, array( 'locations', 'map_shortcode' ), '[put_wpgm id=1]' );
                            echo do_shortcode( $map_shortcode );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $bottom_form = premiaspine_landing_opt( $options, array( 'request_an_appointment_form_3', 'request_an_appointment_form' ) );
        if ( ! $bottom_form ) {
            $bottom_form = premiaspine_landing_opt( $options, array( 'request_an_appointment_form_2', 'request_an_appointment_form' ) );
        }
        ?>
        <?php if ( $bottom_form ) : ?>
            <div class="premia__contact contact-section">
                <div class="contact-section__container">
                    <div class="contact-section__text-block text-block">
                        <h2 class="text-block__title --wide">Get More Information</h2>
                    </div>
                    <div class="contact-section__form">
                        <?php echo do_shortcode( $bottom_form ); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<footer data-fls-footer="" class="footer">
    <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/footer-decor.svg' ) ); ?>" alt="Image" class="footer__decor --pc">
    <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/footer-decor-mob.svg' ) ); ?>" alt="Image" class="footer__decor --mobile">
    <div class="footer__container">
        <div class="footer__logo">
            <img src="<?php echo esc_url( premiaspine_landing_attachment_url( premiaspine_landing_opt( $footer, array( 'logo' ) ), get_theme_file_uri( 'assets/img/logo.svg' ) ) ); ?>" class="ibg ibg--contain" alt="Premia Spine">
        </div>
        <?php if ( $footer_text = premiaspine_landing_opt( $footer, array( 'text' ) ) ) : ?>
            <div class="footer__text"><?php echo premiaspine_landing_render_field_html( $footer_text ); ?></div>
        <?php endif; ?>
        <div class="footer__copy"><?php echo esc_html( premiaspine_landing_opt( $footer, array( 'copyright' ) ) ); ?></div>
    </div>
</footer>


</div>

<?php if(!isBot()) :?>
<?php wp_footer(); ?>
<?php premiaspine_landing_print_chatbot_embed(); ?>
<script type="application/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery-ui.min.js"></script>
<script type="application/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/main.js?v=<?php echo time();?>"></script>
<link href="https://fonts.googleapis.com/css?family=Raleway:400,600,900&display=swap" rel="stylesheet">
<link rel='stylesheet' id='jquery.fancybox.min-css'  href='<?php echo get_stylesheet_directory_uri()?>/css/jquery.fancybox.min.css?v=1580469056&#038;ver=5.3.2' type='text/css' media='all' />
<?php endif;?>
</body>
</html>
<!--
<?php get_footer(); ?>
-->