<?php
$general_options = get_option( 'theme_options', true );
$logo_id         = ! empty( $general_options['header']['logo_dark'] )
	? $general_options['header']['logo_dark']
	: ( ! empty( $general_options['header']['logo'] ) ? $general_options['header']['logo'] : 0 );
$logo_src        = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<script type="text/javascript">!function(){var e,t,s="data:image/webp;base64,UklGRjIAAABXRUJQVlA4ICYAAACyAgCdASoCAAEALmk0mk0iIiIiIgBoSygABc6zbAAA/v56QAAAAA==";e=function(e){if(!e){console.log("webp fix"),window.addEventListener("error",function(e,t){if("IMG"===e.target.tagName&&-1!=e.target.src.indexOf(".webp"))return e.target.src=e.target.src.replace(".webp",""),e.target.srcset&&(e.target.srcset=e.target.srcset.replace(".webp","")),!0},!0),document.addEventListener("DOMContentLoaded",function(){for(var e,t=0;t<document.styleSheets.length;t++){e=document.styleSheets[t].cssRules;for(var s=0;s<e.length;s++)if(e[s].style&&e[s].style.backgroundImage&&e[s].style.backgroundImage.indexOf(".webp")&&(e[s].style.backgroundImage=e[s].style.backgroundImage.replace(".webp","")),e[s].cssRules)for(var r=0;r<e[s].cssRules.length;r++)e[s].cssRules[r].style&&e[s].cssRules[r].style.backgroundImage&&e[s].cssRules[r].style.backgroundImage.indexOf(".webp")&&(e[s].cssRules[r].style.backgroundImage=e[s].cssRules[r].style.backgroundImage.replace(".webp",""))}var a=document.querySelectorAll("[style]");for(s=0;s<a.length;s++)a[s].style["background-image"]&&(a[s].style["background-image"]=a[s].style["background-image"].replace(".webp",""));console.log(a.length)});var s=CSSStyleSheet.prototype.insertRule;CSSStyleSheet.prototype.insertRule=function(e,t){return e.style&&e.style.backgroundImage&&e.style.backgroundImage.indexOf(".webp")&&(e.style.backgroundImage=e.style.backgroundImage.replace(".webp","")),s.apply(this,[e,t])}}},(t=document.createElement("img")).onerror=function(){e(!1)},t.onload=function(){2===this.width&&1===this.height?e(!0):e(!1)},t.setAttribute("src",s)}();</script>
    <meta charset="UTF-8">
    <title><?php echo wp_get_document_title(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<?php if ( ! isBot() ) : ?>
    <meta name="robots" content="noindex,follow" />
	<meta name="facebook-domain-verification" content="c9mcgcinp01rync572ckpcn00m1pcb" />
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17015149521"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'AW-17015149521');
	</script>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PBG4H7J');</script>
	<script async src="//364508.tctm.co/t.js"></script>
	<?php endif; ?>
	<?php wp_head(); ?>
    <script src="//geoip-js.com/js/apis/geoip2/v2.1/geoip2.js" type="text/javascript"></script>
	<meta name="msvalidate.01" content="329018F2E433D2E3A925F40C561E9B86" />
</head>
<body <?php body_class(); ?> id="2">
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PBG4H7J"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<div class="wrapper info-page">
    <div id="header">
        <div class="container">
            <?php if ( $logo_src ) : ?>
            <strong class="logo">
                <a href="<?php echo esc_url( home_url() ); ?>">
                    <img src="<?php echo esc_url( $logo_src[0] ); ?>" alt="PremiaSpine Logo" />
                </a>
            </strong>
            <?php endif; ?>
        </div>
    </div>
