<?php //TODO: нужно проверить
namespace it_hive\core\Blocks\selectCategory;
use \it_hive\THEME;

class Block extends \it_hive\core\Blocks\DataBlock {

    const defaults = array(
        'taxonomy' => 'category'
    );

    const exclude = parent::exclude + self::defaults;

    public function __construct( $params ) {
        $this->params = array_merge(self::defaults, $params);
        parent::__construct( $this->params );
    }

    public function render() {
        $this_cat = $this->get();
        $args = array(
            'taxonomy'   => $this->params['taxonomy'],
            'hide_empty' => true
        );
        $categories = \it_hive\core\source\getCategories::get_categories( $args );
        $options = array();
        if( $categories ) {
//            $options[0] = '<option value="">Please select a Category</option>';
            $options[0] = '<option value="">' . __('Please select a Category', THEME::$textdomain) . '</option>';
            foreach( $categories as $cat ) {
                $selected = '';
                if( $this_cat == $cat->term_id ) {
                    $selected = 'selected="selected"';
                }
                $options[] = '<option value="' . $cat->term_id .'" '. $selected .'>' . $cat->name . '</option>';
            }
        }
        $this->params['options'] = join($options, '');
        $this->params['label'] = $this->params['label'] ? '<h4>' . $this->params['label'] . '</h4>' : '';
        $html = parent::render();
        return $html;
    }
}