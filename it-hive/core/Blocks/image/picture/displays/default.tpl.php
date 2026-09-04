<picture>
	<?php foreach ($params['sources'] as $key => $source):
		if(!$data[$key]) continue;
	?>
		<source media="<?php echo($source);?>" srcset="<?php echo wp_get_attachment_image_url( $data[$key], 'full' ); ?>">
	<?php endforeach;?>
	<img src="<?php echo wp_get_attachment_image_url( $data['image'], 'full' ); ?>" alt="">
</picture>