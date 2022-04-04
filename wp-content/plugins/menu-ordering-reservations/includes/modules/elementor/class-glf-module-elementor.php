<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Glf_Module_Elementor' ) ) {

	/**
	 * GloriaFood Elementor Module implementation
	 *
	 * @since 1.5.0
	 */
	class Glf_Module_Elementor {

		public function __construct() {

			add_action( 'elementor/editor/wp_head', array( $this, 'elementor_panel_style' ) );
			add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );

			/**
             * GLF widget registration action. To be removed in future versions
             *
             * @since 1.5.0
             * @deprecated 2.1.0 New widgets are available {@see 'glf_elementor_widgets'}
             *
             * @action Registers GLF restaurant widgets.
             */
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'glf_elementor_restaurant_widget' ) );

			add_action( 'elementor/widgets/widgets_registered', array( $this, 'glf_register_elementor_widgets' ) );

		}

		public function add_elementor_widget_categories( $elements_manager ) {

			$elements_manager->add_category(
				'gloria-food',
				[
					'title' => __( 'Gloria Food - Restaurant', 'menu-ordering-reservations' )
				]
			);
			$elements_manager->add_category(
				'gloria-food-old',
				[
					'title' => __( 'Gloria Food - Deprecated Widgets', 'menu-ordering-reservations' )
				]
			);
			
		}


        /**
         * GLF register elementor widgets.
         *
         * Register all GLF widgets with elementor
         *
         * @return void
         * @since 2.1.0
         *
         */
		public function glf_register_elementor_widgets() {

			require_once 'widgets/base/class-glf-widget-button-base.php';
			require_once 'widgets/menu-ordering/class-glf-widget-ordering.php';
			require_once 'widgets/reservations/class-glf-widget-reservations.php';
            require_once 'widgets/food-menu/class-glf-module-elementor-food-menu-widget.php';
            require_once 'widgets/opening-hours/class-glf-module-elementor-opening-hours-widget.php';

			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Widget_Ordering() );
			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Widget_Reservations() );
			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Food_Menu_Widget() );
			Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Opening_Hours_Widget());

		}

		public function elementor_panel_style() {
			wp_enqueue_style( 'glf_elementor_panel_style', plugins_url( 'assets/css/glf-elementor-widget.css', __FILE__ ), false, Glf_Utils::$_GLF->version );
		}


        /**
         * Older widgets. To be removed in future versions
         * This will be used only when old widgets were used through out the website
         *
         * @since 1.5.0
         * @deprecated 2.1.0 New widgets are available {@see 'glf_register_elementor_widgets'}
         *
         * @action Register GLF restaurant widgets.
         */
        public function glf_elementor_restaurant_widget() {
            if( get_option( 'glf_check_old_widgets_elementor', 'no' ) === 'yes' ){
                require_once 'widgets/menu-ordering/class-glf-module-elementor-ordering-widget.php';
                require_once 'widgets/reservations/class-glf-module-elementor-reservations-widget.php';

                Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Ordering_Widget() );
                Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Glf_Module_Elementor_Reservations_Widget() );
            }

        }
	}

	new Glf_Module_Elementor();
}