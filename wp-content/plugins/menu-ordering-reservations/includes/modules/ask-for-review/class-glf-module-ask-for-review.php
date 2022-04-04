<?php
/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Glf_Module_Ask_For_Review' ) ) {

	/**
	 * GloriaFood Admin Notice Ask For Review Class
	 *
	 * @since 1.0.0
	 */
	class Glf_Module_Ask_For_Review {

		const DEBUG = false;
		const QA_TESTING = false;

		const ORDERS_MILESTONE_1 = 10;
		const ORDERS_MILESTONE_2 = 100;
		const ORDERS_MILESTONE_3 = 500;
		const CONNECTIVITY_LAST7_DAYS = 0.7;
		const MIN_REVIEW_DAYS = 30;
		const MAX_REVIEW_DAYS = 90;

		const DB_OPTION_ASK_FOR_REVIEW = 'glf_option_ask_for_review';

		private $restaurants = null;
		private $default_option_ask_for_review = array(
			'banner_review_requested_event' => true,
			'already_done_review' => false,
			'last_review_check'   => '',
			'30_days_review'      => false,
			'qa_testing'          => '',
		);
		public $db_option_ask_for_review = null;
		public $current_banner_milestone = 0;

		public function __construct() {
			if ( wp_doing_ajax() ) {
				$this->glf_register_ajax_calls();
				return;
			} else {
				if ( is_admin() ) {
					add_action( 'admin_notices', array( $this, 'glf_admin_notice_init' ) );
				}
			}
		}

		public function glf_admin_notice_init() {
            $account_setup_options_done = Glf_Utils::glf_get_from_wordpress_options( 'setup_options', '' );
			if ( empty( $account_setup_options_done ) ) {
				return;
			}

            Glf_Utils::$_GLF->update_restaurants();
            $restaurants_data = Glf_Utils::glf_more_restaurant_data();
            if ( is_object( $restaurants_data ) && !empty( $restaurants_data ) ) {
                $this->restaurants = $restaurants_data->restaurants;
                $this->glf_register_actions();
                $this->glf_get_database_option();
                if ( !$this->db_option_ask_for_review[ 'already_done_review' ] ) {
                    $this->glf_ask_for_review();
                }
            }
		}

		private function glf_register_actions() {
			add_action( 'ask_for_review_message', array( $this, 'ask_for_review_message' ), 10, 2 );
		}

		private function glf_register_ajax_calls() {
			add_action( 'wp_ajax_glf_action_ask_for_review_user_response', array( $this, 'glf_action_ask_for_review_user_response' ) );
			add_action( 'wp_ajax_glf_action_ask_for_review_cta_clicked', array( $this, 'glf_action_ask_for_review_cta_clicked' ) );
		}

		public function glf_action_ask_for_review_user_response() {
			$this->glf_get_database_option();
			$this->db_option_ask_for_review[ 'already_done_review' ] = true;
			Glf_Utils::glf_database_option_operation( 'update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review );
			echo json_encode( array( 'message' => 'done' ) );
			exit;
		}
		public function glf_action_ask_for_review_cta_clicked() {
			$this->glf_get_database_option();
            $this->db_option_ask_for_review[ 'banner_review_requested_event' ] = true;
            Glf_Utils::glf_database_option_operation( 'update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review );
			echo json_encode( array( 'message' => 'done', 'banner_review_requested_event' => $this->db_option_ask_for_review[ 'banner_review_requested_event' ] ) );
			exit;
		}

		private function glf_get_database_option() {
			//Glf_Utils::glf_database_option_operation( 'delete', self::DB_OPTION_ASK_FOR_REVIEW );
			$db_option = Glf_Utils::glf_database_option_operation( 'get', self::DB_OPTION_ASK_FOR_REVIEW, '' );
			if ( empty( $db_option ) ) {
				Glf_Utils::glf_database_option_operation( 'add', self::DB_OPTION_ASK_FOR_REVIEW, $this->default_option_ask_for_review );
				$db_option = Glf_Utils::glf_database_option_operation( 'get', self::DB_OPTION_ASK_FOR_REVIEW, '' );

			}
			$this->db_option_ask_for_review = $db_option;
		}

		/**
		 *
		 * Checking to see if conditions are met to display the
		 * 'ask for review' notice
		 *
		 */
		private function glf_ask_for_review() {

			$this->debug_and_qa_testing();

			$this->current_banner_milestone = ( self::QA_TESTING ) ? 0 : $this->glf_get_message_number();
			if ( $this->current_banner_milestone !== 0 ) {
				Glf_Utils::glf_database_option_operation( 'update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review );
				$this->glf_ask_for_review_render_notice( $this->current_banner_milestone );
			}
		}

		/**
		 *
		 * Get the message number
		 * @return int
		 */
		private function glf_get_message_number() {
			$notice_number          = 0;
			$days_since_last_review = $this->get_days_since_last_review();

			if ( $this->get_restaurant_property_max_value( $this->restaurants, 'last7_connectivity' ) >= self::CONNECTIVITY_LAST7_DAYS ) {
			    $last28_orders = $this->get_restaurant_property_max_value( $this->restaurants, 'last28_orders' );
				if (
                    $last28_orders >= self::ORDERS_MILESTONE_1 &&
                    $last28_orders < self::ORDERS_MILESTONE_2 &&
					empty( $this->db_option_ask_for_review[ 'last_review_check' ] )
				) {
					$notice_number                                         = 1;
					$this->db_option_ask_for_review[ 'last_review_check' ] = date( 'd-m-Y' );
				} else {
				    $max_total_orders = $this->get_restaurant_property_max_value( $this->restaurants, 'total_orders' );
					if (
                        $max_total_orders >= self::ORDERS_MILESTONE_2 &&
                        $max_total_orders < self::ORDERS_MILESTONE_3 &&
						$days_since_last_review >= self::MIN_REVIEW_DAYS &&
						! $this->db_option_ask_for_review[ '30_days_review' ]
					) {
						$notice_number                                         = 2;
						$this->db_option_ask_for_review[ '30_days_review' ]    = true;
						$this->db_option_ask_for_review[ 'last_review_check' ] = date( 'd-m-Y' );
					} else {
						if (
                            $max_total_orders >= self::ORDERS_MILESTONE_3 &&
							$days_since_last_review >= self::MAX_REVIEW_DAYS
						) {
							$notice_number                                         = 3;
							$this->db_option_ask_for_review[ 'last_review_check' ] = date( 'd-m-Y' );
						}
					}
				}
			}

			return $notice_number;
		}

        /**
         *
         * Get the maximum value available, from all the user restaurants available,
         * for a certain 'x' property
         *
         */
        private function get_restaurant_property_max_value( $restaurants_list, $property ) {
            $max = 0;
            if( !is_array( $restaurants_list ) || empty( $restaurants_list ) ){
                return $max;
            }
            if ( version_compare( PHP_VERSION, '7.0.0' ) >= 0 ) {
                $max = max( array_column( $restaurants_list, $property ) );
            } else {
                foreach ( $restaurants_list as $restaurant ) {
                    $prop_value = $restaurant->{$property};
                    $prop_value = ( is_null( $prop_value ) ? 0 : $prop_value );
                    $max = ( $max > $prop_value ? $max : $prop_value );
                }
            }

            return $max;
        }

		/**
		 *
		 * Method used for DEBUG and QA_TESTING purposes
		 *
		 */
		private function debug_and_qa_testing() {
			if ( self::DEBUG ) {
				var_dump( 'Glf_Module_Ask_For_Review' );
				var_dump( 'Maximum of all restaurants [last28_orders]=' . $this->get_restaurant_property_max_value( $this->restaurants, 'last28_orders' ) );
				var_dump( 'Maximum of all restaurants [total_orders]=' . $this->get_restaurant_property_max_value( $this->restaurants, 'total_orders' ) );
				var_dump( 'Maximum of all restaurants [last7_connectivity]=' . $this->get_restaurant_property_max_value( $this->restaurants, 'last7_connectivity' ) );
				var_dump( $this->db_option_ask_for_review );
			}

			if ( self::QA_TESTING ) {
				if ( empty( $this->db_option_ask_for_review[ 'qa_testing' ] ) ) {
					$messageNumber = 1;
				} else {
					$messageNumber = $this->db_option_ask_for_review[ 'qa_testing' ] + 1;
				}
				$this->db_option_ask_for_review[ 'qa_testing' ] = $messageNumber;

				if ( $messageNumber > 3 ) {
					$messageNumber = 3;
				}
				$this->glf_ask_for_review_render_notice( $messageNumber );
				Glf_Utils::glf_database_option_operation( 'update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review );

				return;
			}
		}

		/**
		 *
		 * Get the number of days that have passsed since last review
		 *
		 * @param int $result
		 */
		private function get_days_since_last_review() {
			$last_date    = $this->db_option_ask_for_review[ 'last_review_check' ];
			$current_date = date( 'd-m-Y' );
			$result       = strtotime( $current_date ) - strtotime( $last_date );

			return round( $result / 86400 );
		}

		/**
		 *
		 * Display the Ask For Review notice
		 *
		 * @param int $notice_message
		 */
		private function glf_ask_for_review_render_notice( $message_number ) {
		    $reviewRequested = !isset( $this->db_option_ask_for_review[ 'banner_review_requested_event' ] ) || $this->db_option_ask_for_review[ 'banner_review_requested_event' ];
            if( $reviewRequested ){
                $data = array(
                    'type' => 'banner_review',
                    'event' => 'wp.banner_review.requested',
                    'milestone' => $this->current_banner_milestone
                );
                Glf_Utils::glf_tracking_send( $data );
                $this->db_option_ask_for_review[ 'banner_review_requested_event' ] = false;
                Glf_Utils::glf_database_option_operation( 'update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review );
            }


			?>
            <div class="glf-notice-wrapper notice notice-info is-dismissible">
                <div class="glf-notice-header"><span class="logo"></span><h2>GloriaFood - Menu - Ordering - Reservations</h2></div>
                <?php do_action( 'ask_for_review_message', $message_number, $this->restaurants ); ?>
                <div class="glf-notice-actions">
                    <a href="https://wordpress.org/plugins/menu-ordering-reservations/#reviews" class="glf-link-review button-primary" target="_blank" rel="noopener noreferrer"><?php _e( 'Sure thing', 'menu-ordering-reservations' ); ?></a>
                    <p class="glf-notice-dismiss" data-type="later"><?php _e( 'Maybe later', 'menu-ordering-reservations' ); ?></p>
                <p class="glf-notice-dismiss" data-type="done"><?php _e( 'I already did', 'menu-ordering-reservations' ); ?></p>
                </div>
                <script type="application/javascript">
                    document.addEventListener( "DOMContentLoaded", function ( event ) {
                        if( document.readyState === 'interactive' ) {
                            let noticeDismiss = jQuery( document ).find( '.glf-notice-dismiss' ),
                                glfNoticeWrapper = jQuery( document ).find( '.glf-notice-wrapper' ),
                                glfLinkReview = jQuery( document ).find( '.glf-link-review' ),
                                milestoneNumber = '<?php echo $this->current_banner_milestone; ?>',
                                glf_ajaxRequest = '';

                            glfLinkReview.off('click');
                            glfLinkReview.on( 'click', function ( e ) {
                                e.preventDefault();
                                let _link = jQuery(this),
                                    trackData = '';
                                trackData = {
                                    type: 'banner_review',
                                    event: 'wp.banner_review.cta_clicked',
                                    cta: 'review-start',
                                    milestone: milestoneNumber
                                };
                                glfNoticeWrapper.off( 'click' );
                                glf_triggerCloseNotification();

                                glf_ajax_ask_for_reviews( { action: 'glf_action_ask_for_review_user_response' } );
                                GLF.core.glf_send_tracking( trackData );

                                setTimeout( function () {
                                    window.open( _link.attr( 'href' ), "_blank" );
                                }, 100 );
                            } );
                            /* catch when the user clicks the wp dismiss notification button */
                            glfNoticeWrapper.off('click');
                            glfNoticeWrapper.on( 'click', function(e){
                                let  targetCheck = jQuery( e.target ),
                                    trackData = '';
                                if( targetCheck.hasClass( 'notice-dismiss' ) ){
                                    trackData = {
                                        type: 'banner_review',
                                        event: 'wp.banner_review.cta_clicked',
                                        cta: 'dismissed',
                                        milestone: milestoneNumber
                                    };
                                    glf_ajax_ask_for_reviews( { action: 'glf_action_ask_for_review_cta_clicked' } );
                                }
                                if( trackData !== '' ){
                                    GLF.core.glf_send_tracking( trackData );
                                }
                            } );

                            noticeDismiss.on( 'click', function ( e ) {
                                e.preventDefault();
                                noticeDismiss.off( 'click' );
                                let type = jQuery( this ).attr( 'data-type' ),
                                    trackData;
                                if( milestoneNumber !== '0' ){
                                    trackData = {
                                        type: 'banner_review',
                                        event: 'wp.banner_review.cta_clicked',
                                        cta: ( type === 'done' ? 'review-done' : 'later' ),
                                        milestone: milestoneNumber
                                    };
                                    GLF.core.glf_send_tracking( trackData );
                                }

                                if( type === 'done' ) {
                                    glf_ajax_ask_for_reviews( { action: 'glf_action_ask_for_review_user_response' } );
                                } else {
                                    glf_ajax_ask_for_reviews( { action: 'glf_action_ask_for_review_cta_clicked' } );
                                }
                                glfNoticeWrapper.off( 'click' );
                                glf_triggerCloseNotification();
                            } );

                            function glf_ajax_ask_for_reviews( data ) {
                                if( typeof window.ajaxurl !== 'undefined' ) {
                                    glf_ajaxRequest = jQuery.ajax( {
                                        url: window.ajaxurl,
                                        type: "POST",
                                        data: data,
                                        dataType: "json",
                                        success: function ( data ) {
                                        },
                                        error: function ( xhr, status, error ) {
                                            console.log( 'Status[' + status + '] Error[' + error + ']' );
                                        }
                                    } );
                                }
                            }
                        }
                    } );

                    function glf_triggerCloseNotification(){
                        let buttonDismiss = jQuery( document ).find( '.glf-notice-wrapper button.notice-dismiss' );
                        if( buttonDismiss.length > 0 ) {
                            buttonDismiss.trigger( 'click' );
                        }
                    }
                </script>
                <style>
                    .glf-notice-wrapper {
                        display: flex;
                        flex-direction: column;
                        align-items: flex-start;
                        padding-left: 72px;
                        padding-top: 20px;
                        padding-bottom: 20px;
                    }

                    .glf-notice-wrapper.notice-info {
                        border-left-color: #3E4957;
                    }

                    .glf-notice-wrapper:before{
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 50px;
                        height: 100%;
                        content: "";
                        display: block;
                        background-color: #3E4957;
                        opacity: 0.08;
                    }

                    .glf-notice-dismiss {
                        cursor: pointer;
                    }

                    .glf-notice-dismiss:hover {
                        text-decoration: underline;
                        color: #0073aa;
                    }
                    .glf-notice-header .logo{
                        position: absolute;
                        top: 20px;
                        left: 0px;
                        width: 50px;
                        height: 20px;
                        background-repeat: no-repeat;
                        background-size: contain;
                        background-position: center;
                        background-image: url('<?php echo GLF_PLUGIN_URL . 'assets/images/GF-icon-black2.svg'; ?>');
                    }
                    .glf-notice-header h2 {
                        margin: 0;
                        font-size: 20px;
                        line-height: 28px;
                        font-weight: 300;
                        color: #1D2327;
                    }

                    .glf-notice-wrapper h4{
                        margin-top: 8px;
                        margin-bottom: 0;
                        color: #23282D;
                        font-size: 13px;
                    }
                    .glf-notice-wrapper > p{
                        margin: 0;
                        padding: 0;
                    }
                    .glf-notice-actions{
                        position: relative;
                        display: flex;
                        align-items: center;
                        flex-direction: row;
                        margin-top: 12px;
                    }

                    .glf-notice-actions p{
                        color: #0073aa;
                        margin: 0 0 0px 20px;
                        padding: 0;
                    }
                </style>
            </div>
			<?php
		}

		public function ask_for_review_message( $message_number, $restaurants ) {

			$messages = array(
				"1" => array(
					"title"   => "Nicely done! You received " . $this->get_restaurant_property_max_value( $restaurants, 'last28_orders' ) . " orders in the last 30 days! You're off to a great start.",
					"message" => "If you have a moment, we would really appreciate if you could support our plugin by giving us a 5-star rating on WP, so we can continue releasing new updates.",
				),
				"2" => array(
					"title"   => "Hooray! You've received more than 100 orders! That's a great milestone!",
					"message" => "Since the plugin has proved useful to you, would you mind helping us spread the word about it and giving us a 5-star rating? We would greatly appreciate it.",
				),
				"3" => array(
					"title"   => "Wow! You've received " . $this->get_restaurant_property_max_value( $restaurants, 'total_orders' ) . " orders! That's a remarkable achievement!",
					"message" => "If you have a moment, we would really appreciate if you could support our plugin by giving us a 5-star rating on WP.",
				),
			);
			?>
            <h4><?php echo _e( $messages[ $message_number ][ 'title' ], "menu-ordering-reservations" ); ?></h4><p><?php echo _e( $messages[ $message_number ][ 'message' ], "menu-ordering-reservations" ); ?></p>
			<?php
		}

	}

	new Glf_Module_Ask_For_Review();

}