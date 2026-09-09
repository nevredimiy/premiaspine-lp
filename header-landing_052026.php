<?php $general_options = get_option( 'theme_options', true );?>

<?php
$phone_display = premiaspine_landing_opt(
    (array) $general_options,
    array( 'header', 'phone_display' ),
    '(646)360-0936'
);
$phone_tel = premiaspine_landing_opt(
    (array) $general_options,
    array( 'header', 'phone_tel' ),
    '6463600936'
);
$header_slogan = premiaspine_landing_opt(
    (array) $general_options,
    array( 'header', 'slogan' )
);

$landing_options = (array) get_post_meta( get_queried_object_id(), 'landing_page_options', true );

if ( ! $header_slogan ) {
    $header_slogan = premiaspine_landing_opt(
        $landing_options,
        array( 'header', 'slogan' )
    );
}

/* ---------- Open Graph / social sharing ---------- */
$og_url = get_permalink();
if ( ! $og_url ) {
    $og_url = home_url( '/' );
}

$og_title = wp_strip_all_tags(
    premiaspine_landing_opt( $landing_options, array( 'header', 'top_title' ), wp_get_document_title() )
);

$og_description = trim(
    preg_replace(
        '/\s+/',
        ' ',
        wp_strip_all_tags(
            premiaspine_landing_opt( $landing_options, array( 'header', 'top_content' ), get_bloginfo( 'description' ) )
        )
    )
);
if ( function_exists( 'mb_strlen' ) && mb_strlen( $og_description ) > 200 ) {
    $og_description = rtrim( mb_substr( $og_description, 0, 197 ) ) . '…';
}

/* ---------- Per-story deep link (?patient=<slug> / ?surgeon=<slug>) ----------
 * Overrides the page-level title/description above with the ones set on the
 * matching Testimonial post (fields "Meta title"/"Meta description" on the
 * story's own edit screen), so a shared/ad link to a single story gets its
 * own <title>/OG preview. See inc/landing-codi-helpers.php for how these
 * params are attached to each slider popup, and js/landing-story-popups.js
 * for how the URL is kept in sync client-side.
 */
$story_post = null;
foreach ( array( 'patient', 'surgeon' ) as $story_param ) {
    if ( empty( $_GET[ $story_param ] ) ) {
        continue;
    }
    $story_slug = sanitize_title( wp_unslash( $_GET[ $story_param ] ) );
    if ( ! $story_slug ) {
        continue;
    }
    $found_story_post = get_page_by_path( $story_slug, OBJECT, 'testimonial' );
    if ( $found_story_post instanceof WP_Post ) {
        $story_post = $found_story_post;
    }
    break;
}

if ( $story_post ) {
    $story_data              = get_post_meta( $story_post->ID, 'story_data', true );
    $story_meta_title        = is_array( $story_data ) ? trim( (string) premiaspine_landing_opt( $story_data, array( 'meta_title' ) ) ) : '';
    $story_meta_description  = is_array( $story_data ) ? trim( (string) premiaspine_landing_opt( $story_data, array( 'meta_description' ) ) ) : '';

    if ( $story_meta_title ) {
        $og_title = wp_strip_all_tags( $story_meta_title );
    } elseif ( get_the_title( $story_post ) ) {
        $og_title = wp_strip_all_tags( get_the_title( $story_post ) ) . ' — ' . $og_title;
    }

    if ( $story_meta_description ) {
        $og_description = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $story_meta_description ) ) );
        if ( function_exists( 'mb_strlen' ) && mb_strlen( $og_description ) > 200 ) {
            $og_description = rtrim( mb_substr( $og_description, 0, 197 ) ) . '…';
        }
    }
}

// The <title> tag below shares whatever the story-link override above produced.
$document_title = $og_title;

$og_image        = '';
$og_image_width  = '';
$og_image_height = '';
foreach (
    array(
        premiaspine_landing_opt( $landing_options, array( 'header', 'og_image' ) ),
        premiaspine_landing_opt( $landing_options, array( 'header', 'top_section_bg' ) ),
        premiaspine_landing_opt( $landing_options, array( 'header', 'doctor_photo' ) ),
    ) as $og_image_id
) {
    if ( empty( $og_image_id ) ) {
        continue;
    }
    $og_image_src = wp_get_attachment_image_src( $og_image_id, 'full' );
    // Social networks do not render SVG previews, so a raster file is required.
    if ( $og_image_src && ! preg_match( '/\.svg(\?|#|$)/i', $og_image_src[0] ) ) {
        $og_image        = $og_image_src[0];
        $og_image_width  = $og_image_src[1];
        $og_image_height = $og_image_src[2];
        break;
    }
}
if ( ! $og_image ) {
    $og_image        = get_stylesheet_directory_uri() . '/images/bg-top-section-new.jpg';
    $og_image_width  = 2750;
    $og_image_height = 1340;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html( $document_title ); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="PremiaSpine" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:url" content="<?php echo esc_url( $og_url ); ?>" />
    <meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>" />
    <meta property="og:description" content="<?php echo esc_attr( $og_description ); ?>" />
<?php if ( $og_image ) : ?>
    <meta property="og:image" content="<?php echo esc_url( $og_image ); ?>" />
    <meta property="og:image:alt" content="<?php echo esc_attr( $og_title ); ?>" />
<?php if ( $og_image_width && $og_image_height ) : ?>
    <meta property="og:image:width" content="<?php echo esc_attr( $og_image_width ); ?>" />
    <meta property="og:image:height" content="<?php echo esc_attr( $og_image_height ); ?>" />
<?php endif; ?>
<?php endif; ?>
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>" />
    <meta name="twitter:description" content="<?php echo esc_attr( $og_description ); ?>" />
<?php if ( $og_image ) : ?>
    <meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>" />
<?php endif; ?>

	<?php // Landing pages are noindex for everyone (paid-traffic pages, not meant
	// to rank). Output is intentionally identical for bots and users so the page
	// stays safe to full-page cache (Cloudflare APO / page cache plugin). ?>
    <meta name="robots" content="noindex,follow" />
	<meta name="facebook-domain-verification" content="c9mcgcinp01rync572ckpcn00m1pcb" />
        <link rel="preload" as="font" type="font/woff2" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/fonts/figtree-variable.woff2' ); ?>" crossorigin>
        <style id="figtree-font">
        @font-face {
            font-family: 'Figtree';
            font-style: normal;
            font-weight: 300 900;
            font-display: swap;
            src: url('<?php echo esc_url( get_stylesheet_directory_uri() . '/fonts/figtree-variable.woff2' ); ?>') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
        </style>
	<!-- Google tag (gtag.js) & GTM initialization -->
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'AW-17015149521');
	</script>
	<?php if ( function_exists( 'premiaspine_landing_is_codi_perf_context' ) && premiaspine_landing_is_codi_perf_context() ) : ?>
	<?php // GTM and Google Ads scripts are lazy-loaded after first interaction / 2.5s idle
	      // in inc/landing-codi-performance.php so they do not block initial paint. ?>
	<?php else : ?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17015149521"></script>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-PBG4H7J');</script>
	<!-- End Google Tag Manager -->
	<?php endif; ?>

        <link rel="preconnect" href="https://premiaspine.com" crossorigin>
        <link rel="dns-prefetch" href="https://premiaspine.com">
	<?php if ( function_exists( 'premiaspine_landing_is_codi_perf_context' ) && premiaspine_landing_is_codi_perf_context() ) : ?>
	<?php // Call-tracking (CallTrackingMetrics) is loaded after the first user
	// interaction — see premiaspine_landing_print_lazy_thirdparty() in
	// inc/landing-codi-performance.php. Keeps an unreachable/slow third-party
	// host from delaying page load. ?>
	<?php else : ?>
	<script async src="//364508.tctm.co/t.js"></script>
	<?php endif; ?>
	<?php wp_head(); ?>
	<meta name="msvalidate.01" content="329018F2E433D2E3A925F40C561E9B86" />
</head>
<body <?php body_class(); ?> id="2">
	<!--email_off-->
	
	<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PBG4H7J"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
	
<div class="wrapper sticky-header new-landing">
    <div id="header">
        <div class="container">
            <strong class="logo">
                <a href="<?php echo home_url(); ?>">
                    <img src="<?php echo wp_get_attachment_image_src($general_options['header']['logo_dark'])[0]; ?>" alt="PremiaSpine Logo" />
                </a>
            </strong>
            <?php if ( $header_slogan ) : ?>
            <strong class="slogan"><?php echo esc_html( $header_slogan ); ?></strong>
            <?php endif; ?>
            <div class="call-now-request">
                <!-- <a href="tel:6463600936">
                    <div class="call-now-block">
                        <span class="phone">(646)360-0936</span>
                    </div>
                </a> -->
                <a href="tel:<?php echo esc_attr( $phone_tel ); ?>">
                    <div class="call-now-block">
                        <span class="phone"><?php echo esc_html( $phone_display ); ?></span>
                    </div>
                </a>
            </div>
        </div>
    </div>