<?php
if(count($children)):?>
<ul>
<?php foreach ($children as $name => $child):?>
	<li>
		<?php $child->display() ;?>
	</li>
<?php endforeach; ?>
</ul>
<?php endif;?>