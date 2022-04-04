<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * This class contains utilities helpers for screenns, options and settings
 *
 * @since     1.1.0
 */
if ( !class_exists( 'Glf_Helper_Screens' ) ) {

    class Glf_Helper_Screens {

        const RESTAURANT_TOTAL_CLIENTS = 500;

        public static $_GLF = null;
        public static $_token = null;
        public static $_language = null;

        //_e( 'Connect to GloriaFood Account', 'menu-ordering-reservations' )
        public static $_default_options = array(
            'title' => 'Default Options',
            'todo' => array(
                'connect_account' => array(
                    'title' => 'Connect to GloriaFood Account',
                    'subtitle' => '1 min',
                    'tracking_title' => 'Connect to GloriaFood Account',
                    'url' => '',
                    'done' => 'false',
                    'dismissable' => false,
                    'state' => 'state-disabled'
                ),
                'setup_restaurant' => array(
                    'title' => 'Setup Restaurant Profile',
                    'subtitle' => '5 min',
                    'tracking_title' => 'Setup Restaurant Profile',
                    'url' => '',
                    'done' => 'false',
                    'dismissable' => false,
                    'state' => 'state-disabled',
                    'required' => array(
                        'connect_account' => array(
                            'key' => 'done',
                            'compare' => 'equal',
                            'value' => 'true'
                        )
                    ),
                    'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin_ftu.setup&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                ),
                'setup_menu' => array(
                    'title' => 'Setup Menu',
                    'subtitle' => '2 min per menu item',
                    'tracking_title' => 'Setup Menu',
                    'url' => '',
                    'done' => 'false',
                    'dismissable' => false,
                    'state' => 'state-disabled',
                    'required' => array(
                        'setup_restaurant' => array(
                            'key' => 'done',
                            'compare' => 'equal',
                            'value' => 'true'
                        )
                    ),
                    'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.setup.menu_app.menu_editor&hide_top_menu=true&hide_left_menu=true&hide_left_navigation=true&acid={{acc_id}}'
                )
            )
        );
        public static $_extra_options = array(
            'button_ordering' => array(
                'title' => 'Online Ordering',
                'preselected' => 'true',
                'todo' => array(
                    'button_ordering' => array(
                        'title' => 'Add an ordering button on a page',
                        'subtitle' => '1 min',
                        'tracking_title' => 'Online Ordering',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => false,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        )
                    )
                )
            ),
            'button_reservations' => array(
                'title' => 'Reservations',
                'preselected' => 'true',
                'todo' => array(
                    'button_reservations' => array(
                        'title' => 'Add a reservations button on a page',
                        'subtitle' => '1 min',
                        'tracking_title' => 'Reservations',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                    )
                )
            ),
            'menu_widget' => array(
                'title' => 'Menu Widget',
                'preselected' => 'true',
                'todo' => array(
                    'menu_widget' => array(
                        'title' => 'Add a menu section on a page',
                        'subtitle' => '1 min',
                        'tracking_title' => 'Menu Widget',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        )
                    )
                )
            ),
            'opening_hours_widget' => array(
                'title' => 'Opening Hours Widget',
                'preselected' => 'true',
                'todo' => array(
                    'opening_hours_widget' => array(
                        'title' => 'Add an opening hours section on a page',
                        'subtitle' => '1 min',
                        'tracking_title' => 'Opening Hours Widget',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        )
                    )
                )
            ),
            'kickstart' => array(
                'title' => 'Kick-start ordering',
                'preselected' => 'true',
                'todo' => array(
                    'first_promo' => array(
                        'title' => 'Encourage first order',
                        'subtitle' => '3 min',
                        'tracking_title' => 'Encourage first order',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.kickstarter.encourage.overview&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                    ),
                    'promotion' => array(
                        'title' => 'Advertise with flyers',
                        'subtitle' => '5 min',
                        'tracking_title' => 'Advertise with flyers',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.kickstarter.flyers.overview&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                    ),
                    'invitations' => array(
                        'title' => 'Invite clients to order online',
                        'subtitle' => '7 min',
                        'tracking_title' => 'Invitations',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.kickstarter.invite_clients.overview&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                    )
                )
            ),
            'facebook_installation' => array(
                'title' => 'Facebook Ordering',
                'preselected' => 'true',
                'todo' => array(
                    'facebook_installation' => array(
                        'title' => 'Add Facebook Order Button',
                        'subtitle' => '2 min',
                        'tracking_title' => 'Facebook Installation',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_restaurant' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            ),
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.setup.publishing.facebook_shop_now&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                    )
                )
            ),
            'onlinepayments' => array(
                'title' => 'Online Payments',
                'paid' => 'true',
                'todo' => array(
                    'onlinepayments' => array(
                        'title' => 'Upgrade & Enable online payments',
                        'subtitle' => '10 min',
                        'tracking_title' => 'Online Payments',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'paid' => 'true',
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.setup.online.services.index&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                    )
                )
            ),
            'autopilot' => array(
                'title' => 'Autopilot Marketing Campaign',
                'paid' => 'true',
                'todo' => array(
                    'autopilot' => array(
                        'title' => 'Enable & Configure Autopilot',
                        'subtitle' => '4 min',
                        'tracking_title' => 'Autopilot',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'paid' => 'true',
                        'state' => 'state-disabled',
                        'required' => array(
                            'setup_menu' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => '{{token}}&parent_window=wordpress&r=app.admin.autopilot.autopilot.overview&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}'
                    )
                )
            ),
            'enroll_partner_program' => array(
                'title' => 'I am setting up for somebody else',
                'todo' => array(
                    'enroll_partner_program' => array(
                        'title' => 'Enroll into Partner Program',
                        'subtitle' => '5 min',
                        'url' => '',
                        'done' => 'false',
                        'dismissable' => true,
                        'state' => 'state-disabled',
                        'required' => array(
                            'connect_account' => array(
                                'key' => 'done',
                                'compare' => 'equal',
                                'value' => 'true'
                            )
                        ),
                        'alternative_url' => 'https://www.gloriafood.com/partner-program'
                    )
                )
            )
        );

        public static $demo_page_title ='How to use GloriaFood widgets';

        public static function get_to_do_options() {
            self::$_token = Glf_Utils::$_GLF->get_glf_mor_token();
            self::$_language = self::get_language_code();

            $available_options = self::$_default_options['todo'];

            foreach ( self::$_default_options[ 'todo' ] as $default_todo_key => $default_todo ){
                $available_options[ $default_todo_key ] = $default_todo;
            }

            if ( Glf_Utils::$_GLF->is_authenticated() ) {
                $setup_options = Glf_Utils::glf_get_from_wordpress_options( 'setup_options', array() );
                foreach ( self::$_extra_options as $extra_opt_key => $extra_opt_value  ) {
                    foreach ( $extra_opt_value['todo'] as $_todo_extra_key => $_todo_extra ) {
                        $add_to_available = false;
                        if( $_todo_extra_key === 'enroll_partner_program' ){
                            $partner_program = Glf_Utils::glf_get_from_wordpress_options( 'partner_program', '' );
                            $partner_program_dismiss = Glf_Utils::glf_get_from_wordpress_options( 'partner_program_dismiss', '' );
                            $add_to_available = ($partner_program === 'yes' && $partner_program_dismiss !== 'yes' );
                        }
                        if ( $add_to_available || ( in_array( $_todo_extra_key, $setup_options, true ) || in_array( $extra_opt_key, $setup_options, true) ) ) {
                            $available_options[ $_todo_extra_key ] = $_todo_extra;
                        }
                    }
                }
            }
            return $available_options;
        }
        public static function set_default_setup_options(){
            $default_setup_options = array();

            foreach ( self::$_default_options[ 'todo' ] as $default_todo_key => $default_todo ) {
                $available_options[ $default_todo_key ] = $default_todo;
            }

            if ( Glf_Utils::$_GLF->is_authenticated() ) {
                foreach ( self::$_extra_options as $extra_opt ) {
                    foreach ( $extra_opt[ 'todo' ] as $_todo_extra_key => $_todo_extra ) {
                        if( self::kickstart_and_autopilot_display_check_setup( $_todo_extra_key ) ){
                            $default_setup_options[] = $_todo_extra_key;
                        }
                    }
                }
            }
            Glf_Utils::glf_add_to_wordpress_options( 'setup_options', $default_setup_options );
        }
        public static function get_verified_available_options( $restaurant = null ) {
            $available_options = self::get_to_do_options();
            $restaurant = ($restaurant === null) ? self::get_default_restaurant() : $restaurant;

            foreach ($available_options as $key => &$option){
                $option = self::set_option_state( $option, $available_options, $restaurant );

                if( isset( $option['paid'] ) ){
                    $option = self::check_option_payed_subscription( $key, $option, $restaurant );
                } else {
                    if ( method_exists( Glf_Helper_Screens::class, 'check_option_' . $key ) ) {
                        $option = self::{'check_option_' . $key}( $key, $option, $restaurant );
                    } else {
                        $option[ 'done' ] = Glf_Utils::glf_get_from_wordpress_options( $key, 'false' );
                    }
                }
            }

            return $available_options;
        }

        public static function get_default_restaurant(){
            if ( !Glf_Utils::$_GLF->is_authenticated() ) {
                return null;
            }
            $restaurant = Glf_Utils::glf_more_restaurant_data()->restaurants[ 0 ];
            $glf_wordpress_options = Glf_Utils::glf_wp_options_data();
            if( isset( $glf_wordpress_options->{'app_options'}[ Glf_Utils::$_GLF->user->id ][ 'default_location' ] )  ){
                $location = Glf_Utils::glf_get_from_wordpress_options( 'default_location', '' );
                if( !empty( $location ) ){
                    $restaurant = Glf_Utils::get_restaurant_data_by_location( $location );
                }

            }
            return $restaurant;
        }

        public static function set_option_state( $option, $all_options ){
            $valid = true;
            if( isset( $option[ 'required' ] ) ){
                foreach ( $option[ 'required' ] as $option_name => $condition ){
                    if( isset( $all_options[ $option_name ], $all_options[ $option_name ][ $condition[ 'key' ] ] ) ){
                        $required = $all_options[ $option_name ][ $condition[ 'key' ] ];
                        if( $condition[ 'compare' ] === 'equal' ){
                            if( $required !== $condition[ 'value' ] ){
                                $valid = false;
                            }
                        }

                    }
                }
                if( $valid ){
                    $option[ 'state' ] = 'state-active';
                }
            }
            else{
                $option[ 'state' ] = 'state-active';
            }

            return $option;
        }
        public static function update_option_state( $option ){
            if( $option[ 'done' ] === 'true' ){
                $option[ 'state' ] = 'state-disabled';
            }
            return $option;
        }
        public static function get_extra_options(){
            return self::$_extra_options;
        }
        public static function kickstart_and_autopilot_display_check_setup( $option_name, $_restaurant = null ){
            $valid = true;
            $restaurant = ( is_null( $_restaurant ) ) ? self::get_default_restaurant() : $_restaurant;
            $total_clients =  $restaurant->total_clients;
            if ( $total_clients >= self::RESTAURANT_TOTAL_CLIENTS && ( $option_name === 'kickstart' || $option_name === 'first_promo' || $option_name === 'promotion' || $option_name === 'invitations') ) {
                $valid = false;
            } else if ( $total_clients < self::RESTAURANT_TOTAL_CLIENTS && $option_name === 'autopilot' ) {
                $valid = false;
            }
            return $valid;
        }
        public static function get_todo_options_of_extra_option( $key ){
            return isset( self::$_extra_options[ $key ], self::$_extra_options[ $key ]['todo'] ) ? self::$_extra_options[ $key ][ 'todo' ] : '';
        }

        public static function get_to_do_text_for_translation( $todo_key, $key ){
            $min =  __( ' min', 'menu-ordering-reservations' );
            $min_per = __( ' min per menu item', 'menu-ordering-reservations' );
            if( $todo_key === 'connect_account' ){

                switch ( $key ) {
                    case 'title':
                        return _e( 'Connect to GloriaFood Account', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '1' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'setup_restaurant' ){

                switch ( $key ) {
                    case 'title':
                        return _e( 'Setup Restaurant Profile', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '5' . $min;
                        break;
                    default:
                        return '';
                        break;
                }

            }
            else if( $todo_key === 'setup_menu' ){
                switch ( $key ) {
                    case 'title':
                        return _e( 'Setup Menu', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '2' . $min_per;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'button_ordering' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Online Ordering', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Add an ordering button on a page', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '1' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'button_reservations' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Reservations', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Add a reservations button on a page', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '1' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'menu_widget' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Menu Widget', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Add a menu section on a page', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '1' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'opening_hours_widget' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Opening Hours Widget', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Add an opening hours section on a page', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '1' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'kickstart' ){
                switch ( $key ) {
                    case 'title':
                        return _e( 'Kick-start ordering', 'menu-ordering-reservations' );
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'first_promo' ) {
                switch ( $key ) {
                    case 'title':
                        return _e( 'Encourage first order', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '3' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'promotion' ){
                switch ( $key ) {
                    case 'title':
                        return _e( 'Advertise with flyers', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '5' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'invitations' ){
                switch ( $key ) {
                    case 'title':
                        return _e( 'Invite clients to order online', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '7' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'facebook_installation' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Facebook Ordering', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Add Facebook Order Button', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '2' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'onlinepayments' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Online Payments', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Upgrade & Enable online payments', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '10' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'autopilot' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'Autopilot Marketing Campaign', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Enable & Configure Autopilot', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '4' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }
            else if( $todo_key === 'enroll_partner_program' ){
                switch ( $key ) {
                    case 'label':
                        return _e( 'I am setting up for somebody else', 'menu-ordering-reservations' );
                        break;
                    case 'title':
                        return _e( 'Enroll into Partner Program', 'menu-ordering-reservations' );
                        break;
                    case 'sub':
                        return '5' . $min;
                        break;
                    default:
                        return '';
                        break;
                }
            }

            return '';
        }

        public static function generate_todo_list( $restaurant = null ){
            ob_start();
            $todo_options = Glf_Helper_Screens::get_verified_available_options( $restaurant );
            $previous_completed = 'false';
            foreach ($todo_options as $todo_key =>  $todo){
                if ( isset( $todo[ 'custom_link' ] ) ) {
                    echo $todo[ 'custom_link' ];
                } else{
                    echo $todo[ 'url' ];
                }

            ?>
                <span class="icon"></span>
                <span class="desc flx-col">
                    <span class="title"><?php echo self::get_to_do_text_for_translation( $todo_key, 'title' ); ?></span>
                    <span class="sub"><?php echo self::get_to_do_text_for_translation( $todo_key, 'sub' ); ?></span>
                </span>
                <span class="flx-right">
                    <?php
                    if ( $todo[ 'dismissable' ] ) {
                        ?>
                        <span class="dismiss glf-button-action" data-id="<?php echo $todo_key; ?>" data-action="option_dismiss"><?php _e( 'Dismiss', 'menu-ordering-reservations' ); ?></span>
                        <?php
                    }
                    ?>
                    <?php
                    if ( $todo_key !== 'connect_account' && $previous_completed !== 'true' ) {
                        ?>
                        <span class="icon-info">
                            <span class="info-tooltip"><?php _e( 'You have to complete the previous step before doing this one.', 'menu-ordering-reservations' ); ?></span>
                        </span>
                        <?php
                    }
                    ?>
                </span>
            </a>
            <?php
                $previous_completed = $todo[ 'done' ];
            }
            return ob_get_clean();
        }


        public static function create_demo_page(){
            if( get_page_by_title( self::$demo_page_title ) === NULL ){
                $page_content = '<!-- wp:heading -->';
                $page_content .= '<h2>' . __( 'How to use GloriaFood widgets', 'menu-ordering-reservations' ) . '</h2>';
                $page_content .= '<!-- /wp:heading -->';

                $page_content .= '<!-- wp:heading {"level":3} -->';
                $page_content .= '<h3>' . __( 'Let\'s start by adding the Ordering button', 'menu-ordering-reservations' ) . '</h3>';
                $page_content .= '<!-- /wp:heading -->';

                $page_content .= '<!-- wp:paragraph -->';
                $page_content .= '<p></p>';
                $page_content .= '<!-- /wp:paragraph -->';

                $page_content .= '<!-- wp:heading {"level":3} -->';
                $page_content .= '<h3>' . __( 'Now let\'s add a Reservation button', 'menu-ordering-reservations' ) . '</h3>';
                $page_content .= '<!-- /wp:heading -->';

                $page_content .= '<!-- wp:paragraph -->';
                $page_content .= '<p></p>';
                $page_content .= '<!-- /wp:paragraph -->';

                $page_content .= '<!-- wp:heading {"level":3} -->';
                $page_content .= '<h3>' . __( 'Now let\'s add the Menu widget', 'menu-ordering-reservations' ) . '</h3>';
                $page_content .= '<!-- /wp:heading -->';

                $page_content .= '<!-- wp:paragraph -->';
                $page_content .= '<p></p>';
                $page_content .= '<!-- /wp:paragraph -->';

                $page_content .= '<!-- wp:heading {"level":3} -->';
                $page_content .= '<h3>' . __( 'Finally let\'s add the Opening Hours widget', 'menu-ordering-reservations' ) . '</h3>';
                $page_content .= '<!-- /wp:heading -->';

                $page_content .= '<!-- wp:paragraph -->';
                $page_content .= '<p></p>';
                $page_content .= '<!-- /wp:paragraph -->';

                $demo_page_details = array(
                    'post_title' => self::$demo_page_title,
                    'post_content' => $page_content,
                    'post_name' => 'how-to-use-gloriafood-widgets',
                    'post_status' => 'draft',
                    'post_author' => 1,
                    'post_type' => 'page'
                );
                wp_insert_post( $demo_page_details );
            }
        }

        public static function get_demo_page_edit_url(){
            $demo_page = get_page_by_title( self::$demo_page_title );
            return is_null( $demo_page ) ? '#' : admin_url() . 'post.php?post=' . $demo_page->ID . '&action=edit';
        }

        public static function check_option_connect_account( $key, $option, $restaurant ){
            $option[ 'done' ] = Glf_Utils::$_GLF->is_authenticated() ? 'true' : 'false';

            $option = self::update_option_state( $option );

            $option[ 'url' ] = '<a id="' . $key . '" href="' . ( !Glf_Utils::$_GLF->is_authenticated() ? admin_url() . 'admin.php?page=glf-screens' : '#' ) . '" ';
            $option[ 'url' ] .= 'class="glf-list-item  glf-button-action ' . $option[ 'state' ] . '" data-action="glf_load_screen" data-screen="overview_authenticate" ';
            $option[ 'url' ] .= 'data-completed="' . $option[ 'done' ] . '"';
            if ( isset( $option[ 'tracking_title' ] ) ) {
                $option[ 'url' ] .= ' data-title="' . $option[ 'tracking_title' ] . '"';
            }
            $option[ 'url' ] .= '>';

            return $option;
        }
        public static function check_option_setup_restaurant( $key, $option, $restaurant ){
            if( isset( $restaurant->completed_screens_admin ) ){
                $option[ 'done' ] = self::is_done_setup_restaurant( $restaurant ) ? 'true' : 'false';
            }

            $option = self::update_option_state( $option );
            return self::set_alternative_flow_url( $key, $option, $restaurant );
        }

        public static function is_done_setup_restaurant( $restaurant = null ){
            $completed_screens = array(
                'app.admin.setup.basics.address_form',
                'app.admin.setup.basics.address_location',
                'app.admin.setup.basics.cuisines',
                'app.admin.setup.basics.email',
                'app.admin.setup.basics.restaurant_website',
                'app.admin.setup.hours.delivery',
                'app.admin.setup.hours.open_hours',
                'app.admin.setup.hours.dine_in',
                'app.admin.setup.hours.order_later',
                'app.admin.setup.hours.pickup',
                'app.admin.setup.hours.table_reservation',
                'app.admin.setup.menu_app.alert_call',
                'app.admin.setup.menu_app.app_installation',
                'app.admin.setup.taxes.payment',
                'app.admin.setup.taxes.tax',
                'app.admin.setup.taxes.terms'
            );
            if( $restaurant === null ){
                return false;
            }
            return ( empty( array_diff( $completed_screens, $restaurant->completed_screens_admin ) ) || $restaurant->setup_completed_admin );
        }

        public static function check_option_setup_menu( $key, $option, $restaurant ){
            $completed_screens = array(
                'app.admin.setup.menu_app.menu_editor'
            );
            $restaurant_completed_screens_admin  = ( empty( $restaurant->completed_screens_admin) ? array() : $restaurant->completed_screens_admin );
            if ( $restaurant !== null ) {
                $option[ 'done' ] = ( empty( array_diff( $completed_screens, $restaurant_completed_screens_admin ) ) || $restaurant->setup_completed_admin )? 'true' : 'false';
            }

            $option = self::update_option_state( $option );

            return self::set_alternative_flow_url( $key, $option, $restaurant );
        }

        /* Button Ordering App Option */
        public static function check_option_button_ordering( $key, $option, $restaurant ){
            return self::demo_page_option( $key,$option);
        }

        /* Button Reservations App Option */
        public static function check_option_button_reservations( $key, $option, $restaurant ){
            return self::demo_page_option( $key, $option, $restaurant );
        }

        /* Widget Menu App Option */
        public static function check_option_menu_widget( $key, $option, $restaurant ){
            return self::demo_page_option( $key, $option );
        }

        /* Widget Opening Hours App Option */
        public static function check_option_opening_hours_widget( $key, $option, $restaurant ){
            return self::demo_page_option( $key, $option );
        }

        public static function demo_page_option( $key, $option, $restaurant = null ){
            $demo_page = get_page_by_title( self::$demo_page_title );
            $button_action = is_null( $demo_page ) ? ' glf-button-action-demo-page ' : ' glf-button-action ';
            $option[ 'done' ] = Glf_Utils::glf_get_from_wordpress_options( $key, 'false' );
            $option[ 'done' ] = Glf_Utils::glf_check_done_by_website_url( $option[ 'done' ], $restaurant );

            $option = self::update_option_state( $option );
            $option[ 'url' ] = '<a href="' . ( Glf_Utils::$_GLF->is_authenticated() ? self::get_demo_page_edit_url() : '#' ) . '" class="glf-list-item ' . $button_action . $option[ 'state' ] . '" target = "_blank" data-completed="' . $option[ 'done' ] . '"';
            if ( isset( $option[ 'tracking_title' ] ) ) {
                $option[ 'url' ] .= ' data-title="' . $option[ 'tracking_title' ] . '"';
            }
            $option[ 'url' ] .= '>';
            return $option;
        }

        public static function check_option_facebook_installation( $key, $option, $restaurant ){
            return self::check_completed_screens( $key, $option, $restaurant );
        }

        /* Kick-start Ordering --- first_promo */
        public static function check_option_first_promo( $key, $option, $restaurant ){
            return self::check_completed_screens( $key, $option, $restaurant );
        }

        /* Kick-start Ordering --- promotion */
        public static function check_option_promotion( $key, $option, $restaurant ){
            return self::check_completed_screens( $key, $option, $restaurant );
        }

        /* Kick-start Ordering --- invitations */
        public static function check_option_invitations( $key, $option, $restaurant ){
            return self::check_completed_screens( $key, $option, $restaurant );
        }

        public static function check_completed_screens( $key, $option, $restaurant ){
            $completed_screens = array(
                'first_promo' => array( 'app.admin.kickstarter.encourage.first_promo' ),
                'promotion' => array( 'app.admin.kickstarter.flyers.promotion' ),
                'invitations' => array( 'app.admin.kickstarter.invite_clients.invitations' ),
                'facebook_installation' => array( 'app.admin.setup.publishing.facebook_shop_now' )
            );
            if ( isset( $completed_screens[ $key ] ) ) {
                $option[ 'done' ] = empty( array_diff( $completed_screens[$key], $restaurant->completed_screens_admin ) ) ? 'true' : 'false';
            }

            return self::set_alternative_flow_url( $key, $option, $restaurant );
        }

        /*
         * Payed Subscription Options Check
         *
         * - ONLINE PAYMENTS
         * - AUTOPILOT
         */
        public static function check_option_payed_subscription( $subscription_name, &$option, $restaurant ){
            $option[ 'done' ] = empty( array_diff( array( strtoupper( $subscription_name ) ), $restaurant->payed_subscriptions->active ) ) ? 'true' : 'false';

            return self::set_alternative_flow_url( $subscription_name, $option, $restaurant );
        }

        public static function check_option_enroll_partner_program( $key, $option ) {
            $partner_program_clicked = Glf_Utils::glf_get_from_wordpress_options( 'partner_program_clicked', '' );
            $option[ 'done' ] = $partner_program_clicked === 'yes' ? 'true' : 'false';
            $option[ 'url' ] = '<a id="' . $key . '"href = "#" class="glf-list-item glf-button-action ' . $option[ 'state' ] . '" data-screen="overview" data-id="new_tab_link" data-action="glf_set_option" data-option="partner_program_clicked" data-value="yes" target="_blank" data-completed="' . $option[ 'done' ] . '"' . ' data-flow-url="' . $option[ 'alternative_url' ] . '" > ';
            return $option;
        }

        public static function set_alternative_flow_url( $key, $option, $restaurant ){
            $baseURL = $option[ 'alternative_url' ];
            $hiddenOption = '';
            if ( Glf_Utils::$_GLF->is_authenticated() ) {
                $baseURL = str_replace(
                    array( '{{token}}', '{{acc_id}}' ),
                    array( self::$_token, $restaurant->account_id ),
                    $baseURL
                );
                $hiddenOption = (!self::kickstart_and_autopilot_display_check_setup( $key, $restaurant )) ? 'hidden-option' : '';
            }

            $option[ 'url' ] = '<a id="' . $key . '" href = "#" class="glf-list-item glf-button-action ' . $option[ 'state' ] . ' ' . $hiddenOption . '" data-id="new_tab_link" data-action="screen_change" target="_parent" ';
            $option[ 'url' ] .= ' data-flow-url="' . $baseURL . '"';
            if( isset( $option['tracking_title'] ) ){
                $option[ 'url' ] .= ' data-title="' . $option[ 'tracking_title' ] . '"';
            }
            $option[ 'url' ] .= 'data-completed="' . $option[ 'done' ] . '">';
            return $option;
        }

        public static function get_language_code() {
            return explode( '_', get_locale() )[ 0 ];
        }
    }
}