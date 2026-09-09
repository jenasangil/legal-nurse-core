<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Control_Sortable_Pages extends \Elementor\Base_Data_Control {

    public function get_type() {
        return 'sortable_pages';
    }

    public function get_default_settings() {
        return [
            'options' => [],
        ];
    }

    public function content_template() {
        ?>
        <div class="elementor-control-field">
            <label class="elementor-control-title">{{{ data.label }}}</label>
            <div class="elementor-control-input-wrapper">
                <select class="sortable-pages-select" style="width:100%;">
                    <option value=""><?php _e( 'Select a page to add...', 'legal-nurse-core' ); ?></option>
                    <# _.each( data.options, function( label, value ) { #>
                        <option value="{{ value }}">{{{ label }}}</option>
                    <# } ); #>
                </select>
                <ul class="sortable-pages-list" style="margin-top:8px; padding-left: 0;"></ul>
            </div>
        </div>
        <?php
    }
}
