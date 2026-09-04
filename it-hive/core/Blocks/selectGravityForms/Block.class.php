<?php
namespace it_hive\core\Blocks\selectGravityForms;
use \it_hive\THEME;

class Block extends \it_hive\core\Blocks\DataBlock {

    public function render() {
        if( !class_exists( 'GFForms' ) ) {
            die();
        }
        $forms = \RGFormsModel::get_forms( 1, 'title' );
        $options = array();
        if( $forms ) {
            $options[0] = '<option value="">' . __( 'Please select a Form', THEME::$textdomain ) .'</option>';
            foreach( $forms as $form ) {
                $selected = '';
                if( $this->get() == $form->id ) {
                    $selected = 'selected="selected"';
                }
                $options[] = '<option value="' . $form->id  . '" ' . $selected . '>' . $form->title . '</option>';
            }
        }
        $this->params['options'] = join($options, '');
        $this->params['label'] = $this->params['label'] ? '<h4>' . $this->params['label'] . '</h4>' : '';
        $html = parent::render();
        return $html;
    }
}