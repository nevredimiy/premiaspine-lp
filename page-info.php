<?php
/*
 * Template Name: Info
 */

get_header();

while ( have_posts() ) :
	the_post();
	$options        = get_post_meta( get_queried_object_id(), 'landing_page_options', true );
	$top_section_bg = ! empty( $options['header']['top_section_bg'] )
		? wp_get_attachment_image_url( $options['header']['top_section_bg'], 'full' )
		: '';
	?>
<section class="top-section top-section--info">
    <!-- <div class="holder"<?php if ( $top_section_bg ) : ?> style="background-image: url('<?php echo esc_url( $top_section_bg ); ?>'); background-size: cover; background-position: 50% 0;"<?php endif; ?>></div> -->
</section>
<section class="contact-info">
    <div class="container" style="margin-top: 150px;">
        <h1 class="section-title"><?php the_title(); ?></h1>
        <?php the_content(); ?>
    </div>
</section>
	<?php
endwhile;

get_footer();
