<?php
namespace it_hive\core\Blocks;

abstract class HtmlBlock extends Box {

	const hiddenDefaults = array(
		'attributes' => array()
	);

	const defaults = array(
		'class'		=>'',
		'id'		=>'',
		'content'	=> ''
	) +self::hiddenDefaults;

	const exclude = parent::exclude + array( 'attributes' =>array() );

	public function __construct( $params ) {
		$this->params = array_merge(self::defaults, $params);
		parent::__construct( $this->params );
	}

	public function render() {
		$html = parent::render();
		$html = str_replace('{{attributes}}', $this->renderAtributes(), $html);
		return $html;
	}

	public function renderAtributes() {
		$params = array();
		foreach( $this->params['attributes'] as $param => $val ) {
			$params[] = $param . '="' . htmlspecialchars($val) . '"';
		}
		return join(' ', $params);
	}
}