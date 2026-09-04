<?php
namespace it_hive\core\Blocks\select;

class Block extends \it_hive\core\Blocks\DataBlock {

	const defaults = [
		'values' => [],
		'defaultLabel' => 'select'
	];

	public function __construct( $params ) {
		$this->params = array_merge(self::defaults,$params);
		parent::__construct( $this->params );
	}

	public function render()
	{
		$this->params['value'] = $this->renderGet();
		$this->params['content'] = $this->createOptions();
		return parent::render();
	}

	protected function createOptions(){
		$options = [];
		$options[] = '<option value=""'.(''===$this->params['value']?' selected="selected"':'').'>'.$this->params['defaultLabel'].'</option>';
		foreach ($this->params['values'] as $value => $label){
			$options[] = '<option value="'.$value.'"'.($value==$this->params['value']?' selected="selected"':'').'>'.$label.'</option>';
		}

		return join('',$options);
	}
}