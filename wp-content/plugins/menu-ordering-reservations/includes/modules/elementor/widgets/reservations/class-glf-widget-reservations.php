<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Scheme_Typography;

if ( !class_exists( 'Glf_Widget_Reservations' ) ) {

    /**
     * GloriaFood Elementor Widget implementation
     *
     * @since 1.5.0
     */
    class Glf_Widget_Reservations extends Glf_Widget_Button_Base {
        /**
         * Set widget name
         *
         * Used by elementor to identify the widget
         */
        public function get_name() {
            return 'glf_elementor_widget_reservations';
        }

        /**
         * Set widget title
         *
         * Visual widget label on the builder
         */
        public function get_title() {
            return __( 'Reservations', 'menu-ordering-reservations' );
        }

        /**
         * Set widget icon
         *
         * Visual widget icon css class
         */
        public function get_icon() {
            return 'glf-elementor-reservations-widget-icon';
        }

        /**
         * Set widget keywords
         *
         * Widget keywords used for searching
         */
        public function get_keywords() {
            return array_merge_recursive( [ 'reservations' ], $this->get_base_keywords() );
        }

        /**
         * Set widget default label
         *
         * Widget button default label
         */
        public function get_button_default_label() {
            return 'Table Reservations';
        }
    }
}