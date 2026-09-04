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

$og_image        = '';
$og_image_width  = '';
$og_image_height = '';
foreach (
    array(
        premiaspine_landing_opt( $landing_options, array( 'header', 'top_section_bg' ) ),
        premiaspine_landing_opt( $landing_options, array( 'header', 'doctor_photo' ) ),
        premiaspine_landing_opt( (array) $general_options, array( 'header', 'logo_dark' ) ),
    ) as $og_image_id
) {
    if ( empty( $og_image_id ) ) {
        continue;
    }
    $og_image_src = wp_get_attachment_image_src( $og_image_id, 'full' );
    if ( $og_image_src ) {
        $og_image        = $og_image_src[0];
        $og_image_width  = $og_image_src[1];
        $og_image_height = $og_image_src[2];
        break;
    }
}
if ( ! $og_image ) {
    $og_image = get_stylesheet_directory_uri() . '/images/person.png';
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<script type="text/javascript">!function(){var e,t,s="data:image/webp;base64,UklGRjIAAABXRUJQVlA4ICYAAACyAgCdASoCAAEALmk0mk0iIiIiIgBoSygABc6zbAAA/v56QAAAAA==";e=function(e){if(!e){console.log("webp fix"),window.addEventListener("error",function(e,t){if("IMG"===e.target.tagName&&-1!=e.target.src.indexOf(".webp"))return e.target.src=e.target.src.replace(".webp",""),e.target.srcset&&(e.target.srcset=e.target.srcset.replace(".webp","")),!0},!0),document.addEventListener("DOMContentLoaded",function(){for(var e,t=0;t<document.styleSheets.length;t++){e=document.styleSheets[t].cssRules;for(var s=0;s<e.length;s++)if(e[s].style&&e[s].style.backgroundImage&&e[s].style.backgroundImage.indexOf(".webp")&&(e[s].style.backgroundImage=e[s].style.backgroundImage.replace(".webp","")),e[s].cssRules)for(var r=0;r<e[s].cssRules.length;r++)e[s].cssRules[r].style&&e[s].cssRules[r].style.backgroundImage&&e[s].cssRules[r].style.backgroundImage.indexOf(".webp")&&(e[s].cssRules[r].style.backgroundImage=e[s].cssRules[r].style.backgroundImage.replace(".webp",""))}var a=document.querySelectorAll("[style]");for(s=0;s<a.length;s++)a[s].style["background-image"]&&(a[s].style["background-image"]=a[s].style["background-image"].replace(".webp",""));console.log(a.length)});var s=CSSStyleSheet.prototype.insertRule;CSSStyleSheet.prototype.insertRule=function(e,t){return e.style&&e.style.backgroundImage&&e.style.backgroundImage.indexOf(".webp")&&(e.style.backgroundImage=e.style.backgroundImage.replace(".webp","")),s.apply(this,[e,t])}}},(t=document.createElement("img")).onerror=function(){e(!1)},t.onload=function(){2===this.width&&1===this.height?e(!0):e(!1)},t.setAttribute("src",s)}();</script>
    <meta charset="UTF-8">
    <title><?php echo wp_get_document_title(); ?></title>
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

	<?php if(!isBot()):?>
    <meta name="robots" content="noindex,follow" />
	<meta name="facebook-domain-verification" content="c9mcgcinp01rync572ckpcn00m1pcb" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17015149521"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'AW-17015149521');
	</script>

	<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PBG4H7J');</script>
<!-- End Google Tag Manager -->

	<script async src="//364508.tctm.co/t.js"></script>
	<?php endif;?>
	<?php wp_head(); ?>
    <script src="//geoip-js.com/js/apis/geoip2/v2.1/geoip2.js" type="text/javascript"></script>
	<meta name="msvalidate.01" content="329018F2E433D2E3A925F40C561E9B86" />
</head>
<body <?php body_class(); ?> id="2">
	
	
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