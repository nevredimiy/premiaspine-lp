<?php
namespace it_hive\core\Blocks\group;

class Block extends \it_hive\core\Blocks\DataBlock {

    public function render() {
        $this->params['label'] = $this->params['label'] ? '<h4>' . $this->params['label'] . '</h4>' : '';
        $html = parent::render();
        return $html;
    }
}