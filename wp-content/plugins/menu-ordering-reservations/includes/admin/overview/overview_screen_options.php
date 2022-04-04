<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
?>
<?php
// Output screens following a predefined sequence
// This sequence can be altered: modify the order or add other screens
function glf_get_screen_sequence( $action ){

    $screens = array(
        'share_usage_data',
        'setup_options',
        'partner_program',
        'alternative_flow',
    );
    $extra = '';
    $screen_sequence = array();
    $restaurant = Glf_Helper_Screens::get_default_restaurant();

    $share_usage = empty( Glf_Utils::glf_get_from_wordpress_options( 'share_usage_data', '' ) );
    $setup_options = empty( Glf_Utils::glf_get_from_wordpress_options( 'setup_options', '' ) );

    $partner_screen = (isset( $_POST[ 'partner_program' ] ) && $_POST[ 'partner_program' ] === 'true');
    $partner_program = Glf_Utils::glf_get_from_wordpress_options( 'partner_program', '' );
    $partner_program = ( $partner_screen ) ? empty( $partner_program ) : false;

    $overview_redirect_url = admin_url() . 'admin.php?page=glf-overview';


    if( $action === 'update' ){
        $screen_sequence = array(
            'share_usage_data' => array(
                'cssClass' => ' screen-active ',
                'next' => 'overview'
            )
        );

    } else {
        $screen_active = false;
        $screen_alternative = false;

        $setup_options = $action === 'sign_up' ? true : $setup_options;

        if( $share_usage ){
            $screen_sequence [ 'share_usage_data' ] = array(
                'cssClass' => (!$screen_active) ? ' screen-active ' : '',
                'next' => 'screen_options'
            );
            $screen_active = true;
            if( !$setup_options ){
                $screen_sequence [ 'share_usage_data' ]['next'] = 'redirect';
                $screen_sequence [ 'share_usage_data' ]['url'] = $overview_redirect_url;
            }
        }
        if( $setup_options ){
            $screen_sequence [ 'setup_options' ] = array(
                'cssClass' => (!$screen_active) ? ' screen-active ' : '',
                'next' => 'screen_partner_program'
            );
            if( !$screen_active ){
                $extra = 'empty';
            }
            $screen_active = true;
            $setup_restaurant = Glf_Helper_Screens::is_done_setup_restaurant( $restaurant );
            $screen_alternative = true;
            if ( $partner_program && is_null( $restaurant->partner_account_id ) ) {
                $screen_sequence [ 'partner_program' ] = array(
                    'cssClass' => (!$screen_active) ? ' screen-active ' : '',
                    'next' => 'screen_alternative_flow'
                );
                if( $setup_restaurant ){
                    $screen_alternative = false;
                    $screen_sequence [ 'partner_program' ][ 'next' ] = 'redirect';
                    $screen_sequence [ 'partner_program' ][ 'url' ] = $overview_redirect_url;
                }

            }
            else{
                if ( $setup_restaurant ) {
                    $screen_sequence [ 'setup_options' ][ 'next' ] = 'redirect';
                    $screen_sequence [ 'setup_options' ][ 'url' ] = $overview_redirect_url;
                }
                else{
                    $screen_sequence [ 'setup_options' ][ 'next' ] = 'screen_alternative_flow';
                }
            }
        }
        if( $screen_alternative ){
            $screen_sequence [ 'alternative_flow' ] = array(
                'cssClass' => (!$screen_active) ? ' screen-active ' : '',
                'next' => ''
            );
        }
    }

    return array( 'output' => screen_output_sequence( $screen_sequence ), 'extra' => $extra );
}


function screen_output_sequence( $screen_sequence = array() ){
    ob_start();
    if( empty( $screen_sequence ) ){
        return ob_get_clean();
    }
    $glf_wordpress_options = Glf_Utils::glf_wp_options_data();
    foreach ( $screen_sequence as $screen_name => $screen_options){
        $method = 'glf_output_screen_' . $screen_name;
        if( function_exists( $method ) ){
            call_user_func( $method, $screen_options );
        }
    }
    $screens_html = ob_get_clean();
    return $screens_html;
}
?>

<?php
// Screen Share Usage Data
function glf_output_screen_share_usage_data( $screen_options ) {
    ?>
    <div id="screen_data_usage" class="screen <?php echo $screen_options[ 'cssClass' ]; ?> glf-section-overlay">
    <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
        <div class="section-title"><?php _e( 'Help us improve GloriaFood', 'menu-ordering-reservations' ); ?></div>
        <div class="glf-line-spacer"></div>
        <div class="glf-overlay-text">
            <span><?php _e( 'Become a super contributor by opting in to share non-sensitive plugin data.', 'menu-ordering-reservations' ); ?> <a href="https://www.gloriafood.com/privacy" target="_blank"><?php _e( 'Learn more', 'menu-ordering-reservations' ); ?></a></span>
        </div>
        <div class="glf-column glf-col-h-right ">
            <?php
            $next_screen = isset( $screen_options[ 'next' ] ) ? $screen_options[ 'next' ] : '';
            $next_url = isset( $screen_options[ 'url' ] ) ? $screen_options[ 'url' ] : '';
            ?>
            <a href="" class="glf-link glf-button-action" data-id="<?php echo $next_screen; ?>" data-id_url="<?php echo $next_url; ?>" data-value="no" data-action="glf_set_option" data-option="share_usage_data"><?php _e( 'Don’t share', 'menu-ordering-reservations' ); ?></a>
            <a href="" class="glf-button glf-button-action" data-id="<?php echo $next_screen; ?>" data-id_url="<?php echo $next_url; ?>" data-value="yes" data-action="glf_set_option" data-option="share_usage_data"><?php _e( 'Share usage data', 'menu-ordering-reservations' ); ?></a>
        </div>
    </div>
</div>
    <?php
}

?>

<?php
// Screen Setup Options
function glf_output_screen_setup_options( $screen_options ){
?>
<div id="screen_options" class="screen <?php echo $screen_options[ 'cssClass' ]; ?> glf-section glf-w-520 glf-white-back flx-col" style="align-self:center; margin-top: 24px;">
    <div class="section-title"><?php _e( 'Select what options you want to setup', 'menu-ordering-reservations' ); ?></div>
    <div class="section-subtitle"><?php _e( 'Choose the features that you need', 'menu-ordering-reservations' ); ?></div>
    <div class="glf-list flx-col">
         <div class="glf-form-field list flx-row form-footer">
            <?php
            foreach ( Glf_Helper_Screens::get_extra_options() as $extra_option_name => $extra_option ) {
                $display = Glf_Helper_Screens::kickstart_and_autopilot_display_check_setup( $extra_option_name );
                if( $extra_option_name !== 'enroll_partner_program' && $display ){
                    if ( !isset( $glf_wordpress_options->app_options[ $extra_option_name ] ) ) {
                        $preselected = ((isset( $extra_option[ 'preselected' ] ) && $extra_option[ 'preselected' ] === 'true') ? 'checked' : '');
                    } else {
                        $preselected = ($glf_wordpress_options->app_options[ $extra_option_name ] === 'true' ? 'checked' : '');
                    }
                ?>

                <div class="glf-filed-checkbox">
                    <input id="<?php echo $extra_option_name; ?>" name="<?php echo $extra_option_name; ?>" type="checkbox" <?php echo $preselected; ?> data-tracking="<?php echo $extra_option[ 'title' ]; ?>">
                    <label for="<?php echo $extra_option_name; ?>"><?php echo $extra_option[ 'title' ]; ?></label>
                    <?php
                    if ( isset( $extra_option[ 'paid' ] ) && $extra_option[ 'paid' ] === 'true' ) {
                        ?>
                        <div class="glf-tag"><?php _e( 'Premium', 'menu-ordering-reservations' ); ?></div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                }
            }
            ?>
            <div class="glf-filed-submit">
                <!--<input type="submit" data-action="glf_form_chosen_options" value="Continue" class="button">-->
                <?php
                $next_screen = isset( $screen_options[ 'next' ] ) ? $screen_options[ 'next' ] : '';
                $next_url = isset( $screen_options[ 'url' ] ) ? $screen_options[ 'url' ] : '';
                ?>
                <div class="glf-button glf-button-action" data-id="<?php echo $next_screen; ?>" data-id_url="<?php echo $next_url; ?>" data-action="glf_chosen_options"><?php _e( 'Continue', 'menu-ordering-reservations' ); ?></div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<?php
// Screen Partner Program
function glf_output_screen_partner_program( $screen_options ) {
    ?>

    <div id="screen_partner_program" class="screen <?php echo $screen_options[ 'cssClass' ]; ?> glf-section-overlay">
    <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
        <div class="section-title"><?php _e( 'Join Partner Program and earn', 'menu-ordering-reservations' ); ?></div>
        <div class="glf-line-spacer"></div>
        <div class="glf-image">
            <img src="<?= GLF_PLUGIN_URL . 'includes/admin/assets/images/illustration.png' ?>" width="221" height="130px" alt="">
        </div>
        <div class="glf-overlay-text">
            <span><?php _e( 'Restaurant that you create for others can bring you additional revenue if you become a partner.', 'menu-ordering-reservations' ); ?></span>
        </div>
        <div class="glf-overlay-text">
            <span><?php _e( 'Are you interested in being part of the Partner Program?', 'menu-ordering-reservations' ); ?></span>
        </div>
        <div class="glf-column glf-col-h-right glf-col-spacing-top">
            <?php
            $next_screen = isset( $screen_options[ 'next' ] ) ? $screen_options[ 'next' ] : '';
            $next_url = isset( $screen_options[ 'url' ] ) ? $screen_options[ 'url' ] : '';
            ?>
            <a href="" class="glf-link glf-button-action" data-id="<?php echo $next_screen; ?>" data-id_url="<?php echo $next_url; ?>" data-value="no" data-action="glf_set_option" data-option="partner_program"><?php _e( 'Not now', 'menu-ordering-reservations' ); ?></a>
            <a href="" class="glf-button glf-button-action" data-id="<?php echo $next_screen; ?>" data-id_url="<?php echo $next_url; ?>" data-value="yes" data-action="glf_set_option" data-option="partner_program"><?php _e( 'Yes, I\'m interested', 'menu-ordering-reservations' ); ?></a>
        </div>
    </div>
</div>
<?php
}
?>

<?php
// Screen Alternative Flow
function glf_output_screen_alternative_flow( $screen_options ){
?>
<div id="screen_alternative_flow" class="glf-section-content screen <?php echo $screen_options[ 'cssClass' ]; ?> glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
    <div class="section-title"><?php _e( 'Admin setup', 'menu-ordering-reservations' ); ?></div>
    <div class="glf-overlay-text light" style="margin-top: 8px;">
        <span><?php _e( 'You will now need to go to GloriaFood Admin in order to setup your restaurant profile. After finishing the setup you can come back and add the widgets on your website.', 'menu-ordering-reservations' ); ?></span>
    </div>
    <div class="glf-column glf-col-h-center glf-col-spacing-top" style="margin-bottom: 16px;">
        <?php
        $restaurant = Glf_Utils::glf_more_restaurant_data()->restaurants[ 0 ];
        $baseURL = '{{token}}&parent_window=wordpress&r=app.admin_ftu.setup&hide_top_menu=true&hide_left_menu=true&acid={{acc_id}}';
        $baseURL = str_replace(
            array( '{{token}}', '{{acc_id}}' ),
            array( Glf_Utils::$_GLF->get_glf_mor_token(), $restaurant->account_id ),
            $baseURL
        );
        ?>
        <a href="<?= $baseURL; ?>" class="glf-button glf-button-action-redirect" data-overview="<?php echo admin_url() . 'admin.php?page=glf-overview'; ?>" target="_blank"><span class="icon"></span><?php _e( 'Start the setup', 'menu-ordering-reservations' ); ?></a>
    </div>
</div>
<?php
}
?>