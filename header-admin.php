<?php $general_options = get_option('theme_options', true);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <script type="text/javascript">!function () {
            var e, t,
                s = "data:image/webp;base64,UklGRjIAAABXRUJQVlA4ICYAAACyAgCdASoCAAEALmk0mk0iIiIiIgBoSygABc6zbAAA/v56QAAAAA==";
            e = function (e) {
                if (!e) {
                    console.log("webp fix"), window.addEventListener("error", function (e, t) {
                        if ("IMG" === e.target.tagName && -1 != e.target.src.indexOf(".webp")) return e.target.src = e.target.src.replace(".webp", ""), e.target.srcset && (e.target.srcset = e.target.srcset.replace(".webp", "")), !0
                    }, !0), document.addEventListener("DOMContentLoaded", function () {
                        for (var e, t = 0; t < document.styleSheets.length; t++) {
                            e = document.styleSheets[t].cssRules;
                            for (var s = 0; s < e.length; s++) if (e[s].style && e[s].style.backgroundImage && e[s].style.backgroundImage.indexOf(".webp") && (e[s].style.backgroundImage = e[s].style.backgroundImage.replace(".webp", "")), e[s].cssRules) for (var r = 0; r < e[s].cssRules.length; r++) e[s].cssRules[r].style && e[s].cssRules[r].style.backgroundImage && e[s].cssRules[r].style.backgroundImage.indexOf(".webp") && (e[s].cssRules[r].style.backgroundImage = e[s].cssRules[r].style.backgroundImage.replace(".webp", ""))
                        }
                        var a = document.querySelectorAll("[style]");
                        for (s = 0; s < a.length; s++) a[s].style["background-image"] && (a[s].style["background-image"] = a[s].style["background-image"].replace(".webp", ""));
                        console.log(a.length)
                    });
                    var s = CSSStyleSheet.prototype.insertRule;
                    CSSStyleSheet.prototype.insertRule = function (e, t) {
                        return e.style && e.style.backgroundImage && e.style.backgroundImage.indexOf(".webp") && (e.style.backgroundImage = e.style.backgroundImage.replace(".webp", "")), s.apply(this, [e, t])
                    }
                }
            }, (t = document.createElement("img")).onerror = function () {
                e(!1)
            }, t.onload = function () {
                2 === this.width && 1 === this.height ? e(!0) : e(!1)
            }, t.setAttribute("src", s)
        }();</script>
    <meta charset="UTF-8">
    <title><?php echo wp_get_document_title(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <?php if (!isBot()): ?>
        <!-- Google Tag Manager -->
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-PBG4H7J');</script>
        <!-- End Google Tag Manager -->
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="wrapper sticky-header ">
    <?php if ( is_page_template('landing_page.php') ) : ?>
        <?php $options = get_post_meta(get_queried_object_id(),'landing_page_options',true); ?>
        <div class="call-now-wrapper">
            <div class="call-now-inner">
                <div class="call-now-right">
                    <div class="call-now-request">
                        <a href="tel:6463600936">
                            <div class="call-now-block">
                                <span><?php echo $options['header']['phone_title']?></span>
                                <span class="phone">(646)360-0936</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <strong class="logo">
        <a href="<?php echo home_url(); ?>">
            <img src="<?php echo wp_get_attachment_image_src($general_options['header']['logo'])[0]; ?>" alt=""/>
        </a>
    </strong>
    </strong>