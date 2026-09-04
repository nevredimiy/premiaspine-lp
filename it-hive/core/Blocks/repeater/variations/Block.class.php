<?php
namespace it_hive\core\Blocks\repeater\variations;

class Block extends \it_hive\core\Blocks\repeater\Block {

	const defaults = array(
		'variations' => array()
	);

	const exclude = parent::exclude + self::defaults;

	protected static $adminScripts = array(
		'repeaterVariations-control' => 'main.js'
	);

	public function __construct( $params ) {
		$this->params = array_merge(self::defaults, $params);
		foreach( $this->params['variations'] as $key => &$variation ) {
			$variation['repeat']['variationType'] = array(
				'type'	=> 'hidden',
				'value' => $key
			);
		}
		parent::__construct( $this->params );
	}

	protected function addChildren() {
		$data = $this->get();
		$data = array_values($data);
		foreach( $data as $item ) {
			$this->params['children'][] =  array(
					'name'		   => $this->repeatCounter,
					'value'		   => $item,
					'type'		   => 'repeater\\repeaterItem',
					'children'	   => $this->params['variations'][$item['variationType']]['repeat'],
					'skin'		   => $this->params['repeatSkin'],
					'localDisplay' => $this->params['variations'][$item['variationType']]['localDisplay'],
					'display'	   => $this->params['variations'][$item['variationType']]['display'],
					'heading'	   => $this->params['variations'][$item['variationType']]['title'],
                    'headingFrom'  => $this->params['variations'][$item['variationType']]['repeatHeadingFrom'],
				);
			$this->repeatCounter++;
		}
	}

	protected function addTamplatesItems() {
		$counter = 0;
		foreach( $this->params['variations'] as $variation ) {
			$this->params['children'][$counter] =  array(
				'name'		=> $counter,
				'type'		=> 'repeater\\repeaterItem',
				'children'	=> $variation['repeat'],
				'skin'		=> $this->params['repeatSkin'],
				'heading'	=> $variation['title'],
				'template'	=> true
			);
			$counter++;
		}
	}
	protected function getChildren()
	{
		$temp = $this->children;
		$counter = 0;
		foreach ($this->params['variations'] as $value){
			unset($temp[$counter]);
			$counter++;
		}
		return $temp;
	}

	protected function setJSTamplates() {
		$tpls = array();
		$counter = 0;
		foreach( $this->params['variations'] as $key => $variation ) {
			$tpls[] = array(
				'id'	=> '#' . $this->children[$counter]->addTemplate(),
				'title' => $variation['title'] ? $variation['title'] : $key
			);
			$counter++;
		}
		$this->params['templateID'] = htmlspecialchars(json_encode( $tpls ));
	}
}