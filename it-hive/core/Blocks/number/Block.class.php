<?php //TODO: нужно проверить
namespace it_hive\core\Blocks\number;

class Block extends \it_hive\core\Blocks\DataBlock {
    const defaults = array(
        'step' => '',
        'min' => '',
        'max' => ''
    ) +self::hiddenDefaults;

    const exclude = parent::exclude + self::hiddenDefaults;

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }

    public function render(){
        $this->params['step'] = $this->params['step'] ? 'step="' . $this->params['step'] . '"' : '';
        $this->params['min'] = $this->params['min'] ? 'min="' . $this->params['min'] . '"' : '';
        $this->params['max'] = $this->params['max'] ? 'max="' . $this->params['max'] . '"' : '';
        $html = parent::render();
        return $html;
    }
}