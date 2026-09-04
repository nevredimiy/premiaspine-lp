<?php
namespace it_hive\core\Blocks\text;

class Block extends \it_hive\core\Blocks\DataBlock {

	public function renderGet( $key = null ) {
		return htmlspecialchars(parent::renderGet( $key ));
	}
}