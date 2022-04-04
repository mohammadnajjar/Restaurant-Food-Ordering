<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$redirect_screen = '';
if( isset( $_REQUEST[ 'redirect' ] )){
    $redirect_screen = $_REQUEST[ 'redirect' ];
}
?>


<div id="glf-consent" data-consent="<?php echo Glf_Utils::glf_get_from_wordpress_options( 'share_usage_data', '' ) ?>"></div>
<div class="glf-wrapper overview" data-redirect="<?php echo $redirect_screen; ?>">
    <?php
    if(Glf_Utils::$_GLF->is_authenticated() ){
    ?>
    <div class="glf-section flx-col">
            <div class="glf-d-flex glf-white-box publish" style="padding: 12px 24px; width: 90vw; max-width: 100%;">
                <?php
                if ( is_array( Glf_Utils::glf_get_restaurants() ) && count( Glf_Utils::glf_get_restaurants() ) > 1) {
                ?>
                <div class="glf-select-label"><?php _e('Restaurant', 'menu-ordering-reservations'); ?></div>
                <?php
                }
                ?>
                <label for="glf_mor_restaurant">
                    <?php
                    $selected_option = '';
                    $custom_css_location = Glf_Utils::glf_get_from_wordpress_options( 'default_location', '' );

                    if ( isset( $custom_css_location ) ) {
                        $selected_option = $custom_css_location;
                    }
                    if ( is_array( Glf_Utils::glf_get_restaurants() ) && count( Glf_Utils::glf_get_restaurants() ) > 1 ) {
                        Glf_Utils::glf_get_restaurants_dropdown( 'js_glf_mor_ruid', 'ruid', 'uid', $selected_option, 'GLF.core.dropdown_change' );
                    } else {
                        ?>
                        <span class="single-restaurant"><?php echo Glf_Utils::glf_get_restaurants()[ 0 ]->name; ?></span>
                        <input type="hidden" name="ruid" id="js_glf_mor_ruid" value="<?= Glf_Utils::glf_get_restaurants()[ 0 ]->uid; ?>">
                        <?php
                    } ?>
                </label>
                <?php
                if ( is_array( Glf_Utils::glf_get_restaurants() ) && count( Glf_Utils::glf_get_restaurants() ) > 1) {
                ?>
                <span class="icon-info">
                    <span class="info-tooltip"><?php _e( 'You can change the restaurant for which you want to configure', 'menu-ordering-reservations' ); ?></span>
                </span>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="glf-section glf-w-520 glf-white-back flx-col">
        <div class="glf-section-header flx-align-space-between">
            <div class="section-title"><?php _e( 'TO DO', 'menu-ordering-reservations' ); ?></div>
            <?php
            if (Glf_Utils::$_GLF->is_authenticated()){
                $location = Glf_Utils::glf_get_from_wordpress_options( 'default_location', '' );
                $selected_restaurant = null;
                if ( !empty( $location ) ) {
                    $selected_restaurant = Glf_Utils::get_restaurant_data_by_location( $location );
                } else if ( count( Glf_Utils::glf_get_restaurants() ) > 1 ) {
                    $selected_restaurant = Glf_Utils::glf_get_restaurants()[ 0 ];
                }
                $acid = ($selected_restaurant !== null ) ? '&acid=' . $selected_restaurant->account_id : '';
            ?>
            <a href="" data-flow-url="<?= Glf_Utils::$_GLF->get_glf_mor_token() . '&r=app.admin.setup&parent_window=wordpress' . $acid; ?>" class="glf-button h36 glf-button-action glf-gap-right-24" data-id="new_tab_link" data-action="screen_change" target="_parent"><span class="icon-glf"></span><?php _e( 'Open GloriaFood Admin', 'menu-ordering-reservations' ); ?></a>
            <?php
            }
            ?>
        </div>
        <div class="glf-list flx-col">
            <?php
            echo Glf_Helper_Screens::generate_todo_list();
            ?>
        </div>
    </div>
    <div id="screen_alternative_flow" class="screen glf-section-overlay">
        <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
            <div class="section-title"><?php _e( 'Admin setup', 'menu-ordering-reservations' ); ?></div>
            <div class="glf-overlay-text light" style="margin-top: 8px;">
                <span><?php _e( 'You will now need to go to GloriaFood Admin in order to setup your restaurant profile. After finishing the setup you can come back and add the widgets on your website.', 'menu-ordering-reservations' ); ?></span>
            </div>
            <div class="glf-column glf-col-h-center glf-col-spacing-top" style="margin-bottom: 16px;">
                <a href="<?= Glf_Utils::$_GLF->get_glf_mor_token() . '&r=app.admin.setup'; ?>" class="glf-button" target="_blank"><span class="icon"></span><?php _e( 'Start the setup', 'menu-ordering-reservations' ); ?></a>
            </div>
        </div>
    </div>
    <?php
    $is_update = Glf_Utils::glf_get_from_wordpress_options( 'is_update', '', 'true' );
    if ( $is_update && Glf_Utils::$_GLF->is_authenticated() ) {
        Glf_Utils::glf_require_once( __DIR__ . '/overview_screen_options.php' );
        Glf_Utils::glf_add_to_wordpress_options( 'is_update', false );
        $screen_output = glf_get_screen_sequence( 'update' );
        echo $screen_output['output'];
    }
    ?>
</div>

<div id="glf_circular_animation" style="display: none;">
    <div class="glf-loader-animation">
        <div class="loader btn">
            <svg class="circular-loader" viewBox="25 25 50 50">
                <circle class="loader-path" cx="50" cy="50" r="20" fill="none" stroke="#ff9432" stroke-width="2"/>
            </svg>
        </div>
    </div>
</div>