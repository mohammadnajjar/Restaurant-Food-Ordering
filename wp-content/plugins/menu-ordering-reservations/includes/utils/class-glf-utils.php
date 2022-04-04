<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * This class contains some utilities needed for the plugin.
 *
 * @since     1.1.0
 */
if ( ! class_exists( 'Glf_Utils' ) ) {

	class Glf_Utils {

		public static $_GLF = null;
		public static $_mor_restaurant_data = null;
		public static $_wp_options_data = null;


		public static function glf_save_user_data( $args ) {
			self::$_GLF->save_user_data( $args );
		}


		public static function glf_mor_remote_call( $url, $mode ) {

			switch ( $mode ) {
				case 'login':
					$action = 'login3';
					break;
				case 'forgot_password':
					$action = 'user/password_reset';
					break;
				default:
					$action = 'register';
			};

			$response = wp_remote_post( $url . $action, array(
					'method'  => 'POST',
					'headers' => array(),
					'body'    => $_POST,
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				die( "Something went wrong: $error_message" );
			}

			return json_decode( $response[ 'body' ] );
		}

		public static function iframe_src( $section ) {
			$params = array( 'parent_window' => 'wordpress' );

			switch ( $section ) {
				case 'menu':
					$params[ 'r' ]                    = 'app.admin.setup.menu_app.menu_editor';
					$params[ 'hide_top_menu' ]        = 'true';
					$params[ 'hide_left_menu' ]       = 'true';
					$params[ 'hide_left_navigation' ] = 'true';
					break;

				case 'setup':
					$params[ 'r' ]              = 'app.admin_ftu.setup';
					$params[ 'hide_top_menu' ]  = 'true';
					$params[ 'hide_left_menu' ] = 'true';
					break;


				default:
					break;
			}

			$src = self::$_GLF->get_glf_mor_token();
			$src .= strpos( $src, '?' ) ? '&' : '?1';

			foreach ( $params as $key => $value ) {
				$src .= "&$key=$value";
			}

			return $src;
		}

		public static function glf_get_restaurants() {
			return self::$_GLF->restaurants;
		}

		public static function glf_more_restaurant_data( $update = 'false', $return = 'true' ) {
			if ( self::$_mor_restaurant_data === null || $update === 'true' ) {
				self::$_mor_restaurant_data = get_option( 'glf_mor_restaurant_data' );
			}
			return ( $return === 'true' ) ? self::$_mor_restaurant_data : '';
		}

		public static function glf_wp_options_data( $update = 'false' ) {
			if ( self::$_wp_options_data === null || $update === 'true' ) {
				self::$_wp_options_data = get_option( 'glf_wordpress_options' );
			}

			return self::$_wp_options_data;
		}
		public static function glf_wp_options_data_get_option( $option_name,  $update = 'false' ) {

		    if ( self::$_wp_options_data === null || $update === 'true' ) {
				self::$_wp_options_data = get_option( 'glf_wordpress_options' );
			}

            $user_app_options = self::$_wp_options_data->{'app_options'}[ self::$_GLF->user->id ];

			return ( isset( $user_app_options[ $option_name ] ) ? $user_app_options[ $option_name ] : '');
		}
		public static function get_restaurant_data_by_location( $locations_uid ){
		    $restaurants = self::glf_get_restaurants();
		    $i = 0;
            if( $restaurants !== null ){
                foreach ( $restaurants as $restaurant ) {
                    if ( isset( $restaurant->uid ) && $restaurant->uid === $locations_uid ) {
                        return $restaurant;
                    }
                }
            }
		    return  '';
        }

		public static function glf_require_once( $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}

		public static function glf_include( $file ) {
			if ( file_exists( $file ) ) {
				include $file;
			}
		}

		public static function glf_url_ends_with( $haystack, $needle ) {
			$length = strlen( $needle );
			if ( $length === 0 ) {
				return true;
			}

			return ( substr( $haystack, - $length ) === $needle );
		}

		public static function glf_mor_get_shortcode( $ruid, $type, $useCustomCss = false, $class = "" ) {
			$code = '[';

			$code .= $type === 'reservations' ? 'restaurant-reservations' : 'restaurant-menu-and-ordering';
			$code .= ' ruid="' . $ruid . '"';

			if ( $useCustomCss ) {
				$code .= ' class="' . $class . '"';
			}

			$code .= ']';

			return $code;
		}

		public static function glf_set_default_app_options( $debug_reset = false, $source = '' ) {
		    if( !Glf_Utils::$_GLF->is_authenticated() && !$debug_reset ){
		        return;
            }
            $glf_wordpress_options = self::glf_wp_options_data();
            $per_user_default_options = array(
                'is_update' => false,
                'custom_css_by_location' => array(),
                'default_location' => '',
                'setup_options' => array(),
                'share_usage_data' => '',
                'partner_program' => ''
            );


            if ( !$glf_wordpress_options || $debug_reset ) {
                $glf_wordpress_options = new stdClass();
                if( $debug_reset ){
                    //$glf_wordpress_options->custom_css_by_location = '';
                    self::glf_database_option_operation( 'update', 'glf_wordpress_options', $glf_wordpress_options );
                    return;
                }
            }
            if ( !isset( $glf_wordpress_options->{'app_options'} ) ) {
                $glf_wordpress_options->{'app_options'} = array();
            }
            if( !isset( $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ] ) ){
                $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ] = array();
            }

            /*
             * Update per user options.
             * This adds feature options on existing installed GloriaFood instances
             * */
            foreach ( $glf_wordpress_options->{'app_options'} as $key => &$user_app_options ){
                foreach ( $per_user_default_options as $default_option_name => $default_option_value ) {
                    if ( !isset( $user_app_options[ $default_option_name ] ) ) {
                        $user_app_options[ $default_option_name ] = $default_option_value;
                    }
                }
            }


            /*
             * Backward compatibility for versions before 2.0.0
             * Update the new app_options per user
             * */
            $is_update = $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ 'is_update' ];
            if ( isset( $glf_wordpress_options->{'custom_css_by_location'} ) ) {
                $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ 'custom_css_by_location' ] = $glf_wordpress_options->{'custom_css_by_location'};
                $is_update = true;
                //unset old stored value
                unset( $glf_wordpress_options->{'custom_css_by_location'} );
            }
            if ( isset( $glf_wordpress_options->{'default_location'} ) ) {
                $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ 'default_location' ] = $glf_wordpress_options->{'default_location'};
                $is_update = true;
                //unset old stored value
                unset( $glf_wordpress_options->{'default_location'} );
            }
            $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ 'is_update' ] = $is_update;
            self::glf_database_option_operation( 'update', 'glf_wordpress_options', $glf_wordpress_options );

            $setup_options = empty( Glf_Utils::glf_get_from_wordpress_options( 'setup_options', '' ) );
            $set_default_setup_options = $source === 'add_menu' && $is_update && $setup_options;
            if ( $set_default_setup_options ) {
                Glf_Helper_Screens::set_default_setup_options();
            }

        }
		public static function glf_custom_css_check_and_set_defaults( $default_css ) {
            $restaurant_data_obj = self::glf_more_restaurant_data();
            if ( !$restaurant_data_obj || ( is_object( $restaurant_data_obj ) && !isset( $restaurant_data_obj->restaurants ) ) ) {
                return;
            }

            $glf_wordpress_options = self::glf_wp_options_data();
            $current_user_app_options = $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ];

            if( !is_null( $restaurant_data_obj->restaurants ) ){
                $update_location_custom_css = array();
                foreach ( $restaurant_data_obj->restaurants as $restaurant ) {
                    if ( !isset( $current_user_app_options[ 'custom_css_by_location' ][ $restaurant->uid ] ) ) {
                        $custom_css = $default_css;
                    } else {
                        $custom_css = $current_user_app_options[ 'custom_css_by_location' ][ $restaurant->uid ];
                    }
                    $update_location_custom_css[ $restaurant->uid ] = $custom_css;
                }
                $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ 'custom_css_by_location' ] = $update_location_custom_css;
                update_option( 'glf_wordpress_options', $glf_wordpress_options );
            }
        }

		public static function glf_add_to_wordpress_options( $option_name, $option_value ){
            if ( Glf_Utils::$_GLF->is_authenticated() ) {
                $glf_wordpress_options = self::glf_wp_options_data();
                $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ $option_name ] = $option_value;
                update_option( 'glf_wordpress_options', $glf_wordpress_options );
            }
        }
		public static function glf_get_from_wordpress_options( $option_name, $return_value, $update='false' ){
            if ( Glf_Utils::$_GLF->is_authenticated() ) {
                $glf_wordpress_options = self::glf_wp_options_data( $update );
                if( isset( $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ $option_name] ) ){
                    $return_value = $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ][ $option_name ];
                }
            }
            return $return_value;
        }

        /**
         *
         * Check if the user has already added an ordering button for a website domain
         *
         * @param string $current_done
         * @param object $restaurant
         *
         * @return string
         */
		public static function glf_check_done_by_website_url( $current_done, $restaurant ){
            if ( !is_null( $restaurant ) && in_array( get_site_url( null, '/' ), $restaurant->restaurant_urls, true )) {
                $current_done = 'true';
            }
            return $current_done;
        }

		/**
		 *
		 * Get all locations or just one location custom_css
		 *
		 * @param string $location_uid
		 *
		 * @return array|string|null
		 */
		public static function glf_get_locations_custom_css( $location_uid = '' ) {
			$glf_wordpress_options = self::glf_wp_options_data();
			if ( ! $glf_wordpress_options ) {
				return null;
			}
            $location_uid_custom_css = '';
            if ( Glf_Utils::$_GLF->is_authenticated() ) {
                $per_user_app_options = $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ];

                $location_uid_custom_css = ( isset( $per_user_app_options[ 'custom_css_by_location' ] ) ? $per_user_app_options[ 'custom_css_by_location' ] : '' );
                if ( is_array( $location_uid_custom_css ) ) {
                    if ( ! empty( $location_uid ) ) {
                        $location_uid_custom_css = $location_uid_custom_css[ $location_uid ];
                    }
                }
			} else if( isset( $glf_wordpress_options->{'app_options'} ) ){
                $app_options = $glf_wordpress_options->{'app_options'};
                $all_available_custom_css_by_location = array();
                if( is_array( $app_options ) ){
                    foreach ( $app_options as $app_user_key => $user_id_app_options ){
                        foreach ( $user_id_app_options[ 'custom_css_by_location' ] as $location_key => $location_value){
                            $all_available_custom_css_by_location[ $location_key] = $location_value;
                        }
                    }
                }
                $location_uid_custom_css = $all_available_custom_css_by_location;
            } else{
                // user has a lower version than 2.0.0 and was logged out at the time of the update
                // to version 2.0.2 - check for backward compatibility
                $glf_wordpress_options = self::glf_wp_options_data();
                if ( $glf_wordpress_options && isset( $glf_wordpress_options->{'custom_css_by_location'} ) ) {
                    $location_uid_custom_css = $glf_wordpress_options->{'custom_css_by_location'};
                }
            }

			return $location_uid_custom_css;
		}

		public static function get_all_locations_labels( $atts, $custom_css ) {
			$type                     = $atts[ 'type' ];
			$location                 = $atts[ 'ruid' ];
			$all_locations_custom_css = self::glf_get_locations_custom_css();
			if ( is_null( $all_locations_custom_css ) ) {
				self::glf_custom_css_check_and_set_defaults( $custom_css );
				$all_locations_custom_css = self::glf_get_locations_custom_css();
			}

			if ( is_array( $all_locations_custom_css ) ) {
				$labels = array( 'labels' => '', 'css' => '', 'customCSS' => '' );
				foreach ( $all_locations_custom_css as $location_ruid => $locations_custom_css ) {
					$label                 = ( $type === 'ordering' ? 'See MENU & Order' : 'Table Reservation' );
					$labels[ 'customCSS' ] = '';
					if ( isset ( $locations_custom_css[ $type ] ) ) {
						$label                 = $locations_custom_css[ $type ][ 'text' ];
						$labels[ 'customCSS' ] = self::get_custom_css_props_style( $locations_custom_css[ $type ] );
					}
					if ( isset( $atts[ 'class' ] ) ) {
						$labels_html = $label;
					} else {
						$labels_html = '<span class="glf-button-default glf-button ' . $atts[ 'extraCss' ] . '" style=\'' . $labels[ 'customCSS' ] . '\'  data-glf-cuid="" data-glf-ruid="' . $atts[ 'ruid' ] . '" ' . $atts[ 'extraAttr' ] . ' data-location="' . $location_ruid . '">' . $label . '</span>';
					}

					if ( empty( $location ) ) {
						$labels[ 'labels' ] .= $labels_html;
						$labels[ 'css' ]    .= ' .glf-' . $type . '-location' . '[data-location="' . $location_ruid . '"] > span[data-location="' . $location_ruid . '"]{ display:block; }';
					} else {
						if ( $location === $location_ruid ) {
							$labels[ 'labels' ] = $labels_html;
						}
					}
				}
			}

			return $labels;
		}

		/**
		 *
		 * Returns all custom css properties that exist.
		 *
		 * @param string $locations_custom_css
		 */
		public static function get_custom_css_props_style( $locations_custom_css ) {
			$customCSS = '';
			foreach ( $locations_custom_css as $key => $value ) {
				if ( $key !== 'text' && $key !== 'type' ) {
					$customCSS .= $key . ':' . $value . ( $key === 'color' ? ' !important; ' : '; ' );

				}
			}

			return $customCSS;
		}

		/**
		 *
		 * Set all locations or just one location custom_css
		 *
		 * @param array $custom_css
		 * @param string $location_uid
		 */
		public static function glf_set_locations_custom_css( $custom_css, $location_uid = '' ) {
			$glf_wordpress_options = self::glf_wp_options_data();
            $per_user_app_options = $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ];
			if ( is_array( $custom_css ) ) {
				if ( ! empty( $location_uid ) ) {
                    $per_user_app_options[ 'custom_css_by_location' ][ $location_uid ] = $custom_css;
				} else {
                    $per_user_app_options[ 'custom_css_by_location' ] = $custom_css;
				}
			}
            $glf_wordpress_options->{'app_options'}[ self::$_GLF->user->id ] = $per_user_app_options;
			update_option( 'glf_wordpress_options', $glf_wordpress_options );
		}

		public static function glf_mor_get_restaurants() {
			$restaurant_data_obj = self::glf_more_restaurant_data();

			return isset( $restaurant_data_obj->restaurants ) ? $restaurant_data_obj->restaurants : null;
		}

		public static function glf_database_option_operation( $action, $option_name, $value = '' ) {
			$result = '';
			if ( $action === 'get' ) {
				$result = get_option( $option_name, false );
			} else {
				if ( $action === 'delete' ) {
					$result = delete_option( $option_name );
				} else {
					$action        = ( $action === 'update' && ! get_option( $option_name ) ) ? 'add' : $action;
					$function_name = $action . '_option';
					if ( function_exists( $function_name ) ) {
						$result = $function_name( $option_name, $value );
					}
				}
			}

			return $result;
		}

		// Sort restaurants and chains alphabetically by company_name.
		// Sort chain locations by name
		public static function glf_get_sorted_restaurants( $args = '' ) {
			$restaurants = empty( $args ) ? self::glf_get_restaurants() : $args;
            if ( !empty( $restaurants ) ) {
                usort( $restaurants, function ( $x, $y ) {
                    //compare all restaurants by company_name
                    $test = strtolower( $x->company_name ) > strtolower( $y->company_name );

                    //whenever two have the same company_name we sort the locations of that chain by location name
                    if ( strtolower( $x->company_name ) === strtolower( $y->company_name ) ) {
                        $test = strtolower( $x->name ) > strtolower( $y->name );
                    }
                    $test = ($test ? 1 : -1);
                    return $test;
                } );
            }

			return $restaurants;
		}

		// output the select dropdown html for admin and publishing
		public static function glf_get_restaurants_dropdown( $id, $name, $property, $default_value, $onchange ) {
			?>
            <select name="<?php echo $name; ?>" id="<?php echo $id; ?>" onchange="<?php echo $onchange; ?>()">
				<?php
				foreach ( self::glf_get_sorted_restaurants() as $restaurant ) {
					$addChainName = $restaurant->is_chain ? '[' . ucwords( $restaurant->company_name ) . '] ' : '';
					$add_selected = '';
					if ( ! empty( $default_value ) && $default_value === $restaurant->uid ) {
						$add_selected = 'selected';
					}
					?>
                    <option value="<?php echo $restaurant->{$property}; ?>" <?= $add_selected; ?> data-uid="<?php echo $restaurant->uid ?>"><?php echo $addChainName . $restaurant->name; ?></option>
					<?php
				} ?>
            </select>
			<?php
		}

		// output the select dropdown html for admin and publishing
		public static function glf_get_restaurants_dropdown_elementor( $default_value = '' ) {
			$result = array(
				'default' => $default_value,
				'options' => array()
			);
			foreach ( self::glf_get_sorted_restaurants() as $restaurant ) {
				if ( $default_value === '' ) {
					$result[ 'default' ] = $restaurant->uid;
					$default_value       = $restaurant->uid;
				}
				$result[ 'options' ][ $restaurant->uid ] = ( $restaurant->is_chain ? '[' . ucwords( $restaurant->company_name ) . '] ' : '' ) . $restaurant->name;
			}

			return $result;
		}

        public static function group_same_schedule_type( $data ) {
            $grouped_data = array();
            //group by type
            foreach ( $data as $d ) {
                if ( !isset( $grouped_data[ $d->type ] ) ) {
                    $grouped_data[ $d->type ] = array();
                }
                $grouped_data[ $d->type ][] = $d;
            }
            // move Opening as first key
            // for the case when the date received doesn't have it as first key
            if ( isset( $grouped_data[ 'opening' ] ) ) {
                $opening = array( 'opening' => $grouped_data[ 'opening' ] );
                $grouped_data = array_reverse( $grouped_data );
                $grouped_data = array_merge( $opening, $grouped_data );
            }
            return $grouped_data;
        }

        public static function glf_prepare_tracking_data( $added, $atts, $editor_type, $option ){
            $currentOption = self::glf_get_from_wordpress_options( $editor_type . '_' . $option, 'false' );
            self::glf_add_to_wordpress_options( $editor_type . '_' . $option, 'true' );
            self::glf_add_to_wordpress_options( $option, 'true' );

            if ( $currentOption === 'false' ) {
                $data = array(
                    'type' => 'added',
                    'added' => $added, //'ordering', 'reservations', 'menu', 'opening-hours'
                    'editor' => $editor_type, // 'classic', 'gutenberg', 'elementor'
                    'ruid' => $atts[ 'ruid' ]
                );
                self::glf_tracking_send( $data );
            }
        }

        public static function glf_tracking_send( $data ) {
            $consent = self::glf_get_from_wordpress_options( 'share_usage_data', '', 'true' ) === 'yes';
            $type = $data[ 'type' ];
            $args = array( 'event' => '', 'data' => '' );
            $plugin_installation_id = get_option( 'glf_mor_installation_id' );
            if ( $type === 'consent' ) {
                $args[ 'event' ] = 'wp.plugin.tracking_consent_given';
                $args[ 'data' ] = array(
                    'website' => get_site_url(),
                    'source' => $data[ 'source' ],
                    'plugin_installation_id' => $plugin_installation_id
                );
            } else if ( $type === 'options' ) {
                $args[ 'event' ] = 'wp.wizard.options_selected';
                $args[ 'data' ] = array(
                    'options' => $data[ 'options' ],
                    'plugin_installation_id' => $plugin_installation_id
                );
            } else if ( $type === 'todo' ) {
                $restaurant = self::get_restaurant_data_by_location( $data[ 'ruid' ] );
                $acc_id = !empty( $restaurant ) ? $restaurant->account_id : '';

                $args[ 'event' ] = 'wp.overview.clicked_todo';
                $args[ 'account_id' ] = $acc_id;
                $args[ 'data' ] = array(
                    'todo' => $data[ 'todo' ],
                    'plugin_installation_id' => $plugin_installation_id
                );
            } else if ( $type === 'added' ) {
                $restaurant = self::get_restaurant_data_by_location( $data[ 'ruid' ] );
                $acc_id = !empty( $restaurant ) ? $restaurant->account_id : '';

                $args[ 'event' ] = 'wp.widget.added';
                $args[ 'account_id' ] = $acc_id;
                $args[ 'data' ] = array(
                    'editor' => $data[ 'editor' ],
                    'type' => $data[ 'added' ],
                    'plugin_installation_id' => $plugin_installation_id
                );
                if( isset( $data['added']) ){
                    if ( $data[ 'added' ] === 'full-menu' ) {
                        self::glf_add_to_wordpress_options( 'widget_menu', 'true' );
                    } else {
                        self::glf_add_to_wordpress_options( $data[ 'editor' ] . '_' . $data[ 'added' ], 'true' );
                    }
                }
            } else if ( $type === 'banner_review' ) {
                $args[ 'event' ] = $data[ 'event' ];
                $args[ 'data' ] = array(
                    'milestone' => $data[ 'milestone' ]
                );

                if ( $data[ 'event' ] === 'wp.banner_review.cta_clicked' ) {
                    $args[ 'data' ]['cta'] = $data['cta'];
                }
            }
            $response = '';
            if ( !empty( $args[ 'event' ] ) && ( $consent || isset($data['consent']) ) ) {
                $response = self::$_GLF->glf_mor_api_call( "tracking", 'POST', $args );
            }

            return array( 'data' => $response, 'args' => $args );
        }

		public static function var_dump( $value ) {
			echo '<pre style="left: 200px; position: relative;">';
			var_dump( $value );
			echo '</pre>';
		}

	}
}