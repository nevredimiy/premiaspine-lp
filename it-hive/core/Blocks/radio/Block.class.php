<?php
namespace it_hive\core\Blocks\radio;

class Block extends \it_hive\core\Blocks\DataBlock {

    const defaults = array(
        'listRadio'	=> array()
    );

    const exclude = parent::exclude + self::defaults;

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        if(is_callable($this->params['listRadio'])){
	        $this->params['listRadio'] = call_user_func($this->params['listRadio']);
        }
        parent::__construct( $this->params );
    }

    public function render(){
        $allRadio = array();
        foreach( $this->params['listRadio'] as $key => $radio ) {
            $checked = '';
            $value = str_replace(' ', '_', strtolower($key));
            if( $value == $this->get() ) {
                $checked = 'checked="checked"';
            }
            $allRadio[] = '<input type="radio" name="{{name}}" value="' . $value . '" ' . $checked . ' />' . ucwords(str_replace('_', ' ', $radio));
        }
        $this->params['radioList'] = '<span class="radio-wrap">'.join($allRadio, '</span><span class="radio-wrap">').'</span>';
        $this->params['label'] = $this->params['label'] ? '<h4>' . $this->params['label'] . '</h4>' : '';
        $html = parent::render();
        return $html;
    }

}