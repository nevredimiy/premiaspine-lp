<?php get_header(); ?>
<div style="background:display:block; background:blue; height:100px; width:100%; "></div>
<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>

        <div class="item" id="post-<?php the_ID(); ?>">
            <a href="<?php the_permalink() ?>" class="wrapper-img"><?php echo the_post_thumbnail()?></a>
            <div class="text-block-blog">
                <h2><?php the_title_attribute(); ?></h2>
                <?php the_content() ?>
            </div>
        </div>

    <?php endwhile; ?>
<?php endif; ?>
<?php get_footer(); ?>