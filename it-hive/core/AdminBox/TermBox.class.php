<?php
namespace it_hive\core\AdminBox;

abstract class TermBox extends Box {
	const defaults = array(
		'taxonomies' => ['category'],
		'source'		=> 'termMeta',
		'sourceObject'  => 0
	);

	const exclude = parent::exclude + self::defaults;

	protected static $counter = 1;

	function __construct( $params ) {
		$this->params = array_merge(self::defaults,$params);
		$this->name = $this->params['name'];
		parent::__construct( $this->params );
	}

	protected function addActions() {
		//add_action( 'init', array( $this, 'add_meta_box' ) );
		foreach ($this->params['taxonomies'] as $taxonomy){
			add_action( $taxonomy.'_add_form_fields', array($this,'show'), 10, 2 );
			add_action( $taxonomy.'_edit_form_fields', array($this,'show'), 10, 2 );
			add_action( 'edited_'.$taxonomy, array( $this, 'save') );
			add_action( 'create_'.$taxonomy, array( $this, 'save') );
		}
	}

	public function show( $term =null,$args=[] ) {
		//TODO: временный хардкод
		if(is_object($term)){
			$this->params['sourceObject'] = $term->term_id;
			echo '<tr><td colspan="2">';
		}
		parent::show();
		if(is_object($term)){
			echo '</td></tr>';
		}
	}

	public function save( $id = 0 ) {
		$this->params['sourceObject'] = $id;
		parent::save();
	}

	public function display($skin = null, $term = 0){
		if(!$term){
			$term = get_queried_object_id();
		}
		$this->params['sourceObject'] = $term;
		parent::display($skin);
	}
}