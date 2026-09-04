<footer id="footer">
    <?php $general_options = get_option( 'theme_options', true ); ?>
	<div class="container bottom-footer__inner">
        <p class="copyright"><?php echo $general_options['footer']['copyright']; ?></p>
        <?php if ( has_nav_menu( 'footer_right_menu' ) ) : ?>
            <nav class="bottom-footer__nav" aria-label="<?php esc_attr_e( 'Footer links', 'premiaspine' ); ?>">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'footer_right_menu',
                        'container'      => false,
                        'menu_class'     => 'bottom-footer-menu',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    )
                );
                ?>
            </nav>
        <?php endif; ?>
	</div>
</footer>

</div>

<?php if(!isBot()) :?>
<?php wp_footer(); ?>
<script type="application/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery-ui.min.js"></script>
<script type="application/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/main.js?v=<?php echo time();?>"></script>
<link href="https://fonts.googleapis.com/css?family=Raleway:400,600,900&display=swap" rel="stylesheet">
<link rel='stylesheet' id='jquery.fancybox.min-css'  href='<?php echo get_stylesheet_directory_uri()?>/css/jquery.fancybox.min.css?v=1580469056&#038;ver=5.3.2' type='text/css' media='all' />
<?php endif;?>
</body>
</html>