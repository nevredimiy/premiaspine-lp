<?php
namespace it_hive\core\Blocks\repeater\repeaterItem;

class Block extends \it_hive\core\Blocks\group\Block {

	const defaults = array(
		'value' 	=> array(),
		'single' 	=> false,
		'template'	=> false,
		'heading'   =>  'Item',
		'removeText'   =>  'Remove',
		'headingFrom' => false
	);

	protected $key = '';

	protected static $templates = array();

	protected static $adminScripts = array(
		'repeater-control' => array('main.js', array('jquery', 'postbox'))
	);

	public function __construct( $params ) {
		$this->key = self::getParamsKey( $params );
		$this->params = array_merge(self::defaults, $params);
		parent::__construct( $this->params );
	}



	public function render() {
		$html = parent::render();
		if( $this->params['template'] ) {
			return '';
		}
		return $html;
	}

	protected function beforeApplyParams(){
		if($this->params['headingFrom'] && key_exists($this->params['headingFrom'],$this->dataChildren) && $this->dataChildren[$this->params['headingFrom']]->getHeading()){
			$this->params['heading'] =  $this->dataChildren[$this->params['headingFrom']]->getHeading();
		}
	}

	protected function get( $key = null ) {
		if( $key !== null ) {
			if( $key === '' ) {
				return $this->params['value'];
			}
			return is_array($this->params['value']) ? $this->params['value'][$key] : '';
		}
		return $this->params['value'];
	}

	public function addTemplate() {
		if( !self::$templates[$this->key] ) {
			$this->render();
			$this->name = '{{parentName}}[{{counter}}]';
			$this->params['single'] = true;
			$id = 'tpl-' . self::$prefix . '-' . self::$counter;
			self::$inline[] = '<script id="' . $id . '"  type="text/template">
			' . $this->getTpl( true ) . '
			</script>';
			self::$templates[$this->key] = $id;
		}
		return self::$templates[$this->key];
	}

	public function getTpl( $display = false ) {
		if( $this->params['template'] && !$display ) {
			return '';
		}
		return parent::getTpl();
	}

	protected function getParamsKey( $params ) {
		unset( $params['dataParent'] );
		unset( $params['parent'] );
		return md5(serialize($params));
	}
}