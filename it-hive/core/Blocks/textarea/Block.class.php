<?php
namespace it_hive\core\Blocks\textarea;

class Block extends \it_hive\core\Blocks\DataBlock {

    const defaults = array(
        'description' => '',
        'maxlength' => '-1',
    ) +self::hiddenDefaults;

    const exclude = parent::exclude + self::hiddenDefaults;

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }

    public function render(){
        $allRadio = array();
        $this->params['radioList'] = join(' ',$allRadio );
        $this->params['description'] = $this->params['description'] ? '<p class="description">' . $this->params['description'] . '</p>' : '';
        $html = parent::render();
        return $html;
    }

}