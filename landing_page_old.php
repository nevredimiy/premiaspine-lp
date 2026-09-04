<?php
/*
 *  Template Name: Lading page (Old)
 */
?>
<?php get_header(); ?>
<?php //if($_SERVER['HTTP_X_FORWARDED_FOR'] == '31.202.100.44' || is_user_logged_in()){
    //get_header('admin');
//}?>
<?php
the_post();
$options = get_post_meta(get_queried_object_id(),'landing_page_options',true);
/*if($_SERVER['REMOTE_ADDR'] == '31.202.100.44'){
    $wp_rocket_settings = get_option('wp_rocket_settings');
    print_r($wp_rocket_settings);
}*/
?>
<section class="top-section">
    <div class="holder">
        <div class="top-section-content">
            <h1><?php echo $options['header']['top_title'] ?></h1>
            <?php echo apply_filters('the_content',$options['header']['top_content']); ?>
        </div>
        <div class="request-appointment-section">
            <?php echo do_shortcode($options['header']['contact_form']); ?>
            <div class="benefits-list-holder">
                <div class="benefits-list">
                    <h3><?php echo $options['header']['title_benefits']?></h3>
                    <ul>
                        <?php foreach($options['header']['benefits'] as $benefit): ?>
                            <li><?php echo $benefit['benefit']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
	<?php if ( !empty($options['advertising']['title']) ) : ?>
		<section class="adv-content">
			<div class="container">
				<h2><?php echo $options['advertising']['title']; ?></h2>
			</div>
		</section>
	<?php endif; ?>
</section>
<?php if(!isBot()) :?>
<div id="main">
    <section class="intro-content">
        <div class="container">
            <h2><?php echo $options['intro_content']['title']?></h2>
            <ul class="advantages">
                <?php foreach ($options['intro_content']['advantages'] as $advantages): ?>
                    <li><?php echo $advantages['text']?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <section class="content-area area-1">
        <div class="container">
            <h2><?php echo $options['content_area']['title']?></h2>
            <div class="content-holder">
                <?php foreach ($options['content_area']['text'] as $text_area): ?>
                    <?php echo $text_area['text']; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="testimonials-section">
        <div class="container">
            <h2><?php echo $options['testimonials']['title'];?></h2>
            <div class="testimonials-holder">
                <?php foreach ($options['testimonials']['testimonials'] as $testimonials): ?>
                    <div class="testimonial-block" draggable="true">
                        <div class="image-holder">
                            <a
		                        class="fancybox iframe"
                                data-fancybox
                                data-description="<?php echo $testimonials['description']; ?>"
                                title="<?php echo $testimonials['title_for_video']; ?>"
                                href="<?php echo $testimonials['link']; ?>">
                                <img src="<?php echo wp_get_attachment_image_url($testimonials['image'],'youtube_image');?>" alt="" />
                                <span class="play-btn"></span>
                            </a>
                        </div>
                        <div class="title">
                            <strong><?php echo $testimonials['text']; ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php if ( !empty($options['header']['advertising_benefits']) ) : ?>
        <section class="benefits-adv-content">
            <div class="container">
                <h2><?php echo $options['header']['advertising_benefits']; ?></h2>
            </div>
        </section>
    <?php endif; ?>
    <div class="benefits-section">
        <div class="benefits-list">
            <h3><?php echo $options['header']['title_benefits']?></h3>
            <ul>
                <?php foreach($options['header']['benefits'] as $benefit): ?>
                    <li><?php echo $benefit['benefit']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="request-appointment gray">
        <?php echo do_shortcode($options['request_an_appointment_form_1']['request_an_appointment_form']); ?>
    </div>
    <section class="content-area area-2">
        <div class="container">
            <?php the_content(); ?>
        </div>
    </section>
    <div class="request-appointment gray">
        <?php echo do_shortcode($options['request_an_appointment_form_2']['request_an_appointment_form']); ?>
    </div>
    <section class="faq-section">
        <div class="container">
            <h3><?php echo $options['questions_and_answer']['title']; ?></h3>
            <ul class="faq">
                <?php foreach($options['questions_and_answer']['questions_and_answer'] as $questions_and_answer): ?>
                    <li>
                        <div class="question">
                            <h4><?php echo $questions_and_answer['question']; ?></h4>
                        </div>
                        <div class="answer">
                            <?php echo apply_filters('the_content',$questions_and_answer['answer']); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <section class="locations-section">
        <div class="container">
            <h3><?php echo $options['locations']['title']; ?></h3>
            <div class="into-area">
                <div class="intro-text">
                    <h4><?php echo $options['locations']['subtitle']; ?></h4>
                </div>
                <div class="map">
                    <?php echo do_shortcode('[put_wpgm id=1]'); ?>
                </div>
            </div>
            <div class="centers-list">
                <div class="double-column">
                    <div class="centers-column">
                        <ul>
                            <?php foreach($options['locations']['first_column']['row'] as $centers_first_column): ?>
                                <ul>
                                    <li><?php echo $centers_first_column['row'];?></li>
                                </ul>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="centers-column">
                        <ul>
                            <?php foreach($options['locations']['second_column']['row'] as $centers_first_column): ?>
                                <ul>
                                    <li><?php echo $centers_first_column['row'];?></li>
                                </ul>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="double-column">
                    <div class="centers-column">
                        <ul>
                            <?php foreach($options['locations']['third_column']['row'] as $centers_first_column): ?>
                                <ul>
                                    <li><?php echo $centers_first_column['row'];?></li>
                                </ul>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="centers-column">
                        <ul>
                            <?php foreach($options['locations']['fourth_column']['row'] as $centers_first_column): ?>
                                <ul>
                                    <li><?php echo $centers_first_column['row'];?></li>
                                </ul>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="request-appointment blue">
        <?php echo do_shortcode($options['request_an_appointment_form_3']['request_an_appointment_form']); ?>

    </div>
</div>
<?php endif;?>
<!--<div style="display: none">-->
<!--    --><?php
//    echo do_shortcode('[cityByIp]');
//    ?>
<!--</div>-->
<?php get_footer(); ?>
