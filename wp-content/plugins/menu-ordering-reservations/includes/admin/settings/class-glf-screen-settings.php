<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * This class contains the overview screen
 *
 * @since     1.1.0
 */
if ( !class_exists( 'Glf_Screens_Settings' ) ) {

    class Glf_Screens_Settings {
        public function __construct() {
            $this->glf_settings_config();
        }

        public function glf_settings_config() {
            if( !Glf_Utils::$_GLF->is_authenticated() ){
                Glf_Utils::glf_require_once( __DIR__ . '/../overview/overview.php' );
            }
            Glf_Utils::glf_require_once( __DIR__ . '/settings.php' );
        }
    }

    new Glf_Screens_Settings();
}