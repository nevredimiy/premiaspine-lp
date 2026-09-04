<?php
/*
 *  Template Name: Thank you
 */
?>
<?php get_header(); ?>
<?php
$options = get_post_meta(get_queried_object_id(),'thank_you_options',true);
?>
<div class="thank-you-page" style='background-image: url("<?php echo wp_get_attachment_image_url($options['background'],'full'); ?>")'>
        <?php echo $options['title']; ?>
        <?php echo $options['content']; ?>
        <a href="<?php echo $options['link']; ?>" class="thnx-button"><?php echo $options['button']; ?></a>
	    <div class="thnx-logo">
	        <img src="<?php echo wp_get_attachment_image_url($options['logo'],'full'); ?>" alt="">
	    </div>
</div>

</div>
<?php wp_footer(); ?>
<script type="application/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery-ui.min.js"></script>
<script type="application/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/main.js"></script>
</body>
</html>