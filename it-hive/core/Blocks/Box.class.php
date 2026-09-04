<?php
namespace it_hive\core\Blocks;

abstract class Box implements \it_hive\__init {

	use \it_hive\init;

	use \it_hive\core\enqueue;

	protected $params;

	const defaults = array(
		'dataParent'	=> false,
		'type'			=> 'html',
		'skin'			=> 'default',
		'name'			=>'',
		'parent'		=> false,
		'children'		=> array()
	);

	const enqueueSettings = array( null, null, null, null, true );

	protected static $prefix = 'it-hive';

	protected static $counter = 1;

	protected $children = array();

	protected $parent;

	protected $name;

	protected $skin;

	protected $tpl = '';

	const exclude = self::defaults;

	public function __construct( $params ) {
		self::$counter++;
		$this->params = array_merge(self::defaults, $params);
		if( !$this->params['id'] ) {
			$this->params['id'] = self::$prefix . '-' . self::$counter;
		}
		$this->name = $this->params['name'];
		$this->skin = $this->params['skin'];
		$this->parent = $this->params['parent'];
        if(is_callable($this->params['children'])){
            $this->params['children'] = call_user_func($this->params['children'],$this->get());
        }
	}

	public function render() {
		$this->CreateChildren();
		$this->beforeApplyParams();
		$html = $this->applyParams();
		$children = array();
		if( count($this->children) ) {
			foreach( $this->children as $child ) {
				$children[] = $child->render();
			}
		}
		$html = str_replace('{{children}}', implode("\n",$children ), $html);
		return $html;
	}

	protected function beforeApplyParams(){

	}

	protected function loadSkin(){
		if( !$this->tpl ) {
			$classInfo = new \ReflectionClass($this);
			$this->tpl = file_get_contents(dirname($classInfo->getFileName()) . DIRECTORY_SEPARATOR . 'skin' . DIRECTORY_SEPARATOR . $this->skin . '.tpl');
		}
		return $this->tpl;
	}

	protected function CreateChildren() {
		foreach( $this->params['children'] as $name => $child ) {
			$type = 'it_hive\\core\\Blocks\\' . ( $child['type'] ? $child['type'] : 'html' ) . '\\Block';
			$this->children[] = new $type(
				$child+
				array(
					'parent'		=> $this,
					'name'			=> $name,
					'dataParent'	=> $this->params['dataParent'],
					'single'		=> $this->params['single']
				)
			);
		}
	}

	protected function CreateChildrenRecursive(){
		$this->CreateChildren();
		foreach ($this->children as $child){
			$child->CreateChildrenRecursive();
		}
	}

	protected function applyParams() {
		$html = $this->loadSkin();
		$class = get_class($this); //TODO: подумать над использованием static::
		foreach( $this->params as $param => $val ) {
			if( key_exists( $param, $class::exclude ) ) {
				continue;
			}
			$html = str_replace('{{' . $param . '}}',(string) $val, $html);
		}
		return $html;
	}

	public function getParam( $param ) {
		return $this->params[$param];
	}

	public function getTpl() {
		$this->params['id'] = '{{idPrefix}}' . $this->params['id'];
		$tpl = $this->applyParams();
		$children = array();
		if( count($this->children )) {
			foreach( $this->children as $child ) {
				$children[] = $child->getTpl();
			}
		}
		$tpl = str_replace('{{children}}', implode("\n",$children ), $tpl);
		return $tpl;
	}

	protected static function init() {
		if( self::class == static::class ) {
			self::initEnqueue();
		}
		self::defineStatic();
	}
}
