<?php
/*
  Plugin Name: Menu - Ordering - Reservations
  Plugin URI: https://www.gloriafood.com/wordpress-restaurant-plugin
  Description: This plugin is all you need to turn your restaurant website into an online business. Using a simple and friendly interface you get a restaurant menu, online food ordering and restaurant booking system. All free, no fees, no hidden costs, no commissions - for unlimited food orders and restaurant reservations.

  Version: 2.2.2
  Author: GloriaFood
  Author URI: https://www.gloriafood.com/
  License: GPLv2+
  Text Domain: menu-ordering-reservations

  @package  RestaurantSystem
  @category Core
  @author   GLOBALFOOD
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'GLF_PHP_COMPARE' ) ) {
	define( 'GLF_PHP_COMPARE', version_compare( PHP_VERSION, '5.3', '<' ) );
}

if ( ! defined( 'GLF_PLUGIN_DIR' ) ) {
	define( 'GLF_PLUGIN_DIR', trailingslashit( ( GLF_PHP_COMPARE ? dirname( __FILE__ ) : __DIR__ ) ) );
}

if ( ! defined( 'GLF_PLUGIN_URL' ) ) {
	define( 'GLF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'GLF_BASE_URL' ) ) {
	define( 'GLF_BASE_URL', 'https://www.restaurantlogin.com/' );
}
if ( ! defined( 'GLF_API_URL' ) ) {
	define( 'GLF_API_URL', GLF_BASE_URL . 'api/' );
}

if ( ! defined( 'GLF_STAGING_URL' ) ) {
	define( 'GLF_STAGING_URL', 'https://staging.restaurantlogin.com/' );
}
if ( ! defined( 'GLF_STAGING_API_URL' ) ) {
	define( 'GLF_STAGING_API_URL', GLF_STAGING_URL . 'api/' );
}

class GLF_Restaurant_System {
	var $version = '2.2.2',
		$api_token = null,
		$custom_css = null,
		$auth_domain = null,
		$auth_token = null,
		$restaurants = null,
		$user = null;

	private static $_instance = null;

	// Constructor
	private function __construct() {
		if ( ! class_exists( 'Glf_Utils' ) ) {
			require_once GLF_PLUGIN_DIR . '/includes/utils/class-glf-utils.php';
		}
		$this->glf_system_config();
		if ( wp_doing_ajax() ) {
			$this->glf_system_ajax();
		} else {
			if ( is_admin() ) {
				$this->glf_system_admin();
			} else {
				$this->glf_system_frontend();
			}
		}
		$this->glf_system_init();
	}

	public function glf_system_config() {
        $this->admin_language = strtolower( str_replace( '_', '-', get_user_locale() ) );
		$this->load_user_data();
        Glf_Utils::glf_require_once( GLF_PLUGIN_DIR . '/includes/admin/class-glf-admin-screens.php' );
		Glf_Utils::glf_require_once( GLF_PLUGIN_DIR . 'includes/modules/class-glf-modules.php' );
	}

	public function glf_system_ajax() {
		add_action( 'wp_ajax_restaurant_system_customize_button', array( $this, 'customize_button_dialog' ) );
		add_action( 'wp_ajax_glf_set_default_location', array( $this, 'glf_set_default_location' ) );
	}
	// store the admin dropdown location selected.
	// Value to be used to pre-select the location in publishing
	public function glf_set_default_location() {
		$location                                = isset( $_POST[ 'location' ] ) ? (string) $_POST[ 'location' ] : '';
		$glf_wordpress_options                   = Glf_Utils::glf_wp_options_data();

        $glf_wordpress_options->{'app_options'}[ Glf_Utils::$_GLF->user->id ]['default_location'] = $location;
		Glf_Utils::glf_database_option_operation( 'update', 'glf_wordpress_options', $glf_wordpress_options );
		echo json_encode( array( 'message' => 'done', 'post' => $_POST ) );
		exit;
	}

	public function glf_system_admin() {
		add_action( 'media_buttons', array( $this, 'add_ordering_media_button' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_media_scripts' ) );
        add_action( 'wp_loaded', array( $this, 'glf_template_redirect_old_link' ) );
    }

    public function glf_template_redirect_old_link( $template ) {
        $old_pages = array( 'glf-admin', 'glf-publishing', 'glf-extras', 'glf-partner', 'glf-options' );
        if( isset( $_GET, $_GET[ 'page' ] ) && in_array( $_GET[ 'page' ], $old_pages, true ) ){
            wp_safe_redirect( admin_url( 'admin.php?page=glf-overview' ) );
            exit;
        }
    }

    public function glf_system_frontend() {
		add_action( 'wp_print_styles', array( $this, 'add_public_media_scripts' ) );
	}

	public function glf_system_init() {
		register_activation_hook( __FILE__, array( 'GLF_Restaurant_System', 'glf_mor_install' ) );
		register_uninstall_hook( __FILE__, array( 'GLF_Restaurant_System', 'glf_mor_uninstall' ) );

		if( get_option( 'glf_check_old_widgets_elementor' ) !== 'no' ){
            add_action( 'wp', array( $this, 'glf_cron_job_register' ) );
            add_action( 'glf_cron_check_elementor', array( 'GLF_Restaurant_System', 'check_for_elementor_old_widgets_in_posts' ) );
        }

        add_action( 'wp_enqueue_scripts', array( $this,  'menu_ordering_reservations_set_script_translations' ), 100 );
        add_action( 'init', array( $this, 'glf_load_languages' ) );
        add_action( 'admin_init', array( $this, 'glf_plugin_activation_redirect' ) );
		add_action( 'wpmu_new_blog', array( $this, 'glf_mor_new_blog' ), 10, 6 );

		//update process complete
        add_action( 'upgrader_process_complete', array( $this, 'glf_upgrader_process_complete'), 10, 2 );
        add_action( 'plugins_loaded', array( $this, 'glf_plugins_loaded' ) );
	}

	public function glf_cron_job_register(){
        if ( !wp_next_scheduled( 'glf_cron_check_elementor' ) ) {
            $event_recurrence = 'daily'; // daily
            wp_schedule_event( time(), $event_recurrence, 'glf_cron_check_elementor' );
        }
    }

    public function glf_upgrader_process_complete( $upgrader_options, $options ) {
        if ( $options['action'] === 'update' && $options['type'] === 'plugin' && is_array( $options['plugins']) && !empty( $options['plugins'])) {
            $glf_plugin = plugin_basename( __FILE__ );
            foreach ( $options[ 'plugins' ] as $plugin ) {
                // check if the plugin is in the update plugins list
                if ( $plugin == $glf_plugin ) {
                    // store old version so we can compare it when the
                    // new version is loaded for the first time
                    update_option( 'glf_plugin_version', Glf_Utils::$_GLF->version );
                }
            }
            unset( $plugin );
        }
    }

    public function glf_plugins_loaded() {
	    // get the previous version that was stored when the action/hook 'upgrader_process_complete' was called
        $glf_plugin_updated_version = get_option( 'glf_plugin_version', '0' );

        // Run code that should match a certain version number.
        /*
         *  This part should be maintained, periodically, when we reach
         *  a certain percentage of installations with a desired version number
         *
         */

        // update code for versions lower than 2.0.2
        if ( Glf_Utils::$_GLF->version === '2.0.2' && version_compare( $glf_plugin_updated_version, '2.0.2', '<' ) && current_user_can('manage_options')) {
            update_option( 'glf_plugin_version', Glf_Utils::$_GLF->version );
            Glf_Utils::glf_set_default_app_options();
            Glf_Utils::glf_custom_css_check_and_set_defaults( Glf_Utils::$_GLF->custom_css );
            Glf_Helper_Screens::set_default_setup_options();
            Glf_Utils::glf_add_to_wordpress_options( 'is_update', true );
        } else if( Glf_Utils::$_GLF->version === '2.1.0' && version_compare( $glf_plugin_updated_version, '2.1.0', '<' ) && current_user_can( 'manage_options' ) ){
            update_option( 'glf_plugin_version', Glf_Utils::$_GLF->version );
            self::check_for_elementor_old_widgets_in_posts();
        }

    }

    public function glf_delete_post_check_old_widget_elementor( $pid ){
        self::check_for_elementor_old_widgets_in_posts();
    }

	public function glf_plugin_activation_redirect() {
	    if( get_option( 'glf_check_old_widgets_elementor' ) !== 'no' ){
            add_action( 'delete_post', array( $this, 'glf_delete_post_check_old_widget_elementor' ), 10 );
        }

        if ( !wp_doing_ajax() &&
            (int)get_option( 'glf_plugin_redirect', false ) === wp_get_current_user()->ID && !is_super_admin( wp_get_current_user()->ID ) ) {
            // Delete the option so we don't redirect more than once
            delete_option( 'glf_plugin_redirect' );
            $_POST['redirect'] = 'glf_load_screen';
            wp_safe_redirect( admin_url( 'admin.php?page=glf-overview&redirect=glf_load_screen' ) );
            //wp_safe_redirect( admin_url( 'admin.php?page=glf-screens' ) );
            exit;
        }
    }
	public function generate_installation_id() {
		return wp_generate_uuid4();
	}

    public function glf_fullscreen_mode() {
        echo '<style type="text/css">
        #adminmenumain, #wpadminbar{
            display: none;
        }

        #wpcontent, #wpfooter {
            margin-left: 0px;
        }

        #wpcontent {
            padding-left: 0px;

        }
        html.wp-toolbar{
            padding-top: 0px;
        }';
    }


	public function add_ordering_media_button() {
		?>
        <a id="glf-ordering" class="button thickbox" onclick="glf_mor_showThickBox('restaurant_system_insert_dialog')">
            <img src="<?= plugins_url( 'assets/images/GF-icon-black.svg', __FILE__ ) ?>"> Menu - Ordering - Reservations
        </a>
		<?php
	}


	public function customize_button_dialog() {
		Glf_Utils::glf_include( GLF_PLUGIN_DIR . 'includes/admin/settings/customize-button.php' );
	}


	public function glf_check_if_error_and_display_it( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->error = $response;
            require(GLF_PLUGIN_DIR . 'includes/admin/overview/overview.php');
			die;
		} else {
			$this->error = null;
		}
	}

	public function get_glf_mor_token( $target = 'admin' ) {
		if ( ! $this->is_authenticated() ) {
			return null;
		}
		$remoteUrl = $this->auth_domain . $this->auth_token . '/' . $target;
		$response  = wp_remote_post( $remoteUrl, array(
				'method'  => 'GET',
				'headers' => array()
			)
		);

		$this->glf_check_if_error_and_display_it( $response );
		$respone_body = json_decode( $response[ 'body' ] );
		if ( isset( $respone_body->errorDescription ) ) {
			$errors = new WP_Error();
			$errors->add( '1', $respone_body->errorDescription );
			$this->glf_check_if_error_and_display_it( $errors );
		}

		$url = $response[ 'body' ];
		if ( $target == 'admin' ) {
			$url .= '&language_code=' . $this->admin_language;
		}

		return $url;
	}

    /**
     * GloriaFood API call
     *
     * @param string $route API route
     * @param string $method wp_remote_post method type: GET or POST
     * @param string $body value that will be encoded to JSON
     *
     */
	public function glf_mor_api_call( $route, $method = 'GET', $body = '' ) {
		if ( ! $this->is_authenticated() ) {
			return null;
		}

		if ( ! $this->api_token ) {
			$api_token = $this->get_glf_mor_token( 'api' );
			$this->glf_check_if_error_and_display_it( $api_token );
			$this->api_token = $api_token;
		}
        $get_connection_type = get_option( 'glf_connection_type', '' );
		$response = wp_remote_post( ($get_connection_type === 'staging' ? GLF_STAGING_API_URL : GLF_API_URL) . $route, array(
				'method'  => $method,
				'headers' => array(
					'Authorization' => $this->api_token,
					'content-type'  => 'application/json'
				),
				'body'    => !empty($body) ? json_encode( $body ) : $body
			)
		);

		$this->glf_check_if_error_and_display_it( $response );

		$respone_body = json_decode( $response[ 'body' ] );
		if ( isset( $respone_body->errorDescription ) ) {
			$errors = new WP_Error();
			$errors->add( '1', $respone_body->errorDescription );
			$this->glf_check_if_error_and_display_it( $errors );
		}

		return $respone_body;
	}

	public function update_restaurants() {
		$restaurants = $this->glf_mor_api_call( 'user/restaurants' );
		$this->save_user_data( array( 'restaurants' => $restaurants ) );
		Glf_Utils::glf_more_restaurant_data( 'true', 'false' );
	}

	public function save_user_data( $options ) {
		$restaurant_data_obj = Glf_Utils::glf_more_restaurant_data();

		if ( ! $restaurant_data_obj ) {
			$restaurant_data_obj = new stdClass();
		}

		foreach ( $options as $key => $value ) {
			$restaurant_data_obj->$key = $value;
		}

		update_option( 'glf_mor_restaurant_data', $restaurant_data_obj );
	}

	/**
	 * Styling & JS: loading stylesheets and js for the plugin.
	 */
	public function add_media_scripts( $page ) {

		wp_enqueue_script( 'restaurant_system_media_btn_js', plugin_dir_url( __FILE__ ) . 'assets/js/wp-editor-glf-media-button.js', array(), $this->version );
		wp_enqueue_script( 'restaurant_system_clipboard_js', plugin_dir_url( __FILE__ ) . 'assets/js/clipboard.min.js', array(), '1.7.1' );

		wp_enqueue_script( 'restaurant_system_customize_btn_js', plugin_dir_url( __FILE__ ) . 'assets/js/admin-customize-button.js', array(), $this->version );

		wp_enqueue_script( 'restaurant_glf_main_js', plugin_dir_url( __FILE__ ) . 'includes/admin/assets/js/main.js', array(), $this->version );
            wp_localize_script( 'restaurant_glf_main_js', 'glf_ajax_url', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        wp_enqueue_script( 'restaurant_system_public_scripts_js', plugin_dir_url( __FILE__ ) . 'assets/js/public-scripts.js', array(), $this->version, true );

		wp_enqueue_script( 'restaurant_system_footer_js', plugin_dir_url( __FILE__ ) . 'assets/js/footer.js', array(), $this->version, true );
		wp_enqueue_style( 'restaurant_system_style', plugins_url( 'assets/css/style.css', __FILE__ ), false, $this->version );
		wp_enqueue_style( 'restaurant_system_public_style', plugins_url( 'assets/css/public-style.css', __FILE__ ), false, $this->version );


		wp_enqueue_style( 'restaurant_css', plugins_url( 'includes/admin/assets/css/main_css.css', __FILE__ ), false, $this->version );


		if ( Glf_Utils::glf_url_ends_with( $page, 'partner' ) || Glf_Utils::glf_url_ends_with( $page, 'extras' ) ) {
			wp_enqueue_style( 'restaurant_system_website_style', plugins_url( 'assets/css/style-website.css', __FILE__ ), false, $this->version );
		}

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
	}

	public function add_public_media_scripts( $page ) {
        wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'restaurant_system_public_style', plugins_url( 'assets/css/public-style.css', __FILE__ ), false, $this->version );
        wp_enqueue_script( 'restaurant_system_public_scripts_js', plugin_dir_url( __FILE__ ) . 'assets/js/public-scripts.js', array(), $this->version, true );
	}

	/*
	 * Propagate action to the whole network
	 */
	public static function glf_mor_propagate_in_network( $networkwide, $action ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $networkwide ) {
				$old_blog_id = $wpdb->blogid;

				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );

					if ( $action === 'install' ) {
						self::_glf_mor_install();
					} else {
						if ( $action === 'uninstall' ) {
							self::_glf_mor_uninstall();
						}
					}
				}

				switch_to_blog( $old_blog_id );

				return;
			}
		}

		if ( $action === 'install' ) {
			self::_glf_mor_install();
		} else {
			if ( $action === 'uninstall' ) {
				self::_glf_mor_uninstall();
			}
		}
	}

	/*
	 * Actions performed on plugin activation
	 */
	public static function glf_mor_install( $networkwide ) {
		self::glf_mor_propagate_in_network( $networkwide, 'install' );

        self::check_for_elementor_old_widgets_in_posts();
	}

	public static function check_multiple_plugins_activations(){
        // don't setup the redirect option if multiple plugins are activated at once
        return ( isset( $_REQUEST[ 'action' ], $_POST[ 'checked' ] ) &&
            'activate-selected' === $_REQUEST[ 'action' ] &&
            count( $_POST[ 'checked' ] ) > 1 );
    }

    public static function check_for_elementor_old_widgets_in_posts(){

        $glf_old_widgets_present = 'no';
        $glf_elementor_widgets_used = '';
        $all_post_types = get_post_types();
        foreach ( $all_post_types as $post_type ) {
            $skip_post_types = [
                'elementor_library',
                'attachment',
                'nav_menu_item',
                'custom_css',
                'custom_css',
                'customize_changeset',
                'oembed_cache',
                'user_request',
                'wp_block',
                'wp_template',
            ];
            if ( !in_array( $post_type, $skip_post_types, false ) ) {
                $search_posts = get_posts( [ 'numberposts' => '-1', 'post_type' => $post_type ] );
                foreach ( $search_posts as $post ) {
                    $meta_elementor_data = get_post_meta( (int)$post->ID, '_elementor_data', true );
                    if ( !empty( $meta_elementor_data ) ) {
                        $glf_elementor_widgets_used .= $meta_elementor_data;
                    }
                }
            }
        }
        if ( strpos( $glf_elementor_widgets_used, 'glf_elementor_ordering' ) !== false || strpos( $glf_elementor_widgets_used, 'glf_elementor_glf_elementor_reservations' ) !== false ) {
            $glf_old_widgets_present = 'yes';
        } else {
            self::glf_cron_deactivate_check_elementor();
        }
        update_option( 'glf_check_old_widgets_elementor', $glf_old_widgets_present );

    }

	public static function _glf_mor_install() {
		if ( ! get_option( 'glf_mor_installation_id' ) ) {
			update_option( 'glf_mor_installation_id', wp_generate_uuid4() );
		}

        if ( self::check_multiple_plugins_activations() ) {
            return;
        }
        add_option( 'glf_plugin_redirect', wp_get_current_user()->ID );
	}

	/*
	 * Actions performed on plugin uninstall
	 */
	public static function glf_mor_uninstall() {
		self::glf_mor_propagate_in_network( true, 'uninstall' );
	}

	public static function _glf_mor_uninstall() {
		delete_option( 'glf_mor_installation_id' );
		delete_option( 'glf_mor_restaurant_data' );

        // deactivate the cron that checks for old widgets elementor on posts
        self::glf_cron_deactivate_check_elementor();
    }
	public static function glf_cron_deactivate_check_elementor(){
        wp_clear_scheduled_hook( 'glf_cron_check_elementor' );
    }


	public function is_authenticated() {
		return $this->auth_token;
	}

	public function load_user_data() {
		$restaurant_data_obj = Glf_Utils::glf_more_restaurant_data();
		$pages               = array( 'auth_domain', 'auth_token', 'restaurants', 'user', 'custom_css' );

		foreach ( $pages as $key ) {
			$this->$key = $restaurant_data_obj && isset( $restaurant_data_obj->$key ) ? $restaurant_data_obj->$key : null;
		}
		$this->installation_id = get_option( 'glf_mor_installation_id' );
	}

	public function remove_user_data() {
		delete_option( 'glf_mor_restaurant_data' );
		$this->load_user_data();
	}

	/*
	 * Actions performed when a new blog is added to the multisite
	 */
	public static function glf_mor_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;

		if ( is_plugin_active_for_network( 'menu-ordering-reservations/restaurant-system.php' ) ) {
			$old_blog_id = $wpdb->blogid;

			switch_to_blog( $blog_id );
			self::_glf_mor_install();

			switch_to_blog( $old_blog_id );
		}
	}

    public function menu_ordering_reservations_set_script_translations() {
        //wp_set_script_translations( 'menu-ordering-editor', 'menu-ordering-reservations', GLF_PLUGIN_DIR . 'languages' );
    }
    public function glf_load_languages() {
        load_plugin_textdomain( 'menu-ordering-reservations', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

	public static function getInstance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new GLF_Restaurant_System();
		}

		Glf_Utils::$_GLF = self::$_instance;

		return self::$_instance;
	}
}

GLF_Restaurant_System::getInstance();
?>
