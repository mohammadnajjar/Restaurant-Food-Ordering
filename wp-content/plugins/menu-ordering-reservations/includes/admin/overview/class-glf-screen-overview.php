<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * This class contains the overview screen
 *
 * @since     1.1.0
 */
if ( !class_exists( 'Glf_Screens_Overview' ) ) {

    class Glf_Screens_Overview {
        public function __construct() {
            $this->glf_overview_config();
            if ( wp_doing_ajax() ) {

            }

        }

        public function glf_overview_config() {
            Glf_Utils::glf_require_once( __DIR__ . '/overview.php' );
        }

    }

    new Glf_Screens_Overview();
}