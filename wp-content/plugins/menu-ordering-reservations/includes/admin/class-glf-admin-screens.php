<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * This class manages the screens
 *
 * @since     1.1.0
 */
if ( !class_exists( 'Glf_Admin_Screens' ) ) {

    class Glf_Admin_Screens {
        public function __construct() {
            $this->glf_admin_screens_config();
            if ( !wp_doing_ajax() ) {
                if ( is_admin() ) {
                    $this->glf_admin_screens_menu();
                }
            }
            else{
                add_action( 'wp_ajax_glf_form_sign_up', array( $this, 'forms_action' ) );
                add_action( 'wp_ajax_glf_form_login', array( $this, 'forms_action' ) );
                add_action( 'wp_ajax_glf_form_forgot_password', array( $this, 'forms_action' ) );
                add_action( 'wp_ajax_glf_form_disconnect', array( $this, 'forms_action' ) );

                add_action( 'wp_ajax_glf_set_option', array( $this, 'set_option' ) );
                add_action( 'wp_ajax_glf_chosen_options', array( $this, 'chosen_options' ) );
                add_action( 'wp_ajax_glf_setup_options_remove', array( $this, 'glf_setup_options_remove' ) );

                add_action( 'wp_ajax_glf_get_updated_urls', array( $this, 'glf_get_updated_urls' ) );

                add_action( 'wp_ajax_glf_load_screen', array( $this, 'glf_load_screen' ) );

                add_action( 'wp_ajax_glf_tracking', array( $this, 'glf_tracking' ) );

                add_action( 'wp_ajax_glf_create_demo_page', array( $this, 'glf_create_demo_page' ) );
            }
        }
        public function glf_admin_screens_config(){
            if ( class_exists( 'Glf_Utils' ) ) {
                Glf_Utils::glf_require_once( GLF_PLUGIN_DIR . '/includes/admin/helper/class-glf-helper-screens.php' );
            }
        }
        public function glf_admin_screens_menu(){
            add_action( 'admin_menu', array( $this, 'glf_wordpress_add_menu_options' ) );
        }

        /*
          * Actions perform at loading of admin menu
        */
        public function glf_wordpress_add_menu_options() {
            $title = 'GloriaFood';
            if ( current_user_can( 'manage_options' ) ) {
                add_menu_page( 'GloriaFood', $title, 'manage_options', 'glf-overview', array(
                    $this,
                    'glf_wordpress_add_menu_include'
                ), GLF_PLUGIN_URL . 'assets/images/GF-icon.svg', '2.2.9' );

                add_submenu_page( 'glf-overview', 'Overview', 'Overview', 'manage_options', 'glf-overview', array(
                    $this,
                    'glf_wordpress_add_menu_include'
                ) );
                add_submenu_page( 'glf-overview', 'Settings', 'Settings', 'manage_options', 'glf-settings', array(
                    $this,
                    'glf_wordpress_add_menu_include'
                ) );
            }
        }

        /*
         * Actions perform on loading of left menu or settings pages
         */
        public function glf_wordpress_add_menu_include() {
            $pages = array( 'overview', 'settings', 'screens', 'html' );
            $screen = get_current_screen();
            foreach ( $pages as $page ) {
                if ( Glf_Utils::glf_url_ends_with( $screen->base, $page ) !== false ) {
                    if ( in_array( $page, array( 'overview', 'settings', 'screens', 'html' ) ) ) {
                        Glf_Utils::$_GLF->update_restaurants();
                        Glf_Utils::$_GLF->load_user_data();
                        Glf_Utils::glf_set_default_app_options( false, 'add_menu' );
                        Glf_Utils::glf_custom_css_check_and_set_defaults( Glf_Utils::$_GLF->custom_css );
                        require(GLF_PLUGIN_DIR . 'includes/admin/' . $page . '/class-glf-screen-' . $page . '.php');
                    }
                    break;
                }
            }
        }


        public function glf_load_screen() {
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';
            $response = array( 'action' => $action );

            $screen = isset( $_POST[ 'data' ], $_POST[ 'data' ][ 'screen' ] ) ? $_POST[ 'data' ][ 'screen' ] : '';

            if ( $action !== 'glf_load_screen' && !empty( $screen )  ) {
                $response[ 'status' ] = 'success';
                echo json_encode( $response );
                exit();
            }

            if( $screen === 'overview_authenticate' ){
                ob_start();
                Glf_Utils::glf_require_once( __DIR__ . '/overview/overview_top_bar.php' );
                $_POST['screen_sign_up'] = 'true';
                Glf_Utils::glf_require_once( __DIR__ . '/overview/overview_authenticate.php' );
                $response[ 'screen' ] = ob_get_clean();
                $response[ 'empty' ] = 'true';
            }


            $response[ 'POST' ] = $_POST;
            $response[ 'status' ] = 'success';
            echo json_encode( $response );
            exit();

        }
        public function forms_action() {
            $errors = array();
            $action = isset( $_POST[ 'action' ] ) ? str_replace( 'glf_form_', '', $_POST[ 'action' ] ) : '';

            $data = $_POST[ 'data' ];
            parse_str( $data, $data );

            $_POST = array( 'action' => $action );
            foreach ( $data as $key => $d ) {
                $_POST[ $key ] = $d;
            }
            $response = array( 'action' => $action );


            if ( in_array( $action, array( 'forgot_password', 'login', 'sign_up' ) ) ) {
                if ( $this->glf_security_check() ) {
                    $this->glf_security_check_failed( $response );
                }

                $response[ 'data' ] = $data;

                $validate_fields = $this->glf_validate_fields( $action );
                if ( !empty( $validate_fields ) ) {
                    $errors = $validate_fields;
                } else {
                    $errors = $this->glf_validate_form( $action );
                }
                if( !empty( $errors ) ){
                    $response[ 'errors' ] = $errors;
                }
                if ( empty( $errors ) ) {
                    $response[ 'status' ] = 'success';
                    $response[ 'post' ] = $_POST;
                    $response[ 'logged_in' ] = Glf_Utils::$_GLF->is_authenticated();

                    Glf_Utils::glf_set_default_app_options( false, $action);
                    $glf_wordpress_options = Glf_Utils::glf_wp_options_data('true');
                    ob_start();
                    if( $action !== 'forgot_password' ){
                        Glf_Utils::$_GLF->update_restaurants();
                        Glf_Utils::$_GLF->load_user_data();
                        $is_update = Glf_Utils::glf_get_from_wordpress_options( 'is_update', '' );

                        if( $is_update ){
                            //Glf_Utils::glf_add_to_wordpress_options( 'is_update', false );
                            Glf_Helper_Screens::set_default_setup_options();
                            $response[ 'action' ] = 'redirect';
                            $response[ 'screen_url' ] = admin_url() . 'admin.php?page=glf-overview';
                        }  else{

                            Glf_Utils::glf_require_once( __DIR__ . '/overview/overview_screen_options.php' );
                            Glf_Utils::glf_add_to_wordpress_options( $action, false );

                            $screen_output = glf_get_screen_sequence( $action );
                            $response[ 'html' ] = $screen_output['output'];
                            if( empty( $response[ 'html' ] ) ){
                                $response[ 'action' ] = 'redirect';
                                $response[ 'screen_url' ] = admin_url() . 'admin.php?page=glf-overview';
                            }
                            else{
                                if( $screen_output['extra'] === 'empty' ){
                                    $response[ 'empty' ] = 'true';
                                }
                                echo $response[ 'html' ];
                            }
                        }


                    }else{
                        Glf_Utils::glf_require_once( __DIR__ . '/overview/overview_authenticate.php' );
                        $response[ 'empty' ] = 'true';
                    }

                    $response[ 'screen' ] = ob_get_clean();

                }
            } else if ( $action === 'disconnect' ) {
                delete_option( 'glf_connection_type' );
                Glf_Utils::$_GLF->remove_user_data();
            }

            $response[ 'POST' ] = $_POST;
            echo json_encode( $response );
            exit();
        }
        public function set_option() {
            $errors = array();
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';

            $data = $_POST[ 'data' ];
            $response = array( 'action' => $action );
            if ( $action === 'glf_set_option' ) {
                Glf_Utils::glf_add_to_wordpress_options( (string)$data[ 'option_name' ], (string)$data[ 'option_value' ] );


                if( isset( $data['screen'] ) ){
                    if( $data[ 'screen' ] === 'overview' ){
                        $response[ 'action' ] = 'redirect';
                        $response[ 'screen_url' ] = admin_url() . 'admin.php?page=glf-overview';

                    } else{
                        $response[ 'action' ] = $data[ 'screen' ];
                    }
                }
                $response[ 'status' ] = 'success';
                $response[ $data['option_name'] ] = Glf_Utils::glf_get_from_wordpress_options( (string)$data[ 'option_name' ], '', 'true' );

            }

            echo json_encode( $response );
            exit();
        }
        public function chosen_options() {
            $errors = array();
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';

            $data = $_POST[ 'data' ];
            $response = array( 'action' => $action );
            if ( $action === 'glf_chosen_options' ) {
                $chosen_options = array();
                foreach ( $data as $option_name => $option ) {
                    if ( $option === 'true' ) {
                        $_extra_options = Glf_Helper_Screens::get_todo_options_of_extra_option( $option_name );
                        if(!empty( $_extra_options) && is_array( $_extra_options)){
                            foreach ( $_extra_options as $_extra_opt_key => $val ) {
                                $chosen_options[] = $_extra_opt_key;
                            }
                        } else{
                            $chosen_options[] = $option_name;
                        }
                    }

                }
                Glf_Utils::glf_add_to_wordpress_options( 'setup_options', $chosen_options );

                $response[ 'status' ] = 'success';
                $response[ 'data' ] = $_POST[ 'data' ];
            }

            echo json_encode( $response );
            exit();
        }
        public function glf_setup_options_remove() {
            $errors = array();
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';

            $data = $_POST[ 'data' ];
            $response = array( 'action' => $action );
            if ( $action === 'glf_setup_options_remove' && isset( $data['option'] )  ) {
                if( $data[ 'option' ] === 'enroll_partner_program' ){
                    Glf_Utils::glf_add_to_wordpress_options( 'partner_program_dismiss', 'yes' );
                }else{
                    $chosen_options = Glf_Utils::glf_get_from_wordpress_options( 'setup_options', array() );
                    foreach ( $chosen_options as $idx => $option ) {
                        if ( $data[ 'option' ] === $option ) {
                            unset( $chosen_options[ $idx ] );
                        }

                    }
                    Glf_Utils::glf_add_to_wordpress_options( 'setup_options', $chosen_options );
                }

                $response[ 'status' ] = 'success';
                $response[ 'data' ] = $_POST[ 'data' ];
            }

            echo json_encode( $response );
            exit();
        }
        public function glf_get_updated_urls(){
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';

            $response = array( 'action' => $action );
            if ( $action === 'glf_get_updated_urls' ) {
                $data = $_POST[ 'data' ];
                $location = isset( $data[ 'location' ] ) ? (string)$data[ 'location' ] : '';

                $glf_wordpress_options = Glf_Utils::glf_wp_options_data();
                $glf_wordpress_options->{'app_options'}[ Glf_Utils::$_GLF->user->id ][ 'default_location' ] = $location;
                Glf_Utils::glf_database_option_operation( 'update', 'glf_wordpress_options', $glf_wordpress_options );

                $response[ 'status' ] = 'success';
                $response[ 'location' ] = $location;
                $restaurant = Glf_Utils::get_restaurant_data_by_location( $location );
                $response[ 'todo' ] = Glf_Helper_Screens::generate_todo_list( $restaurant );
                $response[ 'admin_url' ] = Glf_Utils::$_GLF->get_glf_mor_token() . '&r=app.admin.setup&acid=' . $restaurant->account_id;

            }

            echo json_encode( $response );
            exit();
        }

        public function glf_security_check_failed( $response ) {
            $response[ 'status' ] = 'error';
            $response[ 'msg' ] = 'Access restricted, security check failed!';
            echo json_encode( $response );
            exit();
        }

        public function glf_security_check() {
            return (!(!empty( $_POST[ 'action' ] ) && current_user_can( 'manage_options' ) && isset( $_POST[ '_wpnonce' ] ) && wp_verify_nonce( $_POST[ '_wpnonce' ], 'glf-mor-auth' )));
        }

        public function glf_validate_form( $mode ) {
            $errors = array();

            if( $mode === 'login' ){
                $connection_type = isset( $_POST[ 'glf_connection_type' ] ) ? $_POST[ 'glf_connection_type' ] : '';
                if( !empty( $connection_type ) ){
                    update_option( 'glf_connection_type', $connection_type );
                }
            }
            $get_connection_type = get_option( 'glf_connection_type', '' );
            $response_body = Glf_Utils::glf_mor_remote_call( ( $get_connection_type === 'staging' ? GLF_STAGING_API_URL : GLF_API_URL ), $mode );

            $response[ 'response_body' ] = $response_body;
            if ( isset( $response_body->errorDescription ) ) {
                $errors[ "form" ] = $response_body->errorDescription;
            } else if ( $mode !== 'forgot_password' ) {
                if ( $mode !== 'login' ) {
                    $errors = $this->glf_validate_form( 'login' );
                } else {
                    Glf_Utils::$_GLF->save_user_data( array(
                        'user' => $response_body->user,
                        'auth_domain' => $response_body->domain,
                        'auth_token' => $response_body->token
                    ) );
                    Glf_Utils::$_GLF->load_user_data();
                }
            }
            return $errors;
        }

        public function glf_validate_fields( $mode ) {
            $fields = array(
                "login" => array( "email", "password" ),
                "sign_up" => array( "email", "password", "restaurant_name", "first_name", "last_name" ),
                "forgot_password" => array( "email" ),
            );
            $fields = $fields[ $mode ];
            $errors = array();
            foreach ( $fields as $field ) {
                if ( !isset( $_POST[ $field ] ) || empty( $_POST[ $field ] ) ) {
                    $errors[ $field ] = "Please fill in the " .$this->glf_field_to_label( $field );
                } elseif ( $field === "email" && !is_email( $_POST[ $field ] ) ) {
                    $errors[ $field ] = "The Email Address you inserted is invalid!";
                }
            }

            return $errors;
        }

        public function glf_field_to_label( $field ) {
            switch ( $field ) {
                case 'email':
                    return __( 'Email', 'menu-ordering-reservations' );

                case 'password':
                    return __( 'Password', 'menu-ordering-reservations' );

                case 'restaurant_name':
                    return __( 'Restaurant name', 'menu-ordering-reservations' );

                case 'first_name':
                    return __( 'First name', 'menu-ordering-reservations' );

                case 'last_name':
                    return __( 'Last name', 'menu-ordering-reservations' );

                default:
                    return ucwords( str_replace( "_", " ", $field ) );
            }

        }

        public function glf_tracking() {
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';
            $data = $_POST[ 'data' ];
            $response = array( 'action' => $action );
            if ( $action === 'glf_tracking' ) {
                $response['data'] = Glf_Utils::glf_tracking_send( $data );

            }
            echo json_encode( $response );
            exit();
        }

        public function glf_create_demo_page() {
            $action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : '';
            $data = $_POST[ 'data' ];
            $response = array( 'action' => $action );
            if ( $action === 'glf_create_demo_page' ) {
                Glf_Helper_Screens::create_demo_page();
                $response['url'] = Glf_Helper_Screens::get_demo_page_edit_url();

            }
            echo json_encode( $response );
            exit();
        }

    }

    new Glf_Admin_Screens();
}