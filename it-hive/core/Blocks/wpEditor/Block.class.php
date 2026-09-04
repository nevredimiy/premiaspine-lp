<?php
namespace it_hive\core\Blocks\wpEditor;

class Block extends \it_hive\core\Blocks\DataBlock {

	const hiddenDefaults = array(
		'value'		=> '',
		'name'		=> '',
		'settings'	=> array(),
		'editor'	=> ''
	);

	const defaults = array(
		'rows'	=> 20,
		'class'=> '',
		'cols'	=> 40
	) +self::hiddenDefaults;

	const exclude = parent::exclude + self::hiddenDefaults;

	protected static $define = false;

	protected $editorCode = '';

	protected static $adminScripts = array(
		'wp-editor-control' => 'main.js'
	);


	public function __construct( $params ) {
		$this->params = array_merge(self::defaults, $params);

		parent::__construct( $this->params );
	}

	protected static function define() {
		wp_enqueue_editor();
		return true;
	}

	protected function applyParams() {
		$html = parent::applyParams();
		$html = str_replace('{{value}}', stripslashes($this->get()), $html);
		return $html;
	}

	protected static function init() {
		self::$define = self::define();
		parent::init();
	}

	public function render() {
		$this->params['label'] = $this->params['label'] ? '<h4>' . $this->params['label'] . '</h4>' : '';
		$html = parent::render();
		return $html;
	}
}