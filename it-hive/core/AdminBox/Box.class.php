<?php
namespace it_hive\core\AdminBox;

abstract class Box extends \it_hive\core\Blocks\DataBlock {
	const defaults = array(
		'title' 	=> 'Box',
		'name'      => 'box',
		'single'	=> false,
	);

	const exclude = parent::exclude + self::defaults;

	public function __construct( $params ) {
		$this->params = array_merge(self::defaults,$params);
		$this->name = $this->params['name'];
		$this->addActions();
	}

	protected function addActions(){}

	protected function addNonce() {
		wp_nonce_field( $this->name . '_admin_box', $this->name . '_admin_box_nonce' );
	}

	protected function verify_nonce() {
		if( isset( $_POST[$this->name . '_admin_box_nonce'] ) && wp_verify_nonce( $_POST[$this->name . '_admin_box_nonce'], $this->name . '_admin_box' ) ) {
			return true;
		}
		return false;
	}

	public function show(){
		parent::__construct( $this->params );
		$this->addNonce();
		echo $this->render();
	}

	public function save(){
		if( !$this->verify_nonce() ) {
			return false;
		}
		parent::__construct( $this->params );
		//$this->render(); //TODO : это избыточно, надо подумать
		$this->CreateChildrenRecursive(); //TODO: зксперемент
		parent::save();
		return true;
	}

	public function display($skin = null){
		parent::__construct( $this->params );
		if(!count($this->children)){
			$this->CreateChildrenRecursive();
		}
		parent::display($skin);
	}
}