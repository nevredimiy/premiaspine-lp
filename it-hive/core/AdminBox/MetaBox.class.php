<?php
namespace it_hive\core\AdminBox;

abstract class MetaBox extends Box {
	const defaults = array(
		'screen'		=> 'post',
		'context'		=> 'advanced',
		'priority'		=> 'default',
		'callback_args'	=> array(),
		'forTemplate' => []
	);

	const exclude = parent::exclude + self::defaults;
 
	protected static $counter = 1;

	function __construct( $params ) {
		$this->params = array_merge(self::defaults,$params);
		$this->name = $this->params['name'];
		parent::__construct( $this->params );
	}

	protected function addActions() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save') );
	}

	public function add_meta_box() {
		add_meta_box(
			$this->name,
			$this->params['title'],
			array( $this,'show' ),
			$this->params['screen'],
			$this->params['context'],
			$this->params['priority'],
			$this->params['callback_args']
		);
	}

	public function show( $post =null,$args=[] ) {
		$this->params['sourceObject'] = $post->ID;
		parent::show();
	}

	public function save( $id = 0 ) {
		$this->params['sourceObject'] = $id;
		parent::save();
	}

	public function display($skin = null){
		// TODO: нужно применить кеширование;
		if($this->params['sourceObject'] != get_the_ID()){
			$this->children = [];
		}
		$this->params['sourceObject'] =  get_the_ID();
		parent::display($skin);
	}

	public function render()
	{
		$this->params['content'] .= '<span style="display: none" class="template-restriction">'.implode(',',$this->params['forTemplate']).'</span>';
		return parent::render();
	}
}