<?php
namespace it_hive\core\Blocks\tabs;

class Block extends \it_hive\core\Blocks\HtmlBlock {
	const hiddenDefaults = array(
		'tabs' => array()
	);

	const defaults = array(
		'tabset' =>  ''
	) +self::hiddenDefaults;

	protected static $adminScripts = array(
		'tabs-control'		=> 'main.js',
		'jquery-ui-tabs'    =>  ''
	);

	public function __construct( $params ) {
		$this->params = array_merge(self::defaults, $params);
		parent::__construct( $this->params );
	}

	protected function CreateChildren() {
		$this->addChildren();
		$this->addTabset();
		parent::CreateChildren();
	}

	protected function addChildren() {
		$counter = 0;
		foreach( $this->params['tabs'] as $tab ) {
			$this->params['children'][] =  [
				'id'		=> $this->params['id'] . '-tab-'.$counter,
				'class'		=> 'tab-item',
				'skin'		=> 'div',
				'children'	=> $tab
			];
			$counter++;
		}
	}

	protected function addTabset() {
		$tabset = '';
		$counter = 0;
		foreach( $this->params['tabs'] as $name => $tab ) {
			$tabset .= '<li><a href="#' . $this->params['id'] . '-tab-' . $counter . '">' . $name . '</a></li>';
			$counter++;
		}
		$this->params['tabset'] = '<ul>' . $tabset . '</ul>';
	}
}