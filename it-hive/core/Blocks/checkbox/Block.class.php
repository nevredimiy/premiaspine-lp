<?php
namespace it_hive\core\Blocks\checkbox;

class Block extends \it_hive\core\Blocks\DataBlock {

    const defaults = array(
        'default'	=> ''
    );

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }

    public function render(){
        $this->params['checked'] = ( $this->get() == $this->params['default'] ) ? 'checked="checked"' : '';
        $html = parent::render();
        return $html;
    }

}